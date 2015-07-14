<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/



class BidsHelper {

    static function FileName2ClassName($prefix,$filename,$suffix) {

        jimport('joomla.filesystem.file');
        $class_name= $prefix.ucfirst(strtolower(preg_replace('/\s/','_', JFile::stripExt($filename)))).$suffix;

        return $class_name;
    }

    static function LoadHelperClasses() {

        $helperfolder=JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers';
        
        $files=JFolder::files($helperfolder,'\.php$');

        foreach($files as $helperfile) {

            if ($helperfile==basename(__FILE__))
                continue;
            $class_name=self::FileName2ClassName('BidsHelper',$helperfile,'');
            JLoader::register($class_name,$helperfolder.DS.$helperfile);
        }

        $css = '.icon-48-bids {
                    background-image: url("../components/com_bids/images/auction_48.png");
                }';
        $document = JFactory::getDocument();
        $document->addStyleDeclaration($css);
    } 
}
