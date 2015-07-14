Ext.ns('oseMscAddon');
	
	

	//
	// Addon Msc Panel
	//
	oseMscAddon.company_switch = new Ext.form.FieldSet({
		title: 'Company Switch',
 		labelWidth: 150,
 		defaults: {width: 300,msgTarget : 'side'},
		
	    items:[{
	    	xtype: 'radiogroup'
	    	,ref: 'rg'
            ,fieldLabel: 'Company'
            ,width: 500
            ,fieldLabel: 'Payment Mode'
            ,hiddenName: 'onestep_payment_mode'
        	,defaults: {xtype: 'radio', name: 'onestep_payment_mode'}
		    ,items:[
		    	{boxLabel: 'Corporate', inputValue: 'institute', checked: true}
		    	,{boxLabel: 'Individual', inputValue: 'individual'}
		    ]
        }]
        
	})
	
	
Ext.onReady(function(){
	oseMscAddon.company_switch.rg.on('change' , function( rg, checked )	{
		if(checked)	{
			oseMscAddon.company.setVisible(checked.getGroupValue() == 'institute' );
			oseMscAddon.company.setDisabled(checked.getGroupValue() == 'individual');
			
			Ext.each(oseMscAddon.company.findByType('textfield'),function(item,i,all){
				item.setDisabled(checked.getGroupValue() == 'individual');
			});
		}	
		
	})
	
	oseMscAddon.company_switch.rg.on('render' , function( c )	{
		c.fireEvent('change');
	})
});