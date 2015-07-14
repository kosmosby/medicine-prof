<?php
defined('_JEXEC') or die;

class flexpaperViewPay extends JViewLegacy {

    function showForm($tpl = null) {

        $this->form = $this->get('PaypalForm');

        parent::display($tpl);
    }

    function paymentComplete() {

        parent::display('paymentcomplete');
    }

}
