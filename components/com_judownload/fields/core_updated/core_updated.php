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

class JUDownloadFieldCore_updated extends JUDownloadFieldDateTime
{
	protected $field_name = 'updated';

	public function canView($options = array())
	{
		$storeId = md5(__METHOD__ . "::" . $this->doc_id . "::" . $this->id . "::" . serialize($options));

		if (!isset(self::$cache[$storeId]))
		{
			if (!$this->isPublished())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			if (isset($this->doc) && $this->doc->cat_id)
			{
				$params = JUDownloadHelper::getParams($this->doc->cat_id);
			}
			else
			{
				$params = JUDownloadHelper::getParams(null, $this->doc_id);
			}

			$show_empty_field = $params->get('show_empty_field', 0);
			
			if ($this->doc_id && !$show_empty_field)
			{
				if (intval($this->value) == 0)
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			self::$cache[$storeId] = parent::canView($options);

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$value = !is_null($fieldValue) ? $fieldValue : $this->value;

			$this->addAttribute("type", "text", "input");
			$this->addAttribute("class", $this->getInputClass(), "input");
			$this->addAttribute("class", "readonly", "input");

			if ((int) $this->params->get("size", 32))
			{
				$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
			}
			$this->setAttribute("readonly", "readonly", "input");

			$this->setVariable('value', $value);

			return $this->fetch('input.php', __CLASS__);
		}
		else
		{
			return parent::getInput($fieldValue);
		}
	}

	public function storeValue($value, $type = 'default', $inputData = null)
	{
		if (!$value)
		{
			$db    = JFactory::getDbo();
			$value = $db->getNullDate();
		}

		
		if ($value != $this->doc->updated)
		{
			
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__judownload_versions')
				->set('date = ' . $db->quote($value))
				->where('doc_id = ' . $db->quote($this->doc_id))
				->where('version = ' . $db->quote($this->doc->version));
			$db->setQuery($query);
			$db->execute();

			return parent::storeValue($value, $type, $inputData);
		}
		
		else
		{
			return true;
		}
	}
}

?>