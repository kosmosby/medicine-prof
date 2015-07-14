<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of HelloWorld component
 */
class SocialLoginController extends JController
{
    /**
     * display task
     *
     * @return void
     */

    function display($cachable = false)
    {
        // set default view if not set
        JRequest::setVar('view', JRequest::getCmd('view', 'SocialLogin'));

        // call parent behavior
        parent::display($cachable);
    }

    function apply() {
        $model = &$this->getModel();

        $model->saveSettings();

        $this->setRedirect(JRoute::_('index.php?option=com_sociallogin&view=sociallogin&layout=default', false));
    }

    function check_api_settings() {

        $model = &$this->getModel();

        //Check if all fields have been filled out
        if (empty ($_POST ['api_subdomain']) OR empty ($_POST ['api_key']) OR empty ($_POST ['api_secret']))
        {
            echo 'error_not_all_fields_filled_out';
            $model->set_option ('oa_social_login_api_settings_verified',0);
            die ();
        }

        //Subdomain
        $api_subdomain = trim (strtolower ($_POST ['api_subdomain']));

        //Full domain entered
        if (preg_match ("/([a-z0-9\-]+)\.api\.oneall\.com/i", $api_subdomain, $matches))
        {
            $api_subdomain = $matches [1];
        }

        //Check subdomain format
        if (!preg_match ("/^[a-z0-9\-]+$/i", $api_subdomain))
        {
            echo 'error_subdomain_wrong_syntax';
            $model->set_option ('oa_social_login_api_settings_verified',0);
            die ();
        }

        //Domain
        $api_domain = $api_subdomain . '.api.oneall.com';

        //Key
        $api_key = $_POST ['api_key'];

        //Secret
        $api_secret = $_POST ['api_secret'];

        //Ping
        $curl = curl_init ();
        curl_setopt ($curl, CURLOPT_URL, 'https://' . $api_domain . '/tools/ping.json');
        curl_setopt ($curl, CURLOPT_HEADER, 0);
        curl_setopt ($curl, CURLOPT_USERPWD, $api_key . ":" . $api_secret);
        curl_setopt ($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt ($curl, CURLOPT_VERBOSE, 0);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($curl, CURLOPT_FAILONERROR, 0);

        if (($json = curl_exec ($curl)) === false)
        {
            curl_close ($curl);

            echo 'error_communication';
            $model->set_option ('oa_social_login_api_settings_verified',0);
            die ();
        }

        //Success
        $http_code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
        curl_close ($curl);

        //Authentication Error
        if ($http_code == 401)
        {
            echo 'error_authentication_credentials_wrong';
            $model->set_option ('oa_social_login_api_settings_verified',0);
            die ();
        }
        elseif ($http_code == 404)
        {
            echo 'error_subdomain_wrong';
            $model->set_option ('oa_social_login_api_settings_verified',0);
            die ();
        }
        elseif ($http_code == 200)
        {
            echo 'success';
            $model->set_option ('oa_social_login_api_settings_verified',1);
            die ();
        }
    die();
    }

}