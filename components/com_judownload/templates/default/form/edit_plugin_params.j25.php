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
<fieldset>
	<div id="judl-plugin-params">
		<?php
		echo JHtml::_('tabs.start', 'plugin-params');
		$fieldSets = $this->form->getFieldsets('plugin_params');

		foreach ($this->plugins AS $pluginName => $pluginObj)
		{
			echo JHtml::_('tabs.panel', JText::_($pluginObj['label']), $pluginName . '-plugin-param');

			if ($pluginObj['total_fieldsets'] > 1)
			{
				echo JHtml::_('sliders.start', 'plugin-' . $pluginName);
			}

			foreach ($fieldSets AS $name => $fieldSet)
			{
				if ($fieldSet->plugin_name == $pluginName)
				{
					if ($pluginObj['total_fieldsets'] > 1)
					{
						$label = !empty($fieldSet->label) ? $fieldSet->label : $name;
						echo JHtml::_('sliders.panel', JText::_($label), 'plugin-' . $pluginName . '-' . $fieldSet->name);
					}

					$fields = $this->form->getFieldSet($fieldSet->name);
					if($fields)
					{
						echo "<fieldset class=\"panelform\">";
						$hidden_fields = '';
						echo "<ul class=\"adminformlist\">";
						foreach ($fields AS $field)
						{
							if (!$field->hidden)
							{
								echo "<li>";
								echo $field->label;
								echo $field->input;
								echo "</li>";
							}
							else
							{
								$hidden_fields .= $field->input;
							}
						}
						echo "</ul>";
						echo $hidden_fields;
						echo "</fieldset>";
					}
				}
			}

			if ($pluginObj['total_fieldsets'] > 1)
			{
				echo JHtml::_('sliders.end');
			}
		}

		echo JHtml::_('tabs.end');
		?>
	</div>
</fieldset>
