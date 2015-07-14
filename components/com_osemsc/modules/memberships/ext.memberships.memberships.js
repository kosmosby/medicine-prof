Ext.ns('oseMsc','oseMsc.memberships');
	oseMsc.msg = new Ext.App();

	/*
	oseMsc.memberships.gridStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	        }),
		  baseParams:{task: "getMemberships",limit: 20},
		  reader: new Ext.data.JsonReader({
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'title', type: 'string', mapping: 'treename'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'price', type: 'string', mapping: 'price'},
		    {name: 'image', type: 'string', mapping: 'image'},
		    {name: 'description', type: 'string', mapping: 'description'},
		  ]),
		  sortInfo:{field: 'id', direction: "ASC"},
		  autoLoad: {}
	});

	oseMsc.memberships.tpl = new Ext.XTemplate(
		'<table>',
	    '<tpl for=".">',
	    	'<tr>',
	        	'<td><img width="135" src="{image}">image</td>',
	        	'<td>',
	        	'<div>{name}</div>',
	        	'<div>{price}</div>',
	        	'<div>{period}</div>',
	        	'<div>{description}</div>',
	        	'</td>',
	        '</tr>',
	    '</tpl>',
	    '</table>'
	);


	oseMsc.memberships.panel = new Ext.Panel({
    	items: [
		    new Ext.DataView({
		        store: oseMsc.memberships.gridStore,
		        tpl: oseMsc.memberships.tpl,
		        autoHeight:true,
		        //multiSelect: true,
		        //overClass:'x-view-over',
		        itemSelector:'div.terms',
		        emptyText: 'No images to display',

		    }),

	    ]
    });
    */



