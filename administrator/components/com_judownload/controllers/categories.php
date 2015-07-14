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


class JUDownloadControllerCategories extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_CATEGORIES';
	protected $view_list = 'listcats';

	public function __construct($config = array())
	{

		parent::__construct($config);
		$this->registerTask('inherit_access_unpublish', 'inherit_access_publish');
		$this->registerTask('unfeature', 'feature');
	}

	
	public function getModel($name = 'Category', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function loadCategories()
	{
		$model = $this->getModel();
		$data  = $model->loadCategories();
		JUDownloadHelper::obCleanData();
		echo $data;
		exit();
	}

	
	public function docChangeCategory()
	{
		$model = $this->getModel();
		$data  = $model->docChangeCategory();
		JUDownloadHelper::obCleanData();
		echo $data;
		exit();
	}

	
	public function publish()
	{
		parent::publish();
		$app     = JFactory::getApplication();
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();

		if ($app->input->getString('view', 'listcats') == 'treestructure')
		{
			$this->setRedirect("index.php?option=com_judownload&view=treestructure");
		}
		else
		{
			$cat_id = $app->input->getInt('cat_id', $rootCat->id);
			$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=" . $cat_id);
		}
	}

	
	public function feature()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app     = JFactory::getApplication();
		$cid     = $app->input->get('cid', array(), 'array');
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

	
	public function saveorder()
	{
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		
		$order         = $app->input->post->get('order', null, 'array');
		$originalOrder = explode(',', $app->input->post->get('original_order_values', null, 'string'));

		
		if (!($order === $originalOrder))
		{
			parent::saveorder();
		}

		$cat_id = $app->input->getInt('cat_id', 1);
		$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=" . $cat_id);
	}

	
	public function reorder()
	{
		parent::reorder();
		$app    = JFactory::getApplication();
		$cat_id = $app->input->getInt('cat_id', 1);
		$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$cat_id");
	}

	
	public function delete()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{

			JError::raiseWarning(500, JText::_('COM_JUDOWNLOAD_NO_ITEM_SELECTED'));
		}
		else
		{
			
			$model = $this->getModel();

			
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);
			
			if (!$model->delete($cid))
			{
				$this->setMessage($model->getError());
			}

		}

		if ($app->input->getString('view', 'listcats') == 'treestructure')
		{
			$this->setRedirect("index.php?option=com_judownload&view=treestructure");
		}
		else
		{
			$cat_id = $app->input->getInt('cat_id', 1);
			$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$cat_id");
		}
	}

	
	public function moveCats()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app              = JFactory::getApplication();
		$moveto_cat_id    = $app->input->getInt('categories', 0);
		$moved_cat_id_arr = $app->input->get('cid', array(), 'array');
		$session          = JFactory::getSession();
		
		if (!$moveto_cat_id)
		{
			
			if (!empty($moved_cat_id_arr))
			{
				$session->set('moved_cat_id_arr', $moved_cat_id_arr);
			}
			$this->setRedirect("index.php?option=com_judownload&view=categories&layout=move");
		}
		
		else
		{
			$model                  = $this->getModel();
			$move_option_arr        = $app->input->get('move_options', array(), 'array');
			$total_moved_categories = $model->moveCats($session->get('moved_cat_id_arr'), $moveto_cat_id, $move_option_arr);
			$session->set('moved_cat_id_arr', array());
			if ($total_moved_categories)
			{
				$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$moveto_cat_id", JText::plural($this->text_prefix . '_N_ITEMS_MOVED', $total_moved_categories));
			}
			else
			{
				$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=" . $app->input->getInt("cat_id", 1), JText::plural($this->text_prefix . '_N_ITEMS_MOVED', $total_moved_categories));
			}
		}
	}

	
	public function copyCats()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app               = JFactory::getApplication();
		$copyto_cat_id     = $app->input->get('categories', array(), 'array');
		$copied_cat_id_arr = $app->input->get('cid', array(), 'array');
		$session           = JFactory::getSession();
		
		if (!$copyto_cat_id)
		{
			
			if (!empty($copied_cat_id_arr))
			{
				$session->set('copied_cat_id_arr', $copied_cat_id_arr);
			}
			$this->setRedirect("index.php?option=com_judownload&view=categories&layout=copy");
			
		}
		else
		{
			$model                   = $this->getModel();
			$copy_option_arr         = $app->input->get('copy_options', array(), 'array');
			$total_copied_categories = $model->copyCats($session->get('copied_cat_id_arr'), $copyto_cat_id, $copy_option_arr);
			$session->set('copied_cat_id_arr', array());
			if ($total_copied_categories)
			{
				$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=" . $copyto_cat_id[0], JText::plural($this->text_prefix . '_N_ITEMS_COPIED', $total_copied_categories));
			}
			else
			{
				$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=" . $app->input->getInt("cat_id", 1), JText::plural($this->text_prefix . '_N_ITEMS_COPIED', $total_copied_categories));
			}
		}
	}

	
	public function checkin()
	{
		$app = JFactory::getApplication();
		parent::checkin();
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id  = $app->input->getInt('cat_id', $rootCat->id);

		if ($app->input->getString('view', 'listcats') == 'treestructure')
		{
			$this->setRedirect("index.php?option=com_judownload&view=treestructure");
		}
		else
		{
			$this->setRedirect("index.php?option=com_judownload&view=$this->view_list&cat_id=$cat_id");
		}
	}

	
	public function fastAdd()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$model->fastAdd();
	}
}