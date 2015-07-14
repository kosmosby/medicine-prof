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

JFormHelper::loadFieldClass('list');

class JFormFieldHomeForStyle extends JFormFieldList
{
	
	protected $type = 'homeforstyle';

	
	protected function getOptions()
	{
		$styleId = (int) $this->form->getValue('id');

		if ($styleId)
		{
			$styleObject        = JUDownloadFrontHelperTemplate::getTemplateStyleObject($styleId);
			$defaultStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();

			if ($defaultStyleObject->template_id == $styleObject->template_id)
			{
				return array_merge(parent::getOptions(), JHtml::_('contentlanguage.existing'));
			}
			else
			{
				return parent::getOptions();
			}
		}
		else
		{
			return array_merge(parent::getOptions());
		}
	}

}
