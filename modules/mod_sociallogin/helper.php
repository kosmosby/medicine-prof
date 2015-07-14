<?php
/**
 * @version		$Id: helper.php 21421 2011-06-03 07:21:02Z infograf768 $
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modSocialLoginHelper
{
	static function getReturnURL($params, $type)
	{
		$app	= JFactory::getApplication();
		$router = $app->getRouter();
		$url = null;
		if ($itemid =  $params->get($type))
		{
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);

			$query->select($db->nameQuote('link'));
			$query->from($db->nameQuote('#__menu'));
			$query->where($db->nameQuote('published') . '=1');
			$query->where($db->nameQuote('id') . '=' . $db->quote($itemid));

			$db->setQuery($query);
			if ($link = $db->loadResult()) {
				if ($router->getMode() == JROUTER_MODE_SEF) {
					$url = 'index.php?Itemid='.$itemid;
				}
				else {
					$url = $link.'&Itemid='.$itemid;
				}
			}
		}
		if (!$url)
		{
			// stay on the same page
			$uri = clone JFactory::getURI();
			$vars = $router->parse($uri);
			unset($vars['lang']);
			if ($router->getMode() == JROUTER_MODE_SEF)
			{
				if (isset($vars['Itemid']))
				{
					$itemid = $vars['Itemid'];
					$menu = $app->getMenu();
					$item = $menu->getItem($itemid);
					unset($vars['Itemid']);
					if (isset($item) && $vars == $item->query) {
						$url = 'index.php?Itemid='.$itemid;
					}
					else {
						$url = 'index.php?'.JURI::buildQuery($vars).'&Itemid='.$itemid;
					}
				}
				else
				{
					$url = 'index.php?'.JURI::buildQuery($vars);
				}
			}
			else
			{
				$url = 'index.php?'.JURI::buildQuery($vars);
			}
		}

		return base64_encode($url);
	}

	static function getType()
	{
		$user = JFactory::getUser();
		return (!$user->get('guest')) ? 'logout' : 'login';
	}

    static function oa_social_login_render_login_form_login ()
    {
        //Read settings
        // $settings = get_option ('oa_social_login_settings');

        //Display buttons if option not set or enabled
        // if (!isset ($settings ['plugin_display_in_login_form']) OR $settings ['plugin_display_in_login_form'] == '1')
        //   {
        return self::oa_social_login_render_login_form ('login');
        //   }
    }

    static function oa_social_login_render_login_form ($source)
    {
        //Import providers
        //GLOBAL $oa_social_login_providers;

        //Read settings
        //$settings = get_option ('oa_social_login_settings');
        require(JPATH_BASE.'/administrator/components/com_sociallogin/settings.php');

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

    static function getSettings() {

        $db		= JFactory::getDbo();

        $sql = "SELECT * FROM #__sociallogin_settings";
        $db->setQuery($sql);
        $rows = $db->LoadAssocList();

        $new_arr = array();
        for($i=0;$i<count($rows);$i++) {
            $new_arr[$rows[$i]['setting']] = $rows[$i]['value'];
        }

        @$new_arr['providers'] = unserialize($new_arr['providers']);

        return $new_arr;
    }


}
