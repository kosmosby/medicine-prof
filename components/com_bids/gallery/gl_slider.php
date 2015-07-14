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

require_once(dirname(__FILE__).DS."gallery.php");

class gl_slider extends TheFactoryGalleryObject
{
	function writeJS()
	{
        $jsUrl=JURI::root().'components/'.APP_EXTENSION.'/gallery/js';
        $doc = JFactory::getDocument();
        $doc->addScript($jsUrl."/jquery.js");
        $doc->addScript($jsUrl."/jquery.noconflict.js");
        $doc->addScript($jsUrl."/picture-slider.js");
        $doc->addStyleSheet($jsUrl."/picture-slider.css");
	}
	
	function getGallery()
	{

	    if (count($this->imagelist)>1)
        {
            $this->writeJS();
            $nr=count($this->imagelist);
            $doc = JFactory::getDocument();
	        $img='<div id="picture-slider" style="width: '.$this->medium_width.'px; height: '.$this->medium_height.'px;"></div>';
            $script="window.addEvent('domready', function() {
                    if($('picture-slider'))
                        var ps = new PictureSlider($('picture-slider'), [
                ";
            for($i=0;$i<$nr;$i++){
                $script.="{src:'$this->imageUrl/middle_{$this->imagelist[$i]}'}".(($i<$nr-1)?',':'')."\n";    
            }

            $script.="]);\n});";
            
            $doc->addScriptDeclaration($script);
            
	    }
        elseif(count($this->imagelist)==1) 
        {
    		JHTML::_('behavior.modal');
           	$img= '<a href="'.$this->imageUrl.'/'.$this->imagelist[0].'" class="modal">
                        <img src="'.$this->imageUrl.'/middle_'.$this->imagelist[0].'" border=0 />
                    </a>';
	    }else{
	        $img='<div style="width:180px;"><img src="'.$this->imageUrl.'/no_image.png" border="0" /></div>';
	    }
	    return $img;
	}
	
}

?>
