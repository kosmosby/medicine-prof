/*!
 * Ext JS Library 3.2.1
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.ns('Ext.ux.grid');


Ext.ux.grid.limit = Ext.extend(Ext.util.Observable, {
    
    init : function(tb){
    	//this.grid = grid;
        this.tb = tb;//grid.getBottomToolbar();
       
		this.tb.insert(11,{
			html: ' record(s)'
			,xtype:'box'
		})
		
		this.limitField = new Ext.form.NumberField({
			name: 'showlimit'
			,width: '20'
			,value: 25
		})
		
		this.tb.insert(11,this.limitField)
		this.tb.insert(11,{
			html: ' Show'
			,xtype:'box'
		})
		
		this.limitField.on('blur', this.onBTBlur, this);
		this.limitField.on('specialkey', function(field,e){
			if (e.getKey() == e.ENTER) {
                this.onBTEnter()
            }
		}, this);
		
        this.tb.on('render', this.onBTRender, this);
    }

    // @private
    ,onBTRender: function() {
        this.tb.pageSize = this.limitField.getValue();
        this.tb.store.setBaseParam('limit',this.tb.pageSize);
    }
    
    ,onBTBlur: function() {
        this.tb.pageSize = this.limitField.getValue();
        this.tb.store.setBaseParam('limit',this.tb.pageSize);
    }
    
    ,onBTEnter: function()	{
    	this.onBTBlur()
    	this.tb.doRefresh()
    }
});

Ext.preg('gridLimit', Ext.ux.grid.limit);

//backwards compat
Ext.grid.limit = Ext.ux.grid.limit;