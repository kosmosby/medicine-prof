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
$user= JFactory :: getUser();
$Itemid= JRequest :: getVar("Itemid");
$params= JComponentHelper :: getParams('com_osemsc');
if($user->guest) {
	$user_name= 'Guest';
} else {
	$user_name= $user->name;
}
oseHTML :: script(OSEMSCFOLDER.'/views/member/js/js.member.os.js', '1.5');
oseHTML :: script(oseMscMethods :: getJsModPath('staff', 'member'), '1.5');
?>
<?php
	if($this->menuParams->get('show_page_heading') || $this->menuParams->get('show_page_title'))
	{
?>
		<div class='componentheading <?php echo $this->menuParams->get('pageclass_sfx'); ?>'><?php echo $this->menuParams->get('page_heading'); ?></div>
<?php
	}
?>
<script type="text/javascript">
Ext.ns('oseMemMsc');
Ext.onReady(function(){
	Ext.QuickTips.init();
	oseMemMsc.success = function(form,action)	{
        var msg = action.result;
		oseMemMsc.msg.setAlert(msg.title,msg.content);
	}
	oseMemMsc.failure = function(form,action)	{
		if (action.failureType === Ext.form.Action.CLIENT_INVALID){
			oseMemMsc.msg.setAlert('Notice','Pleas Check The Notice In The Form');
        }

		if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
           Ext.Msg.alert('Error',
            'Status:'+action.response.status+': '+
            action.response.statusText);

        }

        if (action.failureType === Ext.form.Action.SERVER_INVALID){
            var msg = action.result;
			oseMemMsc.msg.setAlert(msg.title,msg.content);
        }
	}
	oseMemMsc.loadAddon('member_user','User Account').render('ose-member_user');
	oseMemMsc.loadAddon('member_company','Company Information').render('ose-member_company');
	oseMemMsc.loadAddon('member_billing','Billing Information').render('ose-member_billing');
	oseMemMsc.loadAddon('member_msc','My Membership').render('ose-member_msc');
	var fieldset = new Ext.form.FieldSet({
		title: '<?php echo JText::_('Member Menu');?>',
        //collapsible: true,
        autoHeight:true,
        contentEl:'ose-account-menu',
        renderTo: 'ose-account-menus'
	});

});
</script>
<div id="ose-my-account">
<p class="hello"><strong><?php echo JText::_("Hello")." ". $user_name;?></strong></p>
<p class="notice"><?php echo JText::_("From your Account Dashboard you have the ability to view a snapshot of your recent account activities and update your account information. Please select a menu to view or edit information.");?></p>
<div id="ose-account-menu">
	<div id="ose-member_user"></div>
	<div id="ose-member_company"></div>
	<div id="ose-member_billing"></div>
	<div id="ose-member_msc"></div>
</div>
<div id="my-memberships"></div>
</div>
<div class='ose-clear'></div>
<?php include(JPATH_COMPONENT.DS."views".DS."footer.php"); ?>