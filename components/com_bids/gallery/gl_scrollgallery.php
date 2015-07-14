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

class gl_scrollgallery extends TheFactoryGalleryObject
{
	function writeJS()
	{
        $jsUrl=JURI::root().'components/'.APP_EXTENSION.'/gallery/js';
        $doc = JFactory::getDocument();
        $doc->addScript($jsUrl."/jquery.js");
        $doc->addScript($jsUrl."/jquery.noconflict.js");
        $doc->addScript($jsUrl."/scrollGallery.js");
        $doc->addStyleSheet($jsUrl."/scrollGallery.css");
        $doc->addStyleDeclaration("
            #gallery{
                width:".($this->medium_width+50)."px; 
            }
            #imageareaContent img{
                width:".($this->medium_width+20)."px; 
            }
        
        ");
        $doc->addScriptDeclaration("
            window.addEvent('domready', function() {
                if ($('imagearea') && $('thumbarea')) //make sure there are any images  
                    var scrollGalleryObj = new scrollGallery({
        				start:0
        			});
            });
        ");
	}

	function getGallery()
	{
		$img = "";
	    if (count($this->imagelist)>1)
        {
            $this->writeJS();
            $nr=count($this->imagelist);
	        $img='<div id="gallery">';
            $thumbs=array();
            $images=array();
            for($i=0;$i<$nr;$i++){
                $thumbs[]="<img src='{$this->imageUrl}/resize_{$this->imagelist[$i]}' width='{$this->thumb_width}' height='{$this->thumb_height}'/>";
                $images[]="<img src='{$this->imageUrl}/middle_{$this->imagelist[$i]}'/>";
            }
            $img.="
                <div id='scrollGalleryHead'>
					<div id='thumbarea'>
						<div id='thumbareaContent'>
                        ".implode("\n",$thumbs)."
						</div> 
					</div>
				</div>
            ";
            $img.="
                <div id='scrollGalleryFoot'>
					<div id='imagearea'>
						<div id='imageareaContent'>
                        ".implode("\n",$images)."
						</div> 
					</div>
				</div>
            ";
            $img.="</div>";            
	    }
        elseif(count($this->imagelist)==1) 
        {
    		JHTML::_('behavior.modal');
           	$img= '<a href="'.$this->imageUrl.'/'.$this->imagelist[0].'" class="modal">
                        <img src="'.$this->imageUrl.'/middle_'.$this->imagelist[0].'" border=0 />
                    </a>';
	    }else{
	        $img='<img src="'.$this->imageUrl.'/no_image.png" border="0" />';
	    }
	    return $img;
	}
	
}

?>
