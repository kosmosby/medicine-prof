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

class gl_lytebox extends TheFactoryGalleryObject
{
	function writeJS()
	{
        JHTML::_('behavior.modal');

        JHtml::script(JURI::root().'components/com_bids/js/jquery/jquery.js');
        JHtml::script(JURI::root().'components/com_bids/js/jquery/jquery.noconflict.js');

        JHtml::script( JURI::root().'components/'.APP_EXTENSION.'/gallery/js/jquery.jcarousel.js' );
        JHtml::stylesheet( JURI::root().'components/'.APP_EXTENSION.'/gallery/js/jquery.jcarousel.css');
        JHtml::stylesheet( JURI::root().'components/'.APP_EXTENSION.'/gallery/js/skin.css' );

        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration(
            ".jcarousel-skin-tango.jcarousel-container-horizontal {
                width: ".$this->medium_width."px;
                padding: 20px 40px;

            }
            .jcarousel-skin-tango .jcarousel-clip-horizontal {
                width:  ".$this->medium_width."px;
                height: ".$this->medium_height."px;
            }
            .jcarousel-skin-tango .jcarousel-item {
                width: ".$this->medium_width."px;
                height: ".$this->medium_height."px;
            }"
        );
	}

	function getGallery()
	{
		$img = "";
	    if (count($this->imagelist)>1)
        {
            $this->writeJS();
            $nr=count($this->imagelist);

            $doc = JFactory::getDocument();
            $doc->addScriptDeclaration("
                jQueryBids(document).ready(function() {
                    jQueryBids('#mycarousel').jcarousel({
                        size: $nr,
                        scroll: 1
                    });
                });
            ");
	        $img.='<ul id="mycarousel" class="jcarousel-skin-tango" >';
	        for($i=0;$i<count($this->imagelist);$i++){
	            $img.='<li style="background-image:url(); list-style:none;width:'.$this->medium_width.'px; text-align:center;">'
	            	 .'<a href="'.$this->imageUrl.'/'.$this->imagelist[$i].'" class="modal">'
	            	 .'<img src="'.$this->imageUrl.'/middle_'.$this->imagelist[$i].'" border="0" />'
	            	 ."</a></li>\n";
	        }
            $img.='</ul>';
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
