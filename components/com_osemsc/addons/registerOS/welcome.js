Ext.ns('oseMscAddon');

	oseMscAddon.welcome = function(mf){

	}

	oseMscAddon.welcome.prototype = {
		init: function()	{
			var juserFieldset = new Ext.Panel({
		 		defaultType: 'textfield',
		 		labelWidth: 130,
		 		defaults: {width: 280,msgTarget : 'side'},
		 		items:[{
		 			xtype: 'box'
		 			,autoEl: {
		 				tag: 'div'
		 				,class: 'osemsc-welcome'
		 				,html: 'Welcome, '+oseMsc.reg.bill.result.name
		 			}
		 			,height: 100
		 		}] 
		 	});

		 	return juserFieldset
		}
	}