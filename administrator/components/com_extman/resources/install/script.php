<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';

class ComExtmanTemporaryDispatcher extends JDispatcher
{
    /**
     * Get rid of registered Logman plugins and disable it permanently afterwards if it's version 1.0
     */
    public static function disableLogman()
    {
        $dispatcher = JDispatcher::getInstance();

        $logman = array();
        foreach ($dispatcher->_observers as $key => $observer)
        {
            if (is_object($observer) && substr(get_class($observer), 0, 9) === 'PlgLogman')
            {
                $logman[] = $key;
                unset($dispatcher->_observers[$key]);
            }
        }

        foreach ($dispatcher->_methods as $method => $observers)
        {
            $unset = array_intersect($logman, $observers);

            if ($unset)
            {
                foreach ($unset as $id)
                {
                    $key = array_search($id, $observers);
                    unset($observers[$key]);
                }

                $dispatcher->_methods[$method] = $observers;
            }
        }

        if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_logman/dispatcher/http.php'))
        {
            $query = "UPDATE #__extensions SET enabled = 0 WHERE type='plugin' AND folder='koowa' AND element='logman'";
            JFactory::getDBO()->setQuery($query)->query();

            $query = "UPDATE #__extensions SET enabled = 0 WHERE type='plugin' AND folder='system' AND element='logman'";
            JFactory::getDBO()->setQuery($query)->query();

            $query = "UPDATE #__modules SET published = 0 WHERE module='mod_logman'";
            JFactory::getDBO()->setQuery($query)->query();
        }
    }
}

class com_extmanInstallerScript
{
    protected static $_files_to_delete = array(
        'administrator/components/com_extman/com_extman.xml', // old manifest
        'administrator/components/com_extman/install/.subscription',
        'administrator/components/com_extman/views/extension/tmpl/success.php',
        'administrator/components/com_extman/views/extension/tmpl/uninstall.php',
        'administrator/components/com_extman/views/extensions/tmpl/default.php',
        'administrator/language/en-GB/com_extman.ini',
	    'administrator/language/en-GB/com_extman.menu.ini',
        'libraries/koowa/components/com_koowa/template/abstract.php',
        'libraries/koowa/components/com_koowa/template/default.php',
        'libraries/koowa/components/com_koowa/template/helper/select.php',
        'libraries/koowa/modules/template/default.php',
        'libraries/koowa/libraries/template/helper/default.php',
        'libraries/koowa/components/com_koowa/dispatcher/response/request.php',
        'libraries/koowa/components/com_koowa/language/en-GB/en-GB.com_koowa.ini',
        'libraries/koowa/libraries/translator/catalogue/cache.php',
        'libraries/koowa/modules/mod_koowa/template/locator/module.php',
        'libraries/koowa/modules/mod_koowa/translator.php',
        'libraries/koowa/modules/mod_koowa/template/default.php',
        'libraries/koowa/libraries/object/bootstrapper/abstract.php',
        'libraries/koowa/libraries/object/bootstrapper/default.php',
        'libraries/koowa/components/com_koowa/translator/abstract.php',
        'libraries/koowa/components/com_koowa/translator/catalogue.php',
        'libraries/koowa/libraries/translator/catalogue/catalogue.php',
        'libraries/koowa/libraries/translator/default.php',

    );

    protected static $_folders_to_delete = array(
        'administrator/components/com_extman/views/dashboard',
        'media/com_extman/bootstrap',
        'media/com_extman/images',
        'media/com_extman/js',
    );

    public $old_version;

	/**
	 * Name of the component
	 */
	public $component;

    protected $_token = 'c7326a714a1275378a6d4608f547737b';

	public function __construct($installer)
	{
        ComExtmanTemporaryDispatcher::disableLogman();

		preg_match('#^com_([a-z0-9_]+)#', get_class($this), $matches);
		$this->component = $matches[1];

		$this->helper = new ComExtmanInstallerHelper();

		$this->helper->installer = $installer->getParent();
		$this->helper->manifest = simplexml_load_file($installer->getParent()->getPath('manifest'));
	}
	
	protected function _fixJoomlaInstallBugs()
	{
	    $db = JFactory::getDbo();
	    
	    // Delete leftover entries from #__assets, #__extensions and #__menu
	    $queries = array();
	    $queries[] = "DELETE FROM #__assets WHERE name = '%s'";
	    $queries[] = "DELETE FROM #__extensions WHERE element = '%s'";
	    $queries[] = "DELETE FROM #__menu 
	        WHERE type = 'component' AND menutype = 'main'
	        AND link LIKE 'index.php?option=%s%%'";
	    
	    foreach ($queries as $query) {
	        $db->setQuery(sprintf($query, 'com_'.$this->component))
	           ->query();
	    }
	}
	
	protected function _fixJoomlaUpdateBugs()
	{
	    $db = JFactory::getDbo();
	    $component = 'com_'.$this->component;
	    
	    // Delete excess entries from #__extensions
	    $query = "SELECT extension_id FROM #__extensions WHERE element = '%s' ORDER BY extension_id ASC";
	    $ids = $db->setQuery(sprintf($query, $component))->loadColumn();

	    if (count($ids) > 1) {
	        $query = sprintf("DELETE FROM #__extensions WHERE element = '%s' AND extension_id <> %d", $component, $ids[0]);
	        $db->setQuery($query)->query();
	    }
	    
	    // Delete excess entries from #__assets
	    $query = "SELECT id FROM #__assets WHERE name = '%s' ORDER BY id ASC LIMIT 1";
	    $ids = $db->setQuery(sprintf($query, $component))->loadColumn();
	    
	    if (count($ids) > 1) {
	        $query = sprintf("DELETE FROM #__assets WHERE name = '%s' AND id <> %d", $component, $ids[0]);
	        $db->setQuery($query)->query();
	    }
	    
	    // Delete entries from #__menu to be sure
	    $query = "DELETE FROM #__menu 
	        WHERE type = 'component' AND menutype = 'main'
	        AND link LIKE 'index.php?option=%s%%'";
	    $query = sprintf($query, $component);

	    $db->setQuery($query)->query();
	}

	public function preflight($type, $installer)
	{
        if ($type === 'update')
        {
            $query = "SELECT manifest_cache FROM #__extensions WHERE element = 'com_extman'";
            if ($result = JFactory::getDBO()->setQuery($query)->loadResult())
            {
                $manifest = new JRegistry($result);
                $this->old_version = $manifest->get('version', null);
            }
        }

        if (in_array($type, array('install'))) {
            $this->_fixJoomlaInstallBugs();
        } else {
            $this->_fixJoomlaUpdateBugs();
        }

		if ($errors = $this->helper->getServerErrors())
		{
			ob_start();
			echo JText::_("The installation can't proceed until you resolve the following: ");
			echo implode(',', $errors);

			$error = ob_get_clean();
			JFactory::getApplication()->enqueueMessage($error, 'error');

			return false;
		}

        return true;
	}

	public function postflight($type, $installer)
	{
        $params     = JComponentHelper::getParams('com_extman');
        $old_uuid   = $params->get('joomlatools_user_id');
        $uuid       = $this->helper->getUUID();
        $user_id_saved = false;

        if ($uuid !== false && $old_uuid != $uuid)
        {
            $user_id_saved = $this->helper->storeUUID($uuid);

            // If we didn't store the user, we don't want to keep track of him either in this run
            if(!$user_id_saved) {
                $uuid = null;
            }
        }

        $this->helper->installFramework($type === 'discover_install');

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        foreach (self::$_files_to_delete as $file)
        {
            $path = JPATH_ROOT.'/'.(string)$file;

            if (file_exists($path)) {
                JFile::delete($path);
            }
        }

        foreach (self::$_folders_to_delete as $folder)
        {
            $path = JPATH_ROOT.'/'.(string)$folder;

            if (file_exists($path)) {
                JFolder::delete($path);
            }
        }

        /*
         * When releasing RC5 we removed the wrong column accidentally
         * which broke uninstall functionality
         *
         * This is here to make sure we fix those installs on the fly by adding the missing column
         */
        $db = JFactory::getDbo();
        $db->setQuery('SHOW TABLES LIKE '.$db->quote($db->replacePrefix('#__extman_extensions')));
        if ($db->loadResult())
        {
            $db->setQuery("SHOW COLUMNS FROM " . $db->replacePrefix('#__extman_extensions'));
            $columns = $db->loadObjectList();

            $fields = array();
            foreach($columns as $column)  {
                $fields[$column->Field] = $column;
            }

            if (!isset($fields['joomla_extension_id']))
            {
                $query = "ALTER TABLE `#__extman_extensions` ADD COLUMN `joomla_extension_id` int(11) unsigned NOT NULL DEFAULT '0' AFTER `identifier`";
                $db->setQuery($query);
                $db->query();
            }

            if (isset($fields['joomlatools_user_id']))
            {
                $query = "ALTER TABLE `#__extman_extensions` DROP COLUMN `joomlatools_user_id`";
                $db->setQuery($query);
                $db->query();
            }
        }

        // Disable template overrides when upgrading to 2.0.0
        if ($this->old_version && version_compare($this->old_version, '1.0.0', '<='))
        {
            $warning = 'This is important! You need to upgrade other Joomlatools extensions too or your site will break.';
            JFactory::getApplication()->enqueueMessage($warning, 'warning');

            $this->disableOverrides();
        }

        if ($this->old_version && version_compare($this->old_version, '2.0.0', '<=')) {
            $this->renameBootstrapOverride();
        }

        $extension = new stdClass();
        $extension->name = 'com_extman';
        $extension->joomlatools_user_id = $uuid;
        $extension->user_id_saved = $user_id_saved;
        $extension->version = (string)$this->helper->manifest->version;

        $event = $type === 'update' ? 'update' : 'install';
        echo $this->track($event, $extension);

        // Need to boot the framework when doing multiple installs in Composer
        if ($type !== 'update' && !class_exists('Koowa')) {
            $this->helper->bootFramework();
        }

        $this->clearCache();
	}

    /**
     * Copied from ComExtmanModelEntityExtension::clearCache since we cannot call that method without Koowa
     * And when we are upgrading from Koowa 1 to 2 it's safer not to use any Koowa code
     */
    public function clearCache()
    {
        // Joomla does not clean up its plugins cache for us
        JCache::getInstance('callback', array(
            'defaultgroup' => 'com_plugins',
            'cachebase'    => JPATH_ADMINISTRATOR . '/cache'
        ))->clean();

        JFactory::getCache('com_koowa.tables', 'output')->clean();
        JFactory::getCache('com_koowa.templates', 'output')->clean();

        // Clear APC opcode cache
        if (extension_loaded('apc'))
        {
            apc_clear_cache();
            apc_clear_cache('user');
        }
    }

    public function renameBootstrapOverride()
    {
        jimport('joomla.filesystem.file');

        $overrides = array_merge(
            glob(JPATH_ADMINISTRATOR . '/templates/*/*-joomlatools-bootstrap'),
            glob(JPATH_SITE . '/templates/*/*-joomlatools-bootstrap')
        );

        foreach($overrides as $override)
        {
            $destination = str_replace('-joomlatools-bootstrap', '-koowa-bootstrap.txt', $override);
            JFile::move($override, $destination);
        }
    }

    public function disableOverrides()
    {
        jimport('joomla.filesystem.folder');

        $folders = array(
            'com_docman',
            'com_extman',
            'com_fileman',
            'com_logman',
            'com_files',
            'com_activities',
            'mod_docman_documents',
            'mod_logman'
        );
        $overrides = array_merge(glob(JPATH_ADMINISTRATOR.'/templates/*/html'), glob(JPATH_SITE.'/templates/*/html'));
        $renamed   = array();

        foreach($overrides as $override)
        {
            foreach ($folders as $folder)
            {
                $path = $override.'/'.$folder;

                if (is_dir($path))
                {
                    $template = preg_replace('#.*?/templates/(.*?)/html#i', '\\1', $override);
                    $renamed[$template][] = $folder;

                    JFolder::move($path, $override.'/_'.$folder);
                }
            }
        }

        if (count($renamed))
        {
            $message = sprintf('We also noticed that you were using <a target="_blank" href="%s">template overrides</a> for our extensions.
                They are likely not compatible with our extensions\' new structure and might break your site,
                so we temporarily disabled them for you.<br />
                For instructions on how to review and enable them again please check out <a target="_blank" href="%s">our detailed tutorial here.</a>
                <br />
                Please write down this list of overrides that have been disabled:<br />',
                'http://www.joomlatools.com/support/forums/topic/1138-20-getting-started-with-template-overrides',
                'http://www.joomlatools.com/support/forums/topic/3447-how-to-migrate-template-overrides');
            $message .= '<ul>';
            foreach ($renamed as $template => $folders)
            {
                foreach ($folders as $folder) {
                    $message.= '<li><strong>'.$folder.'</strong> in the <strong>'.$template.'</strong> template</li>';
                }
            }
            $message .= '</ul>';
            JFactory::getApplication()->enqueueMessage($message, 'warning');
        }
    }

	public function uninstall($installer)
	{
        $db    = JFactory::getDBO();
        $query = sprintf('SHOW TABLES LIKE %s', $db->quote($db->replacePrefix('#__extman_extensions')));

        if ($db->setQuery($query)->loadResult())
        {
            // Pre-cache uninstall tracking code since we are gonna get rid of the framework
            if (class_exists('Koowa'))
            {
                $params = JComponentHelper::getParams('com_extman');
                $uuid   = $params->get('joomlatools_user_id');

                $controller = KObjectManager::getInstance()->getObject('com://admin/extman.controller.extension')
                    ->view('extension')
                    ->layout('uninstall')
                    ->event('uninstall');

                $extension = $controller->read();
                $extension->name = 'EXTman';
                $extension->joomlatools_user_id = $uuid;
                $extension->version = (string)$this->helper->manifest->version;

                echo $controller->render();
            }

            $db->setQuery("SELECT name FROM #__extman_extensions WHERE parent_id = 0 AND identifier <> 'com:extman'");
            $results = $db->loadColumn();

            if (count($results))
            {
                $extension = count($results)  == 1 ? sprintf('the <strong>%s</strong> extension by Joomlatools installed', $results[0]) : sprintf('%d Joomlatools extensions installed', count($results));
                JFactory::getApplication()->enqueueMessage(sprintf(
                    "You have $extension. EXTman is needed for Joomlatools extensions to work properly. These extensions will not work until you re-install EXTman. EXTman database tables are not deleted to make sure your site still works if you install it again.",
                    JRoute::_('index.php?option=com_extman')), 'error');
            }
            else
            {
                $tables = array('#__extman_extensions', '#__extman_dependencies');
                foreach ($tables as $table)
                {
                    $db->setQuery('DROP TABLE IF EXISTS '.$db->replacePrefix($table));
                    $db->query();
                }
            }
        }

		$this->helper->uninstallFramework();
	}

    // Rest is copied from trackable.php
    public function getTrackingInfo($extension)
    {
        $version = new JVersion();

        $server = @php_uname('s').' '.@php_uname('r');

        // php_uname is disabled
        if (empty($server)) {
            $server = 'Unknown';
        }

        $info = array(
            'Product' 			=> $extension->name,
            'Version' 			=> $extension->version,
            'Joomla' 			=> $this->_extractVersionInfo($version->getShortVersion()),
            'Koowa'	 			=> class_exists('Koowa') && method_exists('Koowa', 'getInstance') ? Koowa::getInstance()->getVersion() : 0,
            'PHP' 				=> $this->_extractVersionInfo(phpversion()),
            'Database' 			=> $this->_extractVersionInfo(JFactory::getDBO()->getVersion()),
            'Web Server' 		=> @$_SERVER['SERVER_SOFTWARE'],
            'Web Server OS' 	=> $server,
            'Joomla Language' 	=> JFactory::getLanguage()->getName(),
            'Identifier'        => $extension->joomlatools_user_id
        );

        return $info;
    }

    public function track($event, $extension)
    {
        $info = json_encode($this->getTrackingInfo($extension));

        $domain = JURI::getInstance()->getHost();

        $statements = array(
            "mixpanel.init('".$this->_token."')",
            "mixpanel.name_tag('".$domain."')",
            "mixpanel.track('".$event."', ".$info.")"
        );

        if(!empty($extension->joomlatools_user_id)) {
            array_splice($statements, 2, 0, array("mixpanel.identify('".$extension->joomlatools_user_id."')"));
        }

        if (!empty($extension->joomlatools_user_id) && !empty($extension->name))
        {
            if ($event == 'uninstall' || $extension->user_id_saved)
            {
                $count = $event == 'uninstall' ? -1 : 1;
                $command = "mixpanel.people.increment('".$extension->name."', ".$count.")";

                array_splice($statements, 2, 0, array($command));
            }
        }

        $return = "<script type=\"text/javascript\">(function(c,a){window.mixpanel=a;var b,d,h,e;b=c.createElement(\"script\");b.type=\"text/javascript\";b.async=!0;b.src=(\"https:\"===c.location.protocol?\"https:\":\"http:\")+'//cdn.mxpnl.com/libs/mixpanel-2.2.min.js';d=c.getElementsByTagName(\"script\")[0];d.parentNode.insertBefore(b,d);a._i=[];a.init=function(b,c,f){function d(a,b){var c=b.split(\".\");2==c.length&&(a=a[c[0]],b=c[1]);a[b]=function(){a.push([b].concat(Array.prototype.slice.call(arguments,0)))}}var g=a;\"undefined\"!==typeof f?g=a[f]=[]:f=\"mixpanel\";g.people=g.people||[];h=['disable','track','track_pageview','track_links','track_forms','register','register_once','unregister','identify','alias','name_tag','set_config','people.set','people.set_once','people.increment','people.track_charge','people.append'];for(e=0;e<h.length;e++)d(g,h[e]);a._i.push([b,c,f])};a.__SV=1.2;})(document,window.mixpanel||[]);</script>"
            . "<script type=\"text/javascript\">".implode('; ', $statements)."</script>";

        return $return;
    }

    protected function _extractVersionInfo($version)
    {
        return substr($version, 0, strpos($version, '.', strpos($version, '.')+1));
    }
}