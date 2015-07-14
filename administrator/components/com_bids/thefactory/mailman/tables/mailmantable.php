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
 * @subpackage: mailman
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryMailmanTable extends JTable
{
    var $id;
    var $mail_type;
    var $content;
    var $subject;
    var $enabled;
	function __construct(&$db)
	{
		parent::__construct( '#__'.APP_PREFIX.'_mails', 'mail_type', $db );
	}
	function store( $updateNulls=false ) {
		$k = $this->getKeyName();
        $db= $this->getDbo();
		if ($this->$k) {
			$ret = $db->updateObject($this->getTableName(), $this, $k, $updateNulls);
		} else {
			$ret = $db->insertObject($this->getTableName(), $this, $k);
		}

		if (!$ret) {
			$this->setError(strtolower(get_class($this))."::store failed <br />" . $db->getErrorMsg());
			return false;
		} else {
			return true;
		}
	}
 
}

?>
