<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
?>
<style>
div.pagetitle {
	height:90px;
	width:427px;
}

.icon-48-awdwallproc{

	background-image:url('components/com_awdwall/images/Jomwall-Logo.jpg');

	font-size:0px!important;

	line-height:88px!important;

	height:90px;

}

</style>

<form name="adminForm" id="adminForm" action="" method="post" enctype="multipart/form-data">
	
	<p class="uploadtheme"><?php echo JText::_('Upload theme');?>&nbsp;<input type="file" name="zip_file" size="35" /></p>
	
	<input type="hidden" name="option" value="com_awdwall" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="uploadthemes" />
</form>

