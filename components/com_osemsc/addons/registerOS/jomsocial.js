Ext.ns('oseMscAddon','oseMscAddon.basicParams');
oseMsc.msg = new Ext.App();
	oseMscAddon.jomsocial = function(){

	}
	oseMscAddon.jomsocial.prototype = {
		init: function()	{
		var fs = new Ext.form.FieldSet({
	    title: Joomla.JText._('Additional_Information')
	    ,labelWidth: 130
		,defaults: {width: 280}
	    ,border: false
	    ,items:[{
        	xtype: 'combo',
        	id:'combo',
        	allowBlank: false,
        	editable: false,
        	msgTarget : 'side',
        	fieldLabel: 'Gender',
        	hiddenName: 'field_2',
        	typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:true,
		    mode: 'local',
		    store: new Ext.data.ArrayStore({
		        id: 0,
		        fields: [
		            'vfield', 'dfield'
		        ],
		        data: [
		        	//['0', 'Select below'],
		        	['Male', 'Male'],
                    ['Female', 'Female']

		        ]
		    }),
		    valueField: 'vfield',
		    displayField: 'dfield',

	    },{
			fieldLabel:'Birthday',
			xtype:'datefield',
			editable: false,
			format: 'Y-m-d',
			width: 280,
			allowBlank: false,
			name:'field_3',
			msgTarget : 'side'
		},{
			fieldLabel:'About me',
			xtype:'textarea',
			width: 280,
			allowBlank: false,
			name:'field_4',
			msgTarget : 'side'

		},{
			fieldLabel:'Mobile phone',
			xtype:'textfield',
			width: 280,
			name:'field_6'
		},{
			fieldLabel:'Land phone',
			xtype:'textfield',
			width: 280,
			name:'field_7'
		},{
			fieldLabel:'Website',
			xtype: 'textfield',
			name: 'field_12',
			vtype:'url',
			allowBlank: false,
			width:280,
			msgTarget : 'side'
		}]

	});
		return fs;
	}
}