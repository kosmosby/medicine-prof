<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterJomsocial extends oseMscAddon
{
	public static function save( $params )
    {
    	$db = oseDB::instance();
    	$post = JRequest::get('post');

    	$member_id = $params['member_id'];

    	//JRequest::setVar('member_id',$member_id);

    	if(empty($member_id))
    	{
    		$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
    	}

    	/*
    	$uploadImg = JRequest::getVar('image', null, 'files', 'array' );

    	if(!empty($uploadImg['tmp_name']))
    	{
    		$upload = self::uploadFile($uploadImg['tmp_name']);
    		if($upload['uploaded'])
    		{
    			$avatar = $upload['avatar'];
    			$thumb = $upload['thumb'];
    		}else{
    			$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText :: _('Error');

				return $result;
    		}
    	}else{
    		$avatar = 'components/com_community/assets/default.jpg';
    		$thumb = 'components/com_community/assets/default_thumb.jpg';
    	}
    	*/

    	$avatar = 'components/com_community/assets/default.jpg';
    	$thumb = 'components/com_community/assets/default_thumb.jpg';

    	$query = "SELECT country_name FROM `#__osemsc_country` WHERE `country_2_code` = '{$post['bill_country']}'";
    	$db->setQuery($query);
    	$country = $db->loadResult();

    	$post['field_8'] = $post['bill_addr1'];
    	$post['field_9'] = $post['bill_city'];
    	$post['field_10'] = $post['bill_state'];
    	$post['field_11'] = empty($country)?$post['bill_country']:$country;

    	$query = " SELECT count(*) FROM `#__community_users` WHERE `userid` = '{$member_id}'";
    	$db->setQuery($query);
    	$exists = $db->loadResult();
    	if(empty($exists))
    	{
    		$query = " INSERT INTO `#__community_users` "
			."(`userid`, `avatar`, `thumb`) "
			."VALUES "
			."('{$member_id}', '{$avatar}', '{$thumb}')"
			;

    	}else{
    		$query = " UPDATE `#__community_users` SET `avatar` = '{$avatar}', `thumb` = '{$thumb}'"
					." WHERE `userid` ={$member_id}"
					;
    	}

    	$db->setQuery($query);
    	if(!oseDB::query())
		{
			$result = array();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Fail Saving Jomsocial User Info.');
			return $result;
		}

	    $query = " SELECT count(*) FROM `#__components` WHERE `link` = 'option=com_xipt'";
	    $db->setQuery($query);
	    $exists = $db->loadResult();
	    if($exists>0)
	   	{
		   	$params = &JComponentHelper::getParams( 'com_xipt');
			$default_pid = $params->get('defaultProfiletypeID');

			$query = " SELECT template FROM `#__xipt_profiletypes` WHERE `id`  = '{$default_pid}'";
		   	$db->setQuery($query);
		   	$template = $db->loadResult();

		   	if(empty($template) && !empty($default_pid))
		   	{
		    	$query = " INSERT INTO `#__xipt_users` "
						." (`userid`, `profiletype`, `template`) "
						." VALUES "
						." ('{$member_id}', '{$default_pid}', '{$template}')"
						;
				$db->setQuery($query);
		    	if(!oseDB::query())
				{
					$result = array();
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText :: _('Fail Saving JSPT Info.');

					return $result;
				}
		   	}
	   	}

	    $fields = array();

		foreach($post as $key => $value)
		{
			if(strstr($key,'field_'))
			{
				$billKey = preg_replace('/field_/','',$key,1);
				 $fields[$billKey] = $value;
			}
		}

		foreach($fields as $key => $field)
		{
			$query = " INSERT INTO `#__community_fields_values` "
			    	." (user_id, field_id, value) "
				    ." VALUES "
				    ." ('{$member_id}', '{$key}', '{$field}')";
			$db->setQuery($query);

			if(!oseDB::query())
			{
				$result = array();
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText :: _('Fail Saving Jomsocial User Info.');
				return $result;
			}
		}

    	$query = " SELECT count(*) FROM `#__community_groups_members` WHERE `memberid` = '{$member_id}'";
	   	$db->setQuery($query);
	   	$gm_exists = $db->loadResult();

	   	if(empty($gm_exists) && !empty($default_pid))
	   	{
	   		$query = " SELECT `group` FROM `#__xipt_profiletypes` WHERE `id` = '{$default_pid}'";
		   	$db->setQuery($query);
		   	$gid = $db->loadResult();

		    $query = " INSERT INTO `#__community_groups_members` "
					." (groupid, memberid, approved, permissions) "
					." VALUES "
					." ('{$gid}','{$member_id}','1','0')"
					;

			$db->setQuery($query);
			if(!oseDB::query())
			{
				$result = array();
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText :: _('Fail Saving Jomsocial User Info.');
				return $result;
			}else
			{
				$result = array();
				$result['success'] = true;
				$result['title'] = 'Done';
				$result['content'] = JText :: _('Saved Jomsocial User Info.');
				return $result;
			}
	   	}

	   	$result = array();
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText :: _('Saved Jomsocial User Info.');

    	return $result;

    }


    public static function uploadFile($file)
    {
    	require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
    	require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'helpers'.DS.'image.php');

		$config	= CFactory::getConfig();

    	$fileName = JUtility::getHash($file. time() );
		$hashFileName = JString::substr( $fileName , 0 , 24 );
		$fileType = self::getMimeType($file);

    	//$result['img_path'] = JURI::root().$config->getString('imagefolder').'/'.'avatar'.'/'.$hashFileName.CImageHelper::getExtension($uploadImg['type']);
		$result['avatar'] = $config->getString('imagefolder').'/'.'avatar'.'/'.$hashFileName.CImageHelper::getExtension($fileType);
		$result['thumb'] = $config->getString('imagefolder').'/'.'avatar'.'/'.'thumb_'.$hashFileName.CImageHelper::getExtension($fileType);
    	$avatar = JPATH_ROOT.DS.$config->getString('imagefolder').DS.'avatar'.DS.$hashFileName.CImageHelper::getExtension($fileType);
    	$thumbnail = JPATH_ROOT.DS.$config->getString('imagefolder').DS.'avatar'.DS.'thumb_'.$hashFileName.CImageHelper::getExtension($fileType);
   		$imageMaxWidth	= 160;
   		if(self::resizeProportional($file, $avatar , $fileType , $imageMaxWidth ))
   		{
   			if(self::createThumb($file, $thumbnail , $fileType ))
   			{
   				$result['uploaded'] = true;
   				return $result;
   			}
   		}else{
   			$result['uploaded'] = false;
   			return $result;
   		}
    }

    public static function validate()
    {
    	$result = array();
    	$result['success'] = true;
    	$result['uploaded'] = false;

    	$uploadImg = JRequest::getVar('image', null, 'files', 'array' );

    	if(JFile::exists($uploadImg['tmp_name']))
    	{
    		$size = filesize($uploadImg['tmp_name']);

    		if(!self::checkImageFormat($uploadImg['tmp_name']))
    		{
    			$result['success'] = true;
		    	$result['uploaded'] = false;
		    	$result['title'] = JText::_('Error');
		    	$result['content'] = JText::_('Only .gif,.png,.jpeg is allowed');

		    	$result = oseJson::encode($result);
				oseExit($result);
    		}

    		require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
    		require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'helpers'.DS.'image.php');

			$config	= CFactory::getConfig();
			$maxuploadsize = (double) $config->get('maxuploadsize');
			$uploadLimit = ( $maxuploadsize * 1024 * 1024 );
    		//$photospath = $config->get('photospath');

    		if($size > $uploadLimit )
    		{
    			$result['success'] = true;
		    	$result['uploaded'] = false;
		    	$result['title'] = JText::_('Error');
		    	$result['content'] = JText::_('Image size must be lower than '.$maxuploadsize.'M');

		    	$result = oseJson::encode($result);
				oseExit($result);
    		}

    		if( !self::isValid($uploadImg['tmp_name']) )
			{
				$result['success'] = true;
		    	$result['uploaded'] = false;
		    	$result['img_path'] = null;
		    	$result['title'] = JText::_('Error');
		    	$result['content'] = JText::_('Uploaded file type is not supported');

		    	$result = oseJson::encode($result);
				oseExit($result);
			}

    		$result['uploaded'] = true;
    	}else{
    		$result['title'] = JText::_('Error');
		    $result['content'] = JText::_('Error');
    	}

    	$result = oseJson::encode($result);
		oseExit($result);

    }

    public static function getMimeType($file)
    {
	    if (function_exists('finfo_open'))
	    {
		    $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
		    $content_type = finfo_file($finfo, $file);
		    finfo_close($finfo);
	    }
	    else
	    {
	    	$content_type = mime_content_type($file);
	    }
	    return $content_type;
    }

	public static function checkImageFormat($file)
	{
		$allowExt = array();
		$allowExt[] = 'png';
		$allowExt[] = 'gif';
		$allowExt[] = 'jpeg';

		$img_type = self::getMimeType($file);
		$ext = explode('/',$img_type);

		if(in_array($ext[1],$allowExt))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	public static function isValid( $file )
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
		$config			=& CFactory::getConfig();
				// Use imagemagick if available
		$imageEngine 	= $config->get('imageengine');
		$magickPath		= $config->get( 'magickPath' );

		if( class_exists('Imagick') && ($imageEngine == 'auto' || $imageEngine == 'imagick' ) )
		{

			$thumb = new Imagick();
			$imageOk = $thumb->readImage($file);
			$thumb->destroy();

			return $imageOk;
		}
		else if( !empty( $magickPath ) && !class_exists( 'Imagick' ) )
		{

			// Execute the command to resize. In windows, the commands are executed differently.
			if( JString::strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' )
			{
				$identifyFile	= rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'identify.exe';
				$command		= '""' . rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'identify.exe" -ping "' . $file . '""';
			}
			else
			{
				$identifyFile	= rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'identify';
				$command		= '"' . rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'identify" -ping "' . $file . '"';
			}

			if( JFile::exists( $identifyFile ) && function_exists( 'exec') )
			{
				$output		= exec( $command );

				// Test if there's any output, otherwise we know the exec failed.
				if( !empty( $output ) )
				{
					return true;
				}
			}
		}


		# JPEG:
		if( function_exists( 'imagecreatefromjpeg' ) )
		{
			$im = @imagecreatefromjpeg($file);
			if ($im !== false){ return true; }
		}

		if( function_exists( 'imagecreatefromgif' ) )
		{
			# GIF:
			$im = @imagecreatefromgif($file);
			if ($im !== false) { return true; }
		}

		if( function_exists( 'imagecreatefrompng' ) )
		{
			# PNG:
			$im = @imagecreatefrompng($file);
			if ($im !== false) { return true; }
		}

		if( function_exists( 'imagecreatefromgd' ) )
		{
			# GD File:
			$im = @imagecreatefromgd($file);
			if ($im !== false) { return true; }
		}

		if( function_exists( 'imagecreatefromgd2' ) )
		{
			# GD2 File:
			$im = @imagecreatefromgd2($file);
			if ($im !== false) { return true; }
		}

		if( function_exists( 'imagecreatefromwbmp' ) )
		{
			# WBMP:
			$im = @imagecreatefromwbmp($file);
			if ($im !== false) { return true; }
		}

		if( function_exists( 'imagecreatefromxbm' ) )
		{
			# XBM:
			$im = @imagecreatefromxbm($file);
			if ($im !== false) { return true; }
		}

		if( function_exists( 'imagecreatefromxpm' ) )
		{
			# XPM:
			$im = @imagecreatefromxpm($file);
			if ($im !== false) { return true; }
		}

		// If all failed, this photo is invalid
		return false;
	}

	public static function resizeProportional($srcPath, $destPath, $destType, $destWidth=0, $destHeight=0)
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');

		list($currentWidth, $currentHeight) = getimagesize( $srcPath );

		$config =& CFactory::getConfig();

		if($destWidth == 0)
		{
			// Calculate the width if the width is not set.
			$destWidth = intval($destHeight/$currentHeight * $currentWidth);
		}
		else
		{
			// Calculate the height if the width is set.
			$destHeight = intval( $destWidth / $currentWidth * $currentHeight);
		}

		$imageEngine	= $config->get('imageengine');
		$magickPath		= $config->get( 'magickPath' );

		// Use imageMagick if available
		if( class_exists('Imagick') && !empty( $magickPath ) && ($imageEngine == 'auto' || $imageEngine == 'imagick') )
		{
			$thumb = new Imagick();
			$thumb->readImage($srcPath);
			$thumb->resizeImage($destWidth,$destHeight, MAGICK_FILTER ,1);
			$thumb->writeImage($destPath);
			$thumb->clear();
			$thumb->destroy();
			return true;
		}
		else if( !empty( $magickPath ) && !class_exists( 'Imagick' ) )
		{
			// Execute the command to resize. In windows, the commands are executed differently.
			if( JString::strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' )
			{
				$file		= rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert.exe';
				$command	= '"' . rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert.exe"';
			}
			else
			{
				$file		= rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert';
				$command	= '"' . rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert"';
			}


			if( JFile::exists( $file ) && function_exists( 'exec') )
			{
				$execute	= $command . ' -resize ' . $destWidth . 'x' . $destHeight . ' ' . $srcPath . ' ' . $destPath;
				exec( $execute );

				// Test if the files are created, otherwise we know the exec failed.
				if( JFile::exists( $destPath ) )
				{
					return true;
				}
			}
		}

		// IF all else fails, we try to use GD
		return self::resize($srcPath, $destPath, $destType, $destWidth, $destHeight);
	}

	public static function  resize($srcPath, $destPath, $destType, $destWidth, $destHeight, $sourceX	= 0, $sourceY	= 0, $currentWidth=0, $currentHeight=0)
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
    	require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'helpers'.DS.'image.php');

		// See if we can grab image transparency
		$image				= CImageHelper::open( $srcPath , $destType );
		$transparentIndex	= imagecolortransparent( $image );

		// Create new image resource
		$image_p			= ImageCreateTrueColor( $destWidth , $destHeight );
		$background			= ImageColorAllocate( $image_p , 255, 255, 255 );

		// test if memory is enough
		if($image_p == FALSE){
			echo 'Image resize fail. Please increase PHP memory';
			return false;
		}

		// Set the new image background width and height
		$resourceWidth		= $destWidth;
		$resourceHeight		= $destHeight;

		if(empty($currentHeight) && empty($currentWidth))
		{
			list($currentWidth , $currentHeight) = getimagesize( $srcPath );
		}
		// If image is smaller, just copy to the center
		$targetX = 0;
		$targetY = 0;

		// If the height and width is smaller, copy it to the center.
		if( $destType != 'image/jpg' &&	$destType != 'image/jpeg' && $destType != 'image/pjpeg' )
		{
			if( ($currentHeight < $destHeight) && ($currentWidth < $destWidth) )
			{
				$targetX = intval( ($destWidth - $currentWidth) / 2);
				$targetY = intval( ($destHeight - $currentHeight) / 2);

				// Since the
		 		$destWidth = $currentWidth;
		 		$destHeight = $currentHeight;
			}
		}

		// Resize GIF/PNG to handle transparency
		if( $destType == 'image/gif' )
		{
			$colorTransparent = imagecolortransparent($image);
			imagepalettecopy($image, $image_p);
			imagefill($image_p, 0, 0, $colorTransparent);
			imagecolortransparent($image_p, $colorTransparent);
			imagetruecolortopalette($image_p, true, 256);
			imagecopyresized($image_p, $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth , $destHeight , $currentWidth , $currentHeight );
		}
		else if( $destType == 'image/png' || $destType == 'image/x-png')
		{
			// Disable alpha blending to keep the alpha channel
			imagealphablending( $image_p , false);
			imagesavealpha($image_p,true);
			$transparent		= imagecolorallocatealpha($image_p, 255, 255, 255, 127);

			imagefilledrectangle($image_p, 0, 0, $resourceWidth, $resourceHeight, $transparent);
			imagecopyresampled($image_p , $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth, $destHeight, $currentWidth, $currentHeight);
		}
		else
		{
			// Turn off alpha blending to keep the alpha channel
			imagealphablending( $image_p , false );
			imagecopyresampled( $image_p , $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth , $destHeight , $currentWidth , $currentHeight );
		}

		// Output
		ob_start();

		// Test if type is png
		if( $destType == 'image/png' || $destType == 'image/x-png' )
		{
			imagepng( $image_p );
		}
		elseif ( $destType == 'image/gif')
		{
			imagegif( $image_p );
		}
		else
		{
			// We default to use jpeg
			imagejpeg($image_p, null, 80);
		}

		$output = ob_get_contents();
		ob_end_clean();

		// @todo, need to verify that the $output is indeed a proper image data
		return JFile::write( $destPath , $output );
	}

	public static function createThumb($srcPath, $destPath, $destType, $destWidth=64, $destHeight=64)
	{
		// Get the image size for the current original photo
		list( $currentWidth , $currentHeight )	= getimagesize( $srcPath );
		$config  = CFactory::getConfig();
		$jconfig = JFactory::getConfig();

		// Find the correct x/y offset and source width/height. Crop the image squarely, at the center.
		if( $currentWidth == $currentHeight )
		{
			$sourceX = 0;
			$sourceY = 0;
		}
		else if( $currentWidth > $currentHeight )
		{
			$sourceX			= intval( ( $currentWidth - $currentHeight ) / 2 );
			$sourceY 			= 0;
			$currentWidth		= $currentHeight;
		}
		else
		{
			$sourceX		= 0;
			$sourceY		= intval( ( $currentHeight - $currentWidth ) / 2 );
			$currentHeight	= $currentWidth;
		}

		$imageEngine 	= $config->get('imageengine');
		$magickPath		= $config->get( 'magickPath' );
		// Use imageMagick if available
		if( class_exists('Imagick') && ($imageEngine == 'auto' || $imageEngine == 'imagick' ) )
		{
			// Put the new image in temporary dest path, and move them using
			// Joomla API to ensure new folder is created
			$tempFilename = $jconfig->getValue('tmp_path'). DS . md5($destPath);

			$thumb = new Imagick();
			$thumb->readImage($srcPath);
			$thumb->cropThumbnailImage($destWidth, $destHeight);
			$thumb->writeImage($tempFilename);
			$thumb->clear();
			$thumb->destroy();

			// Move to the correct path
			JFile::move($tempFilename,$destPath);
			return true;
		}
		else if( !empty( $magickPath ) && !class_exists( 'Imagick' ) )
		{
			// Execute the command to resize. In windows, the commands are executed differently.
			if( JString::strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' )
			{
				$file		= rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert.exe';
				$command	= '"' . rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert.exe"';
			}
			else
			{
				$file		= rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert';
				$command	= '"' . rtrim( $config->get( 'magickPath' ) , '/' ) . DS . 'convert"';
			}


			if( JFile::exists( $file ) && function_exists( 'exec') )
			{
				$execute	= $command . ' -resize ' . $destWidth . 'x' . $destHeight . ' ' . $srcPath . ' ' . $destPath;
				exec( $execute );

				// Test if the files are created, otherwise we know the exec failed.
				if( JFile::exists( $destPath ) )
				{
					return true;
				}
			}
		}

		// IF all else fails, we try to use GD
		return self::resize( $srcPath , $destPath , $destType , $destWidth , $destHeight , $sourceX , $sourceY , $currentWidth , $currentHeight);
	}
}
?>