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
oseHTML::script(oseMscMethods::getJsModPath('payment.vm','payment'),'1.5');
oseHTML::script(OSEMSCFOLDER."/views/payment/js/js.payment.vm.js",'1.5');
?>
<div id="osemsc-payment-view-button"></div>
<div id="osemsc-payment"></div>
<div id="osemsc-payment-mode"></div>
<div id="osemsc-payment-confirm"></div>
<style type="text/css">
.msc-sub {padding: 0 0 0 10px}
</style>
<script type="text/javascript">
Ext.onReady(function(){
	<?php if(count($this->oseMscPayment) > 0): ?>
	var pay_msc_id = <?php echo $this->oseMscPayment['msc_id'];?>;
	var pay_payment_mode = '<?php echo $this->oseMscPayment['payment_mode'];?>';
	if(pay_msc_id > 0)	{
		Ext.Ajax.request({
			url: 'index.php?option=com_osemsc&controller=payment',
			params:{task: 'generateConfirm', 'msc_id':pay_msc_id,payment_mode: pay_payment_mode},
			success: function(response,opt){
				var msg = Ext.decode(response.responseText);
				oseMsc.payment.confirmForm.body.update(msg.content);
			},
		});
	}
	<?php endif; ?>
});
</script>