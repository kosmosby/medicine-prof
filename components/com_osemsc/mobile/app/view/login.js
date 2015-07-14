/*
 * File: app/view/login.js
 *
 * This file was generated by Sencha Architect version 2.1.0.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Sencha Touch 2.0.x library, under independent license.
 * License of Sencha Architect does not include license for Sencha Touch 2.0.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('MyApp.view.login', {
    extend: 'Ext.form.Panel',
    alias: 'widget.loginform',

    config: {
        items: [
            {
                xtype: 'toolbar',
                docked: 'bottom',
                items: [
                    {
                        xtype: 'button',
                        itemId: 'register',
                        text: 'Not a member? Register Now!'
                    },
                    {
                        xtype: 'spacer'
                    },
                    {
                        xtype: 'button',
                        itemId: 'mybutton',
                        ui: 'confirm',
                        text: 'Login'
                    }
                ]
            },
            {
                xtype: 'textfield',
                label: 'Username',
                name: 'username'
            },
            {
                xtype: 'passwordfield',
                label: 'Password',
                name:'password'
            }
        ],
        listeners: [
            {
                fn: 'onRegisterTap',
                event: 'tap',
                delegate: '#register'
            },
            {
                fn: 'onMybuttonTap',
                event: 'tap',
                delegate: '#mybutton'
            }
        ]
    },

    onRegisterTap: function(button, e, options) {
        window.location=getCurrentUrl()+"index.php?option=com_osemsc&view=register";
    },

    onMybuttonTap: function(button, e, options) {
        var fp = button.up('loginform');
        fp.submit({
            url: getCurrentUrl()+'index.php?option=com_osemsc',
            params: {controller: 'register',task:'login'},
            success: function(form,result){
            	var msg = result;
            	Ext.Viewport.setMasked({
            		xtype: 'loadmask'
            	});
            	//Ext.Msg.wait(Joomla.JText._('Redirecting_please_wait'),Joomla.JText._('Login_Successfully'));
				window.location = msg.returnUrl;
            },
            failure: function(form,result){
            	var msg = result;
            	Ext.Msg.alert(msg.title, msg.content, function(){});
            }
        })
    }

});