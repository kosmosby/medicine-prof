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



defined('JPATH_PLATFORM') or die;


jimport('joomla.html.pagination');

class JUDLPagination extends JPagination
{

	
	public function getLimitBox()
	{
		$app = JFactory::getApplication();

		
		$limits = array();

		$limitArray = JUDownloadFrontHelper::customLimitBox();

		$keyAllLimit = array_search(0, $limitArray);
		if ($keyAllLimit)
		{
			$limitAll = true;
			unset($limitArray[$keyAllLimit]);
		}
		else
		{
			$limitAll = false;
		}

		$limitArray = array_values($limitArray);
		sort($limitArray);

		if (empty($limitArray))
		{
			return parent::getLimitBox();
		}

		
		foreach ($limitArray AS $limitValue)
		{
			$limits[] = JHtml::_('select.option', "$limitValue");
		}

		if ($limitAll)
		{
			$limits[] = JHtml::_('select.option', '0', JText::_('JALL'));
		}

		
		$jversion_arr = explode(".", JVERSION);
		$priVersion   = $jversion_arr[0];

		if ($priVersion == 2)
		{
			$selected = $this->_viewall ? 0 : $this->limit;
		}
		elseif ($priVersion == 3)
		{
			$selected = $this->viewall ? 0 : $this->limit;
		}

		
		if ($app->isAdmin())
		{
			$html = JHtml::_(
				'select.genericlist',
				$limits,
				$this->prefix . 'limit',
				'class="inputbox" size="1" onchange="Joomla.submitform();"',
				'value',
				'text',
				$selected
			);
		}
		else
		{
			$html = JHtml::_(
				'select.genericlist',
				$limits,
				$this->prefix . 'limit',
				'class="inputbox" size="1" onchange="this.form.submit()"',
				'value',
				'text',
				$selected
			);
		}

		return $html;
	}

}