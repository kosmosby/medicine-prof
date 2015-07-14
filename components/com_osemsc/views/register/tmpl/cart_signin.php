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
$session =& JFactory::getSession();

$cart = $session->get('osecart');
?>
<script type="text/javascript">
Ext.onReady(function()	{

	Ext.fly('oseregister').hide();
	Ext.fly('oselogin').hide();

	oseMsc.reg.signinForm = new Ext.Panel({
		renderTo: 'ose-reg-signin-panel'
		,border:false
		,layout: 'hbox'
		,autoHeight: true
		,autoWidth: true
		,defaults:{width:Ext.get('ose-reg-signin-panel').getWidth()/2,height: 300,border:false}
		,buttonAlign: 'left'
		,fbar: new Ext.Toolbar({
			items:[{
				text:'Back to Cart'
				,listeners: {
					click:function()	{
						Ext.Ajax.request({
							url: 'index.php?option=com_osemsc'
							,params:{ task:'backStep', step:'cart','controller':'register'}
							,callback: function()	{
								oseMsc.reg.panel.fireEvent('load');
							}
						})
					}
				}
			}]
		})
		,items:[{
			xtype: 'form'
			,defaults:{border:false}
			,items: [{
				id: 'ose-login-introduction'
				,items: [{
					xtype: 'box'
					,autoEl:{
						html: '<h1>Account registration</h1>'
					}
				},{
					contentEl:'instructions'
					,border:false
				}]
			}]
			,buttons: [{
				//text: 'Not a member yet? Register now!'
				text: Ext.fly('oseregister').dom.innerHTML
				,handler: function()	{
					Ext.Ajax.request({
						url: 'index.php?option=com_osemsc&controller=register'
						,params: {task: 'save',signin:'register'}
						,callback: function()	{
							oseMsc.reg.panel.fireEvent('load');
						}
					})
				}
			}]
		},{
			xtype: 'form'
			,ref: 'loginForm'
			,defaults:{border:false}
			,items: [{
				id: 'ose-login'
				,items:[{
					xtype: 'box'
					,autoEl:{
						html: '<h1>Existing user login</h1>'
					}
				},{
					xtype:'fieldset'
					,defaultType: 'textfield'
					,defaults:{allowBlank: false,msgTarget: 'side'}
					,items:[{
						fieldLabel: 'Username'
						,name: 'username'
					},{
						fieldLabel: 'Password'
						,name: 'password'
						,inputType:'password'
						,listeners: {
							specialkey: function(field, e){
			                    if (e.getKey() == e.ENTER) {
			                        oseMsc.reg.signinForm.loginForm.buttons[0].fireEvent('click')
			                    }
			                }
						}
					}]
				}]
			}]
			,buttons: [{
				text: Ext.fly('oselogin').dom.innerHTML
				,listeners:{
					click: function()	{
						oseMsc.reg.signinForm.loginForm.getForm().submit({
							url: 'index.php?option=com_osemsc&controller=register'
							,waitMsg: 'Loading...'
							,params: {task: 'save',signin:'login'}
							,success: function(form,action)	{
								//oseMsc.reg.panel.fireEvent('load');
								window.location.reload();
							}
							,failure: function(form,action)	{
								oseMsc.formFailureMB(form,action);
							}
						})
					}
				}
			}]
		}]
	})

})
</script>

<div id='thermometer'>
	<div id='image'>
		<img src ='/components/com_osemsc/assets/images/therm_cart_signin.gif' border='0'>
	</div>
	<div class='therm_sec'>
		<div class="text">
			<?php echo JText::_('Shopping Basket');?>
		</div>

		<div class="text">
			<span class='selected'>
			<?php echo JText::_('Sign In');?>
			</span>
		</div>
		<div class="text"><?php echo JText::_('Billing');?></div>
		<div class="text"><?php echo JText::_('Order Confirm');?></div>
	</div>
</div>

<div id="ose-reg-signin-panel"></div>

<ul id="instructions">
	<li ><?php echo JText::_("Choose a subscription plan"); ?></li>
	<li ><?php echo JText::_("Fill in billing information"); ?></li>
	<li ><?php echo JText::_("Confirm your order"); ?></li>
	<li ><?php echo JText::_("Become a member and access restricted content!"); ?></li>
</ul>


<div id="oselogin"><?php echo JText::_("Login"); ?></div>
<div id="oseregister"><?php echo JText::_("Not a member yet? Register now!"); ?></div>


<div class='ose-clear'></div>