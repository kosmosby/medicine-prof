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
 * @subpackage: category
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryTableCategory extends JTable {
	var $id                = null;
	var $catname           = null;
	var $description       = null;
	var $parent            = null;
	var $hash              = null;
	var $ordering          = null;

	function __construct( &$db )
	{
        $myApp=JTheFactoryApplication::getInstance();
		parent::__construct($myApp->getIniValue('table','categories'),'id',$db);
	}
}


?>
