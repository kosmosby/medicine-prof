<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
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
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/

defined('_JEXEC') or die("Direct Access Not Allowed");
$error_msg = "";
oseHTML::script(oseMscMethods::getJsModPath('reg','reg'),'1.5');
echo $this->initJs();
?>
<style type="text/css">
	.combo-left	{
		text-align: left
	}
</style>

<script type="text/javascript">
Ext.onReady(function(){
	var headerObjs = <?php echo oseJson::encode($this->registerOS_header);?>;
	var bodyObjs = <?php echo oseJson::encode($this->registerOS_body);?>;
	var footerObjs = <?php echo oseJson::encode($this->registerOS_footer);?>;

	var oseViewRegfp = oseMsc.reg.buildForm2c.buildForm(Ext.get('osemsc-reg').getWidth());

	oseMsc.reg.fp = new oseMsc.payment.form(oseViewRegfp);


	oseMsc.reg.n = 0;
	oseMsc.reg.fp.onLoadAddons = function(addons,position,getForm)	{
		Ext.each(addons, function(item,i,all)	{
			var sort = oseMsc.reg.n % 2;
			sort = String(sort); // 1-left,0-right
			if(item.addon_name == 'oseMscAddon.msc_cart' || item.addon_name == 'oseMscAddon.coupon')	{
				var posItem = eval("this.fp."+'left');
			}	else	{
				var posItem = eval("this.fp."+'right');
			}

			if(item.addon_name)	{
				//alert(item.addon_name);

				if(getForm)	{
					var obj = eval("new "+item.addon_name+"(this.fp)")
				}else{
					var obj = eval("new "+item.addon_name+"()")
				}
				var objInit = obj.init();
				posItem.add(objInit)

				if(!objInit.hidden)	{
					oseMsc.reg.n++;
				}
			}
		},this);
	}

	oseMsc.reg.fp.addReader = function()	{
		this.fp.getForm().reader = new Ext.data.JsonReader({
		    root: 'results'
		    ,totalProperty: 'total'
		    ,idProperty: 'id'
		    ,fields:[
			    {name: 'msc_id', type: 'string', mapping: 'msc_id'}
			    ,{name: 'msc_option', type: 'string', mapping: 'msc_option'}
			    ,{name: 'ose_currency', type: 'string', mapping: 'ose_currency'}
		  	]
	  	})
	}

	//oseMsc.reg.fp.addReader()

	oseMsc.reg.fp.onLoadAddons(headerObjs,'regHeader',true);
	oseMsc.reg.fp.onLoadAddons(bodyObjs,'regBody',true);
	oseMsc.reg.fp.onLoadAddons(footerObjs,'regFooter',true);
	oseMsc.reg.fp.setClickBtnAction('submitBtnOk');
	oseMsc.reg.fp.setBtnActive('submitBtnOk',false);
	oseMsc.reg.fp.render('osemsc-reg');
	//oseMsc.reg.fp.setMscList();
	oseMsc.reg.fp.loadRegInfo();
	//alert(oseMscAddon.terms);

});
</script>

<?php
	if($this->menuParams->get('show_page_heading') || $this->menuParams->get('show_page_title'))
	{
?>
		<div class='componentheading <?php echo $this->menuParams->get('pageclass_sfx'); ?>'><?php echo $this->menuParams->get('page_heading'); ?></div>
<?php
	}
?>

<div id="osemsc-reg-heading"></div>
<div id="osemsc-reg"></div>

<div id="ose-payment-callback-form"></div>
<?php include(JPATH_COMPONENT.DS."views".DS."footer.php"); ?>