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
defined('_JEXEC') or die('Restricted access');


class JUDownloadTableDocument extends JTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_documents', 'id', $db);
	}

	
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_judownload.document.' . (int) $this->$k;
	}

	
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		
		$assetId = null;

		
		if (isset($this->cat_id) && $this->cat_id)
		{
			$cat_id = $this->cat_id;
		}
		elseif ($this->id)
		{
			$cat_id = JUDownloadHelper::getDocumentById($this->id)->cat_id;
		}

		if ($cat_id)
		{
			
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('asset_id'));
			$query->from($this->_db->quoteName('#__judownload_categories'));
			$query->where($this->_db->quoteName('id') . ' = ' . $cat_id);

			
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		
		if ($assetId === null)
		{
			
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('id'));
			$query->from($this->_db->quoteName('#__assets'));
			$query->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote('com_judownload'));

			
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	
	public function check()
	{
		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '')
		{
			$this->fulltext = '';
		}

		
		

		
		
		if (!empty($this->metakeyword))
		{
			
			$bad_characters = array("\n", "\r", "\"", "<", ">"); 
			$after_clean    = JString::str_ireplace($bad_characters, "", $this->metakeyword); 
			$keys           = explode(',', $after_clean); 
			$clean_keys     = array();

			foreach ($keys AS $key)
			{
				if (trim($key))
				{
					
					$clean_keys[] = trim($key);
				}
			}
			$this->metakeyword = implode(", ", $clean_keys); 
		}

		return true;
	}

	
	public function delete($pk = null)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		

		$db = JFactory::getDbo();

		
		$query = "DELETE FROM #__judownload_subscriptions WHERE item_id = $pk AND type = 'document'";
		$db->setQuery($query);
		$db->execute();

		
		$query = "SELECT * FROM #__judownload_comments WHERE doc_id = " . $pk;
		$db->setQuery($query);
		$commentIds = $db->loadColumn();
		if ($commentIds)
		{
			$commentTable = JTable::getInstance("Comment", "JUDownloadTable");
			foreach ($commentIds AS $commentId)
			{
				$commentTable->delete($commentId);
			}
		}

		
		$query = "SELECT id FROM #__judownload_rating WHERE doc_id = $pk";
		$db->setQuery($query);
		$ratingItemIds = $db->loadColumn();
		if ($ratingItemIds)
		{
			$ratingTable = JTable::getInstance("Rating", "JUDownloadTable");
			foreach ($ratingItemIds AS $ratingItemId)
			{
				$ratingTable->delete($ratingItemId);
			}
		}

		
		$query = "DELETE FROM #__judownload_reports WHERE item_id = $pk AND type = 'document'";
		$db->setQuery($query);
		$db->execute();

		
		$query = "SELECT id FROM #__judownload_collections_items WHERE doc_id = $pk";
		$db->setQuery($query);
		$collectionItemIds = $db->loadColumn();
		if ($collectionItemIds)
		{
			$collectionItemTable = JTable::getInstance("CollectionItem", "JUDownloadTable");
			foreach ($collectionItemIds AS $collectionItemId)
			{
				$collectionItemTable->delete($collectionItemId);
			}
		}

		
		JUDownloadHelper::deleteLogs('document', $pk);

		
		$query = "DELETE FROM #__judownload_mailqs
			      WHERE item_id =" . $pk . "
						AND email_id IN (SELECT id FROM #__judownload_emails WHERE (`event` LIKE 'document.%' AND `event` != 'document.delete'))";
		$db->setQuery($query);
		$db->execute();

		
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_documents');
		$query->where('approved=' . (-$pk));
		$db->setQuery($query);
		$tempDocIds = $db->loadColumn();
		if (count($tempDocIds))
		{
			foreach ($tempDocIds AS $tempDocId)
			{
				$this->deleteMainData($tempDocId, true);
			}
		}

		
		$cat_id = JUDownloadFrontHelperCategory::getMainCategoryId($this->id);

		$this->deleteMainData($pk);

		if (parent::delete($pk))
		{
			$app = JFactory::getApplication();
			if ($app->isSite())
			{
				$registry = new JRegistry;
				$registry->loadObject($this);
				$mailData           = $registry->toArray();
				$mailData['cat_id'] = $cat_id;

				if ($this->approved < 1)
				{
					JUDownloadFrontHelperMail::sendEmailByEvent('document.reject', $this->id, $mailData);
				}
				else
				{
					JUDownloadFrontHelperMail::sendEmailByEvent('document.delete', $this->id, $mailData);
				}

			}

			return true;
		}
		else
		{
			return false;
		}
	}

	
	public function deleteMainData($pk, $deleteSelf = false)
	{
		
		$db = JFactory::getDbo();

		
		JUDownloadHelper::deleteFieldValuesOfDocument($pk);

		
		$query = "DELETE FROM #__judownload_documents_relations WHERE doc_id = " . $pk;
		$db->setQuery($query);
		$db->execute();

		
		$query = "DELETE FROM #__judownload_changelogs WHERE doc_id = " . $pk;
		$db->setQuery($query);
		$db->execute();

		
		$query = "DELETE FROM #__judownload_files WHERE doc_id = " . $pk;
		$db->setQuery($query);
		$db->execute();

		
		$query = "DELETE FROM #__judownload_versions WHERE doc_id = " . $pk;
		$db->setQuery($query);
		$db->execute();

		
		$file_dir = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/") . $pk . "/";
		if (JFolder::exists($file_dir))
		{
			JFolder::delete($file_dir);
		}

		
		$query = "DELETE FROM #__judownload_documents_xref WHERE doc_id = " . $pk;
		$db->setQuery($query);
		$db->execute();

		if ($deleteSelf)
		{
			return parent::delete($pk);
		}

		return true;
	}

	
	public function feature($pks = null, $state = 1, $userId = 0)
	{
		
		$k = $this->_tbl_key;

		
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			
			else
			{
				$e = new JException(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				$this->setError($e);

				return false;
			}
		}

		
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('featured = ' . (int) $state);

		
		if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'))
		{
			$query->where('(checked_out = 0 OR checked_out = ' . (int) $userId . ')');
			$checkin = true;
		}
		else
		{
			$checkin = false;
		}

		
		$query->where($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query);

		
		if (!$this->_db->execute())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_FEATURE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			
			foreach ($pks AS $pk)
			{
				$this->checkin($pk);
			}
		}

		
		if (in_array($this->$k, $pks))
		{
			$this->featured = $state;
		}

		$this->setError('');

		return true;
	}

	
	public function store($updateNulls = false)
	{
		
		$k = $this->_tbl_key;

		$currentAssetId = 0;

		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}
		if (isset($this->cat_id))
		{
			$cat_id = $this->cat_id;
			unset($this->cat_id);
		}

		
		if ($this->$k)
		{
			$stored = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		
		if (!$stored)
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		
		if (!$this->_trackAssets)
		{
			return true;
		}

		if ($this->_locked)
		{
			$this->_unlock();
		}

		//
		
		//
		if (isset($cat_id))
		{
			$this->cat_id = $cat_id;
		}

		$parentId = $this->_getAssetParentId();
		$name     = $this->_getAssetName();
		$title    = $this->_getAssetTitle();

		$asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$asset->loadByName($name);

		
		$this->asset_id = $asset->id;

		
		if ($error = $asset->getError())
		{
			$this->setError($error);

			return false;
		}

		
		if (empty($this->asset_id) || $asset->parent_id != $parentId)
		{
			$asset->setLocation($parentId, 'last-child');
		}

		
		$asset->parent_id = $parentId;
		$asset->name      = $name;
		$asset->title     = $title;

		if ($this->_rules instanceof JAccessRules)
		{
			$asset->rules = (string) $this->_rules;
		}

		if (!$asset->check() || !$asset->store($updateNulls))
		{
			$this->setError($asset->getError());

			return false;
		}

		
		if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
		{
			
			$this->asset_id = (int) $asset->id;

			$query = $this->_db->getQuery(true);
			$query->update($this->_db->quoteName($this->_tbl));
			$query->set('asset_id = ' . (int) $this->asset_id);
			$query->where($this->_db->quoteName($k) . ' = ' . (int) $this->$k);
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED_UPDATE_ASSET_ID', $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}
		}

		return true;
	}

	
	public function bind($array, $ignore = array())
	{
		
		
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		
		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		
		if (!is_object($array) && !is_array($array))
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_BIND_FAILED_INVALID_SOURCE_ARGUMENT', get_class($this)));
			$this->setError($e);

			return false;
		}

		
		if (is_object($array))
		{
			$array = get_object_vars($array);
		}

		
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		
		$properties           = $this->getProperties();
		$properties['cat_id'] = '';

		foreach ($properties AS $k => $v)
		{
			
			if (!in_array($k, $ignore))
			{
				if (isset($array[$k]))
				{
					$this->$k = $array[$k];
				}
			}
		}

		return true;
	}
}
