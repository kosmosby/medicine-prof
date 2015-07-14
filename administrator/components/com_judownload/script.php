<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */


// No direct access to this file
defined('_JEXEC') or die ('Restricted access');

JLoader::register('JUDownloadInstallerHelper', __DIR__ . '/admin/helpers/installer.php');
JLoader::register('JUDownloadInstallerHelper', JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/installer.php');




class Com_JUDownloadInstallerScript
{
	
	public function install($parent)
	{
	}

	public function discover_install($parent)
	{
	}

	
	public function uninstall($parent)
	{
		
		$JUDownloadInstallerHelper = new JUDownloadInstallerHelper();
		$JUDownloadInstallerHelper->deleteJUDLMenu();
	}

	
	public function update($parent)
	{
		
		
		

		$old_version = $this->getOldVersion();
		if ($old_version < '1.0.5')
		{
			
			if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_judownload/fields/'))
			{
				JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_judownload/fields/');
			}

			if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_judownload/plugins/'))
			{
				JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_judownload/plugins/');
			}

			
			$j3x_backend_files = JFolder::files(JPATH_ROOT . '/administrator/components/com_judownload/views/', '\.j3x\.php',
				true, true);
			foreach ($j3x_backend_files as $file)
			{
				try
				{
					JFile::delete($file);
				}
				catch (Exception $e)
				{
				}
			}

			$j3x_frontend_files = JFolder::files(JPATH_ROOT . '/components/com_judownload/templates/', '\.j3x\.php',
				true, true);
			foreach ($j3x_frontend_files as $file)
			{
				try
				{
					JFile::delete($file);
				}
				catch (Exception $e)
				{
				}
			}

			if (JFolder::exists(JPATH_ROOT . '/components/com_judownload/templates/default/fields/core_rating/'))
			{
				JFolder::delete(JPATH_ROOT . '/components/com_judownload/templates/default/fields/core_rating/');
			}

			if (JFolder::exists(JPATH_ROOT . '/components/com_judownload/templates/default/fields/multirating/'))
			{
				JFolder::delete(JPATH_ROOT . '/components/com_judownload/templates/default/fields/multirating/');
			}
		}

		if ($old_version < '1.0.9')
		{
			
			JLoader::register('JUDownloadHelper', JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/judownload.php');
			JLoader::register('JUDownloadFrontHelperCategory', JPATH_SITE . '/components/com_judownload/helpers/category.php');
			$params = JUDownloadHelper::getParams();
			$params->set('document_original_image_directory', 'media/com_judownload/images/gallery/original/');
			$params->set('document_small_image_directory', 'media/com_judownload/images/gallery/small/');
			$params->set('document_big_image_directory', 'media/com_judownload/images/gallery/big/');
			$params->set('document_icon_directory', 'media/com_judownload/images/document/');
			$params->set('avatar_directory', 'media/com_judownload/images/avatar/');
			$params->set('collection_icon_directory', 'media/com_judownload/images/collection/');

			if ($params->get('document_default_icon', 'default-document.png') == 'default-icon.png')
			{
				$params->set('document_default_icon', 'default-document.png');
			}

			if ($params->get('collection_default_icon', 'default-collection.png') == 'collection-default-icon.png')
			{
				$params->set('collection_default_icon', 'default-collection.png');
			}

			$db    = JFactory::getDbo();
			$query = 'UPDATE #__judownload_categories SET config_params = ' . $db->quote($params->toString()) . ' WHERE id = 1';
			$db->setQuery($query);
			$db->execute();

			
			$user  = JFactory::getUser();
			$date  = JFactory::getDate();
			$query = "insert into `#__judownload_plugins`(`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values ('field','Gallery','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','10 Jan 2015','GNU/GPL','core_gallery',1,1,0,'0000-00-00 00:00:00','',0)";
			$db->setQuery($query);
			$db->execute();

			$galleryPluginId = $db->insertid();

			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$fieldTable = JTable::getInstance('Field', 'JUDownloadTable');
			$data       = array(
				'group_id'                    => 1,
				'plugin_id'                   => $galleryPluginId,
				'field_name'                  => 'gallery',
				'caption'                     => 'Gallery',
				'hide_caption'                => '0',
				'alias'                       => 'gallery',
				'description'                 => '',
				'attributes'                  => '',
				'predefined_values_type'      => '1',
				'predefined_values'           => '',
				'php_predefined_values'       => '',
				'prefix_text_mod'             => '',
				'suffix_text_mod'             => '',
				'prefix_text_display'         => '',
				'suffix_text_display'         => '',
				'prefix_suffix_wrapper'       => 1,
				'list_view'                   => 0,
				'details_view'                => 1,
				'simple_search'               => 0,
				'advanced_search'             => 0,
				'filter_search'               => 0,
				'allow_priority'              => 0,
				'priority'                    => 40,
				'priority_direction'          => 'asc',
				'backend_list_view'           => 0,
				'backend_list_view_ordering'  => 40,
				'required'                    => 0,
				'language'                    => '*',
				'params'                      => '',
				'checked_out'                 => 0,
				'checked_out_time'            => '0000-00-00 00:00:00',
				'access'                      => 1,
				'who_can_download_can_access' => 0,
				'published'                   => 1,
				'publish_up'                  => '0000-00-00 00:00:00',
				'publish_down'                => '0000-00-00 00:00:00',
				'ordering'                    => 40,
				'frontend_ordering'           => 0,
				'metatitle'                   => '',
				'metakeyword'                 => '',
				'metadescription'             => '',
				'metadata'                    => '{"robots":"","author":"","rights":"","xreference":""}',
				'ignored_options'             => '',
				'created'                     => $date->tosql(),
				'created_by'                  => $user->id,
				'modified'                    => '0000-00-00 00:00:00',
				'modified_by'                 => 0
			);

			if ($fieldTable->bind($data) && $fieldTable->check())
			{
				$fieldTable->store();
			}
		}
	}

	
	public function preflight($type, $parent)
	{
		
		$phpVersion = floatval(phpversion());
		if ($phpVersion < 5)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage('Installation was unsuccessful because you are using an unsupported version of PHP. JUDownload supports only PHP5 and above. Please kindly upgrade your PHP version and try again.', 'error');

			return false;
		}

		$old_version = $this->getOldVersion();
		if ($old_version < '1.0.9')
		{
			
			if (JFolder::exists(JPATH_ROOT . '/media/com_judownload/avatars/'))
			{
				JFolder::move(JPATH_ROOT . '/media/com_judownload/avatars/', JPATH_ROOT . '/media/com_judownload/images/avatar/');
			}

			if (JFolder::exists(JPATH_ROOT . '/media/com_judownload/collection_icons/'))
			{
				JFolder::move(JPATH_ROOT . '/media/com_judownload/collection_icons/', JPATH_ROOT . '/media/com_judownload/images/collection/');
			}

			if (JFolder::exists(JPATH_ROOT . '/media/com_judownload/images/document/'))
			{
				JFolder::move(JPATH_ROOT . '/media/com_judownload/images/document/', JPATH_ROOT . '/media/com_judownload/images/gallery/');
			}

			if (JFolder::exists(JPATH_ROOT . '/media/com_judownload/icons/'))
			{
				JFolder::move(JPATH_ROOT . '/media/com_judownload/icons/', JPATH_ROOT . '/media/com_judownload/images/document/');
			}

			if (JFolder::exists(JPATH_ROOT . '/media/com_judownload/images/document/default/'))
			{
				JFolder::move(JPATH_ROOT . '/media/com_judownload/images/document/default/', JPATH_ROOT . '/media/com_judownload/images/default/');
			}

			if (JFolder::exists(JPATH_ROOT . '/media/com_judownload/default_images/'))
			{
				JFolder::delete(JPATH_ROOT . '/media/com_judownload/default_images/');
			}
		}
	}

	
	public function postflight($type, $parent)
	{
		
		$JUDownloadInstallerHelper = new JUDownloadInstallerHelper();
		$JUDownloadInstallerHelper->createJUDLMenu();

		if ($type == 'install')
		{
			$db   = JFactory::getDbo();
			$date = JFactory::getDate();
			$user = JFactory::getUser();

			
			$query = "UPDATE #__judownload_emails SET `created` ='" . $date->tosql() . "', `created_by` = " . $user->id . ", `checked_out` = 0, `checked_out_time` = '0000-00-00 00:00:00', `modified_by` = 0, `modified` = '0000-00-00 00:00:00'";
			$db->setQuery($query);
			$db->execute();

			
			$query = "UPDATE #__judownload_fields_groups SET `created` ='" . $date->tosql() . "', `created_by` = " . $user->id . ", `checked_out` = 0, `checked_out_time` = '0000-00-00 00:00:00', `modified_by` = 0, `modified` = '0000-00-00 00:00:00'";
			$db->setQuery($query);
			$db->execute();

			
			$query = "UPDATE #__judownload_fields SET `created` ='" . $date->tosql() . "', `created_by` = " . $user->id . ", `checked_out` = 0, `checked_out_time` = '0000-00-00 00:00:00', `modified_by` = 0, `modified` = '0000-00-00 00:00:00'";
			$db->setQuery($query);
			$db->execute();

			
			$query = "UPDATE #__judownload_template_styles SET `created` ='" . $date->tosql() . "', `created_by` = " . $user->id . ", `checked_out` = 0, `checked_out_time` = '0000-00-00 00:00:00', `modified_by` = 0, `modified` = '0000-00-00 00:00:00'";
			$db->setQuery($query);
			$db->execute();

			
			$asset_str = '{"core.admin":[],"core.manage":{"6":1},"core.create":{"2":1},"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":{"2":1},"judl.category.create":{"6":1},"judl.category.edit":{"6":1},"judl.category.edit.state":{"6":1},"judl.category.edit.own":{"6":1},"judl.category.delete":{"6":1},"judl.category.delete.own":{"6":1},"judl.document.create":{"6":1,"2":1},"judl.document.create.auto_approval":[],"judl.document.edit":{"6":1},"judl.document.edit.own":{"6":1},"judl.document.edit.auto_approval":[],"judl.document.delete":{"6":1},"judl.document.delete.own":{"6":1},"judl.document.download":{"6":1,"2":1},"judl.document.download.own.no_restrict":{"1":1},"judl.document.report":{"1":1},"judl.document.report.no_captcha":[],"judl.document.contact":{"1":1},"judl.document.contact.no_captcha":[],"judl.comment.create":{"6":1,"2":1},"judl.comment.create.many_times":{"6":1,"2":1},"judl.comment.auto_approval":[],"judl.comment.reply":{"6":1,"2":1},"judl.comment.reply.auto_approval":[],"judl.comment.no_captcha":[],"judl.comment.vote":{"6":1,"2":1},"judl.comment.report":{"6":1,"2":1},"judl.comment.report.no_captcha":[],"judl.single.rate":{"6":1,"2":1},"judl.single.rate.many_times":[],"judl.criteria.rate":{"6":1,"2":1},"judl.criteria.rate.many_times":[],"judl.moderator.create":[],"judl.moderator.edit":[],"judl.moderator.edit.state":[],"judl.moderator.delete":[],"judl.field.value.submit":{"6":1,"2":1},"judl.field.value.edit":[],"judl.field.value.edit.own":{"6":1,"2":1},"judl.field.value.search":{"1":1,"6":1,"2":1}}';
			$query     = 'UPDATE #__assets SET `rules` = ' . $db->quote($asset_str) . ' WHERE `name` = "com_judownload"';
			$db->setQuery($query);
			$db->execute();

			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');

			
			$categoryTable = JTable::getInstance('Category', 'JUDownloadTable');
			$query         = 'SELECT id FROM #__judownload_categories';
			$db->setQuery($query);
			$categoryIds = $db->loadColumn();
			if ($categoryIds)
			{
				foreach ($categoryIds AS $categoryId)
				{
					if ($categoryTable->load($categoryId, true))
					{
						if ($categoryTable->check())
						{
							$categoryTable->store();
						}
					}
				}
			}

			
			$fieldGroupTable = JTable::getInstance('FieldGroup', 'JUDownloadTable');
			$query           = 'SELECT id FROM #__judownload_fields_groups';
			$db->setQuery($query);
			$fieldGroupIds = $db->loadColumn();
			if ($fieldGroupIds)
			{
				foreach ($fieldGroupIds AS $fieldGroupId)
				{
					if ($fieldGroupTable->load($fieldGroupId, true))
					{
						if ($fieldGroupTable->check())
						{
							$fieldGroupTable->store();
						}
					}
				}
			}

			
			$fieldTable = JTable::getInstance('Field', 'JUDownloadTable');
			$query      = 'SELECT id FROM #__judownload_fields';
			$db->setQuery($query);
			$fieldIds = $db->loadColumn();
			if ($fieldIds)
			{
				foreach ($fieldIds AS $fieldId)
				{
					if ($fieldTable->load($fieldId, true))
					{
						if ($fieldTable->check())
						{
							$fieldTable->store();
						}
					}
				}
			}

			
			$parent->getParent()->setRedirectURL('index.php?option=com_judownload');
		}
	}

	public function getOldVersion()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('manifest_cache')
			->from('#__extensions')
			->where('element = ' . $db->quote('com_judownload'));
		$db->setQuery($query);
		$result   = $db->loadResult();
		$manifest = new JRegistry($result);

		return $manifest->get('version');
	}
}
