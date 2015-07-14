<?php
/**
  * @version     4.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence {@link
http://www.opensource-excellence.co.uk}
  * @author        EasyJoomla {@link http://www.easy-joomla.org
Easy-Joomla.org}
  * @author        SSRRN {@link http://www.ssrrn.com}
  * @author        Created on 15-Sep-2008
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- ... Open Source Excellence
*/

defined('_JEXEC') or die("Direct Access Not Allowed");

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
//JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));


$current = JURI::current();
JHtml :: stylesheet('administrator/components/com_osemsc/assets/css/extra.css');
?>
<script type="text/javascript">
Ext.onReady(function(){
	Ext.QuickTips.init();

	var iterateLoadok = function(task,id,fn)	{
		//Ext.Msg.wait('Processing..');
		Ext.Ajax.request({
			url: 'index.php?option=com_osemsc'
			,params: {controller: 'paypal', task: task ,id: id}
		,timeout: 120000
			,success: function(res)	{
				var msg = Ext.decode(res.responseText);
				if(msg.finish == true)	{
					//fn.createDelegate(this, [res]);
					Ext.Msg.hide();
					fn.call(this, res);
				}	else	{
					iterateLoadok(task,msg.id,fn);
				}
			}
			,scope: this
		});
	};

	Ext.select('.ose-action-validate').addListener('click',function(e,t)	{
		var id = Ext.get(t).getAttribute('oid').replace('ose-action-','');
		iterateLoadok('validate',id,function(res)	{
			var msg = Ext.decode(res.responseText);
			if(msg.success == true)	{
				Ext.Msg.wait('Updated! Refreshing...');
				window.location = window.location.href;
			}	else	{
				Ext.Msg.alert(msg.title,msg.content);
			}
		});
	});

	Ext.select('.ose-action-edit').addListener('click',function(e,t)	{
		var el = Ext.fly(t);
		var id = el.getAttribute('id').replace('ose-action-','');
		var w = new Ext.Window({
			title: 'Edit Paypal Order Info'
			,width: 600
			,items: [{
				xtype: 'form'
				,defaults: {anchor: '90%'}
				,labelWidth: 218
				,items: [{
					xtype: 'hidden'
					,name: 'id'
					,value: id
				},{
					xtype: 'textfield'
					,name: 'email'
					,fieldLabel: 'Paypal Buyer Email'
				},{
					xtype: 'textfield'
					,name: 'order_number'
					,fieldLabel: 'Invoice Number'
				},{
					xtype: 'textfield'
					,name: 'payment_serial_number'
					,fieldLabel: 'Profile ID'
				},{
					xtype: 'numberfield'
						,name: 'payment_price'
						,fieldLabel: 'Price'
						,allowDecimals: true
					}]
				,buttons: [{
					text: 'Submit'
					,handler: function(btn)	{
						var bf = btn.findParentByType('form');
						bf.getForm().submit({
							url: 'index.php?option=com_osemsc'
							,params: {controller: 'paypal' , task: 'updatePaypalOrderInfo'}
							,success: function(form,action)	{
								ose.ajax.formSuccess(form,action);
								window.location = window.location.href;
							}
							,failure: function(form,action)	{
								ose.ajax.formFailureMB(form,action);
							}
						});
					}
				},{
					text: 'Cancel'
					,handler: function()	{
						w.close();
					}
				}]
				,listeners: {
					render: function(f)	{
						if(id > 0)	{
							f.getForm().load({
								url: 'index.php?option=com_osemsc'
								,params: {controller: 'paypal', task: 'loadOrderInfo',id:id}
							});
						}
					}
				}
			}]
			
		}).show().alignTo(Ext.getBody(),'t-t',[0,10]);
	});

	Ext.select('.ose-action-delete').addListener('click',function(e,t,o)	{
		var el = Ext.fly(t); 
		var id = el.getAttribute('oid').replace('ose-action-','');
		Ext.Msg.confirm('Notice','Are you sure to mark it as useless?',function(btn,txt)	{
			if(btn == "yes")	{
				Ext.Ajax.request({
					url: 'index.php?option=com_osemsc'
					,params: {controller:'authorize',task: 'markuseless',id:id}
					,success: function(res)	{
						var msg = Ext.decode(res.responseText);
						if(msg.success)	{
							ose.ajax.ajaxSuccess(res);
							window.location = window.location.href;
						}
					}
				});
			}
		});
		
	});

	Ext.select('.ose-action-edit-date').addListener('click',function(e,t)	{
		var w = new Ext.Window({
			title: 'Edit Expired Date'
			,width: 600
			,items: [{
				xtype: 'form'
				,defaults: {anchor: '90%'}
				,labelWidth: 218
				,items: [{
					xtype: 'datefield'
					,name: 'expired_date'
					,format: 'Y-m-d'
					,fieldLabel: 'Expiry Date'
				},{
					xtype: 'hidden'
					,name: 'id'
					,value: Ext.get(t).getAttribute('id').replace('ose-action-','')
				}]
				,buttons: [{
					text: 'Submit'
					,handler: function(btn)	{
						var bf = btn.findParentByType('form');
						bf.getForm().submit({
							url: 'index.php?option=com_osemsc'
							,params: {controller: 'authorize' , task: 'updateMemberExpiryDate'}
							,success: function(form,action)	{
								ose.ajax.formSuccess(form,action);
								Joomla.submitbutton();
								//window.location = window.location.href;
							}
							,failure: function(form,action)	{
								ose.ajax.formFaileMB(form,action);
							}
						});
					}
				},{
					text: 'Cancel'
					,handler: function()	{
						w.close();
					}
				}]
			}]
			
		}).show().alignTo(Ext.getBody(),'t-t',[0,10]);
	});
});
</script>
<div style="margin-bottom:10px">
	<div class="btntips"><?php echo JHtml::_('image','menu/icon-16-edit.png','',null,true);?>: Edit	</div>
	<div class="btntips"><?php echo JHtml::_('image','menu/icon-16-links.png','',null,true);?>: Validate</div>
	<div class="btntips"><?php echo JHtml::_('image','menu/icon-16-calendar.png','',null,true);?>: Edit Expired Date</div>
	<div class="btntips"><?php echo JHtml::_('image','menu/icon-16-deny.png','',null,true);?>: Mark Useless</div>
</div>
<div class="clr"> </div>
<fieldset>
<legend>Paypal</legend>
<form
	action="<?php echo JRoute::_('index.php?option=com_osemsc&view=paypal');?>"
	method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="Username, Name, Email, Paypal Email" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_searchid"><?php echo 'Filter By Member ID: '; ?></label>
			<input type="text" name="filter_searchid" id="filter_searchid" value="<?php echo $this->escape($this->state->get('filter.searchid')); ?>" title="<?php echo JText::_('COM_MODULES_MODULES_FILTER_SEARCH_DESC'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_searchid').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_searchpe"><?php echo 'Filter By Paypal Email: '; ?></label>
			<input type="text" name="filter_searchpe" id="filter_searchpe" value="<?php echo $this->escape($this->state->get('filter.searchpe')); ?>" title="<?php echo JText::_('COM_MODULES_MODULES_FILTER_SEARCH_DESC'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_searchpe').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
	</fieldset>
	
	<div class="clr"> </div>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%" rowspan="2"><input type="checkbox"
					name="checkall-toggle" value=""
					title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
				</th>
				<th>
					<?php echo JText::_('MEMBERSHIP'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('USERNAME'); ?>
				</th>
				
				<th width="12%" >
					<?php echo JHtml::_('grid.sort', 'Name', 'd.name', $listDirn, $listOrder); ?>
				</th>
				
				<th width="6%" >
					<?php echo JText::_('Skipped Payment'); ?>
				</th>
				
				<th width="15%" >
					<?php echo JText::_('EMAIL'); ?>
				</th>
				
				<th width="15%" >
					<?php echo JText::_('Paypal EMAIL'); ?>
				</th>
				
				<th width="12%">
					<?php echo JHtml::_('grid.sort', 'Expired Date', 'c.expired_date', $listDirn, $listOrder); ?>
				</th>
				
				<th width="12%" >
					<?php echo JText::_('ACTION'); ?>
				</th>
				
				
				
				<th width="2%" class="nowrap" >
				<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
				<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center" width="1%">
				<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php echo $item->membership;?>
				</td>
				<td ><?php echo $item->username;?></td>
				<td >
					<?php echo $item->name;?>
				</td>
				
				<td class="center">
					<?php if(@$item->order_status == 'skipped'):?>
					<?php echo JHtml::_('image','admin/tick.png','',null,true);?>
					<?php endif;?>
				</td>
				
				<td ><?php echo $item->email;?></td>
				
				<td ><?php echo $item->paypal_email;?></td>
				
				<td ><?php echo $item->expired_date;?></td>
				
				<td>
					<div style="float:left;width:30px;height:10px;"></div>
					<div class="ose-action-edit center btns" id="ose-action-<?php echo $item->id;?>" style="float:left;margin-right:5px"></div>
					
					<?php if(@$item->order_id):?>
					<div class="ose-action-validate center btns" oid="ose-action-<?php echo $item->order_id;?>" style="float:left"></div>
					<?php endif;?>
					<div class="ose-action-edit-date center btns" id="ose-action-<?php echo $item->id;?>" title="Edit Expired Date"></div>
					<div class="ose-action-delete center btns" oid="ose-action-<?php echo @$item->order_id;?>"  title="Mark Useless"></div>
				</td>
				
				<td class="center btns" width="2%">
					<?php echo $item->id;?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" /> <input type="hidden"
			name="boxchecked" value="0" /> <input type="hidden"
			name="filter_order" value="<?php echo $listOrder; ?>" /> <input
			type="hidden" name="filter_order_Dir"
			value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
</fieldset>
<?php include_once(JPATH_COMPONENT.DS."views".DS."footer.php"); ?>