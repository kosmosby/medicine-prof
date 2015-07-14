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
jimport('joomla.application.component.view');


class JUDownloadControllerDocuments extends JControllerAdmin
{
	protected $view_list = 'listcats';

	
	protected $text_prefix = 'COM_JUDOWNLOAD_DOCUMENTS';

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unfeature', 'feature');
		$this->registerTask('inherit_access_unpublish', 'inherit_access_publish');
	}

	
	public function feature()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app     = JFactory::getApplication();
		$cid     = $app->input->post->get('documentid', array(), 'array');
		$data    = array('feature' => 1, 'unfeature' => 0);
		$task    = $this->getTask();
		$value   = JArrayHelper::getValue($data, $task, 0, 'int');
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();

		if (empty($cid))
		{
			JError::raiseWarning(500, JText::_('COM_JUDOWNLOAD_NO_ITEM_SELECTED'));
		}
		else
		{
			
			$model = $this->getModel();
			
			JArrayHelper::toInteger($cid);

			
			if (!$model->feature($cid, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_FEATURED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNFEATURED';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}

		$extension    = $app->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$catURL       = '&cat_id=' . $app->input->getInt('cat_id', $rootCat->id);
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $catURL . $extensionURL, false));
	}

	
	public function getModel($name = 'Document', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function moveDocuments()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app               = JFactory::getApplication();
		$cat_id            = $app->input->getInt('categories', 0);
		$moved_document_id = $app->input->post->get('documentid', array(), 'array');
		$session           = JFactory::getSession();
		if (!$cat_id)
		{
			if (!empty($moved_document_id))
			{
				$session->set('moved_document_id', $moved_document_id);
			}
			$this->setRedirect("index.php?option=com_judownload&view=documents&layout=move");
		}
		else
		{
			$model                 = $this->getModel();
			$move_option_arr       = $app->input->post->get('move_options', array(), 'array');
			$total_moved_documents = $model->moveDocuments($session->get('moved_document_id'), $cat_id, $move_option_arr);
			$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$cat_id", JText::plural($this->text_prefix . '_N_ITEMS_MOVED', $total_moved_documents));
		}
	}

	
	public function copyDocuments()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app                = JFactory::getApplication();
		$cat_id             = $app->input->get('categories', array(), 'array');
		$copied_document_id = $app->input->post->get('documentid', array(), 'array');
		$session            = JFactory::getSession();
		if (empty($cat_id))
		{
			if (!empty($copied_document_id))
			{
				$session->set('copied_document_id', $copied_document_id);
			}
			$this->setRedirect("index.php?option=com_judownload&view=documents&layout=copy");
		}
		else
		{
			$model                  = $this->getModel();
			$copy_option_arr        = $app->input->post->get('copy_options', array(), 'array');
			$total_copied_documents = $model->copyDocuments($session->get('copied_document_id'), $cat_id, $copy_option_arr);
			$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$cat_id[0]", JText::plural($this->text_prefix . '_N_ITEMS_COPIED', $total_copied_documents));
		}
	}

	
	public function checkin()
	{
		$app        = JFactory::getApplication();
		$documentid = $app->input->post->get('documentid', array(), 'array');
		$app->input->post->set('cid', $documentid);
		$_POST['cid'] = $documentid;

		parent::checkin();

		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id  = $app->input->getInt('cat_id', $rootCat->id);
		$this->setRedirect("index.php?option=com_judownload&view=$this->view_list&cat_id=$cat_id");
	}

	
	public function publish()
	{
		$app        = JFactory::getApplication();
		$documentid = $app->input->post->get('documentid', array(), 'array');
		$app->input->set('cid', $documentid);
		$_POST['cid'] = $documentid;

		parent::publish();

		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id  = $app->input->getInt('cat_id', $rootCat->id);
		$this->setRedirect("index.php?option=com_judownload&view=$this->view_list&cat_id=$cat_id");
	}

	
	public function saveorder()
	{
		$app        = JFactory::getApplication();
		$documentid = $app->input->post->get('documentid', array(), 'array');
		$app->input->post->set('cid', $documentid);
		$_POST['cid'] = $documentid;
		$rootCat      = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id       = $app->input->getInt('cat_id', $rootCat->id);

		parent::saveorder();

		$this->setRedirect("index.php?option=com_judownload&view=$this->view_list&cat_id=$cat_id");
	}

	
	public function reorder()
	{
		$app        = JFactory::getApplication();
		$documentid = $app->input->post->get('documentid', array(), 'array');
		$app->input->post->set('cid', $documentid);
		$_POST['cid'] = $documentid;
		$rootCat      = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id       = $app->input->getInt('cat_id', $rootCat->id);

		parent::reorder();

		$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$cat_id");
	}

	
	public function delete()
	{
		$app        = JFactory::getApplication();
		$documentid = $app->input->post->get('documentid', array(), 'array');
		$app->input->set('cid', $documentid);
		$_POST['cid'] = $documentid;
		$rootCat      = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id       = $app->input->getInt('cat_id', $rootCat->id);

		parent::delete();

		$this->setRedirect("index.php?option=com_judownload&view=$this->view_list&cat_id=$cat_id");
	}
}
