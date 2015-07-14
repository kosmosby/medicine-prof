<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */



defined('JPATH_PLATFORM') or die;


class JImage
{
	
	const SCALE_FILL = 1;

	
	const SCALE_INSIDE = 2;

	
	const SCALE_OUTSIDE = 3;

	
	const CROP = 4;

	
	const CROP_RESIZE = 5;

	
	const SCALE_FIT = 6;

	
	protected $handle;

	
	protected $path = null;

	
	protected static $formats = array();

	
	public function __construct($source = null)
	{
		
		if (!extension_loaded('gd'))
		{
			
			JLog::add('The GD extension for PHP is not available.', JLog::ERROR);
			throw new RuntimeException('The GD extension for PHP is not available.');

			
		}

		
		if (!isset(self::$formats[IMAGETYPE_JPEG]))
		{
			$info = gd_info();
			self::$formats[IMAGETYPE_JPEG] = ($info['JPEG Support']) ? true : false;
			self::$formats[IMAGETYPE_PNG] = ($info['PNG Support']) ? true : false;
			self::$formats[IMAGETYPE_GIF] = ($info['GIF Read Support']) ? true : false;
		}

		
		if (is_resource($source) && (get_resource_type($source) == 'gd'))
		{
			$this->handle = &$source;
		}
		elseif (!empty($source) && is_string($source))
		{
			
			$this->loadFile($source);
		}
	}

	
	public static function getImageFileProperties($path)
	{
		
		if (!file_exists($path))
		{
			throw new InvalidArgumentException('The image file does not exist.');
		}

		
		$info = getimagesize($path);

		if (!$info)
		{
			
			throw new RuntimeException('Unable to get properties for the image.');

			
		}

		
		$properties = (object) array(
			'width' => $info[0],
			'height' => $info[1],
			'type' => $info[2],
			'attributes' => $info[3],
			'bits' => isset($info['bits']) ? $info['bits'] : null,
			'channels' => isset($info['channels']) ? $info['channels'] : null,
			'mime' => $info['mime']
		);

		return $properties;
	}

	
	public function generateThumbs($thumbSizes, $creationMethod = self::SCALE_INSIDE)
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		
		if (!is_array($thumbSizes))
		{
			$thumbSizes = array($thumbSizes);
		}

		
		$generated = array();

		if (!empty($thumbSizes))
		{
			foreach ($thumbSizes AS $thumbSize)
			{
				
				$size = explode('x', strtolower($thumbSize));

				if (count($size) != 2)
				{
					throw new InvalidArgumentException('Invalid thumb size received: ' . $thumbSize);
				}

				$thumbWidth  = $size[0];
				$thumbHeight = $size[1];

				switch ($creationMethod)
				{
					
					case 4:
						$thumb = $this->crop($thumbWidth, $thumbHeight, null, null, true);
						break;

					
					case 5:
						$thumb = $this->cropResize($thumbWidth, $thumbHeight, true);
						break;

					default:
						$thumb = $this->resize($thumbWidth, $thumbHeight, true, $creationMethod);
						break;
				}

				
				$generated[] = $thumb;
			}
		}

		return $generated;
	}

	
	public function createThumbs($thumbSizes, $creationMethod = self::SCALE_INSIDE, $thumbsFolder = null)
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		
		if (is_null($thumbsFolder))
		{
			$thumbsFolder = dirname($this->getPath()) . '/thumbs';
		}

		
		if (!is_dir($thumbsFolder) && (!is_dir(dirname($thumbsFolder)) || !@mkdir($thumbsFolder)))
		{
			throw new InvalidArgumentException('Folder does not exist and cannot be created: ' . $thumbsFolder);
		}

		
		$thumbsCreated = array();

		if ($thumbs = $this->generateThumbs($thumbSizes, $creationMethod))
		{
			
			$imgProperties = self::getImageFileProperties($this->getPath());

			foreach ($thumbs AS $thumb)
			{
				
				$thumbWidth     = $thumb->getWidth();
				$thumbHeight    = $thumb->getHeight();

				
				$filename       = pathinfo($this->getPath(), PATHINFO_FILENAME);
				$fileExtension  = pathinfo($this->getPath(), PATHINFO_EXTENSION);
				$thumbFileName  = $filename . '_' . $thumbWidth . 'x' . $thumbHeight . '.' . $fileExtension;

				
				$thumbFileName = $thumbsFolder . '/' . $thumbFileName;

				if ($thumb->toFile($thumbFileName, $imgProperties->type))
				{
					
					$thumb->path = $thumbFileName;
					$thumbsCreated[] = $thumb;
				}
			}
		}

		return $thumbsCreated;
	}

	
	public function crop($width, $height, $left = null, $top = null, $createNew = true)
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		
		$width = $this->sanitizeWidth($width, $height);

		
		$height = $this->sanitizeHeight($height, $width);

		
		if (is_null($left))
		{
			$left = round(($this->getWidth() - $width) / 2);
		}

		if (is_null($top))
		{
			$top = round(($this->getHeight() - $height) / 2);
		}

		
		$left = $this->sanitizeOffset($left);

		
		$top = $this->sanitizeOffset($top);

		
		$handle = imagecreatetruecolor($width, $height);

		
		imagealphablending($handle, false);
		imagesavealpha($handle, true);

		if ($this->isTransparent())
		{
			
			$rgba = imageColorsForIndex($this->handle, imagecolortransparent($this->handle));
			$color = imageColorAllocate($this->handle, $rgba['red'], $rgba['green'], $rgba['blue']);

			
			imagecolortransparent($handle, $color);
			imagefill($handle, 0, 0, $color);

			imagecopyresized($handle, $this->handle, 0, 0, $left, $top, $width, $height, $width, $height);
		}
		else
		{
			imagecopyresampled($handle, $this->handle, 0, 0, $left, $top, $width, $height, $width, $height);
		}

		
		if ($createNew)
		{
			
			$new = new JImage($handle);

			return $new;

			
		}
		
		else
		{
			
			$this->destroy();

			$this->handle = $handle;

			return $this;
		}
	}

	
	public function filter($type, array $options = array())
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		
		$filter = $this->getFilterInstance($type);

		
		$filter->execute($options);

		return $this;
	}

	
	public function getHeight()
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		return imagesy($this->handle);
	}

	
	public function getWidth()
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		return imagesx($this->handle);
	}

	
	public function getPath()
	{
		return $this->path;
	}

	
	public function isLoaded()
	{
		
		if (!is_resource($this->handle) || (get_resource_type($this->handle) != 'gd'))
		{
			return false;
		}

		return true;
	}

	
	public function isTransparent()
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		return (imagecolortransparent($this->handle) >= 0);
	}

	
	public function loadFile($path)
	{
		
		$this->destroy();

		
		if (!file_exists($path))
		{
			throw new InvalidArgumentException('The image file does not exist.');
		}

		
		$properties = self::getImageFileProperties($path);

		
		switch ($properties->mime)
		{
			case 'image/gif':
				
				if (empty(self::$formats[IMAGETYPE_GIF]))
				{
					
					JLog::add('Attempting to load an image of unsupported type GIF.', JLog::ERROR);
					throw new RuntimeException('Attempting to load an image of unsupported type GIF.');

					
				}

				
				$handle = imagecreatefromgif($path);

				if (!is_resource($handle))
				{
					
					throw new RuntimeException('Unable to process GIF image.');

					
				}

				$this->handle = $handle;
				break;

			case 'image/jpeg':
				
				if (empty(self::$formats[IMAGETYPE_JPEG]))
				{
					
					JLog::add('Attempting to load an image of unsupported type JPG.', JLog::ERROR);
					throw new RuntimeException('Attempting to load an image of unsupported type JPG.');

					
				}

				
				$handle = imagecreatefromjpeg($path);

				if (!is_resource($handle))
				{
					
					throw new RuntimeException('Unable to process JPG image.');

					
				}

				$this->handle = $handle;
				break;

			case 'image/png':
				
				if (empty(self::$formats[IMAGETYPE_PNG]))
				{
					
					JLog::add('Attempting to load an image of unsupported type PNG.', JLog::ERROR);
					throw new RuntimeException('Attempting to load an image of unsupported type PNG.');

					
				}

				
				$handle = imagecreatefrompng($path);

				if (!is_resource($handle))
				{
					
					throw new RuntimeException('Unable to process PNG image.');

					
				}

				$this->handle = $handle;

				
				if (!$this->isTransparent())
				{
					
					$transparency = imagecolorallocatealpha($handle, 0, 0, 0, 127);

					imagecolortransparent($handle, $transparency);
				}

				break;

			default:
				JLog::add('Attempting to load an image of unsupported type: ' . $properties->mime, JLog::ERROR);
				throw new InvalidArgumentException('Attempting to load an image of unsupported type: ' . $properties->mime);
				break;
		}

		
		$this->path = $path;
	}

	
	public function resize($width, $height, $createNew = true, $scaleMethod = self::SCALE_INSIDE)
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		
		$width = $this->sanitizeWidth($width, $height);

		
		$height = $this->sanitizeHeight($height, $width);

		
		$dimensions = $this->prepareDimensions($width, $height, $scaleMethod);

		
		$offset = new stdClass;
		$offset->x = $offset->y = 0;

		
		if ($scaleMethod == self::SCALE_FIT)
		{
			
			$offset->x	= round(($width - $dimensions->width) / 2);
			$offset->y	= round(($height - $dimensions->height) / 2);

			$handle = imagecreatetruecolor($width, $height);

			
			if (!$this->isTransparent())
			{
				$transparency = imagecolorAllocateAlpha($this->handle, 0, 0, 0, 127);
				imagecolorTransparent($this->handle, $transparency);
			}
		}
		else
		{
			$handle = imagecreatetruecolor($dimensions->width, $dimensions->height);
		}

		
		imagealphablending($handle, false);
		imagesavealpha($handle, true);

		if ($this->isTransparent())
		{
			
			$rgba = imageColorsForIndex($this->handle, imagecolortransparent($this->handle));
			$color = imageColorAllocateAlpha($this->handle, $rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);

			
			imagecolortransparent($handle, $color);
			imagefill($handle, 0, 0, $color);

			imagecopyresized($handle, $this->handle, $offset->x, $offset->y, 0, 0, $dimensions->width, $dimensions->height, $this->getWidth(), $this->getHeight());
		}
		else
		{
			imagecopyresampled($handle, $this->handle, $offset->x, $offset->y, 0, 0, $dimensions->width, $dimensions->height, $this->getWidth(), $this->getHeight());
		}

		
		if ($createNew)
		{
			
			$new = new JImage($handle);

			return $new;

			
		}
		
		else
		{
			
			$this->destroy();

			$this->handle = $handle;

			return $this;
		}
	}

	
	public function cropResize($width, $height, $createNew = true)
	{
		$width   = $this->sanitizeWidth($width, $height);
		$height  = $this->sanitizeHeight($height, $width);

		if (($this->getWidth() / $width) < ($this->getHeight() / $height))
		{
			$this->resize($width, 0, false);
		}
		else
		{
			$this->resize(0, $height, false);
		}

		return $this->crop($width, $height, null, null, $createNew);
	}

	
	public function rotate($angle, $background = -1, $createNew = true)
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		
		$angle = (float) $angle;

		
		$handle = imagecreatetruecolor($this->getWidth(), $this->getHeight());

		
		if ($background == -1)
		{
			
			imagealphablending($handle, false);
			imagesavealpha($handle, true);

			$background = imagecolorallocatealpha($handle, 0, 0, 0, 127);
		}

		
		imagecopy($handle, $this->handle, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());

		
		$handle = imagerotate($handle, $angle, $background);

		
		if ($createNew)
		{
			
			$new = new JImage($handle);

			return $new;

			
		}
		
		else
		{
			
			$this->destroy();

			$this->handle = $handle;

			return $this;
		}
	}

	
	public function toFile($path, $type = IMAGETYPE_JPEG, array $options = array())
	{
		
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		switch ($type)
		{
			case IMAGETYPE_GIF:
				return imagegif($this->handle, $path);
				break;

			case IMAGETYPE_PNG:
				return imagepng($this->handle, $path, (array_key_exists('quality', $options)) ? $options['quality'] : 0);
				break;

			case IMAGETYPE_JPEG:
			default:
				return imagejpeg($this->handle, $path, (array_key_exists('quality', $options)) ? $options['quality'] : 100);
		}
	}

	
	protected function getFilterInstance($type)
	{
		
		$type = strtolower(preg_replace('#[^A-Z0-9_]#i', '', $type));

		
		$className = 'JImageFilter' . ucfirst($type);

		if (!class_exists($className))
		{
			JLog::add('The ' . ucfirst($type) . ' image filter is not available.', JLog::ERROR);
			throw new RuntimeException('The ' . ucfirst($type) . ' image filter is not available.');
		}

		
		$instance = new $className($this->handle);

		
		if (!($instance instanceof JImageFilter))
		{
			
			JLog::add('The ' . ucfirst($type) . ' image filter is not valid.', JLog::ERROR);
			throw new RuntimeException('The ' . ucfirst($type) . ' image filter is not valid.');

			
		}

		return $instance;
	}

	
	protected function prepareDimensions($width, $height, $scaleMethod)
	{
		
		$dimensions = new stdClass;

		switch ($scaleMethod)
		{
			case self::SCALE_FILL:
				$dimensions->width = (int) round($width);
				$dimensions->height = (int) round($height);
				break;

			case self::SCALE_INSIDE:
			case self::SCALE_OUTSIDE:
			case self::SCALE_FIT:
				$rx = ($width > 0) ? ($this->getWidth() / $width) : 0;
				$ry = ($height > 0) ? ($this->getHeight() / $height) : 0;

				if ($scaleMethod != self::SCALE_OUTSIDE)
				{
					$ratio = max($rx, $ry);
				}
				else
				{
					$ratio = min($rx, $ry);
				}

				$dimensions->width = (int) round($this->getWidth() / $ratio);
				$dimensions->height = (int) round($this->getHeight() / $ratio);
				break;

			default:
				throw new InvalidArgumentException('Invalid scale method.');
				break;
		}

		return $dimensions;
	}

	
	protected function sanitizeHeight($height, $width)
	{
		
		$height = ($height === null) ? $width : $height;

		
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $height))
		{
			$height = (int) round($this->getHeight() * (float) str_replace('%', '', $height) / 100);
		}
		
		else
		{
			$height = (int) round((float) $height);
		}

		return $height;
	}

	
	protected function sanitizeOffset($offset)
	{
		return (int) round((float) $offset);
	}

	
	protected function sanitizeWidth($width, $height)
	{
		
		$width = ($width === null) ? $height : $width;

		
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $width))
		{
			$width = (int) round($this->getWidth() * (float) str_replace('%', '', $width) / 100);
		}
		
		else
		{
			$width = (int) round((float) $width);
		}

		return $width;
	}

	
	public function destroy()
	{
		if ($this->isLoaded())
		{
			return imagedestroy($this->handle);
		}

		return false;
	}

	
	public function __destruct()
	{
		$this->destroy();
	}
}
