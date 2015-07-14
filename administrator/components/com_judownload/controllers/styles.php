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

class JUDownloadControllerStyles extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_STYLES';

	public function getModel($name = 'Style', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function duplicate()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$pks = $app->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new Exception(JText::_('COM_JUDOWNLOAD_NO_TEMPLATE_SELECTED'));
			}

			JArrayHelper::toInteger($pks);

			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::_('COM_JUDOWNLOAD_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_judownload&view=styles');
	}

	public function setDefault()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->post->get('cid', array(), 'array');
		if (is_array($cid))
		{
			$id = $cid[0];
		}
		else
		{
			$id = $cid;
		}
		$model = $this->getModel();
		$model->setDefault($id);

		$this->setRedirect(JRoute::_("index.php?option=com_judownload&view=styles", false), JText::_('COM_JUDOWNLOAD_STYLE_HAS_BEEN_SET_DEFAULT'), 'message');
	}

	
	public function unsetDefault()
	{
		
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$pks = $app->input->get->get('cid', array(), 'array');
		JArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(JText::_('COM_JUDOWNLOAD_NO_TEMPLATE_SELECTED'));
			}

			
			$id    = array_shift($pks);
			$model = $this->getModel();
			$model->unsetDefault($id);
			$this->setMessage(JText::_('COM_JUDOWNLOAD_STYLE_HAS_BEEN_UNSET_DEFAULT'));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_judownload&view=styles');
	}
}