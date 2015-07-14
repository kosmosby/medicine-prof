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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class Watermark
{
	
	const ALIGN_LEFT = -1;
	const ALIGN_CENTER = 0;
	const ALIGN_RIGHT = +1;

	
	const ALIGN_TOP = -1;
	const ALIGN_MIDDLE = 0;
	const ALIGN_BOTTOM = +1;

	
	protected $image = '';
	protected $watermark = '';
	protected $halign = self::ALIGN_RIGHT;
	protected $valign = self::ALIGN_BOTTOM;
	protected $offset_x = 0;
	protected $offset_y = 0;
	protected $fontPath = '';
	protected $fontSize = 12;
	protected $fontColor = 'ffffff';
	protected $backgroundColor = '144274';
	protected $opacity = 0;
	protected $rotate = 0;

	public function __construct($image, $options = array())
	{
		$this->image = $image;
		if ($options['watermark'])
		{
			$this->watermark = $options['watermark'];
		}
		if (isset($options['halign']))
		{
			$this->halign = $options['halign'];
		}
		if (isset($options['valign']))
		{
			$this->valign = $options['valign'];
		}
		if ($options['offset_x'])
		{
			$this->offset_x = $options['offset_x'];
		}
		if ($options['offset_y'])
		{
			$this->offset_y = $options['offset_y'];
		}
		if ($options['fontPath'])
		{
			$this->fontPath = $options['fontPath'];
		}
		if ($options['fontSize'])
		{
			$this->fontSize = $options['fontSize'];
		}
		if ($options['fontColor'])
		{
			$this->fontColor = $options['fontColor'];
		}
		if ($options['backgroundColor'])
		{
			$this->backgroundColor = $options['backgroundColor'];
		}
		if ($options['opacity'])
		{
			$this->opacity = $options['opacity'];
		}
		if ($options['rotate'])
		{
			$this->rotate = $options['rotate'];
		}
		list($width, $height, $type) = getimagesize($image);
		if ($type)
		{
			$this->type = $type;
		}
		if ($width)
		{
			$this->width = $width;
		}
		if ($height)
		{
			$this->height = $height;
		}
	}

	public function loadImage($image)
	{
		$this->image = $image;
	}

	
	public function output($output = null)
	{
		
		$renderImage = self::_render();
		if (!$renderImage)
		{
			user_error('Error rendering image', E_USER_NOTICE);

			return false;
		}

		
		if (empty($output))
		{
			$content_type = image_type_to_mime_type($this->type);
			if (!headers_sent())
			{
				header('Content-Type: ' . $content_type);
			}
			else
			{
				user_error('Headers have already been sent. Could not display image.', E_USER_NOTICE);

				return false;
			}
		}

		
		switch ($this->type)
		{
			case IMAGETYPE_GIF:
				$result = empty($output) ? imagegif($renderImage) : imagegif($renderImage, $output);
				break;

			case IMAGETYPE_PNG:
				$result = empty($output) ? imagepng($renderImage) : imagepng($renderImage, $output);
				break;

			case IMAGETYPE_JPEG:
				$result = empty($output) ? imagejpeg($renderImage, null, 100) : imagejpeg($renderImage, $output, 100);
				break;

			default:
				user_error('Image type ' . $content_type . ' not supported by PHP', E_USER_NOTICE);

				return false;
		}

		
		if (!$result)
		{
			user_error('Error output image', E_USER_NOTICE);

			return false;
		}

		
		imagedestroy($renderImage);

		return true;
	}

	
	private function _render()
	{
		
		$sourceImage = $this->_imageCreateFromFile($this->image);
		if (!is_resource($sourceImage))
		{
			user_error('Invalid image resource', E_USER_NOTICE);

			return false;
		}

		
		if (is_file($this->watermark))
		{
			$watermark = $this->_imageCreateFromFile($this->watermark);
		}
		else
		{
			if (is_string($this->watermark))
			{
				$watermark = $this->initializeTextImage($this->watermark, $this->fontPath, $this->fontSize, $this->fontColor, 0, $this->backgroundColor);
			}
		}
		if ($this->opacity)
		{
			self::opacity($watermark, $this->opacity);
		}
		if ($this->rotate)
		{
			self::rotate($watermark, $this->rotate);
		}
		if (!is_resource($watermark))
		{
			user_error('Invalid watermark resource', E_USER_NOTICE);

			return false;
		}

		$image_width      = imagesx($sourceImage);
		$image_height     = imagesy($sourceImage);
		$watermark_width  = imagesx($watermark);
		$watermark_height = imagesy($watermark);

		$hshift = $this->getOffset('h');
		$vshift = $this->getOffset('v');
		$X      = self::_coord($this->halign, $image_width, $watermark_width) + $hshift;
		$Y      = self::_coord($this->valign, $image_height, $watermark_height) + $vshift;

		imagecopy($sourceImage, $watermark, $X, $Y, 0, 0, $watermark_width, $watermark_height);
		imagedestroy($watermark);

		return $sourceImage;
	}

	private function getOffset($direc)
	{
		if ($direc == 'h')
		{
			$type   = 'halign';
			$offset = 'offset_x';
		}
		else
		{
			$type   = 'valign';
			$offset = 'offset_y';
		}
		if ($this->$type == 1)
		{
			$value = -$this->$offset;
		}
		else
		{
			$value = $this->$offset;
		}

		return $value;
	}

	
	private function _imageCreateFromFile($filename)
	{
		if (!is_file($filename) || !is_readable($filename))
		{
			user_error('Unable to open file "' . $filename . '"', E_USER_NOTICE);

			return false;
		}

		
		list(, , $type) = getimagesize($filename);
		switch ($type)
		{
			case IMAGETYPE_GIF:
				return imagecreatefromgif($filename);
				break;

			case IMAGETYPE_JPEG:
				return imagecreatefromjpeg($filename);
				break;

			case IMAGETYPE_PNG:
				return imagecreatefrompng($filename);
				break;
		}
		user_error('Unsupport image type', E_USER_NOTICE);

		return false;
	}

	
	private function _imageCreateFromString($string)
	{
		if (!is_string($string) || empty($string))
		{
			user_error('Invalid image value in string', E_USER_NOTICE);

			return false;
		}

		return imagecreatefromstring($string);
	}

	
	private static function _coord($align, $image_dimension, $watermark_dimension)
	{
		if ($align < self::ALIGN_CENTER)
		{
			$result = 0;
		}
		elseif ($align > self::ALIGN_CENTER)
		{
			$result = $image_dimension - $watermark_dimension;
		}
		else
		{
			$result = ($image_dimension - $watermark_dimension) >> 1;
		}

		return $result;
	}

	
	public function initializeTextImage($text, $fontPath, $fontSize = 13, $fontColor = "ffffff", $textRotation = 0, $backgroundColor = null)
	{
		$textDimensions = self::getTextBoxDimension($fontSize, $textRotation, $fontPath, $text);
		$padding        = $textDimensions["height"] * 0.3;
		if ($backgroundColor)
		{
			$textimage = self::generateImage($textDimensions["width"] + $padding * 2, $textDimensions["height"] + $padding * 2, $backgroundColor, 0);
		}
		else
		{
			$textimage = self::generateImage($textDimensions["width"], $textDimensions["height"]);
		}
		$this->write($textimage, $text, $fontPath, $fontSize, $fontColor, $textDimensions["left"] + $padding, $textDimensions["top"] + $padding, $textRotation);

		return $textimage;
	}

	
	public static function getTextBoxDimension($fontSize, $fontAngle, $fontFile, $text)
	{
		if (!file_exists($fontFile))
		{
			throw new Exception('Can\'t find a font file at this path : "' . $fontFile . '".');
		}

		$box = imagettfbbox($fontSize, $fontAngle, $fontFile, $text);

		if (!$box)
		{

			return false;
		}

		$minX   = min(array($box[0], $box[2], $box[4], $box[6]));
		$maxX   = max(array($box[0], $box[2], $box[4], $box[6]));
		$minY   = min(array($box[1], $box[3], $box[5], $box[7]));
		$maxY   = max(array($box[1], $box[3], $box[5], $box[7]));
		$width  = ($maxX - $minX);
		$height = ($maxY - $minY);
		$left   = abs($minX) + $width;
		$top    = abs($minY) + $height;

		
		$img   = @imagecreatetruecolor($width << 2, $height << 2);
		$white = imagecolorallocate($img, 255, 255, 255);
		$black = imagecolorallocate($img, 0, 0, 0);
		imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);

		
		imagettftext($img, $fontSize, $fontAngle, $left, $top, $white, $fontFile, $text);

		
		$rleft   = $w4 = $width << 2;
		$rright  = 0;
		$rbottom = 0;
		$rtop    = $h4 = $height << 2;

		for ($x = 0; $x < $w4; $x++)
		{

			for ($y = 0; $y < $h4; $y++)
			{

				if (imagecolorat($img, $x, $y))
				{

					$rleft   = min($rleft, $x);
					$rright  = max($rright, $x);
					$rtop    = min($rtop, $y);
					$rbottom = max($rbottom, $y);
				}
			}
		}

		imagedestroy($img);

		return array(
			'left'   => $left - $rleft,
			'top'    => $top - $rtop,
			'width'  => $rright - $rleft + 1,
			'height' => $rbottom - $rtop + 1,
		);
	}

	
	public static function generateImage($width = 100, $height = 100, $color = "ffffff", $opacity = 127)
	{
		$RGBColors = self::convertHexToRGB($color);

		$image = imagecreatetruecolor($width, $height);
		imagesavealpha($image, true);
		$color = imagecolorallocatealpha($image, $RGBColors["R"], $RGBColors["G"], $RGBColors["B"], $opacity);
		imagefill($image, 0, 0, $color);

		return $image;
	}

	
	public function write($textimage, $text, $fontPath, $fontSize = 13, $color = "ffffff", $positionX = 0, $positionY = 0, $fontRotation = 0)
	{
		if (!file_exists($fontPath))
		{
			throw new ImageWorkshopLayerException('Can\'t find a font file at this path : "' . $fontPath . '".', self::ERROR_FONT_NOT_FOUND);
		}

		$RGBTextColor = self::convertHexToRGB($color);
		$textColor    = imagecolorallocate($textimage, $RGBTextColor['R'], $RGBTextColor['G'], $RGBTextColor['B']);

		imagettftext($textimage, $fontSize, $fontRotation, $positionX, $positionY, $textColor, $fontPath, $text);

		return $textimage;
	}

	
	public static function convertHexToRGB($hex)
	{
		return array(
			"R" => base_convert(substr($hex, 0, 2), 16, 10),
			"G" => base_convert(substr($hex, 2, 2), 16, 10),
			"B" => base_convert(substr($hex, 4, 2), 16, 10),
		);
	}

	
	public function opacity(&$image, $opacity)
	{
		$image_width      = imagesx($image);
		$image_height     = imagesy($image);
		$transparentImage = self::generateImage($image_width, $image_height);

		self::imagecopymergealpha($transparentImage, $image, 0, 0, 0, 0, $image_width, $image_height, $opacity);

		$image = $transparentImage;
		unset($transparentImage);

		return;
	}

	
	public static function imagecopymergealpha(&$destImg, &$srcImg, $destX, $destY, $srcX, $srcY, $srcW, $srcH, $pct = 0)
	{
		$destX = (int) $destX;
		$destY = (int) $destY;
		$srcX  = (int) $srcX;
		$srcY  = (int) $srcY;
		$srcW  = (int) $srcW;
		$srcH  = (int) $srcH;
		$pct   = (int) $pct;
		$destW = imagesx($destImg);
		$destH = imagesy($destImg);

		for ($y = 0; $y < $srcH + $srcY; $y++)
		{

			for ($x = 0; $x < $srcW + $srcX; $x++)
			{

				if ($x + $destX >= 0 && $x + $destX < $destW && $x + $srcX >= 0 && $x + $srcX < $srcW && $y + $destY >= 0 && $y + $destY < $destH && $y + $srcY >= 0 && $y + $srcY < $srcH)
				{

					$destPixel     = imagecolorsforindex($destImg, imagecolorat($destImg, $x + $destX, $y + $destY));
					$srcImgColorat = imagecolorat($srcImg, $x + $srcX, $y + $srcY);

					if ($srcImgColorat > 0)
					{

						$srcPixel = imagecolorsforindex($srcImg, $srcImgColorat);

						$srcAlpha  = 1 - ($srcPixel['alpha'] / 127);
						$destAlpha = 1 - ($destPixel['alpha'] / 127);
						$opacity   = $srcAlpha * $pct / 100;

						if ($destAlpha >= $opacity)
						{
							$alpha = $destAlpha;
						}

						if ($destAlpha < $opacity)
						{
							$alpha = $opacity;
						}

						if ($alpha > 1)
						{
							$alpha = 1;
						}

						if ($opacity > 0)
						{

							$destRed   = round((($destPixel['red'] * $destAlpha * (1 - $opacity))));
							$destGreen = round((($destPixel['green'] * $destAlpha * (1 - $opacity))));
							$destBlue  = round((($destPixel['blue'] * $destAlpha * (1 - $opacity))));
							$srcRed    = round((($srcPixel['red'] * $opacity)));
							$srcGreen  = round((($srcPixel['green'] * $opacity)));
							$srcBlue   = round((($srcPixel['blue'] * $opacity)));
							$red       = round(($destRed + $srcRed) / ($destAlpha * (1 - $opacity) + $opacity));
							$green     = round(($destGreen + $srcGreen) / ($destAlpha * (1 - $opacity) + $opacity));
							$blue      = round(($destBlue + $srcBlue) / ($destAlpha * (1 - $opacity) + $opacity));

							if ($red > 255)
							{
								$red = 255;
							}

							if ($green > 255)
							{
								$green = 255;
							}

							if ($blue > 255)
							{
								$blue = 255;
							}

							$alpha = round((1 - $alpha) * 127);
							$color = imagecolorallocatealpha($destImg, $red, $green, $blue, $alpha);
							imagesetpixel($destImg, $x + $destX, $y + $destY, $color);
						}
					}
				}
			}
		}
	}

	
	public function rotate(&$image, $degrees)
	{
		if ($degrees != 0)
		{

			if ($degrees < -360 || $degrees > 360)
			{

				$degrees = $degrees % 360;
			}

			if ($degrees < 0 && $degrees >= -360)
			{

				$degrees = 360 + $degrees;
			}

			
			$imageRotated = imagerotate($image, -$degrees, -1);
			imagealphablending($imageRotated, true);
			imagesavealpha($imageRotated, true);
			$image = $imageRotated;
			unset($imageRotated);

			return true;
		}
	}

}