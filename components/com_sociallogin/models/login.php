<?php
/**
 * @version		$Id: login.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');
/**
 * Rest model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class SocialloginModelLogin extends JModelForm
{
	/**
	 * Method to get the login form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.login', 'login', array('load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	array	The default data is an empty array.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered login form data.
		$app	= JFactory::getApplication();
		$data	= $app->getUserState('users.login.form.data', array());

		// check for return URL from the request first
		if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
			$data['return'] = base64_decode($return);
			if (!JURI::isInternal($data['return'])) {
				$data['return'] = '';
			}
		}

		// Set the return URL if empty.
		if (!isset($data['return']) || empty($data['return'])) {
			$data['return'] = 'index.php?option=com_users&view=profile';
		}
		$app->setUserState('users.login.form.data', $data);

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Get the application object.
		$params	= JFactory::getApplication()->getParams('com_users');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param	object	A form object.
	 * @param	mixed	The data expected for the form.
	 * @param	string	The name of the plugin group to import (defaults to "content").
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		// Import the approriate plugin group.
		JPluginHelper::importPlugin($group);

		// Get the dispatcher.
		$dispatcher	= JDispatcher::getInstance();

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			// Get the last error.
			$error = $dispatcher->getError();

			// Convert to a JException if necessary.
			if (!JError::isError($error)) {
				throw new Exception($error);
			}
		}
	}

    static function getSettings() {

        $db		= JFactory::getDbo();

        $sql = "SELECT * FROM #__sociallogin_settings";
        $db->setQuery($sql);
        $rows = $db->LoadAssocList();

        $new_arr = array();
        for($i=0;$i<count($rows);$i++) {
            $new_arr[$rows[$i]['setting']] = $rows[$i]['value'];
        }

        $new_arr['providers'] = unserialize($new_arr['providers']);

        return $new_arr;
    }



    static function oa_social_login_render_login_form ($source)
    {
        //Import providers
        //GLOBAL $oa_social_login_providers;

        //Read settings
        //$settings = get_option ('oa_social_login_settings');
        require_once(JPATH_BASE.'/administrator/components/com_sociallogin/settings.php');

        $settings= self::getSettings();

        //API Subdomain
        $api_subdomain = (!empty ($settings ['api_subdomain']) ? $settings ['api_subdomain'] : '');

        //API Subdomain Required
        if (!empty ($api_subdomain))
        {
            //Caption
            $plugin_caption = (!empty ($settings ['plugin_caption']) ? $settings ['plugin_caption'] : '');

            //Build providers
            $providers = array ();
            if (is_array ($settings ['providers']))
            {
                foreach ($settings ['providers'] AS $settings_provider_key => $settings_provider_name)
                {
                    if (isset ($oa_social_login_providers [$settings_provider_key]))
                    {
                        $providers [] = $settings_provider_key;
                    }
                }
            }

            //Widget
            if ($source == 'widget')
            {
                $css_theme_uri = 'http://oneallcdn.com/css/api/socialize/themes/wp_widget.css';
                $show_title = false;
            }
            //Inline
            else
            {
                //For all page, except the Widget
                $css_theme_uri = 'http://oneallcdn.com/css/api/socialize/themes/wp_inline.css';
                $show_title = (empty ($plugin_caption) ? false : true);

                //Anchor to comments
                if ($source == 'comments')
                {
                    $source .= '#comments';
                }
            }

            $settings['providers'] = $providers;
            $settings['show_title'] = $show_title;
            $settings['plugin_caption'] = $plugin_caption;
            $settings['source'] = $source;
            $settings['css_theme_uri'] = $css_theme_uri;

            return $settings;

        }
        else {
            echo "Please, enter api subdomain settings in component config";
        }



    }



}
