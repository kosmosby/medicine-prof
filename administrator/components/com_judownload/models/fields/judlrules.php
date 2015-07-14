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

JFormHelper::loadFieldClass('rules');


class JFormFieldJUDLRules extends JFormFieldRules
{
	
	public $type = 'JUDLRules';

	protected function getInput()
	{
		
		$joomla_version_arr = explode(".", JVERSION);
		$priVersion         = $joomla_version_arr[0];

		if ($priVersion == 3)
		{
			return $this->getInput3();
		}
		else
		{
			return $this->getInput2();
		}
	}

	
	protected function getInput2()
	{
		JHtml::_('behavior.tooltip');

		
		$section    = $this->element['section'] ? (string) $this->element['section'] : '';
		$component  = $this->element['component'] ? (string) $this->element['component'] : '';
		$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

		
		$actions = JAccess::getActions($component, $section);

		
		foreach ($this->element->children() AS $el)
		{
			if ($el->getName() == 'action')
			{
				$actions[] = (object) array('name'        => (string) $el['name'], 'title' => (string) $el['title'],
				                            'description' => (string) $el['description']);
			}
		}

		
		$sectionComponentArr = array('component', 'component_category', 'component_document',
			'component_comment', 'component_single_rating', 'component_criteria', 'component_moderator',
			'component_field_value');
		if (in_array($section, $sectionComponentArr))
		{
			
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__assets'));
			$query->where($db->quoteName('name') . ' = ' . $db->quote($component));
			$db->setQuery($query);
			$assetId = (int) $db->loadResult();

			if ($error = $db->getErrorMsg())
			{
				JError::raiseNotice(500, $error);
			}
		}
		else
		{
			
			
			$assetId = $this->form->getValue($assetField);
		}

		
		
		
		

		
		
		$assetRules = JAccess::getAssetRules($assetId);

		
		$groups = $this->getUserGroups();

		
		$curLevel = 0;

		
		$html   = array();
		$html[] = '<div id="permissions-sliders-' . $section . '">';
		$html[] = '<div id="permissions-sliders" class="pane-sliders">';
		$html[] = '<p class="rule-desc">' . JText::_('JLIB_RULES_SETTINGS_DESC') . '</p>';
		$html[] = '<ul id="rules">';

		
		foreach ($groups AS $group)
		{
			$difLevel = $group->level - $curLevel;

			if ($difLevel > 0)
			{
				$html[] = '<li><ul>';
			}
			elseif ($difLevel < 0)
			{
				$html[] = str_repeat('</ul></li>', -$difLevel);
			}

			$html[] = '<li>';

			$html[] = '<div class="panel">';
			$html[] = '<h3 class="pane-toggler title"><a href="javascript:void(0);"><span>';
			$html[] = str_repeat('<span class="level">|&ndash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = '</span></a></h3>';
			$html[] = '<div class="pane-slider content pane-hide">';
			$html[] = '<div class="mypanel">';
			$html[] = '<table class="group-rules">';
			$html[] = '<thead>';
			$html[] = '<tr>';

			$html[] = '<th class="actions" id="actions-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_ACTION') . '</span>';
			$html[] = '</th>';

			$html[] = '<th class="settings" id="settings-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
			$html[] = '</th>';

			
			$canCalculateSettings = ($group->parent_id || !empty($component));
			if ($canCalculateSettings)
			{
				$html[] = '<th id="aclactionth' . $group->value . '">';
				$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
				$html[] = '</th>';
			}

			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody>';

			foreach ($actions AS $action)
			{
				$html[] = '<tr>';
				$html[] = '<td headers="actions-th' . $group->value . '">';
				$html[] = '<label class="hasTip" for="' . $this->id . '_' . $action->name . '_' . $group->value . '" title="'
					. htmlspecialchars(JText::_($action->title) . '::' . JText::_($action->description), ENT_COMPAT, 'UTF-8') . '">';
				$html[] = JText::_($action->title);
				$html[] = '</label>';
				$html[] = '</td>';

				$html[] = '<td headers="settings-th' . $group->value . '">';

				$this->name = $this->formControl . '[rules]';

				$html[] = '<select name="' . $this->name . '[' . $action->name . '][' . $group->value . ']" id="' . $this->id . '_' . $action->name
					. '_' . $group->value . '" title="'
					. JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->text)) . '">';

				$inheritedRule = JAccess::checkGroup($group->value, $action->name, $assetId);

				
				$assetRule = $assetRules->allow($action->name, $group->value);

				

				
				$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>'
					. JText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED') . '</option>';
				$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_ALLOWED')
					. '</option>';
				$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_DENIED')
					. '</option>';

				$html[] = '</select>&#160; ';

				
				if (($assetRule === true) && ($inheritedRule === false))
				{
					$html[] = JText::_('JLIB_RULES_CONFLICT');
				}

				$html[] = '</td>';

				
				
				if ($canCalculateSettings)
				{
					$html[] = '<td headers="aclactionth' . $group->value . '">';

					
					

					if (JAccess::checkGroup($group->value, 'core.admin', $assetId) !== true)
					{
						if ($inheritedRule === null)
						{
							$html[] = '<span class="icon-16-unset">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === true)
						{
							$html[] = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							if ($assetRule === false)
							{
								$html[] = '<span class="icon-16-denied">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
							}
							else
							{
								$html[] = '<span class="icon-16-denied"><span class="icon-16-locked">' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED')
									. '</span></span>';
							}
						}
					}
					elseif (!empty($component))
					{
						$html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
							. '</span></span>';
					}
					else
					{
						
						
						if ($action->name === 'core.admin')
						{
							$html[] = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							
							$html[] = '<span class="icon-16-denied"><span class="icon-16-locked">'
								. JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span></span>';
						}
						else
						{
							$html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
								. '</span></span>';
						}
					}

					$html[] = '</td>';
				}

				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table></div>';

			$html[] = '</div></div>';
			$html[] = '</li>';
		}

		$html[] = str_repeat('</ul></li>', $curLevel);
		$html[] = '</ul><div class="rule-notes">';
		if ($section == 'component' || $section == null)
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES');
		}
		else
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}
		$html[] = '</div></div></div>';

		$js = "window.addEvent('domready', function(){ new Fx.Accordion($$('div#permissions-sliders-" . $section . " div#permissions-sliders.pane-sliders .panel h3.pane-toggler'),"
			. "$$('div#permissions-sliders-" . $section . " div#permissions-sliders.pane-sliders .panel div.pane-slider'), {onActive: function(toggler, i) {toggler.addClass('pane-toggler-down');"
			. "toggler.removeClass('pane-toggler');i.addClass('pane-down');i.removeClass('pane-hide');Cookie.write('jpanesliders_permissions-sliders-" . $section
			. $component
			. "',$$('div#permissions-sliders-" . $section . " div#permissions-sliders.pane-sliders .panel h3').indexOf(toggler));},"
			. "onBackground: function(toggler, i) {toggler.addClass('pane-toggler');toggler.removeClass('pane-toggler-down');i.addClass('pane-hide');"
			. "i.removeClass('pane-down');}, duration: 300, display: "
			. JRequest::getInt('jpanesliders_permissions-sliders' . $component, 0, 'cookie') . ", show: "
			. JRequest::getInt('jpanesliders_permissions-sliders' . $component, 0, 'cookie') . ", alwaysHide:true, opacity: false}); });";

		JFactory::getDocument()->addScriptDeclaration($js);

		return implode("\n", $html);

	}


	
	protected function getInput3()
	{
		$css      = 'div[id$="permissions"].tab-pane .control-group .controls {
						margin-left: 0;
					}
					div[id$="permissions"].tab-pane label.hasTooltip {
						float: left;
					}';
		$document = JFactory::getDocument();
		$document->addStyleDeclaration($css);

		JHtml::_('bootstrap.tooltip');

		
		$section    = $this->section;
		$component  = $this->component;
		$assetField = $this->assetField;

		
		$actions = JAccess::getActions($component, $section);

		
		foreach ($this->element->children() AS $el)
		{
			if ($el->getName() == 'action')
			{
				$actions[] = (object) array('name'        => (string) $el['name'], 'title' => (string) $el['title'],
				                            'description' => (string) $el['description']);
			}
		}

		
		$sectionComponentArr = array('component', 'component_category', 'component_document',
			'component_comment', 'component_single_rating', 'component_criteria', 'component_moderator',
			'component_field_value');
		if (in_array($section, $sectionComponentArr))
		{
			
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . ' = ' . $db->quote($component));
			$db->setQuery($query);
			$assetId = (int) $db->loadResult();
		}
		else
		{
			
			
			$assetId = $this->form->getValue($assetField);
		}

		if (strpos($section, '_') != false)
		{
			$customNameArray = explode('_', $section);
			if (count($customNameArray) == 2)
			{
				$customName = $customNameArray[1];
				$customName .= '_';
			}
			elseif (count($customNameArray) > 2)
			{
				unset($customNameArray[0]);
				$customName = implode('_', $customNameArray);
				$customName .= '_';
			}
			else
			{
				$customName = $section . '_';
			}
		}
		else
		{
			$customName = '';
		}

		

		
		$assetRules = JAccess::getAssetRules($assetId);

		
		$groups = $this->getUserGroups();

		
		$html = array();

		
		$html[] = '<p class="rule-desc">' . JText::_('JLIB_RULES_SETTINGS_DESC') . '</p>';

		

		$html[] = '<div id="' . $customName . 'permissions-sliders" class="tabbable tabs-left">';

		
		$html[] = '<ul class="nav nav-tabs">';

		foreach ($groups AS $group)
		{
			
			$active = "";

			if ($group->value == 1)
			{
				$active = "active";
			}

			$html[] = '<li class="' . $active . '">';

			$html[] = '<a href="#' . $customName . 'permission-' . $group->value . '" data-toggle="tab">';
			$html[] = str_repeat('<span class="level">&ndash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = '</a>';
			$html[] = '</li>';
		}

		$html[] = '</ul>';

		$html[] = '<div class="tab-content">';

		
		foreach ($groups AS $group)
		{

			
			$active = "";

			if ($group->value == 1)
			{
				$active = " active";
			}

			$html[] = '<div class="tab-pane' . $active . '" id="' . $customName . 'permission-' . $group->value . '">';
			$html[] = '<table class="table table-striped">';
			$html[] = '<thead>';
			$html[] = '<tr>';

			$html[] = '<th class="actions" id="actions-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_ACTION') . '</span>';
			$html[] = '</th>';

			$html[] = '<th class="settings" id="settings-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
			$html[] = '</th>';

			
			$canCalculateSettings = ($group->parent_id || !empty($component));

			if ($canCalculateSettings)
			{
				$html[] = '<th id="aclactionth' . $group->value . '">';
				$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
				$html[] = '</th>';
			}

			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody>';

			foreach ($actions AS $action)
			{
				$html[] = '<tr>';
				$html[] = '<td headers="actions-th' . $group->value . '">';
				$html[] = '<label for="' . $this->id . '_' . $action->name . '_' . $group->value . '" class="hasTooltip" title="'
					. '<strong>' . htmlspecialchars(JText::_($action->title) . '</strong><br/>' . JText::_($action->description), ENT_COMPAT, 'UTF-8') . '">';
				$html[] = JText::_($action->title);
				$html[] = '</label>';
				$html[] = '</td>';

				$html[] = '<td headers="settings-th' . $group->value . '">';

				$this->name = $this->formControl . '[rules]';

				$html[] = '<select class="input-small" name="' . $this->name . '[' . $action->name . '][' . $group->value . ']" id="' . $this->id . '_' . $action->name
					. '_' . $group->value . '" title="'
					. JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->text)) . '">';

				$inheritedRule = JAccess::checkGroup($group->value, $action->name, $assetId);

				
				$assetRule = $assetRules->allow($action->name, $group->value);

				

				
				$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>'
					. JText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED') . '</option>';
				$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_ALLOWED')
					. '</option>';
				$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_DENIED')
					. '</option>';

				$html[] = '</select>&#160; ';

				
				if (($assetRule === true) && ($inheritedRule === false))
				{
					$html[] = JText::_('JLIB_RULES_CONFLICT');
				}

				$html[] = '</td>';

				
				
				if ($canCalculateSettings)
				{
					$html[] = '<td headers="aclactionth' . $group->value . '">';

					
					

					if (JAccess::checkGroup($group->value, 'core.admin', $assetId) !== true)
					{
						if ($inheritedRule === null)
						{
							$html[] = '<span class="label label-important">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === true)
						{
							$html[] = '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							if ($assetRule === false)
							{
								$html[] = '<span class="label label-important">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
							}
							else
							{
								$html[] = '<span class="label"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED')
									. '</span>';
							}
						}
					}
					elseif (!empty($component))
					{
						$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
							. '</span>';
					}
					else
					{
						
						
						if ($action->name === 'core.admin')
						{
							$html[] = '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							
							$html[] = '<span class="label label-important"><i class="icon-lock icon-white"></i> '
								. JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span>';
						}
						else
						{
							$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
								. '</span>';
						}
					}

					$html[] = '</td>';
				}

				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table></div>';


		}

		$html[] = '</div></div>';

		$html[] = '<div class="alert">';

		if ($section == 'component' || $section == null)
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES');
		}
		else
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}

		$html[] = '</div>';

		$html[] = '<script type="text/javascript">';
		$html[] = 'jQuery(document).ready(function(){';
		$html[] = 'var loadTabFix = function() {';
		$html[] = '     var addClassActive = true;';
		$html[] = '     jQuery("#' . $customName . 'permissions-sliders ul li").each(function(){';
		$html[] = '         if(jQuery(this).hasClass("active")){';
		$html[] = '             addClassActive = false;';
		$html[] = '         }';
		$html[] = '     });';
		$html[] = '     if(addClassActive){';
		$html[] = '         jQuery("#' . $customName . 'permissions-sliders a:first").tab("show");';
		$html[] = '     }';
		$html[] = '}';
		$html[] = 'setTimeout(loadTabFix, 110);';
		$html[] = '});';
		$html[] = '</script>';

		return implode("\n", $html);
	}

}
