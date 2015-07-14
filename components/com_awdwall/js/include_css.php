<?php
/**
 * @version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>
<?php 
	$config 		= &JComponentHelper::getParams('com_awdwall');
	$template 		= $config->get('temp', 'blue');
	
?>
	<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/style_<?php echo $template; ?>.css"  type="text/css" />
