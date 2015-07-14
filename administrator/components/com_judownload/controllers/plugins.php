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


class JUDownloadControllerPlugins extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_PLUGINS';

	
	public function getModel($name = 'plugin', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function getTemplateId($pluginId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_templates');
		$query->where('plugin_id =' . $pluginId);
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	public function getChildTemplateIdByTree($pk)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('n.id')
			->from('#__judownload_templates AS n,#__judownload_templates AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('p.id = ' . (int) $pk)
			->order('n.lft');
		$db->setQuery($query);

		return $db->loadColumn();
	}

	public function orderTemplateTreeBeforeDelete($templateString)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('plg.id,plg.title,plg.folder');
		$query->select('tpl.*');
		$query->from('#__judownload_templates AS tpl');
		$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
		$query->where('tpl.id IN(' . $templateString . ')');
		$query->order('tpl.lft ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function checkDeleteTemplateHasChild()
	{
		$app    = JFactory::getApplication();
		$jInput = $app->input;

		$result = array('status' => 0, 'message' => '');

		$data             = $jInput->post->get("pluginIDs", array(), 'array');
		$dataTemplateId   = array();
		$dataTemplateTree = array();

		foreach ($data AS $pluginId)
		{
			$templateId = $this->getTemplateId($pluginId);
			if ($templateId)
			{
				$dataTemplateId[] = $templateId;
			}
		}

		$dataTemplateId = array_unique($dataTemplateId);

		foreach ($dataTemplateId AS $templateId)
		{
			$dataTemplateTree = array_merge($dataTemplateTree, $this->getChildTemplateIdByTree($templateId));
			$dataTemplateTree = array_unique($dataTemplateTree);
		}

		$dataTemplateTree = array_unique($dataTemplateTree);

		if (!empty($dataTemplateId) && !empty($dataTemplateTree))
		{
			if (implode(',', $dataTemplateId) != implode(',', $dataTemplateTree))
			{
				$result['status'] = 1;
				$dataMessage      = array();
				if (is_array($dataTemplateTree) && count($dataTemplateTree))
				{
					$dataTemplateTreeString = implode(',', $dataTemplateTree);
					$dataMessage            = $this->orderTemplateTreeBeforeDelete($dataTemplateTreeString);
				}

				$html = '<div class="alert alert-warning">' . JText::_('COM_JUDOWNLOAD_DELETE_TEMPLATE_WARNING_MESSAGE') . '</div>';
				$html .= '<table class="table table-condensed">';
				$html .= '<thead>';
				$html .= '<tr>';
				$html .= '<th>' . JText::_('COM_JUDOWNLOAD_TEMPLATE_ID') . '</th>';
				$html .= '<th>' . JText::_('COM_JUDOWNLOAD_TEMPLATE_TITLE') . '</th>';
				$html .= '</tr>';
				$html .= '</thead>';
				$html .= '<tbody>';
				foreach ($dataMessage AS $element)
				{
					$html .= '<tr>';
					$html .= '<td>' . $element->id . '</td>';
					$html .= '<td>' . str_repeat('<span class="gi">&mdash;</span>', $element->level - 1) . ' ' . ucfirst($element->title) . '</td>';
					$html .= '</tr>';
				}
				$html .= '</tbody>';
				$html .= '</table>';
				$result['message'] = $html;
			}
		}

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;
		exit;
	}


	
	public function remove()
	{
		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');

		require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/manage.php';
		$lang = JFactory::getLanguage();
		$lang->load('com_installer');

		$model = new InstallerModelManage();
		JArrayHelper::toInteger($cid, array());

		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('plg.id, plg.title');
		$query->from('#__judownload_plugins AS plg');
		$query->join('', '#__judownload_templates AS tpl ON tpl.plugin_id = plg.id');
		$query->join('', '#__judownload_template_styles AS style ON style.template_id = tpl.id');
		$query->where('style.home = 1');
		$query->where('plg.id IN(' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$templateHomeId = $db->loadObject();
		
		if ($templateHomeId)
		{
			JError::raiseWarning(500, JText::sprintf('COM_JUDOWNLOAD_CAN_NOT_DELETE_PLUGIN_X_BECAUSE_IT_IS_EXTENDED_BY_DEFAULT_TEMPLATE_STYLE', $templateHomeId->title));
			
			$cid = array_diff($cid, array($templateHomeId->id));
		}

		if ($cid)
		{
			$query = $db->getQuery(true);
			$query->select('extension_id')
				->from('#__judownload_plugins')
				->where('id IN(' . implode(',', $cid) . ')');
			$db->setQuery($query);
			$eid = $db->loadColumn();
			$model->remove($eid);
		}

		$this->setRedirect(JRoute::_('index.php?option=com_judownload&view=plugins', false));
	}
}
