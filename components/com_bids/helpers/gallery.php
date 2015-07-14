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
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class BidsHelperGallery{

	static function &getGalleryPlugin()
    {
        static $gallery;
        if (isset($gallery)&&is_object($gallery))
            return $gallery;
            	   
        $cfg= BidsHelperTools::getConfig();
		$gallery_name = "gl_".$cfg->bid_opt_gallery;
		require_once(JPATH_COMPONENT_SITE.DS."gallery".DS."$gallery_name.php");
        
		$gallery = new $gallery_name( AUCTION_PICTURES,
            $cfg->bid_opt_medium_width,
            $cfg->bid_opt_medium_height,
            $cfg->bid_opt_thumb_width,
            $cfg->bid_opt_thumb_height
         );
		return $gallery;
	}

}