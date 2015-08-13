<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanInstallerHelper
{
	public function getServerErrors()
	{
		$errors = array();

		if(!class_exists('mysqli')) {
		    $errors[] = JText::_("We're sorry but your server isn't configured with the MySQLi database driver. Please
		    contact your host and ask them to enable MySQLi for your server.");
		}

		if(version_compare(phpversion(), '5.3', '<')) {
		    $errors[] = sprintf(JText::_("EXTman requires PHP 5.3 or later. Your server is running PHP %s."), phpversion());
		}

        if (!function_exists('token_get_all')) {
            $errors[] = 'PHP tokenizer extension must be enabled by your host.';
        }

		if(version_compare(JFactory::getDBO()->getVersion(), '5.0.41', '<')) {
		    $errors[] = sprintf(JText::_("EXTman requires MySQL 5.0.41 or later. Your server is running MySQL %s."),
                JFactory::getDBO()->getVersion());
		}

        // Check a bunch of Ohanah v2 files to see if it is installed
        if (file_exists(JPATH_ADMINISTRATOR.'/components/com_ohanah/controllers/event.php')
            || file_exists(JPATH_SITE.'/components/com_ohanah/dispatcher.php')
            || file_exists(JPATH_SITE.'/components/com_ohanah/controllers/event.php'))
        {
            $errors[] = sprintf("You have the Ohanah event management extension installed.
            Ohanah works with an older version of our Nooku framework, so updating EXTman now would break your site.
            Installation is aborting. For more information please read our detailed explanation <a target=\"_blank\" href=\"%s\">here</a>.",
            'http://www.joomlatools.com/framework-known-issues');
        }

		if (class_exists('Koowa') && (!method_exists('Koowa', 'getInstance') || version_compare(Koowa::getInstance()->getVersion(), '1', '<')))
        {
			$errors[] = sprintf(JText::_("Your site has an older version of our library already installed. Installation
			 is aborting to prevent creating conflicts with other extensions."));
		}

		//Some hosts that specialize on Joomla are known to lock permissions to the libraries folder
		if(!is_writable(JPATH_LIBRARIES)) 
        {
		    $errors[] = sprintf(JText::_("The <em title=\"%s\">libraries</em> folder needs to be writable in order for
		    EXTman to install correctly."), JPATH_LIBRARIES);
		}

		if (count($errors) === 0 && $this->checkDatabaseType() === false)
		{
			$link     = JRoute::_('index.php?option=com_config');
			$errors[] = "In order to use Joomlatools extensions, your database type in Global Configuration should be set
			to <strong>MySQLi</strong>. Please go to <a href=\"$link\">Global Configuration</a> and in the 'Server' tab
			change your Database Type to <strong>MySQLi</strong>.";
		}

		return $errors;
	}

	public function checkDatabaseType()
	{
		$result = true;
		if(JFactory::getApplication()->getCfg('dbtype') === 'mysql')
		{
			$result = $this->setDatabaseType();
			if ($result) {
				JFactory::getApplication()->enqueueMessage("Your database type has been converted to 'mysqli'.");
			}
		}

		return $result;
	}

	public function setDatabaseType()
	{
	    $path = JPATH_CONFIGURATION.'/configuration.php';
	    $result = false;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp = JClientHelper::getCredentials('ftp');

	    jimport('joomla.filesystem.path');
		if ($ftp['enabled'] || (JPath::isOwner($path) && JPath::setPermissions($path, '0644'))) {
	    	$search     = JFile::read($path);
	        $replaced   = str_replace('$dbtype = \'mysql\';', '$dbtype = \'mysqli\';', $search);
	        $result 	= JFile::write($path, $replaced);

	        if (!$ftp['enabled'] && JPath::isOwner($path)) {
	        	JPath::setPermissions($path, '0444');
	        }
	    }

	    return $result;
	}

    public function getFrameworkExtensions()
    {
        return array(
            'plugin'  => array('type' => 'plugin', 'element' => 'koowa', 'folder' => 'system'),
            'file'    => array('type' => 'file', 'element' => 'files_koowa'),
            'package' => array('type' => 'package', 'element' => 'pkg_koowa')
        );
    }

    public function deleteKoowaV1()
    {
        if (is_file(JPATH_ROOT.'/libraries/koowa/koowa.php'))
        {
            $query = "DELETE FROM #__extensions WHERE type='plugin' AND folder='system' AND element='koowa'";
            JFactory::getDBO()->setQuery($query)->query();

            $delete = array(
                'administrator/components/com_default',
                'administrator/modules/mod_default',
                'components/com_default',
                'media/com_default',
                'media/lib_koowa',
                'modules/mod_default',
                'plugins/system/koowa',
                'plugins/system/koowa.php',
                'plugins/system/koowa.xml'
            );

            foreach ($delete as $node)
            {
                $path = JPATH_ROOT.'/'.$node;

                if (is_file($path)) {
                    JFile::delete($path);
                } else if (is_dir($path)) {
                    JFolder::delete($path);
                }
            }

            JFolder::delete(JPATH_ROOT.'/libraries/koowa');
        }
    }

    public function installFramework($is_discover_install = false)
    {
        $result = false;

        if ($is_discover_install === false)
        {
            $this->deleteKoowaV1();

            $path      = $this->installer->getPath('source').'/pkg_koowa';
            $installer = new JInstaller();

            $result = $installer->install($path);
        }
        else
        {
            $check = JFactory::getDbo()
                ->setQuery("SELECT extension_id, folder AS type, element AS name, state
                    FROM #__extensions
                    WHERE folder = 'system' AND element = 'koowa'"
                )->loadObject();

            if (empty($check) || $check->state == -1)
            {
                if ($check && $check->state == -1) {
                    $instance = JTable::getInstance('extension');
                    $instance->load($check->extension_id);
                }
                else {
                    $instance = $this->_discoverPlugin();
                }

                if ($instance) {
                    $installer = JInstaller::getInstance();
                    $result = $installer->discover_install($instance->extension_id);
                }
            }

            // Insert package and file installers into the database
            if ($result)
            {
                $package_id = $this->getExtensionId(array('type' => 'package', 'element' => 'pkg_koowa'));
                if (!$package_id)
                {
                    $manifest = JPATH_SITE.'/administrator/manifests/packages/pkg_koowa.xml';

                    if (file_exists($manifest)) {
                        $manifest_details = JInstaller::parseXMLInstallFile($manifest);
                    }
                    else $manifest_details = new stdClass();

                    $extension = JTable::getInstance('extension');
                    $extension->set('name', 'koowa');
                    $extension->set('type', 'package');
                    $extension->set('element', 'pkg_koowa');
                    $extension->set('folder', '');
                    $extension->set('client_id', 0);
                    $extension->set('protected', 1);
                    $extension->set('manifest_cache', json_encode($manifest_details));
                    $extension->set('params', '{}');

                    $extension->store();
                }

                $files_id = $this->getExtensionId(array('type' => 'file', 'element' => 'files_koowa'));
                if (!$files_id)
                {
                    $manifest = JPATH_SITE.'/administrator/manifests/files/files_koowa.xml';

                    if (file_exists($manifest)) {
                        $manifest_details = JInstaller::parseXMLInstallFile($manifest);
                    }
                    else $manifest_details = new stdClass();

                    $extension = JTable::getInstance('extension');
                    $extension->set('name', 'files_koowa');
                    $extension->set('type', 'file');
                    $extension->set('element', 'files_koowa');
                    $extension->set('folder', '');
                    $extension->set('client_id', 0);
                    $extension->set('protected', 1);
                    $extension->set('manifest_cache', json_encode($manifest_details));
                    $extension->set('params', '{}');

                    $extension->store();
                }
            }
        }

        if ($result)
        {
            $parts = $this->getFrameworkExtensions();

            $this->setCoreExtension($parts['package'], 1);
            $this->setCoreExtension($parts['file'], 1);
            $this->setCoreExtension($parts['plugin'], 1);

            // Enable plugin
            $query = sprintf('UPDATE #__extensions SET enabled = 1 WHERE extension_id = %d', $this->getExtensionId($parts['plugin']));
            JFactory::getDBO()->setQuery($query)->query();
        }
    }

	/**
	 * Can't use JPluginHelper here since there is no way
	 * of clearing the cached list of plugins.
	 *
	 * @return PlgSystemKoowa Instantiated plugin object
	 */
    public function bootFramework()
    {
        if (class_exists('Koowa')) {
            return true;
        }

        $path = JPATH_PLUGINS.'/system/koowa/koowa.php';

        if (!file_exists($path)) {
            return false;
        }

        require_once $path;

        $dispatcher = JDispatcher::getInstance();
        $className  = 'plgSystemKoowa';

        // Constructor does all the work in the plugin
        if (class_exists($className))
        {
            $db = JFactory::getDbo();
            $db->setQuery("SELECT folder AS type, element AS name, params
			 FROM #__extensions
			 WHERE folder = 'system' AND element = 'koowa'"
            );
            $plugin = $db->loadObject();

            new $className($dispatcher, (array) ($plugin));
        }

        return class_exists('Koowa');
    }

    public function storeUUID($uuid = false)
    {
        if($uuid === false) {
            return false;
        }

        if($this->_isLocal()) {
            return false;
        }

        $db     = JFactory::getDbo();

        $params = JComponentHelper::getParams('com_extman');
        $params->set('joomlatools_user_id', $uuid);

        $componentId = JComponentHelper::getComponent('com_extman')->id;

        $query = "UPDATE #__extensions SET params = ".$db->quote($params->toString())." WHERE `extension_id` = ". (int) $componentId;

        $db->setQuery($query);
        $db->query();

        return true;
    }

    public function getUUID()
    {
        $file   = $this->installer->getPath('source').'/resources/install/.subscription';

        if(JFile::exists($file))
        {
            $uuid = trim(JFile::read($file));

            return $uuid;
        }

        return false;
    }

    public function setCoreExtension($extension, $value = true)
    {
        $value = (int) $value;
        $id    = (int) $this->getExtensionId($extension);
        $db    = JFactory::getDBO();

        $query = "UPDATE #__extensions SET protected = {$value}
					WHERE extension_id = {$id}
					LIMIT 1";

        $db->setQuery($query);

        return $db->query();
    }

	public function getExtensionId($extension)
	{
		$type    = (string)$extension['type'];
		$element = (string)$extension['element'];
		$folder  = isset($extension['folder']) ? (string) $extension['folder'] : '';
		$cid     = isset($extension['client_id']) ? (int) $extension['client_id'] : 0;

		if ($type == 'component') {
			$cid = 1;
		}

		if ($type == 'component' && substr($element, 0, 4) !== 'com_') {
			$element = 'com_'.$element;
		} elseif ($type == 'module' && substr($element, 0, 4) !== 'mod_') {
			$element = 'mod_'.$element;
		}

		$db = JFactory::getDBO();
        $query = "SELECT extension_id FROM #__extensions
            WHERE type = '$type' AND element = '$element' AND folder = '$folder' AND client_id = '$cid'
            LIMIT 1
        ";

		$db->setQuery($query);

		return $db->loadResult();
	}

    protected function _discoverPlugin()
    {
        $installer = JInstaller::getInstance();
        $installer->loadAllAdapters();

        $adapter    = $installer->getAdapter('plugin');
        $discovered = $adapter->discover();
        $instance   = null;

        foreach ($discovered as $plugin)
        {
            if ($plugin->element === 'koowa' && $plugin->folder === 'system') {
                $instance = $plugin;
                $instance->store();
                break;
            }
        }

        return $instance;
    }

    public function uninstallFramework()
    {
        $parts = $this->getFrameworkExtensions();

        $this->setCoreExtension($parts['package'], 0);
        $this->setCoreExtension($parts['file'], 0);
        $this->setCoreExtension($parts['plugin'], 0);

        $package_id = $this->getExtensionId($parts['package']);
        if ($package_id)
        {
            $installer = new JInstaller();
            $installer->uninstall('package', $this->getExtensionId($parts['package']), 0);
        }
    }

    /**
     * Tests a list of DB privileges against the current application DB connection.
     *
     * @param array $privileges An array containing the privileges to be checked.
     *
     * @return array True An array containing the privileges that didn't pass the test, i.e. not granted.
     */
    public function checkDatabasePrivileges($privileges)
    {
        $privileges = (array) $privileges;

        $db = JFactory::getDBO();

        $query = 'SELECT @@SQL_MODE';
        $db->setQuery($query);
        $sql_mode = $db->loadResult($query);

        $db_name = JFactory::getApplication()->getCfg('db');

        // Quote and escape DB name.
        if (strtolower($sql_mode) == 'ansi_quotes') {
            // Double quotes as delimiters.
            $db_name = '"' . str_replace('"', '""', $db_name) . '"';
        } else {
            $db_name = '`' . str_replace('`', '``', $db_name) . '`';
        }

        // Properly escape DB name.
        $possible_tables = array(
            '*.*',
            $db_name . '.*',
            str_replace('_', '\_', $db_name) . '.*'
        );

        $query = 'SHOW GRANTS';
        $db->setQuery($query);

        $grants = $db->loadColumn();
        $granted = array();

        foreach ($privileges as $privilege)
        {
            foreach ($grants as $grant)
            {
                $regex = '/(grant\s+|,\s*)' . $privilege . '(\s*,|\s+on)/i';

                if (stripos($grant, 'ALL PRIVILEGES') || preg_match($regex, $grant))
                {
                    // Check tables
                    $tables = substr($grant, stripos($grant, ' ON ') + 4);
                    $tables = substr($tables, 0, stripos($tables, ' TO'));
                    $tables = trim($tables);

                    if (in_array($tables, $possible_tables)) {
                        $granted[] = $privilege;
                    }
                }
            }
        }

        return array_diff($privileges, $granted);
    }

    protected function _isLocal()
    {
        $isLocal = false;
        $ip      = @$_SERVER['REMOTE_ADDR'];

        if(!empty($ip))
        {
            $isLoopback = preg_match('/^localhost$|^127(?:\.[0-9]+){0,2}\.[0-9]+$|^(?:0*\:)*?:?0*1$/', $ip) ? true : false;

            if(!$isLoopback)
            {
                $result  = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
                $isLocal = $result === false ? true : false;
            }
            else $isLocal = true;
        }

        return $isLocal;
    }
}