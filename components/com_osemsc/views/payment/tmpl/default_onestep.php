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
$headerObjs= oseMscAddon :: getAddonList('paymentOS', false, null, 'obj');
oseMscAddon::loadAddons($headerObjs);
oseHTML::script(OSEMSCFOLDER."/views/payment/js/js.payment.js",'1.6');

?>
<script type="text/javascript">
Ext.onReady(function(){
	var headerObjs = <?php echo oseJson::encode($headerObjs);?>;

	var oseViewPaymentfp = oseMsc.payment.buildForm();
	oseMsc.payment.fp = new oseMsc.payment.form(oseViewPaymentfp);
	//alert(oseMsc.payment.fp.fp.toSource(0))
	oseMsc.payment.fp.onLoadAddons(headerObjs,'payment',false);
	oseMsc.payment.fp.setClickBtnAction('submitBtnOk');
	oseMsc.payment.fp.render('ose-payment');
	oseMsc.payment.fp.loadRegInfo();


	//alert(monthArray.toSource())
	//var list = new oseMscAddon.msc_list;
	//list.init()
})
</script>
<?php
	if($this->menuParams->get('show_page_heading') || $this->menuParams->get('show_page_title'))
	{
?>
		<div class='componentheading <?php echo $this->menuParams->get('pageclass_sfx'); ?>'><?php echo $this->menuParams->get('page_heading'); ?></div>
<?php
	}
?>

<div id="ose-translate-box"></div>


<div id="ose-payment"></div>
<div id="ose-payment-callback-form"></div>