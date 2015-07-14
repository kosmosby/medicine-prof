<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Gallery
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


abstract class TheFactoryGalleryObject extends JObject
{
	var $imagelist = array();
    var $imageUrl=null;
    var $medium_width;
    var $medium_height;
    var $thumb_width;
    var $thumb_height;
    
    function __construct($imageUrl,$medium_width,$medium_height,$thumb_width,$thumb_height)
    {
        $this->imageUrl=$imageUrl;
        $this->medium_width=$medium_width;
        $this->medium_height=$medium_height;
        $this->thumb_width=$thumb_width;
        $this->thumb_height=$thumb_height;
    }
    
    function addImageList($imagelist)
    {
        if (!count($imagelist)) return;
        foreach ($imagelist as $image)
            $this->addImage($image);
    }
	
	function addImage($imagename)
	{
        if ($imagename)
            $this->imagelist[]=$imagename;
	}
	
	function clearImages()
	{
		$this->imagelist=array();
	}
	function writeThumbImage($imagenr)
	{
	    echo $this->getThumbImage($imagenr);
	}
	function writeMediumImage($imagenr)
	{
	    echo $this->getMediumImage($imagenr);
	}
    function getThumbImage($imagenr=0)
    {
		JHTML::_('behavior.modal');
        if(!isset($this->imagelist[$imagenr]) || !$this->imagelist[$imagenr]){
  			return '<img src="'.$this->imageUrl.'no_image.png" border="0" alt="'.JText::_("COM_BIDS_NO_IMAGE").'" />';
        }
        $img_small=$this->imageUrl.'resize_'.$this->imagelist[$imagenr];
        //$img_middle=$this->imageUrl.'middle_'.$this->imagelist[$imagenr];
        //$img_big=$this->imageUrl.$this->imagelist[$imagenr];

        //return "<a href='$img_big' class='modal'><img src='$img_small' border='0' alt=''></a>";
        return '<img src="'.$img_small.'" border="0" alt="" />';
    }
    function getMediumImage($imagenr=0)
    {
		JHTML::_('behavior.modal');
        if(!isset($this->imagelist[$imagenr]) || !$this->imagelist[$imagenr]){
  			return '<img src="'.$this->imageUrl.'/no_image.png" border="0" alt="'.JText::_("COM_BIDS_NO_IMAGE").'" width="'.$this->medium_width.'"/>';
        }
        $img_middle=$this->imageUrl.'/middle_'.$this->imagelist[$imagenr];
        $img_full=$this->imageUrl.'/'.$this->imagelist[$imagenr];
        return "<a href='$img_full' class='modal'><img src='$img_middle' border=0></a>";     
    }
	function writeGallery()
    {
	    echo $this->getGallery();
	}
	abstract function writeJS();
    abstract function getGallery();
    
}

?>
