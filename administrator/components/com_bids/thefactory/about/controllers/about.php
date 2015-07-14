<?php 
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: about
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


class JTheFactoryAboutController extends JTheFactoryController
{
    var $name='About';
    var $_name='About';
    /**
        	 *         Field related Tasks / Admin Section
        	 */
    function Main()
    {
        $MyApp= JTheFactoryApplication::getInstance();

		$filename=$MyApp->getIniValue('version_root').'/'.APP_EXTENSION.".xml";
        $doc=JTheFactoryHelper::remote_read_url($filename);
        $xml= JFactory::getXML($doc,false);

        $view=$this->getView('main');  

        if(version_compare(COMPONENT_VERSION,(string)$xml->latestversion)>=0) {
            $view->assign('isnew_version', false);
        } else {
            $view->assign('isnew_version', true);
        }

        $view->assign('latestversion',(string)$xml->latestversion);
        $view->assign('versionhistory',(string)$xml->versionhistory);
        $view->assign('downloadlink',(string)$xml->downloadlink);
        $view->assign('aboutfactory',html_entity_decode((string)$xml->aboutfactory));
        $view->assign('otherproducts',html_entity_decode((string)$xml->otherproducts));

        $view->assign('build',(string)$xml->build);

        $view->display();
    }

}
