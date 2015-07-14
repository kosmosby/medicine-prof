<?php
/**
 * @ version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.utilities.utility');

// Resize the given image to a dest path. Src must exist
// If original size is smaller, do not resize just make a copy
function imageResize($srcPath, $destPath, $destType, $destWidth, $destHeight, $sourceX	= 0, $sourceY	= 0, $currentWidth=0, $currentHeight=0)
{
	// See if we can grab image transparency
	$image				= imageOpen( $srcPath , $destType );
	$transparentIndex	= imagecolortransparent( $image );

	// Create new image resource
	$image_p			= ImageCreateTrueColor( $destWidth , $destHeight );
	$background			= ImageColorAllocate( $image_p , 255, 255, 255 ); 
	
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

	return JFile::write( $destPath , $output );
}

function imageResizep($srcPath, $destPath, $destType, $destWidth=0, $destHeight=0)
{ 
	list($currentWidth, $currentHeight) = getimagesize( $srcPath );	
	
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
	
	$imageEngine	= null;
	$magickPath		= null;

	// Use imageMagick if available
	if( class_exists('Imagick') && ($imageEngine == 'auto' || $imageEngine == 'imagick') )
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
	return imageResize($srcPath, $destPath, $destType, $destWidth, $destHeight);
}


function imageTypeToExt($type)
{
	$type = JString::strtolower($type);

	if( $type == 'image/png' || $type == 'image/x-png' )
	{
		return '.png';
	}
	elseif ( $type == 'image/gif')
	{
		return '.gif';
	}
	
	return '.jpg';
}

function imageOpen( $file , $type )
{

	// @rule: Test for JPG image extensions
	if( function_exists( 'imagecreatefromjpeg' ) && ( ( $type == 'image/jpg') || ( $type == 'image/jpeg' ) || ( $type == 'image/pjpeg' ) ) )
	{
		$im	= @imagecreatefromjpeg( $file );

		if( $im !== false ) { return $im; }
	}
	
	// @rule: Test for png image extensions
	if( function_exists( 'imagecreatefrompng' ) && ( ( $type == 'image/png') || ( $type == 'image/x-png' ) ) )
	{
		$im	= @imagecreatefrompng( $file );

		if( $im !== false ) { return $im; }
	}

	// @rule: Test for png image extensions
	if( function_exists( 'imagecreatefromgif' ) && ( ( $type == 'image/gif') ) )
	{
		$im	= @imagecreatefromgif( $file );

		if( $im !== false ) { return $im; }
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