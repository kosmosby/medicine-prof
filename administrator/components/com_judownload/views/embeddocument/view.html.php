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


class JUDownloadViewEmbedDocument extends JUDLViewAdmin
{
	protected $items;
	protected $pagination;
	protected $state;

	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		$this->setDocument();

		parent::display($tpl);
	}

	protected function setDocument()
	{
		JText::script('COM_JUDOWNLOAD_PLEASE_SELECT_DOCUMENT');
		$document = JFactory::getDocument();
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.embeddocument.js");
	}

	public function getFieldDisplay()
	{
		
		$fields = array(
			'title'      => JText::_('Title'),
			'introtext'  => JText::_('Introtext'),
			'categories' => JText::_('Categories'),
			'created'    => JText::_('Created'),
			'created_by' => JText::_('Created by'),
			'icon'       => JText::_('Icon'),
			'hits'       => JText::_('Hits'),
			'downloads'  => JText::_('Downloads'),
			'rating'     => JText::_('Rating'),
			'tag'        => JText::_('Tag'),
			'task'       => JText::_('Task')
		);

		return $fields;
	}
}
