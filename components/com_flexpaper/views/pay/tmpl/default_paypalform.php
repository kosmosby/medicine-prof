<?php

/**
 * @package		Joomla.Tutorials
 * @subpackage	Component
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');


//echo $this->form;

?>

<div>You have selected an item with price €<?php echo $this->form->price;?> and ID <?php echo $this->form->id;?>. Click submit button and buy it with paypal</div>
<br />

<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="payPalForm">

    <input type="hidden" name="item_name" value="<?php echo $this->form->id;?>">
    <input type="hidden" name="item_number" value="<?php echo $this->form->id;?>">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="no_note" value="1">
    <input type="hidden" name="business" value="kosmos.by-sellEU@gmail.com">
    <input type="hidden" name="currency_code" value="EUR">
    <input type="hidden" name="return" value="<?php echo JURI::root();?>component/isearch/?task=paymentcomplete">
    <input type="hidden" name="cancel_return" value="<?php echo JURI::root();?>component/isearch/?task=paymentincomplete">
    <input type="hidden" name="notify_url" value="http://stockmile.com/component/isearch/?task=updatepayment">
    <input type="hidden" name="item_name" value="<?php echo $this->form->id;?>">
    <input type="hidden" name="amount" value="<?php echo $this->form->price;?>">
    <input type="hidden" name="custom" value="<?php echo $this->form->user_id;?>">

    <input type="submit" name="Submit" value="Submit">

</form>


