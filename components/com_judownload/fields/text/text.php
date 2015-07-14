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

class JUDownloadFieldText extends JUDownloadFieldBase
{
	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if ($this->params->get("tag_search", 0))
		{
			$document = JFactory::getDocument();
			JUDownloadFrontHelper::loadjQueryUI();
			$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/assets/css/tagit-style.css");
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/tagit.js");
			$tagScript = '
            jQuery(document).ready(function($){
                $("#' . $this->getId() . '_tags").tagit({
                    tagSource:' . ($this->params->get('auto_suggest') ? $this->getTags() : '[]') . ',
                    initialTags: ' . $this->getInitialTags($fieldValue) . ',
                    tagsChanged: function(){ getTags($("#' . $this->getId() . '_tags").tagit("tags")); },
                    triggerKeys:["enter", "comma", "tab"],
                    minLength: 3,
                    maxLength: 50,
                    maxTags: 50,
                    sortable: "handle",
                    placeholder: " ' . $this->params->get("placeholder", "") . ' "
                });

                function getTags(tags) {
                    var newtags = [];
                    for (var i in tags){
	                    if (tags[i].label != undefined ){
	                        var tagvalue = tags[i].value.replace("|", "");
	                        newtags.push(tagvalue);
	                        $(tags[i].element[0]).find(".tagit-label").text(tagvalue);
	                    }
                    }
                    var newtags_str = newtags.join(",");
                    $("#' . $this->getId() . '").val(newtags_str);
                }
            });
            ';
			$document->addScriptDeclaration($tagScript);

			
			$this->params->set("auto_suggest", 0);
			$this->params->set("regex", "");

			$value = !is_null($fieldValue) ? $fieldValue : $this->value;
			$this->setVariable('value', $value);

			return $this->fetch('input.php', __CLASS__);
		}
		else
		{
			return parent::getInput($fieldValue);
		}
	}

	protected function getTags()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("DISTINCT REPLACE(value,'|',',')");
		$query->from("#__judownload_fields_values");
		$query->where("field_id = " . $this->id);
		$db->setQuery($query);
		$values = $db->loadColumn();
		$tags   = array();
		if ($values)
		{
			foreach ($values AS $value)
			{
				if (strpos($value, ",") !== false)
				{
					$subValueArr = explode(",", $value);
					foreach ($subValueArr AS $subValue)
					{
						$tags[] = $subValue;
					}
				}
				else
				{
					$tags[] = $value;
				}
			}
		}

		$tags = array_unique($tags);

		return json_encode(array_values($tags));
	}

	protected function getInitialTags($default = null)
	{
		if (!$default)
		{
			$tags = array();
			if ($this->doc_id)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("REPLACE(value, '|', ',')");
				$query->from("#__judownload_fields_values");
				$query->where("doc_id = " . $this->doc_id . " AND field_id = " . $this->id);
				$db->setQuery($query);
				$value = $db->loadResult();
				if ($value)
				{
					if (strpos($value, ",") !== false)
					{
						$valueArr = explode(",", $value);
						foreach ($valueArr AS $value)
						{
							$tags[$value] = $value;
						}
					}
					else
					{
						$tags[$value] = $value;
					}
				}
			}
		}
		else
		{
			$default = str_replace("|", ",", $default);
			$tags    = explode(",", $default);
		}

		return json_encode(array_values($tags));
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if ($this->params->get("tag_search", 0))
		{
			$document = JFactory::getDocument();
			JUDownloadFrontHelper::loadjQueryUI();
			$document->addStyleSheet(JUri::root() . 'components/com_judownload/assets/css/tagit-style.css');
			$document->addScript(JUri::root() . "components/com_judownload/assets/js/tagit.js");
			$tagScript = '
            jQuery(document).ready(function($){
                    $("#' . $this->getId() . '_tags").tagit({
                    tagSource:' . ($this->params->get('auto_suggest') ? $this->getTags() : '[]') . ',
                    initialTags: ' . $this->getInitialTags($defaultValue) . ',
                    tagsChanged: function(){ getTags($("#' . $this->getId() . '_tags").tagit("tags")); },
                    triggerKeys:["enter", "comma", "tab"],
                    minLength: 3,
                    maxLength: 50,
                    maxTags: 50,
                    sortable: "handle",
                    placeholder: " ' . $this->params->get("placeholder", "") . ' "
                });

                function getTags(tags) {
                    var newtags = [];
                    for (var i in tags){
	                    if (tags[i].label != undefined ){
	                        var tagvalue = tags[i].value.replace("|", "");
	                        newtags.push(tagvalue);
	                        $(tags[i].element[0]).find(".tagit-label").text(tagvalue);
	                    }
                    }
                    var newtags_str = newtags.join(",");
                    $("#' . $this->getId() . '").val(newtags_str);
                }
            });
            ';
			$document->addScriptDeclaration($tagScript);

			$this->setVariable('value', $this->value);

			return $this->fetch('searchinput.php', __CLASS__);
		}
		else
		{
			return parent::getSearchInput($defaultValue);
		}
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			if ($this->params->get("tag_search", 0))
			{
				$search = explode(",", $search);
			}

			return parent::onSearch($query, $where, $search);
		}
	}
}

?>