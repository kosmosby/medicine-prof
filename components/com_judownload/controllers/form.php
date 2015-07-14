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

class JUDownloadControllerForm extends JControllerForm
{
	
	protected $context = 'document';

	
	protected $view_item = 'form';

	
	protected $view_list = 'category';

	
	protected $text_prefix = 'COM_JUDOWNLOAD_DOCUMENT';

	
	public function getModel($name = 'Form', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function autoSuggest()
	{
		$app = JFactory::getApplication();

		$field  = $app->input->get('field', '', 'string');
		$string = $app->input->get('string', '', 'string');
		$model  = $this->getModel();
		$result = $model->getAutoSuggestValues($field, $string);
		if ($result === false)
		{
			exit;
		}

		JUDownloadHelper::obCleanData();
		echo json_encode($result);
		exit;
	}

	
	

	
	

	
	

	
	

	
	public function uploader()
	{
		$model = $this->getModel();
		if (!$model->isValidUploadURL())
		{
			die(json_encode(array(
				'OK'    => 0,
				'error' => array(
					'code'    => -1,
					'message' => JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_UPLOAD_FILE')
				)
			)));
		}

		JUDownloadHelper::obCleanData();
		$model->uploader();
	}

	
	} 
