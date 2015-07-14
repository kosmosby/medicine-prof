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

class JUDownloadFieldCore_icon extends JUDownloadFieldBase
{
	protected $field_name = 'icon';

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUDOWNLOAD_NOT_SET') . '</span>';
	}

	public function PHPValidate($values)
	{
		$values = $this->value;
		
		if (!$values)
		{
			$app    = JFactory::getApplication();
			$icon   = $app->input->files->get($this->getId() . "_icon");
			$values = $icon['name'];
		}

		return parent::PHPValidate($values);
	}

	
	public function loadDefaultAssets($loadJS = true, $loadCSS = true)
	{
		static $loaded = array();

		if ($this->folder && !isset($loaded[$this->folder]))
		{
			$document = JFactory::getDocument();
			
			if ($loadJS)
			{
				$script = '
					jQuery(document).ready(function($){
						var default_icon_url = "' . JUri::root(true) . '/' . JUDownloadFrontHelper::getDirectory("document_image_directory", "media/com_judownload/images/document/", true) . 'default/";
						var default_icon = "' . JUDownloadHelper::getDefaultDocumentIcon() . '";
						var icon_wrap = jQuery("#' . $this->getId() . '_wrap");
						var icon_src = jQuery(".icon-src", icon_wrap);
						var remove_icon = jQuery(".remove-icon", icon_wrap);
						var revert_icon = jQuery(".revert-icon", icon_wrap);
						var icon_value = jQuery(".icon-value", icon_wrap);
						var img_el = jQuery("img", icon_wrap);
						jSelectIcon = function(iconName){
							
							if(iconName){
								icon_src.attr("src", default_icon_url + iconName);
							
							}else if(default_icon){
								icon_src.attr("src", default_icon);
							
							}else{
								icon_src.remove();
							}

							
							if(remove_icon.hasClass("remove")){
								revert_icon.data("removeHidden", "1");
							
							}else{
								revert_icon.removeClass("hidden");
							}

							icon_value.val("default/" + iconName);

							if(SqueezeBox){
								SqueezeBox.close();
							}
						}

						remove_icon.click(function(e){
							e.preventDefault();
							icon_value.val("");
							if($(this).hasClass("remove")){
								remove_icon.removeClass("remove").html("<i class=\"icon-trash\"></i> ' . JText::_('COM_JUDOWNLOAD_REMOVE') . '");
								img_el.css("opacity", 1);
								
								if(revert_icon.data("removeHidden") == "1"){
									revert_icon.removeClass("hidden");
									revert_icon.data("removeHidden", "0");
								}
							}else{
								remove_icon.addClass("remove").html("<i class=\"icon-undo\"></i> ' . JText::_('COM_JUDOWNLOAD_RESTORE') . '");
								img_el.css("opacity", 0.5);
								
								if(!revert_icon.hasClass("hidden")){
									revert_icon.addClass("hidden");
									revert_icon.data("removeHidden", "1");
								}
							}
						});

						
						revert_icon.click(function(e){
							e.preventDefault();

							var imageUrl = image_value.data("ori-image-url");
							var value = image_value.data("ori-image-value");
							image_src.attr("src", imageUrl);
							image_value.val(value);

							$(this).addClass("hidden");
						});
					});
				';

				$document->addScriptDeclaration($script);
			}

			if ($loadCSS)
			{
				$document->addStyleSheet(JUri::root() . "components/com_judownload/fields/" . $this->folder . "/" . "style.css");
			}

			$loaded[$this->folder] = true;
		}
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->loadDefaultAssets();

		
		if (isset($this->doc) && $this->doc->cat_id)
		{
			$params = JUDownloadHelper::getParams($this->doc->cat_id);
		}
		else
		{
			$params = JUDownloadHelper::getParams(null, $this->doc_id);
		}

		$max_upload = ini_get('upload_max_filesize');
		$max_upload = JUDownloadHelper::formatBytes(self::convertBytes($max_upload));

		$value    = !is_null($fieldValue) ? $fieldValue : $this->value;
		$icon_src = JUDownloadHelper::getDocumentIcon($value);

		$this->setAttribute("type", "file", "input");
		
		if (!$this->value)
		{
			$this->addAttribute("class", "validate-images", "input");
			$this->addAttribute("class", $this->getInputClass(), "input");
		}

		$this->setVariable('icon_src', $icon_src);
		$this->setVariable('max_upload', $max_upload);
		$this->setVariable('params', $params);
		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	protected function convertBytes($value)
	{
		if (is_numeric($value))
		{
			return $value;
		}
		else
		{
			$value_length = strlen($value);
			$qty          = substr($value, 0, $value_length - 1);
			$unit         = strtolower(substr($value, $value_length - 1));
			switch ($unit)
			{
				case 'k':
					$qty *= 1024;
					break;
				case 'm':
					$qty *= 1048576;
					break;
				case 'g':
					$qty *= 1073741824;
					break;
			}

			return $qty;
		}
	}

	public function getBackendOutput()
	{
		$html     = '';
		$icon_src = JUDownloadHelper::getDocumentIcon($this->value);
		if ($icon_src)
		{
			$html = '<a href="' . $icon_src . '" title="' . JText::_('COM_JUDOWNLOAD_PREVIEW_IMAGE') . '" class="modal">
						<img src="' . $icon_src . '" style="max-width: 20px; max-height: 20px" />
					</a>';
		}

		return $html;
	}

	public function canView($options = array())
	{
		$params       = JUDownloadHelper::getParams(null, $this->doc_id);
		$default_icon = JUDownloadHelper::getDefaultDocumentIcon();
		if ($this->value == "" && $default_icon)
		{
			$this->value = "default/" . $params->get('listing_default_icon', 'default-document.png');
		}

		return parent::canView($options);
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

		$icon_src = JUDownloadHelper::getDocumentIcon($this->value);

		if (!$this->doc_id || !$icon_src)
		{
			return '';
		}

		$isDetailsView = $this->isDetailsView($options);

		$this->setVariable('icon_src', $icon_src);
		$this->setVariable('isDetailsView', $isDetailsView);

		return $this->fetch('output.php', __CLASS__);
	}

	public function storeValue($value, $type = 'default', $inputData = null)
	{
		if ($type == 'migrate')
		{
			
			$iconKeyArray = $this->getId() . "_icon";
			$icon         = $inputData[$iconKeyArray];

			
			$mime_types = array("image/jpeg", "image/pjpeg", "image/png", "image/gif", "image/bmp", "image/x-windows-bmp");
			if ($icon['name'])
			{
				if (in_array($icon['type'], $mime_types))
				{
					
					

					
					$iconDirectory  = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_icon_directory", "media/com_judownload/images/document/");
					$icon_file_name = $this->doc_id . "_" . JUDownloadHelper::fileNameFilter($icon['name']);
					if (JFile::copy($icon['tmp_name'], $iconDirectory . "original/" . $icon_file_name)
						&& JUDownloadHelper::renderImages($iconDirectory . "original/" . $icon_file_name, $iconDirectory . $icon_file_name, 'doc_icon', true, null, $this->doc_id)
					)
					{
						$value = $icon_file_name;
						parent::storeValue($value, $type, $inputData);
					}
				}
			}
			elseif ($value == "" || strpos($value, 'default/') === 0)
			{
				if ($this->doc && $this->doc->icon && strpos($this->doc->icon, 'default/') === false)
				{
					$this->removeIcon();
				}
				parent::storeValue($value, $type, $inputData);
			}
		}
		else
		{
			$app = JFactory::getApplication();

			
			$icon = $app->input->files->get($this->getId() . "_icon");
			
			$mime_types = array("image/jpeg", "image/pjpeg", "image/png", "image/gif", "image/bmp", "image/x-windows-bmp");
			if ($icon['name'])
			{
				if (in_array($icon['type'], $mime_types))
				{
					
					$this->removeIcon();

					
					$iconDirectory  = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_icon_directory", "media/com_judownload/images/document/");
					$icon_file_name = $this->doc_id . "_" . JUDownloadHelper::fileNameFilter($icon['name']);
					if (JFile::upload($icon['tmp_name'], $iconDirectory . "original/" . $icon_file_name)
						&& JUDownloadHelper::renderImages($iconDirectory . "original/" . $icon_file_name, $iconDirectory . $icon_file_name, 'doc_icon', true, null, $this->doc_id)
					)
					{
						$value = $icon_file_name;
						parent::storeValue($value, $type, $inputData);
					}
				}
				else
				{
					JError::raise(
						E_NOTICE,
						500,
						JText::sprintf('COM_JUDOWNLOAD_ICON_IS_NOT_VALID_MIME_TYPE')
					);
				}
			}
			elseif ($value == "" || strpos($value, 'default/') === 0)
			{
				if ($this->doc && $this->doc->icon && strpos($this->doc->icon, 'default/') === false)
				{
					$this->removeIcon();
				}
				parent::storeValue($value, $type, $inputData);
			}
		}
	}

	protected function removeIcon()
	{
		$iconDirectory = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_icon_directory", "media/com_judownload/images/document/");

		
		if (JFile::exists($iconDirectory . $this->doc->icon))
		{
			JFile::delete($iconDirectory . $this->doc->icon);
		}

		if (JFile::exists($iconDirectory . "original/" . $this->doc->icon))
		{
			JFile::delete($iconDirectory . "original/" . $this->doc->icon);
		}
	}

	public function onDelete($deleteAll = false)
	{
		if ($this->value)
		{
			$this->removeIcon();
		}
	}

	public function onCopy($toDocumentId, &$fieldData = array())
	{
		if ($this->doc_id && isset($fieldData[$this->id]))
		{
			$copiedDocumentObject = JUDownloadHelper::getDocumentById($toDocumentId);
			$fieldData[$this->id] = $copiedDocumentObject->icon;
		}
	}
}

?>