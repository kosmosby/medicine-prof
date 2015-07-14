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


class JUDownloadControllerPendingComment extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_PENDING_COMMENT';

	
	protected $context = 'comment';

	protected $view_item = 'comment';

	protected $view_list = 'pendingcomments';

	
	public function getModel($name = 'PendingComment', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function save($key = null, $urlVar = null)
	{

		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app            = JFactory::getApplication();
		$comment_option = $app->input->get('approval_option');

		if ($comment_option == "approve" || $comment_option == "ignore")
		{
			parent::save();
		}
		elseif ($comment_option == "delete")
		{
			$model      = $this->getModel();
			$comment_id = $app->input->getInt('id', 0);
			if ($model->delete($comment_id))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', 1));
			}
			else
			{
				$this->setMessage($model->getError());
			}
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
		}
	}

	public function saveAndNext()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$this->save();
		$model           = $this->getModel();
		$next_comment_id = $model->getPrevOrNextCommentId('next');
		$this->setRedirect('index.php?option=com_judownload&task=comment.edit&approve=1&id=' . $next_comment_id);
	}

	public function saveAndPrev()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$this->save();
		$model           = $this->getModel();
		$prev_comment_id = $model->getPrevOrNextCommentId('prev');
		$this->setRedirect('index.php?option=com_judownload&task=comment.edit&approve=1&id=' . $prev_comment_id);
	}
}
