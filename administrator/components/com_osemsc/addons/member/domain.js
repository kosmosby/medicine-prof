Ext.ns('oseMscAddon');

var oseMscAddonDomain = function(mscId,userId)	{
	this.mscId = mscId;
	this.userId = userId;
	this.buildForm = function()	{
		var editForm = new Ext.FormPanel({
			border: false
			,bodyStyle: 'padding: 10px'
			,labelWidth: 200
			,defaults: {width: 230,msgTarget:'side'}
			,height: 200
			,items:[{
	            xtype:'hidden'
	            ,name: 'id'
	        },{
	            xtype:'hidden'
	            ,name: 'msc_id'
	            ,value:mscId
	        },{
	            xtype:'hidden'
	            ,name: 'user_id'
	            ,value: userId
	        },{
	            xtype:'textfield'
	            ,fieldLabel: 'Domain'
	            ,name: 'domain'
	            ,allowBlank: false
	        },{
	        	xtype: 'datefield'
	        	,name: 'start_date'
	        	,fieldLabel: 'Start Date'
	        	,format: 'Y-m-d'
	        },{
	        	xtype: 'datefield'
	        	,name: 'end_date'
	        	,fieldLabel: 'Expired Date'
	        	,format: 'Y-m-d'
	        }]
		    ,buttons: [{
		    	text: 'Save'
		    	,handler: function()	{
		    		editForm.getForm().submit({
		    			url: 'index.php?option=com_osemsc'
		    			,params:{
		    				task: "action"
							,action: "member.domain.save"
							,controller: "members"
						}
		    			,success: function(form,action)	{
		    				oseMsc.formSuccess(form, action);
		    				this.grid.getStore().reload();
		    				this.grid.getView().refresh();
		    				editForm.ownerCt.close();
		    			}
		    			,failure: function(form,action)	{
		    				 oseMsc.formFailure(form, action);
		    			}
		    			,scope:this
		    		})
		    	}
		    	,scope: this
		    }]
		})
		return editForm;
	}
	
	this.buildWin = function(form,title)	{
		new Ext.Window({
			title: title
			,width: 530
			,modal: true
			,autoHeight: true
			,items:[form]
			,autoScroll: true
			//,height:500
		}).show().alignTo(Ext.getBody(),'t-t');
	}
}

oseMscAddonDomain.prototype = {
	buildGrid: function()	{
		var userId = this.userId;
		var mscId = this.mscId;
		var sm = new Ext.grid.CheckboxSelectionModel({
			//singleSelect:true
			listeners: {
				selectionchange: function(sm)	{
					sm.grid.getTopToolbar().getComponent('btnDel').setDisabled(sm.getCount() < 1);
				}
			}
		});
	
		var cm = new Ext.grid.ColumnModel({
	        defaults: {
	            sortable: false, width: 150
	        },
	        columns: [
	        	sm
	        	,new Ext.grid.RowNumberer({header:'#'})
	            ,{id: 'id', header: 'ID',  hidden: true, dataIndex: 'id', width: 20}
			    ,{id: 'domain', header: 'Domain', dataIndex: 'domain'}
			    ,{id: 'start_date', header: 'Start Date', dataIndex: 'start_date'}
			    ,{id: 'end_date', header: 'End Date', dataIndex: 'end_date'}
	            ,{
	            	id: 'action', header: 'Action',  xtype: 'actioncolumn',align: 'center',width:90
	                ,align: 'center'
	                ,items: [{
	                    getClass: function(v, meta, rec)	{
	                		return 'edit-col';
	                	}
	                    ,tooltip: 'Click to edit'
	                    ,handler: function(grid, rowIndex, colIndex) {
	                    }
	                    ,scope:this
	                },{
	                    getClass: function(v, meta, rec)	{
	                		return 'delete-col';
	                	}
	                    ,tooltip: 'view license keys'
	                    ,handler: function(grid, rowIndex, colIndex) {
							
	                    }
	                    ,scope: this
	                }]
	            }
	        ]
	    });
	
		var store = new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc'
	            ,method: 'POST'
	      	})
		  	,baseParams:{
				task: "action"
				,action: "member.domain.getList"
				,limit: 20
				,controller: "members"
				,msc_id: mscId, user_id: userId
		 	}
		  	,reader: new Ext.data.JsonReader({
			    root: 'results',
			    totalProperty: 'total',
			    fields: [
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'userID', type: 'int', mapping: 'userID'}
				    ,{name: 'username', type: 'int', mapping: 'username'}
				    ,{name: 'domain', type: 'string', mapping: 'domain'}
				    ,{name: 'mscID', type: 'int', mapping: 'mscID'}
				    ,{name: 'start_date', type: 'datetime', mapping: 'start_date'}
				    ,{name: 'end_date', type: 'datetime', mapping: 'end_date'}
	  			]
	  		})
		  	,autoLoad:{}
		});
		
		var buildGrid = new ose.quickGrid.build(store,cm,sm);

		buildGrid.init = function(id)	{
			this.grid = new Ext.grid.GridPanel({
				store: this.store
				,cm: this.cm
				,sm: this.sm
				,id: id
				,height: 500
				,autoExpandColumn: 'domain'
				,tbar: this.buildTopToolbar()
				,bbar: this.buildBottomToolbar()
			})
		}
		
		buildGrid.init('ose-msc-member-domain-grid');
		
		buildGrid.buildForm = this.buildForm;
		buildGrid.buildWin = this.buildWin;
		
		buildGrid.setDeleteAction('id',{
			url: 'index.php?option=com_osemsc'
			,params: {
				task: "action"
				,action: "member.domain.remove"
				,controller: "members"
			}
		});
		
		buildGrid.setTopBtnAction('btnAdd',function(){
			var form = this.buildForm();
			this.buildWin(form,'Add Domain');
		});
		
		buildGrid.setColumnAction('action',0,function(grid, rowIndex, colIndex)	{
	    	var rs = grid.getStore().getAt(rowIndex)
        	grid.getSelectionModel().selectRow(rowIndex);
        	var editForm = this.buildForm();
        	editForm.getForm().setValues(rs.data);
			this.buildWin(editForm,'Edit Domain');
		})
		
		buildGrid.setColumnAction('action',1,function(grid, rowIndex, colIndex){
			var s = this.grid.getSelectionModel().selectRow(rowIndex);
			this.grid.getTopToolbar().getComponent('btnDel').fireEvent('click');
		})
		
		var grid = buildGrid.output();
		
		return grid;
	
	}
}

var addonDomain = new oseMscAddonDomain(oseMemsMsc.msc_id,oseMemsMsc.member_id);
oseMscAddon.domain = addonDomain.buildGrid();