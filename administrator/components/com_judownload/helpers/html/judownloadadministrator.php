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


abstract class JHtmlJUDownloadAdministrator
{
	
	static function criteriaRequired($value = 0, $i, $canChange = true, $controller = 'criterias')
	{
		
		$states = array(
			0 => array('disabled.png', 'icon-unfeatured', '', $controller . '.required', JText::_('COM_JUDOWNLOAD_NOT_REQUIRED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_REQUIRE')),
			1 => array('featured.png', 'icon-featured', 'active', $controller . '.unrequired', JText::_('COM_JUDOWNLOAD_REQUIRED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_TURN_OFF_REQUIRE'))
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);

		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a href="#" class="btn btn-micro ' . $state[2] . ' hasTooltip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[3] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
		}
		else
		{
			$html = JHtml::_('image', 'admin/' . $state[0], $state[4], null, true);
			if ($canChange)
			{
				$html = '<a href="#" class="hasTip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "::" . $state[5] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function featured($value = 0, $i, $canChange = true, $controller = 'documents', $id = 'cb')
	{
		
		$states = array(
			0 => array('disabled.png', 'icon-unfeatured', '', $controller . '.feature', JText::_('COM_JUDOWNLOAD_UNFEATURED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_FEATURE')),
			1 => array('featured.png', 'icon-featured', 'active', $controller . '.unfeature', JText::_('COM_JUDOWNLOAD_FEATURED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_UNFEATURE'))
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);

		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a href="#" class="btn btn-micro ' . $state[2] . ' hasTooltip" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
		}
		else
		{
			$html = JHtml::_('image', 'admin/' . $state[0], $state[4], null, true);
			if ($canChange)
			{
				$html = '<a href="#" class="hasTip" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "::" . $state[5] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function subscriptionPublish($value = 0, $i, $canChange = true, $controller = 'subscriptions')
	{
		
		$states = array(
			-1 => array('publish_y.png', 'icon-pending', '', $controller . '.activate', JText::_('COM_JUDOWNLOAD_INACTIVE'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_ACTIVATE')),
			0  => array('publish_x.png', 'icon-unpublish', '', $controller . '.publish', JText::_('COM_JUDOWNLOAD_UNPUBLISHED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_PUBLISH')),
			1  => array('tick.png', 'icon-publish', 'active', $controller . '.unpublish', JText::_('COM_JUDOWNLOAD_PUBLISHED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_UNPUBLISH'))
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);

		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a href="#" class="btn btn-micro ' . $state[2] . ' hasTooltip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
		}
		else
		{
			$html = JHtml::_('image', 'admin/' . $state[0], $state[4], null, true);
			if ($canChange)
			{
				$html = '<a href="#" class="hasTip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "::" . $state[5] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function read($value = 0, $i, $canChange = true, $controller = 'reports')
	{
		
		$states = array(
			0 => array('publish_x.png', 'icon-unpublish', '', $controller . '.read', JText::_('COM_JUDOWNLOAD_UNREAD'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_MARK_AS_READ')),
			1 => array('tick.png', 'icon-publish', 'active', $controller . '.unread', JText::_('COM_JUDOWNLOAD_READ'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_MARK_AS_UNREAD'))
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);

		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a href="#" class="btn btn-micro ' . $state[2] . ' hasTooltip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
		}
		else
		{
			$html = JHtml::_('image', 'admin/' . $state[0], $state[4], null, true);
			if ($canChange)
			{
				$html = '<a href="#" class="hasTip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "::" . $state[5] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function commentApproved($value = 0, $i, $canChange = true, $controller = 'comment')
	{
		
		$states = array(
			0 => array('disabled.png', 'icon-unfeatured', '', $controller . '.approve', JText::_('COM_JUDOWNLOAD_UNAPPROVED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_APPROVE')),
			1 => array('featured.png', 'icon-featured', 'active', $controller . '.unapprove', JText::_('COM_JUDOWNLOAD_APPROVED'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_UNAPPROVE'))
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);

		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a href="#" class="btn btn-micro ' . $state[2] . ' hasTooltip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[3] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" title="' . $state[4] . "<br/>" . $state[5] . '">' . $html . '</a>';
			}
		}
		else
		{
			$html = JHtml::_('image', 'admin/' . $state[0], $state[4], null, true);
			if ($canChange)
			{
				$html = '<a href="#" class="hasTip" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . "::" . $state[5] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function changAjaxValue($id, $column, $value = 0, $canChange = true)
	{
		
		$states   = array(
			0 => array('state unpublish', 'icon-unpublish', '', JText::_('COM_JUDOWNLOAD_NO'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_YES')),
			1 => array('state publish', 'icon-publish', 'active', JText::_('COM_JUDOWNLOAD_YES'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_NO'))
		);
		$state    = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$tovalue  = $value == 0 ? 1 : 0;
		$fnchange = 'changeValue(this, ' . $id . ', \'' . $column . '\', ' . $tovalue . '); return false;';

		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a href="#" class="btn btn-micro ' . $state[2] . ' hasTooltip" onclick="' . $fnchange . '" title="' . $state[3] . "<br/>" . $state[4] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" title="' . $state[3] . "<br/>" . $state[4] . '">' . $html . '</a>';
			}
		}
		else
		{
			if ($canChange)
			{
				$html = '<span alt="' . $state[3] . '" class="' . $state[0] . '"><span class="text">' . $state[3] . '</span></span>';
				$html = '<a href="#" class="jgrid hasTip" onclick="' . $fnchange . '" title="' . $state[3] . "::" . $state[4] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<span alt="' . $state[3] . '" class="' . $state[0] . ' disabled"><span class="text">' . $state[3] . '</span></span>';
				$html = '<a class="jgrid hasTip" title="' . $state[3] . "::" . $state[4] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function changeAjaxBLVorder($id, $value = 0, $canChange = true)
	{
		
		$states   = array(
			0 => array('state unpublish', 'icon-unpublish', '', JText::_('COM_JUDOWNLOAD_HIDE_IN_LIST_VIEW'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_SHOW_IN_LIST_VIEW')),
			1 => array('state publish-pending', 'icon-pending', '', JText::_('COM_JUDOWNLOAD_SHOW_IN_LIST_VIEW'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_SHOW_IN_LIST_VIEW_AND_SHOW_BY_DEFAULT')),
			2 => array('state publish', 'icon-publish', 'active', JText::_('COM_JUDOWNLOAD_SHOW_IN_LIST_VIEW_AND_SHOW_BY_DEFAULT'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_HIDE_IN_LIST_VIEW'))
		);
		$state    = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$tovalue  = $value == 0 ? 1 : ($value == 1 ? 2 : 0);
		$fnchange = 'changeBLVorder(this, ' . $id . ', ' . $tovalue . '); ';
		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a href="#" class="btn btn-micro ' . $state[2] . ' hasTooltip" onclick="' . $fnchange . '" title="' . $state[3] . "<br/>" . $state[4] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" title="' . $state[3] . "<br/>" . $state[4] . '">' . $html . '</a>';
			}
		}
		else
		{
			if ($canChange)
			{
				$html = '<span alt="' . $state[3] . '" class="' . $state[0] . '"><span class="text">' . $state[3] . '</span></span>';
				$html = '<a href="#" class="jgrid hasTip" onclick="' . $fnchange . '" title="' . $state[3] . "::" . $state[4] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<span alt="' . $state[3] . '" class="' . $state[0] . ' disabled"><span class="text">' . $state[3] . '</span></span>';
				$html = '<a class="jgrid hasTip" title="' . $state[3] . "::" . $state[4] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function priorityDirection($id, $value = "esc", $canChange = true)
	{
		
		$states         = array(
			"asc"  => array('sort asc-direction', '', JText::_('COM_JUDOWNLOAD_ASC'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_DESC')),
			"desc" => array('sort desc-direction', 'active', JText::_('COM_JUDOWNLOAD_DESC'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_ASC'))
		);
		$state          = JArrayHelper::getValue($states, strtolower(trim($value)), $states['asc']);
		$disabled_class = $canChange ? '' : ' disabled';
		$tovalue        = $value == "asc" ? "desc" : "asc";
		$fnchange       = 'changePriorityDirection(this, ' . $id . ', \'' . $tovalue . '\'); ';
		$html           = '<span alt="' . $state[2] . '" class="' . $state[0] . $disabled_class . '"><span class="text">' . $state[2] . '</span></span>';
		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<a class="jgrid btn btn-micro ' . $state[1] . ' hasTooltip" href="#" onclick="' . $fnchange . 'return false;" title="' . $state[2] . '<br/>' . $state[3] . '">' . $html . '</a>';
		}
		else
		{
			$html = '<a class="jgrid hasTip" href="#" onclick="' . $fnchange . 'return false;" title="' . $state[2] . '::' . $state[3] . '">' . $html . '</a>';
		}

		return $html;
	}

	
	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null)
	{
		static $done;

		if ($done === null)
		{
			$done = array();
		}

		$readonly = isset($attribs['readonly']) && $attribs['readonly'] == 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] == 'disabled';
		if (is_array($attribs))
		{
			$attribs = JArrayHelper::toString($attribs);
		}

		if (!$readonly && !$disabled)
		{
			
			JHtml::_('behavior.calendar');
			JHtml::_('behavior.tooltip');

			
			if (!in_array($id, $done))
			{
				$document = JFactory::getDocument();
				$script   =
					'window.addEvent(\'domready\', function() {
						Calendar.setup({
							
							inputField: "' . $id . '",
								
								ifFormat: "' . $format . '",
								
								button: "' . $id . '_img",
								
								align: "Tl",
								singleClick: true,
								firstDay: ' . JFactory::getLanguage()->getFirstDay() . '
							});
						});';
				$document->addScriptDeclaration($script);
				$done[] = $id;
			}

			$html = '<div class="input-append">';
			$html .= '<input type="text" title="' . (0 !== (int) $value ? JHtml::_('date', $value, null, null) : '') . '" name="' . $name . '" id="' . $id
				. '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />'
				. '<span class="add-on icon-calendar fa fa-calendar" id="' . $id . '_img"></span>';
			$html .= '</div>';

			return $html;
		}
		else
		{
			$html = '<div class="input-append">';
			$html .= '<input type="text" title="' . (0 !== (int) $value ? JHtml::_('date', $value, null, null) : '')
				. '" value="' . (0 !== (int) $value ? JHtml::_('date', $value, 'Y-m-d H:i:s', null) : '') . '" ' . $attribs
				. ' /><input type="hidden" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" />';
			$html .= '</div>';

			return $html;
		}
	}

	
	public static function user($value, $name, $id, $attribs = null)
	{
		JHtml::_('behavior.modal');
		$document = JFactory::getDocument();
		$script   = '
			function jSelectUser_' . $id . '(id, title) {
				var old_id = document.getElementById("' . $id . '").value;
				if (old_id != id) {
					document.getElementById("' . $id . '").value = id;
					document.getElementById("' . $id . '_name").value = title;
				}
				SqueezeBox.close();
			}';
		$document->addScriptDeclaration($script);

		$html = '<div class="input-append">
						<input type="text" value="' . $value->name . '" id="' . $id . '_name" ' . $attribs . '/>
						<a rel="{handler: \'iframe\', size: {x: 570, y: 420}}"
							onclick="IeCursorFix(); return false;"
							href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $id . '"
							title="Image" class="add-on btn modal">
							<i class="icon-user"></i>
						</a>
					</div>
					<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . $value->id . '"/>';

		return $html;
	}

	
	static function setPrivate($value = 0, $i, $canChange = true, $controller = 'collections', $id = 'cb')
	{
		
		$states = array(
			0 => array('publish_x.png', 'icon-unpublish', '', $controller . '.private', JText::_('COM_JUDOWNLOAD_INPRIVATE'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_SET_PRIVATE')),
			1 => array('tick.png', 'icon-publish', 'active', $controller . '.inprivate', JText::_('COM_JUDOWNLOAD_PRIVATE'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_UNSET_PRIVATE'))
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);
		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a class="btn btn-micro ' . $state[2] . ' hasTooltip" href="#" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . '<br/>' . $state[5] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" href="#" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . '<br/>' . $state[5] . '">' . $html . '</a>';
			}
		}
		else
		{
			$html = JHtml::_('image', 'admin/' . $state[0], $state[4], null, true);
			if ($canChange)
			{
				$html = '<a class="hasTip" href="#" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . '::' . $state[5] . '">' . $html . '</a>';
			}
		}

		return $html;
	}

	
	static function setGlobal($value = 0, $i, $canChange = true, $controller = 'collections', $id = 'cb')
	{
		
		$states = array(
			0 => array('publish_x.png', 'icon-unpublish', '', $controller . '.setGlobal', JText::_('COM_JUDOWNLOAD_NOT_GLOBAL'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_SET_GLOBAL')),
			1 => array('tick.png', 'icon-publish', 'active', $controller . '.unsetGlobal', JText::_('COM_JUDOWNLOAD_GLOBAL'), JText::_('COM_JUDOWNLOAD_TOGGLE_TO_UNSET_GLOBAL'))
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);
		if (JUDownloadHelper::isJoomla3x())
		{
			$html = '<i class="' . $state[1] . '"></i>';
			if ($canChange)
			{
				$html = '<a class="btn btn-micro ' . $state[2] . ' hasTooltip" href="#" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . '<br/>' . $state[5] . '">' . $html . '</a>';
			}
			else
			{
				$html = '<a class="btn btn-micro disabled hasTooltip" href="#" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . '<br/>' . $state[5] . '">' . $html . '</a>';
			}
		}
		else
		{
			$html = JHtml::_('image', 'admin/' . $state[0], $state[4], null, true);
			if ($canChange)
			{
				$html = '<a class="hasTip" href="#" onclick="return listItemTask(\'' . $id . $i . '\',\'' . $state[3] . '\')" title="' . $state[4] . '::' . $state[5] . '">' . $html . '</a>';
			}
		}

		return $html;
	}
}
