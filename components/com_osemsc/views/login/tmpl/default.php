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
if ($user->guest) {
	oseHTML::script(oseMscMethods::getJsModPath('login', 'login'), '1.5');
} else {
	oseHTML::script(oseMscMethods::getJsModPath('logout', 'login'), '1.5');
}
$link = JRoute::_('');
?>
<?php
if ($this->menuParams->get('show_page_heading')) {
?>
		<div class='componentheading <?php echo $this->menuParams->get('pageclass_sfx'); ?>'><?php echo $this->menuParams->get('page_heading'); ?></div>
<?php
}
?>
<?php if ($user->guest) : ?>
<?php
	if ($this->enable_fblogin == true) {
?>
<script>
  window.fbAsyncInit = function() {
	FB.init({
	  appId      : '<?php echo $this->facebookapiid; ?>',
	  status     : true, 
	  cookie     : true, 
	  xfbml      : true  
	});
  };
  // Load the SDK Asynchronously
  (function(d){
	 var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
	 if (d.getElementById(id)) {return;}
	 js = d.createElement('script'); js.id = id; js.async = true;
	 js.src = "//connect.facebook.net/en_US/all.js";
	 ref.parentNode.insertBefore(js, ref);
   }(document));
	function loginFB()
	{
		FB.login(function(response) {
			if (response.status=='connected')
			{	
				var username = 'Facebook';
				var password = 'Facebook';
				Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_Wait'));
				Ext.Ajax.request({
					url: 'index.php?option=com_osemsc'
					,params:{controller:'register', view:'register', task: 'login', username: username, password: password}
					,callback: function(el,success,response,opt)	{
						Ext.Msg.hide();
						var msg = Ext.decode(response.responseText);
						if(msg.success)	{
							oseMsc.reload()
						}	else	{
							Ext.Msg.alert(msg.title,msg.content);
						}
					}
				})
			}
			else
			{
				Ext.Msg.alert('Error','Cannot connect to Facebook')
			}	
		}, {scope: 'email'});	
	} 
  
</script>
<?php
	}
?>
<script type="text/javascript">
Ext.onReady(function() {
	Ext.QuickTips.init();
	oseMsc.login.form = new oseMsc.login.login();
	oseMsc.login.formInit = oseMsc.login.form.init();
	oseMsc.login.panel = new Ext.Panel({
		title: Joomla.JText._('Members_Login')
		,layout: 'column'
		,border: false
		,defaults: {border: false}
		,items:[{
			columnWidth: .5
			,items: [{
				id: 'ose-login-introduction'
				,border:false
				,items: [{
					xtype: 'box'
					,autoEl:{
						html: '<h1>'+Joomla.JText._('Account_registration')+'</h1>	'
					}
				},{
					contentEl:'instructions'
					,border:false
				}]
			}]
			,buttons: [{
				xtype: 'box'
				,contentEl: 'oseregister'
			}]
		},{
			columnWidth: .5
			,bodyStyle: 'padding: 0px'
			,border:false
			,items: [{
				id: 'ose-login'
				,border:false
				,items:[{
					xtype: 'box'
					,autoEl:{
						html: '<h1>'+Joomla.JText._('Existing_user_login')+'</h1>'
					}
				}
				,oseMsc.login.formInit
				]
			}]
			,buttons: [{
				xtype: 'box'
				,contentEl: 'oselogin'
			}
			<?php
				if ($this->enable_fblogin == true) {
					echo ",{
							 xtype: 'box'
							,contentEl: 'fbButton'
						  }";
				}
			?>
  			]
		}]
		,renderTo: 'ose-login-box'
	});
	Ext.get('oseregister').on('click', function(){
		window.location = '<?php echo str_replace("&amp;", "&", JRoute::_(JURI::root() . 'index.php?option=com_osemsc&view=register')); ?>';
	});
	Ext.get('oselogin').on('click', function(){
		oseMsc.login.formInit.fireEvent('login')
	});
	oseMsc.login.formInit.getForm().findField('username').focus();
});
</script>
<div id='ose-login-box'></div>
<ul id="instructions">
	<li ><?php echo JText::_("CHOOSE_A_SUBSCRIPTION_PLAN"); ?></li>
	<li ><?php echo JText::_("FILL_IN_BILLING_INFORMATION"); ?></li>
	<li ><?php echo JText::_("CONFIRM_YOUR_ORDER"); ?></li>
	<li ><?php echo JText::_("BECOME_A_MEMBER_AND_ACCESS_RESTRICTED_CONTENT"); ?></li>
</ul>
<div id="ose-login-form"></div>
<div class="oseloginbutton">
	<button class="ose-button" id ="oselogin"><?php echo JText::_("LOGIN"); ?></button>
	<?php
	if ($this->enable_fblogin == true) {
	?>
	<button class="ose-button fbButton" id ="fbButton" type="button" onClick="loginFB();"><?php echo JText::_("FBLOGIN"); ?></button>
	<?php
	}
	?>
</div>
<div class="osejoinbutton">
	<button class="ose-button" id ="oseregister"><?php echo JText::_("NOT_A_MEMBER_YET_REGISTER_NOW"); ?></button>
</div>
<div class="oseforgetpass">
	<ul>
		<?php
	$remindlink = (JOOMLA16 == true) ? "index.php?option=com_users&view=remind" : "index.php?option=com_user&view=remind";
	$resetlink = (JOOMLA16 == true) ? "index.php?option=com_users&view=reset" : "index.php?option=com_user&view=reset";
	$remindlink = str_replace("&amp;", "&", JRoute::_($remindlink));
	$resetlink = str_replace("&amp;", "&", JRoute::_($resetlink));
	echo "<li><a href = '{$resetlink}'>" . JText::_("FORGOT_YOUR_PASSWORD") . "</a></li>";
	echo "<li><a href = '{$remindlink}'>" . JText::_("FORGOT_YOUR_USERNAME") . "</a></li>";
		?>
	</ul>
</div>
<div class='ose-clear'></div>
<?php else : ?>
<script type="text/javascript">
Ext.onReady(function()	{
	oseMsc.login.logout.form = new oseMsc.login.logout();
	oseMsc.login.logout.formInit = oseMsc.login.logout.form.init();
	oseMsc.login.logout.formInit.render('ose-logout');
})
</script>
<div id="ose-logout"></div>
<?php endif; ?>