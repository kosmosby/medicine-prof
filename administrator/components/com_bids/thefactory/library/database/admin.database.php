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
 * @subpackage: library
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryDatabase {

    static function getQuery() {
        $JTFapp = JTheFactoryApplication::getInstance();
        JLoader::register('JTheFactoryDatabaseQuery', dirname(__FILE__) . DS . 'helper' . DS .  'query.php');
        return new JTheFactoryDatabaseQuery;
    }
}
