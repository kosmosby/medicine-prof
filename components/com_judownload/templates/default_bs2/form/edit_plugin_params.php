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
?>
<div class="judl-plugin-params">
	<?php
	$fieldSets = $this->form->getFieldsets('plugin_params');
	$fieldSetsNames = array_keys($fieldSets);
	echo JHtml::_('bootstrap.startTabSet', 'plugin-params', array('active' => $fieldSetsNames[0] . '-plugin-param'));

	foreach ($this->plugins AS $pluginName => $pluginObj)
	{
		echo JHtml::_('bootstrap.addTab', 'plugin-params', $pluginName . '-plugin-param', JText::_($pluginObj['label']));
		if ($pluginObj['total_fieldsets'] > 1)
		{
			echo JHtml::_('bootstrap.startAccordion', 'plugin-' . $pluginName);
		}

		foreach ($fieldSets AS $name => $fieldSet)
		{
			if ($fieldSet->plugin_name == $pluginName)
			{
				if ($pluginObj['total_fieldsets'] > 1)
				{
					echo JHtml::_('bootstrap.addSlide', 'plugin-' . $pluginName, JText::_($fieldSet->label ? $fieldSet->label : strtoupper('COM_JUDOWNLOAD_FIELDSET_' . $fieldSet->name)), 'plugin-' . $pluginName . '-' . $fieldSet->name, 'plugin-' . $pluginName . '-' . $fieldSet->name);
				}

				$fields = $this->form->getFieldSet($fieldSet->name);
				if ($fields)
				{
					foreach ($fields AS $field)
					{
						echo $field->getControlGroup();
					}
				}

				if ($pluginObj['total_fieldsets'] > 1)
				{
					echo JHtml::_('bootstrap.endSlide');
				}
			}
		}

		if ($pluginObj['total_fieldsets'] > 1)
		{
			echo JHtml::_('bootstrap.endAccordion');
		}

		echo JHtml::_('bootstrap.endTab');
	}
	echo JHtml::_('bootstrap.endTabSet');
	?>
</div>