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

class JUDownloadFieldTextarea extends JUDownloadFieldBase
{
	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->addAttribute("class", "field-textarea clearfix", "input");
		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		$editor = $this->getEditor();
		if ($editor)
		{
			$editorHtml = $this->getEditorHtml($editor, $value);
		}
		else
		{
			$editorHtml = $this->getTextArea($value, $this->getInputClass());
		}

		$this->setVariable('value', $value);
		$this->setVariable('editorHtml', $editorHtml);

		return $this->fetch('input.php', __CLASS__);
	}

	protected function getEditor()
	{
		$app    = JFactory::getApplication();
		$editor = '';
		if ($app->isAdmin())
		{
			if ($this->params->get('use_editor_back_end', 1))
			{
				$editor = $this->params->get('backend_editor', 'tinymce');
			}
		}
		else
		{
			if ($this->params->get('use_editor_front_end', 1))
			{
				$editor = $this->params->get('frontend_editor', 'tinymce');
				if ($editor && $editor != 'none')
				{
					$groupsCanUseFrontendEditor = (array) $this->params->get('groups_can_use_frontend_editor', array());
					$user                       = JFactory::getUser();
					$userGroups                 = $user->getAuthorisedViewLevels();
					if (!count(array_intersect($userGroups, $groupsCanUseFrontendEditor)) > 0)
					{
						$editor = '';
					}
				}
			}
		}

		return $editor;
	}

	protected function getEditorHtml($selectedEditor, $value)
	{
		$html         = '';
		$class        = get_class();
		$called_class = get_called_class();

		$button = ($class != $called_class) ? array('pagebreak') : array('pagebreak', 'readmore');

		if (!$this->checkEditor($selectedEditor))
		{
			$selectedEditor = 'none';
		}

		$editor = JFactory::getEditor($selectedEditor);
		$html .= $editor->display($this->getName(), htmlspecialchars($value, ENT_COMPAT, 'UTF-8'), $this->params->get('width', 400),
			$this->params->get('height', 300), $this->params->get('cols', 50), $this->params->get('rows', 5), $button, $this->getId());
		if ($this->params->get('trigger_window_resize', 0))
		{
			$document = JFactory::getDocument();
			$script   = "jQuery(document).ready(function(){
							jQuery('ul.nav-tabs li a[href=\"#fields\"], dl.tabs dt.fields').on('click', function(){
								 jQuery(window).trigger('resize');
								 var evt = document.createEvent('UIEvents');
							     evt.initUIEvent('resize', true, false, window, 0);
							     window.dispatchEvent(evt);
							});
						});";
			$document->addScriptDeclaration($script);
		}

		return $html;
	}

	public function getTextArea($value, $class = '')
	{
		$placeholder = $this->params->get("placeholder", "") ? "placeholder=\"" . htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8') . "\"" : "";
		$width       = $this->params->get('width', 400);
		$height      = $this->params->get('height', 300);
		$cols        = $this->params->get('cols', 50);
		$rows        = $this->params->get('rows', 5);
		$html        = '<textarea id="' . $this->getId() . '" name="' . $this->getName() . '" class="' . $class . '"
							style="width: ' . $width . 'px; height: ' . $height . 'px;"
						    cols="' . $cols . '" rows="' . $rows . '" ' . $placeholder . ' >' . $value . '</textarea>';

		return $html;
	}

	protected function checkEditor($name)
	{
		
		$name = JFilterInput::getInstance()->clean($name, 'cmd');
		$path = JPATH_PLUGINS . '/editors/' . $name . '.php';

		if (!JFile::exists($path))
		{
			$path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';
			if (!JFile::exists($path))
			{
				return false;
			}
		}

		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('element');
		$query->from('#__extensions');
		$query->where('element = ' . $db->quote($name));
		$query->where('folder = ' . $db->quote('editors'));
		$query->where('enabled = 1');

		
		$db->setQuery($query, 0, 1);
		$editor = $db->loadResult();
		if (!$editor)
		{
			return false;
		}

		return true;
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (!$this->value)
		{
			return "";
		}

		$value = $this->value;

		
		if ($this->isDetailsView($options))
		{
			if ($this->params->get("strip_tags_details_view", 0))
			{
				$allowable_tags = $this->params->get("allowable_tags", "u,b,i,a,ul,li,pre,blockquote,strong,em");
				$allowable_tags = str_replace(' ', '', $allowable_tags);
				$allowable_tags = "<" . str_replace(',', '><', $allowable_tags) . ">";
				$value          = strip_tags($value, $allowable_tags);
			}

			if ($this->params->get("parse_plugin", 0))
			{
				$value = JHtml::_('content.prepare', $value);
			}

			if ($this->params->get("nl2br_details_view", 0))
			{
				$value = nl2br($value);
			}

			if ($this->params->get("auto_link", 1))
			{
				$trim_long_url     = $this->params->get('trim_long_url', 0);
				$front_portion_url = $this->params->get('front_portion_url', 0);
				$back_portion_url  = $this->params->get('back_portion_url', 0);
				$regex             = "#http(?:s)?:\/\/(?:www\.)?[\.0-9a-z]{1,255}(\.[a-z]{2,4}){1,2}([\/\?][^\s]{1,}){0,}[\/]?#i";
				preg_match_all($regex, $value, $matches);

				$matches = array_unique($matches[0]);

				if (count($matches) > 0)
				{
					if ($this->params->get('nofollow_link', 1))
					{
						$noFollow = 'rel="nofollow"';
					}
					else
					{
						$noFollow = '';
					}

					foreach ($matches AS $url)
					{
						$shortenUrl = urldecode($url);
						
						if ($trim_long_url > 0 && strlen($shortenUrl) > $trim_long_url)
						{
							if ($front_portion_url > 0 || $back_portion_url > 0)
							{
								$frontStr   = $front_portion_url > 0 ? substr($shortenUrl, 0, $front_portion_url) : "";
								$backStr    = $back_portion_url > 0 ? substr($shortenUrl, (int) (0 - $back_portion_url)) : "";
								$shortenUrl = $frontStr . '...' . $backStr;
							}

							$shortenUrl = '<a ' . $noFollow . ' href="' . $url . '">' . $shortenUrl . '</a> ';
							$value      = str_replace(trim($url), $shortenUrl, $value);
							$value      = JUDownloadFrontHelperString::replaceIgnore(trim($url), $shortenUrl, $value);
						}
						
						else
						{
							$value = JUDownloadFrontHelperString::replaceIgnore($url, '<a ' . $noFollow . ' href="' . $url . '">' . trim($shortenUrl) . '</a> ', $value);
						}
					}
				}
			}
		}
		
		else
		{
			if ($this->params->get("strip_tags_list_view", 1))
			{
				$allowable_tags = $this->params->get("allowable_tags", "u,b,i,a,ul,li,pre,blockquote,strong,em");
				$allowable_tags = str_replace(' ', '', $allowable_tags);
				$allowable_tags = "<" . str_replace(',', '><', $allowable_tags) . ">";
				$value          = strip_tags($value, $allowable_tags);
			}

			if ($this->params->get("use_html_entities", 0))
			{
				$value = htmlentities($value);
			}

			if ($this->params->get("truncate", 1))
			{
				$value = JUDownloadFrontHelperString::truncateHtml($value, $this->params->get("limit_char_in_list_view", 200));
			}

			if ($this->params->get("parse_plugin", 0))
			{
				$value = JHtml::_('content.prepare', $value);
			}
		}

		$this->setVariable('value', $value);

		return $this->fetch('output.php', __CLASS__);
	}

	public function filterField($value)
	{
		$return = parent::filterField($value);

		
		if ($this->params->get("strip_tags_before_save", 0))
		{
			$allowable_tags = $this->params->get("allowable_tags", "u,b,i,a,ul,li,pre,blockquote,strong,em");
			$allowable_tags = str_replace(' ', '', $allowable_tags);
			$allowable_tags = "<" . str_replace(',', '><', $allowable_tags) . ">";
			$return         = strip_tags($return, $allowable_tags);
		}

		return $return;
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setVariable('value', $defaultValue);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function getPredefinedValuesHtml()
	{
		$default_predefined = $this->getDefaultPredefinedValues();

		return "<textarea name=\"jform[predefined_values]\" rows=\"15\" cols=\"50\">" . $default_predefined . "</textarea>";
	}
}

?>