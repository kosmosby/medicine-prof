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


jimport('joomla.application.component.controllerform');


class JUDownloadControllerField extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_FIELD';

	
	protected function allowAdd($data = array())
	{
		return false;
	}

	public function add()
	{
		$this->setMessage(JText::_('Lite Version does not support to create new field for document, please upgrade to <a href="http://www.joomultra.com/ju-download-comparison.html">Pro Version</a> to use this feature.'), 'error');

		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. $this->getRedirectToListAppend(), false
			)
		);
	}

	
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getUser();
		$userId   = $user->get('id');
		$asset    = 'com_judownload.field.' . $recordId;

		
		if ($user->authorise('core.edit', $asset))
		{
			return true;
		}

		
		
		if ($user->authorise('core.edit.own', $asset))
		{
			
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId)
			{
				
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		
		return parent::allowEdit($data, $key);
	}

	public function fastAddOptions()
	{
		$app      = JFactory::getApplication();
		$postData = $app->input->post->get("data", '', 'raw');
		$postData = str_replace(array("\r\n", "\r"), array("\\n", "\\n"), $postData);
		JUDownloadHelper::obCleanData();
		if ($postData)
		{
			$data = array();

			
			if (!is_null(json_decode($postData)))
			{
				$jsonData = json_decode($postData);
				if (is_array($jsonData) || is_object($jsonData))
				{
					foreach ($jsonData AS $option)
					{
						if (is_object($option))
						{
							$option = get_object_vars($option);
						}

						if (is_array($option))
						{
							$optionArray    = array();
							$option         = array_values($option);
							$optionArray[0] = $option[0];
							$optionArray[1] = $option[1];
							$data[]         = $optionArray;
						}
					}
				}
			}
			
			else
			{
				$delimiter    = $app->input->getWord("delimiter", ",");
				$enclosure    = $app->input->getWord("enclosure", '"');
				$csvDataArray = explode("\n", $postData);
				if ($csvDataArray)
				{
					foreach ($csvDataArray AS $csvData)
					{
						$csvArray           = str_getcsv($csvData, $delimiter, $enclosure);
						$data[$csvArray[0]] = $csvArray;
					}
				}
			}

			if ($data)
			{
				echo json_encode(array_values($data));
			}
			else
			{
				echo '0';
			}
			exit;
		}

		echo '0';
		exit;
	}

	public function testPhpCode()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$app       = JFactory::getApplication();
		$field_id  = $app->input->post->getInt('field_id', 0);
		$plugin_id = $app->input->post->getInt('plugin_id', 0);

		$php_code = $app->input->post->get('php_predefined_values', '', 'raw');

		if (trim($php_code))
		{
			
			if ($plugin_id)
			{
				$db    = JFactory::getDbo();
				$query = "SELECT folder FROM #__judownload_plugins WHERE id = " . $plugin_id;
				$db->setQuery($query);
				$folder = $db->loadResult();

				$fieldClassName = 'JUDownloadField' . $folder;
				if ($field_id)
				{
					$fieldObj = new $fieldClassName($field_id);
				}
				else
				{
					$fieldObj = new $fieldClassName();
				}
			}
			
			else
			{
				echo 'No plugin selected';
				exit;
			}

			$fieldObj->php_predefined_values = $php_code;

			JUDownloadHelper::obCleanData(true);
			
			$result = $fieldObj->getPredefinedFunction();
			echo '<div class="return">';
			var_dump($result);
			echo '</div>';
		}

		exit;
	}
}
