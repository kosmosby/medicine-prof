<?php
/**
 * @version     5.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author        Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
 */
defined('_JEXEC') or die("Direct Access Not Allowed");
$user = JFactory::getUser();
$Itemid = JRequest::getVar("Itemid");
$params = JComponentHelper::getParams('com_osemsc');
if ($user->guest) {
	$user_name = 'Guest';
} else {
	$user_name = $user->name;
}
oseHTML::script(OSEMSCFOLDER . '/views/member/js/js.member.os.js', '1.5');
echo $this->initJs();
echo '<script type="text/javascript">';
echo 'Ext.onReady(function(){';
echo "oseMemMsc.loadAddon('member_user',\"" . JText::_('MEMBER_USER_ACCOUNT') . "\").render('ose-member_user');";
if (count($this->companyAddons) > 0) {
	echo "oseMemMsc.loadAddon('member_company',\"" . JText::_('COMPANY_INFORMATION') . "\").render('ose-member_company');";
}
echo "oseMemMsc.loadAddon('member_billing',\"" . JText::_('BILLING_INFORMATION') . "\").render('ose-member_billing');";
echo "oseMemMsc.loadAddon('member_msc',\"" . JText::_('MY_MEMBERSHIP') . "\").render('ose-member_msc');";
echo '})';
echo '</script>';
?>
<?php
if ($this->menuParams->get('show_page_heading')) {
?>
		<div class='componentheading <?php echo $this->menuParams->get('pageclass_sfx'); ?>'><?php echo $this->menuParams->get('page_heading'); ?></div>
<?php
}
?>
<div id="ose-my-account">
<p class="hello"><strong><?php echo JText::_("HELLO") . " " . $user_name; ?></strong></p>
<p class="notice"><?php echo JText::_("FROM_YOUR_ACCOUNT_DASHBOARD_YOU_HAVE_THE_ABILITY_TO_VIEW_A_SNAPSHOT_OF_YOUR_RECENT_ACCOUNT_ACTIVITY_AND_UPDATE_YOUR_ACCOUNT_INFORMATION");
echo JText::_("SELECT_A_LINK_BELOW_TO_VIEW_OR_EDIT_INFORMATION"); ?></p>
<div id="ose-account-menu">
	<div id="ose-member_user"></div>
	<div id="ose-member_company"></div>
	<div id="ose-member_billing"></div>
	<div id="ose-member_msc"></div>
</div>
</div>
<div class='ose-clear'></div>
<?php include(JPATH_COMPONENT . DS . "views" . DS . "footer.php"); ?>