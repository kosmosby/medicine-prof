/**
 *
 * Amazon payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id$
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004-2014 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

jQuery().ready(function ($) {

    /************/
    /* Handlers */
    /************/
    handleRegionParameters = function () {
        var region = $('#paramsregion').val();

        $('.region-other').parents('tr').hide();

        if (region === 'OTHER') {
            $('.region-other').parents('tr').show();
        }
    }


    handleAuthorizationERPEnabled = function () {
        var authorization = $('#paramsauthorization_mode_erp_enabled').val();

        if (authorization === 'authorization_done_by_erp') {
            $('.capture_mode').parents('tr').hide();
            $('.capture_mode_warning').parents('tr').hide();
            $('.capture_mode_warning').hide();
            $('.status_authorization').parents('tr').hide();
            $('.status_capture').parents('tr').hide();
            $('.status_refunded').parents('tr').hide();
            $('.status_refunded').parents('tr').hide();
            $('.ipnurl').parents('tr').hide();
            $('.ipn_warning').parents('tr').hide();
            $('.soft_decline').parents('tr').hide();
            $('.sandbox_error_simulation').parents('tr').hide();

        } else {
            $('.capture_mode').parents('tr').show();
            $('.status_capture').parents('tr').show();
            $('.status_authorization').parents('tr').show();
            $('.ipnurl').parents('tr').show();
            $('.ipn_warning').parents('tr').show();
            $('.soft_decline').parents('tr').show();
            $('.sandbox_error_simulation').parents('tr').show();
            handleCaptureMode();
        }
    }

    handleAuthorizationERPDisabled = function () {
        var authorization = $('#paramsauthorization_mode_erp_disabled').val();
        $('.capture_mode').parents('tr').show();
        handleCaptureMode();
    }

    handleERPMode = function () {
        var erp_mode = $('#paramserp_mode').val();


        $('.authorization_mode_erp_enabled').parents('tr').hide();
        $('.authorization_mode_erp_disabled').parents('tr').hide();
        $('.erp_mode_enabled_warning').parents('tr').hide();

        if (erp_mode === 'erp_mode_disabled') {
            $('.authorization_mode_erp_disabled').parents('tr').show();
            handleAuthorizationERPDisabled();
        } else if (erp_mode === 'erp_mode_enabled') {
            $('.erp_mode_enabled_warning').parents('tr').show();
            $('.authorization_mode_erp_enabled').parents('tr').show();
            handleAuthorizationERPEnabled();
        }
    }

    handleCaptureMode = function () {
        var capture_mode = $('#paramscapture_mode').val();
        $('.capture_mode_warning').parents('tr').hide();
        $('.capture_mode_warning').hide();

        if (capture_mode === 'capture_immediate') {
            $('.capture_mode_warning').parents('tr').show();
            $('.capture_mode_warning').show();
        }
    }


    handleEnvironment = function () {
        var environment = $('#paramsenvironment').val();
        $('.sandbox_error_simulation').parents('tr').hide();
        $('.ipn-sandbox').hide();
        if (environment === 'sandbox') {
            $('.sandbox_error_simulation').parents('tr').show();
            $('.ipn-sandbox').show();
        }
    }

    handleIPNDisabled = function () {
        var ipn_reception = $('#paramsipn_reception').val();
        $('.ipn_reception_disabled').parents('tr').hide();
        $('.ipnurl').parents('tr').hide();
        $('.ipn_warning').parents('tr').hide();
        if (ipn_reception === 'ipn_reception_disabled') {
            $('.ipn_reception_disabled').parents('tr').show();
        } else {
            $('.ipnurl').parents('tr').show();
            $('.ipn_warning').parents('tr').show();
        }
    }

    /**********/
    /* Events */
    /**********/
    $('#paramsregion').change(function () {
        handleRegionParameters();
    });
    $('#paramserp_mode').change(function () {
        handleERPMode();
    });
    $('#paramsauthorization_mode_erp_enabled').change(function () {
        handleAuthorizationERPEnabled();
    });
    $('#authorization_mode_erp_disabled').change(function () {
        handleAuthorizationERPDisabled();
    });
    $('#paramscapture_mode').change(function () {
        handleCaptureMode();
    });

    $('#paramsenvironment').change(function () {
        handleEnvironment();
    });
    $('#paramsipn_reception').change(function () {
        handleIPNDisabled();
    });

    /*****************/
    /* Initial calls */
    /*****************/
    handleRegionParameters();
    handleERPMode();
    handleAuthorizationERPEnabled();
    handleAuthorizationERPDisabled();
    handleEnvironment();
    handleCaptureMode();
    handleIPNDisabled();

});
