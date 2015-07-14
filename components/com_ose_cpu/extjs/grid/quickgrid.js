Ext.ns('ose','ose.quickGrid');
ose.quickGrid.build = function(store,cm,sm)	{
	this.store = store;
	this.cm = cm;
	this.sm = sm;
	this.grid = '';
	this.editForm = '';
	this.formOpt = '';
	this.winOpt = '';

	this.buildTopToolbar = function()	{
		return new Ext.Toolbar({
			items: [{
				text: 'Add'
				,itemId: 'btnAdd'
				,scope:this
			},{
				text: 'Delete'
				,itemId: 'btnDel'
				,scope:this
			}]
		})
	};

	this.buildBottomToolbar = function()	{
		return new Ext.PagingToolbar({
			pageSize: 20
			,store: this.store
			,displayInfo: true
		    ,displayMsg: 'Displaying items {0} - {1} of {2}'
		    ,emptyMsg: "No items to display"
			,plugins: new Ext.ux.grid.limit({})
	    })
	};
}

ose.quickGrid.build.prototype = {
	init: function(id)	{
		this.grid = new Ext.grid.GridPanel({
			store: this.store
			,cm: this.cm
			,sm: this.sm
			,'id': id
			,tbar: this.buildTopToolbar()
			,bbar: this.buildBottomToolbar()
		})

		//this.editForm = this.buildForm();
		//return this.grid;
	}

	,output: function()	{
		return this.grid;
	}

	,addTopBtn: function(opt)	{
		this.grid.getTopToolbar().add(opt);
	}

	,setTopBtnAction: function(itemId,fn)	{
		this.grid.getTopToolbar().getComponent(itemId).on('click',fn,this)
	}

	,setAddAction: function()	{
		this.setTopBtnAction('btnAdd',function(){
			var form = this.buildForm();
			this.buildWin(form,'close');
		})
	}

	,setDeleteAction: function(key,opt)	{
		this.setTopBtnAction('btnDel',function()	{
			var s = this.grid.getSelectionModel().getSelections();
			var ids = Array();
			Ext.each(s,function(item,i,all)	{
				ids[i] = item.get(key);
			})

			opt.params['ids[]'] = ids;
			Ext.Ajax.request({
				url: opt.url//'index.php?option=com_osemsc&controller=config'
				,params: opt.params//{task: 'removeTax','ids[]':ids}
				,callback: function(el,success,response,opt1)	{
					var msg = Ext.decode(response.responseText);
					if(msg.success)	{
						this.grid.getStore().remove(s)
						oseMsc.ajaxSuccess(response,opt1)
					}	else	{
						oseMsc.ajaxFailure(response,opt1)
					}
				}
				,scope:this
			})
		});
	}

	,setColumnAction: function(cId,i,fn)	{
		var c = this.grid.getColumnModel().getColumnById(cId);
		c.items[i].scope = this;
		c.items[i].handler = fn;
	}

	,setEditAction: function(cId)	{
		this.setColumnAction(cId,0,function(grid, rowIndex, colIndex) {
        	var rs = grid.getStore().getAt(rowIndex)
        	grid.getSelectionModel().selectRow(rowIndex);
        	this.editForm.getForm().setValues(rs.data);
			this.buildWin(this.editForm,'hide');
        })
	}

	,getFormOpt: function(opt)	{
		this.formOpt = opt;
	}

	,getWinOpt: function(opt)	{
		this.winOpt = opt;
	}

	,buildForm: function()	{
		// a sample only;
		return new Ext.FormPanel(this.formOpt)
	}

	,buildWin: function(form,closeAction)	{
		this.winOpt.items = [form];
		this.winOpt.closeAction = closeAction;
		new Ext.Window(this.winOpt).show().alignTo(Ext.getBody(),'t-t',[10,0]);
	}

	,relate: function(cId)	{
		this.setAddAction();
		this.editForm = this.buildForm();
		this.setEditAction(cId);

		this.setColumnAction('action',1,function(grid, rowIndex, colIndex){
			var s = this.grid.getSelectionModel().selectRow(rowIndex);
			this.grid.getTopToolbar().getComponent('btnDel').fireEvent('click');

		})
	}
}