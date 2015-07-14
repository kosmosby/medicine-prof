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


class JUDownloadControllerComment extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_COMMENT';

	
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();

		return $user->authorise('judl.comment.create', $this->option);
	}

	
	public function add()
	{
		
		$app     = JFactory::getApplication();
		$context = "$this->option.edit.$this->context";

		
		if (!$this->allowAdd())
		{
			
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		
		$app->setUserState($context . '.data', null);
		$append = '';
		if ($app->input->getInt('doc_id', 0))
		{
			$append = "&doc_id=" . $app->input->getInt('doc_id', 0);
		}

		
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend() . $append, false
			)
		);

		return true;
	}

	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$app    = JFactory::getApplication();
		$tmpl   = $app->input->get('tmpl');
		$layout = $app->input->get('layout', 'edit');
		$append = '';

		
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		if ($app->input->getInt('approve', 0) == 1)
		{
			$append .= '&approve=1';
		}

		return $append;
	}

	
	protected function allowEdit($data = array(), $key = 'id')
	{
		
		$recordId    = (int) isset($data[$key]) ? $data[$key] : 0;
		$user        = JFactory::getUser();
		$userId      = $user->get('id');
		$rootComment = JUDownloadFrontHelperComment::getRootComment();
		if ($recordId && $recordId == $rootComment->id)
		{
			return false;
		}

		
		if ($user->authorise('core.edit', 'com_judownload'))
		{
			return true;
		}

		
		
		if ($user->authorise('core.edit.own', 'com_judownload'))
		{
			
			$ownerId = (int) isset($data['user_id']) ? $data['user_id'] : 0;
			if (empty($ownerId) && $recordId)
			{
				
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->user_id;
			}

			
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		
		return parent::allowEdit($data, $key);
	}
}
