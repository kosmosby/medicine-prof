Ext.ns('oseMemMsc', 'oseMscAddon');
var ccGetMonthList = function() {
	var monthArray = Array();
	Ext.each(Date.monthNames, function(item, i, all) {
		var n = String.leftPad(i + 1, 2, '0');
		monthArray[i] = [ n, item ];//item;
	});

	return new Ext.form.ComboBox({
		hiddenName : 'creditcard_month',
		width : 100
		// create the combo instance
		,
		typeAhead : true,
		editable : false,
		triggerAction : 'all',
		lazyRender : true,
		mode : 'local'
		//,listClass: 'combo-left'
		,
		forceSelection : true,
		store : new Ext.data.ArrayStore({
			id : 0,
			fields : [ 'myId', 'displayText' ],
			data : monthArray
		}),
		valueField : 'myId',
		displayField : 'displayText',
		listeners : {
			render : function(c) {
				c.setValue('01');
			}
		}
	});
};
var ccGetYearList = function() {
	return new Ext.form.ComboBox({
		hiddenName : 'creditcard_year',
		width : 100,
		typeAhead : true,
		triggerAction : 'all',
		editable : false,
		lazyRender : true,
		mode : 'local'
		//,listClass: 'combo-left'
		,
		forceSelection : true,
		store : new Ext.data.ArrayStore(
				{
					id : 0,
					fields : [ 'myId', 'displayText' ],
					data : [ [ '2011', '2011' ], [ '2012', '2012' ],
							[ '2013', '2013' ], [ '2014', '2014' ],
							[ '2015', '2015' ], [ '2016', '2016' ],
							[ '2017', '2017' ], [ '2018', '2018' ],
							[ '2019', '2019' ], [ '2020', '2020' ],
							[ '2021', '2021' ] ]
				}),
		valueField : 'myId',
		displayField : 'displayText',
		listeners : {
			render : function(c) {
				c.setValue('2011');
			}
		}
	});
};
oseMscAddon.creditcardupdate = new Ext.FormPanel(
{
	border : false,
	labelWidth : 200,
	height : 250,
	defaults : {
		border : false,
		width : 300,
		msgTarget : 'side'
	},
	items : [
			{
				xtype : 'combo',
				fieldLabel : 'Order ID',
				name : 'order_id',
				typeAhead : true,
				allowBlank : false,
				triggerAction : 'all',
				editable : false,
				lazyRender : true,
				mode : 'local',
				listClass : 'combo-left',
				forceSelection : true,
				store : new Ext.data.Store({
					proxy : new Ext.data.HttpProxy({
						url : 'index.php?option=com_osemsc',
						method : 'POST'
					}),
					baseParams : {
						task : "getOrders",
						limit : 50,
						controller : 'creditcardupdate'
					},
					reader : new Ext.data.JsonReader({
						root : 'results',
						totalProperty : 'total'
					}, [ {
						name : 'order_id',
						type : 'int',
						mapping : 'order_id'
					}, {
						name : 'title',
						type : 'string',
						mapping : 'title'
					}
					/*
					 * ,{name: 'user_id', type: 'int', mapping:
					 * 'user_id'} ,{name: 'create_date', type: 'string',
					 * mapping: 'create_date'}
					 * 
					 * ,{name: 'mscTitle', type: 'string', mapping:
					 * 'mscTitle'} ,{name: 'name', type: 'string',
					 * mapping: 'name'} ,{name: 'payment_price', type:
					 * 'string', mapping: 'payment_price'} ,{name:
					 * 'payment_currency', type: 'string', mapping:
					 * 'payment_currency'} ,{name: 'order_status', type:
					 * 'string', mapping: 'order_status'} ,{name:
					 * 'payment_serial_number', type: 'string', mapping:
					 * 'payment_serial_number'} ,{name: 'payment_from',
					 * type: 'string', mapping: 'payment_from'} ,{name:
					 * 'payment_mode', type: 'string', mapping:
					 * 'payment_mode'} ,{name: 'params', type: 'string',
					 * mapping: 'params'}
					 */
					]),
					autoLoad : {},
					listeners : {
						load : function(s, r) {
							Ext.Msg.hide();
							if (r.length > 0) {

								oseMscAddon.creditcardupdate.getForm()
										.findField('order_id')
										.setValue(
												s.getAt(0).get(
														'order_id'));
							}
						}
					}
				}),
				valueField : 'order_id',
				displayField : 'title',
				emptyText : 'Please Choose'
			},
			{
				fieldLabel : Joomla.JText._('Credit_Card'),
				xtype : 'panel',
				layout : 'hbox',
				border : false,
				anchor : '100%'
				// ,defaults:{labelWidth:250,anchor: '90%'}
				,
				style : 'padding-left: 0px',
				items : [
						{
							hiddenName : 'creditcard_type',
							forceSelection : true,
							xtype : 'combo',
							typeAhead : true,
							editable : false,
							triggerAction : 'all',
							lazyRender : true,
							mode : 'local',
							store : new Ext.data.ArrayStore({
								id : 0,
								fields : [ 'myId', 'displayText' ],
								data : [ [ 'VISA', 'Visa' ],
										[ 'MC', 'MasterCard' ],
										[ 'amex', 'American Express' ]
								// ,['discover','Discover Card']
								]
							}),
							valueField : 'myId',
							displayField : 'displayText',
							allowBlank : false,
							listeners : {
								render : function(c) {
									// c.setValue('VISA');
								},
								select : function(c, r, i) {
									var nextS = c.nextSibling();
									// alert(nextS.ctCls);
									nextS
											.removeClass('creditcardimg-VISA');
									nextS
											.removeClass('creditcardimg-MC');
									nextS
											.removeClass('creditcardimg-amex');
									nextS.addClass('creditcardimg-'
											+ c.getValue());
								}
							/*
							 * ,change: function(c,nv,ov) { var nextS =
							 * c.nextSibling(); //alert(nextS.ctCls);
							 * nextS.removeClass('creditcardimg-'+ov);
							 * nextS.addClass('creditcardimg-'+nv); }
							 */
							},
							emptyText : 'Please Choose'
						}, {
							// cls: 'credicardimg'
							border : false,
							xtype : 'box'
						} ]
			},
			{
				fieldLabel : Joomla.JText._('Name_On_Card'),
				name : 'creditcard_name',
				allowBlank : false,
				vtype : 'noSpace',
				xtype : 'textfield'
			},
			{
				fieldLabel : 'New '
						+ Joomla.JText._('Credit_Card_Number'),
				name : 'creditcard_number',
				allowBlank : false,
				vtype : 'alphanum',
				xtype : 'numberfield'
			},
			{
				fieldLabel : 'New ' + Joomla.JText._('Expiration_Date')
				// ,name: 'creditcard_expirationdate'
				// ,format: 'M-Y'
				,
				xtype : 'panel',
				layout : 'hbox',
				style : 'padding-left: 0px',
				labelWidth : 250,
				width : 500,
				items : [ ccGetMonthList(), {
					xtype : 'displayfield',
					html : '/'
				}, ccGetYearList(), {
					xtype : 'displayfield',
					html : Joomla.JText._('Month_Year')
				} ]
			},
			{
				fieldLabel : Joomla.JText._('Security_Code'),
				xtype : 'compositefield',
				style : 'padding-left: 0px',
				items : [
						{
							name : 'creditcard_cvv',
							xtype : 'textfield',
							size : 280,
							allowBlank : true,
							vtype : 'noSpace'
						},
						{
							xtype : 'box',
							autoEl : {
								tag : 'div',
								id : 'trackCallout',
								style : "width: 100px; text-align:center; padding: 3px 0; border:0px dotted #99bbe8; color: #666; cursor: pointer; font:bold 11px tahoma,arial,sans-serif; float:left; text-decoration:underline;",
								html : Joomla.JText._('WHAT_IS_THIS')
							},
							listeners : {
								render : function(b) {
									Ext.QuickTips.register({
										target : b.getEl(),
										anchor : 'right',
										text : Joomla.JText
												._('WHAT_IS_THIS_EXP'),
										width : 250
									});
								}
							}
						} ]
			} ],
	buttons : [ {
		text : 'Save',
		handler : function() {
			var f = oseMscAddon.creditcardupdate;

			// if(f.getForm().findField('order_id').isDirty() != false)
			// {
			f.getForm().submit({
				url : 'index.php?option=com_osemsc',
				params : {
					controller : 'creditcardupdate',
					task : 'update'
				},
				success : function(form, action) {
					oseMsc.formSuccess(form, action);
				},
				failure : function(form, action) {
					oseMsc.formFailureMB(form, action);
				}
			});
			// } else {
			// Ext.Msg.alert('Notice','Please Select One Order To Update
			// The Information');
			// }
		}
	} ],
	listeners : {
		render : function() {
			Ext.Msg.wait('Loading...');
		}
	}
});
