Ext.ns('oseMscs');

oseMscs.propertyAccordian = function()	{

}

oseMscs.propertyAccordian.prototype = {
		init: function()	{
			oseMscs.store = new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=memberships'
			            ,method: 'POST'
			      })
				  ,baseParams:{task: "getFullTree",limit: 20}
				  ,reader: new Ext.data.JsonReader({
				              // we tell the datastore where to get his data from
				    root: 'results',
				    totalProperty: 'total'
				  },[
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'title', type: 'string', mapping: 'treename'}
				    ,{name: 'name', type: 'string', mapping: 'alias'}
				    ,{name: 'total', type: 'string', mapping: 'total'}
				    ,{name: 'totalAct', type: 'string', mapping: 'totalAct'}
				    ,{name: 'totalExp', type: 'string', mapping: 'totalExp'}
				  ])
				  ,autoLoad:{}
			});

			oseMscs.tbar = new Ext.Toolbar({
			    items: [{
		    		ref:'addBtn',
		            iconCls: 'icon-user-add',
		            text: 'Add'
		        },{
		        	ref: 'editBtn',
		            iconCls: 'icon-user-edit',
		            text: 'Edit',
		            disabled: true
		        },{
		        	ref: 'removeBtn',
		            iconCls: 'icon-user-delete',
		            text: 'Remove',
		            disabled: true

		        }]
			});

			oseMscs.gridSm = new Ext.grid.CheckboxSelectionModel({
				singleSelect:false
				,listeners: {
					selectionchange: function(sm)	{
						oseMscs.grid.getTopToolbar().removeBtn.setDisabled(sm.getCount() < 1);
						if(sm.getCount() > 0)	{
							oseMscs.propertyAccordian.getEl().unmask();
						}	else	{
							oseMscs.propertyAccordian.getEl().mask(Joomla.JText._('PLEASE_SELECT_A_MEMBERSHIP_FIRST'));
						}
						//oseMsc.edit.form.getTopToolbar().saveBtn.setDisabled(sm.getCount() < 1);
					}
				}
			});

			oseMscs.grid = new Ext.grid.GridPanel({
				title: Joomla.JText._('Membership_List'),
				store: oseMscs.store,
				region: 'west',
				width: 500,
				height: 500,
				margins:'5 3 5 5',

				colModel: new Ext.grid.ColumnModel({
			        defaults: {
			            sortable: false
			        },
			        columns: [
			        	oseMscs.gridSm
			        	,new Ext.grid.RowNumberer({header:'#'})
			            ,{id: 'id', header: Joomla.JText._('ID'),  hidden: false, dataIndex: 'id', width: 20}
			            ,{header: Joomla.JText._('Title'), dataIndex: 'title',id:'title'}
			            ,{header: Joomla.JText._('Total'), dataIndex: 'total', width: 50,align:'center'}
			            ,{header: Joomla.JText._('Active'), dataIndex: 'totalAct', width: 50,align:'center'}
			            ,{header: Joomla.JText._('Expired'), dataIndex: 'totalExp', width: 50,align:'center'}
			        ]
			    }),
				autoExpandColumn:'title',
			    //viewConfig: {forceFit: true},
			    sm: oseMscs.gridSm,
			    bbar:new Ext.PagingToolbar({
		    		pageSize: 20,
		    		store: oseMscs.store,
		    		displayInfo: true,
				    displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				    emptyMsg: Joomla.JText._("No_topics_to_display")

			    })

				,tbar: new Ext.Panel({
					width : '90%'

					,layout: 'toolbar'
					,autoHeight: true
					,border: false
					,bodyStyle:'padding: 2px; margin-top: 2px;margin-bottom: 2px'
					,items:[{
						xtype: 'button'
						,text: Joomla.JText._('New')
						,ref: 'newBtn'
					},{
						xtype: 'button'
						,text: Joomla.JText._('Remove')
						,ref: 'removeBtn'
						,disabled: true
					}]

				})
			});

			return oseMscs.propertyAccordian = new Ext.TabPanel({
				region:'center'
				,id: 'osemsc-property'
				,margins:'5 5 5 3'
				,width: 600
				//,layout: 'accordion'
				,activeTab: 0
				//,items:[]
				,listeners: {
					render: function(p)	{
						//p.getEl().mask('Please select a membership first!');

					}
				}
			});
		}
}
	