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


jimport('joomla.application.component.controlleradmin');


class JUDownloadControllerDashboard extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_DASHBOARD';

	
	public function getModel($name = 'Dashboard', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function show()
	{
		$this->setRedirect("index.php?option=com_judownload&view=dashboard");
	}

	public function getChartData()
	{
		$app  = JFactory::getApplication();
		$type = $app->input->get('type');

		$model = $this->getModel();
		$data  = $model->getUploadDownloadData($type);

		$app = JFactory::getApplication();
		$app->setUserState('com_judownload.dashboard.chart.type', $type);

		JUDownloadHelper::obCleanData();
		echo json_encode($data);
		exit;
	}
}
