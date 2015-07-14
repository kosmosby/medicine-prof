Ext.ns('oseMscAddon');
/*
	Ext.Ajax.request({
			url:'index.php?option=com_osemsc&controller=member',
			params:{task:'action',action:'member.company.getItem',},
			success: function(response,opt){
				var msg = Ext.decode(response.responseText);
				
				
				var tpl = new Ext.Template(
					'<table>',
					'<tr><td>Company:</td> <td>{company}</td></tr>',
					'<tr><td>Street Address1:</td> <td>{addr1}</td></tr>',
					'<tr><td>Street Address2:</td> <td>{addr2}</td></tr>',
					'<tr><td>City:</td> <td>{city}</td></tr>',
					'<tr><td>State/Province:</td> <td>{state}</td></tr>',
					'<tr><td>Country:</td> <td>{country}</td></tr>',
					'<tr><td>Zip/Postal Code:</td> <td>{postcode}</td></tr>',
					'<tr><td>Phone:</td> <td>{telephone}</td></tr>',
					'</table>'
				);
				
				oseMscAddon.company = new Ext.Panel({
					title:'Company Info.',
					data:msg.result[0],
					tpl:tpl,
				});
			}
		
	});
*/

/*
	var addonMemCompanyViewStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url:'index.php?option=com_osemsc&controller=member'
	            ,method: 'POST'
	      })
		  ,baseParams:{task:'action',action:'member.company.getItem'}
		  ,reader: new Ext.data.JsonReader({   
		    root: 'result'
		    ,totalProperty: 'total'
		    ,idProperty: 'company_id'
		  },[ 
		    {name: 'company_id', type: 'int', mapping: 'company_id'}
		    ,{name: 'company', type: 'string', mapping: 'company'}
		    ,{name: 'addr1', type: 'string', mapping: 'addr1'}
		    ,{name: 'addr2', type: 'string', mapping: 'addr2'}
		    ,{name: 'city', type: 'string', mapping: 'city'}
		    ,{name: 'state', type: 'string', mapping: 'state'}
		    ,{name: 'country', type: 'string', mapping: 'country'}
		    ,{name: 'postcode', type: 'string', mapping: 'postcode'}
		    ,{name: 'telephone', type: 'string', mapping: 'telephone'}
		  ])
		  ,autoLoad:{}
	});
	
	var addonMemCompanyViewDataView = new Ext.DataView({
        store: addonMemCompanyViewStore
        ,tpl  : new Ext.XTemplate(
            '<tpl for=".">',
                '<table>',
				'<tr><td>Company:</td> <td>{company}</td></tr>',
				'<tr><td>Street Address1:</td> <td>{addr1}</td></tr>',
				'<tr><td>Street Address2:</td> <td>{addr2}</td></tr>',
				'<tr><td>City:</td> <td>{city}</td></tr>',
				'<tr><td>State/Province:</td> <td>{state}</td></tr>',
				'<tr><td>Country:</td> <td>{country}</td></tr>',
				'<tr><td>Zip/Postal Code:</td> <td>{postcode}</td></tr>',
				'<tr><td>Phone:</td> <td>{telephone}</td></tr>',
				'</table>',
            '</tpl>'
        )
      
        ,id: 'mem-ext-panel'
        ,itemSelector: 'li.ext-addon'
        ,singleSelect: true
        ,multiSelect : false
        ,autoScroll  : true
    });
*/	
	oseMscAddon.company_view = new Ext.FormPanel({
		border: false
		,bodyStyle: 'padding: 10px'
		,layout: 'form'
		,labelWidth: 150
		,defaults: {xtype: 'displayfield', width: 300}
		,items: [{
			fieldLabel: 'Company'
			,name: 'company.company'
		},{
			fieldLabel: 'Address 1'
			,name: 'company.addr1'
		},{
			fieldLabel: 'Address 2'
			,name: 'company.addr2'
		},{
			fieldLabel: 'City'
			,name: 'company.city'
		},{
			fieldLabel: 'State'
			,name: 'company.state'
		},{
			fieldLabel: 'Post Code'
			,name: 'company.postcode'
		},{
			fieldLabel: 'Company'
			,name: 'company.telephone'
		}]
		,height: 300
		,reader:new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'company_id',
		    fields:[ 
			    {name: 'company.company', type: 'string', mapping: 'company'},
			    {name: 'company.addr1', type: 'string', mapping: 'addr1'},
			    {name: 'company.addr2', type: 'string', mapping: 'addr2'},
			    {name: 'company.city', type: 'string', mapping: 'city'},
			    {name: 'company.state', type: 'string', mapping: 'state'},
			    {name: 'company.country', type: 'string', mapping: 'country'},
			    {name: 'company.postcode', type: 'string', mapping: 'postcode'},
			    {name: 'company.telephone', type: 'string', mapping: 'telephone'}
		  	]
	  	})
		
		,listeners: {
			render: function(p){
				oseMscAddon.company_view.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member'
					,waitMsg: 'Loading'
					,params:{
						task:'action'
						,action:'member.company.getItem'
					}
					,callback: function(el,success,response,opt)	{
						var msg = Ext.decode(response.responseText);
						
						if(!msg.result.country)	{
							oseMscAddon.company.findById('company_country').setValue('US');
						}
					}
				})
			}
		}
	});