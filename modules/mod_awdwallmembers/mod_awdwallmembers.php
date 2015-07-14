<?php
/**
 * @package    JomWALL -Joomla
 * @subpackage 
 * @link http://www.AWDsolution.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * Shows the avatar of jomwall user
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
error_reporting(1);

// Include the syndicate functions only once
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'helpers' . DS . 'user.php');
require_once( dirname(__FILE__).DS.'helper.php' );
 
$rows = modAwdwallmembersHelper::getMembers( $params );
require( JModuleHelper::getLayoutPath( 'mod_awdwallmembers' ) );
?>
