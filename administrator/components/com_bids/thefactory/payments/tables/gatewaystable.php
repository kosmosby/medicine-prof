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
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


class JTheFactoryGatewaysTable extends JTable {
    var $id=null;
    var $paysystem=null;
    var $classname=null;
    var $enabled=null;
    var $params=null;
    var $ordering=null;
    var $isdefault=null;

    function __construct( &$db ) {
        parent::__construct( '#__'.APP_PREFIX.'_paysystems', 'id', $db );
    }
}
