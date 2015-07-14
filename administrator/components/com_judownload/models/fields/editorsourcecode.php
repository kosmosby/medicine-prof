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

JFormHelper::loadFieldClass('textarea');

class JFormFieldEditorSourceCode extends JFormFieldTextarea
{
	
	public $type = 'EditorSourceCode';

	
	protected $editor;

	
	protected $height;

	
	protected $width;

	
	protected $assetField;

	
	protected $authorField;

	
	protected $asset;

	
	protected $buttons;

	
	protected $hide;

	
	protected $editorType;

	
	public function __get($name)
	{
		switch ($name)
		{
			case 'height':
			case 'width':
			case 'assetField':
			case 'authorField':
			case 'asset':
			case 'buttons':
			case 'hide':
			case 'editorType':
				return $this->$name;
		}

		return parent::__get($name);
	}

	
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'height':
			case 'width':
			case 'assetField':
			case 'authorField':
			case 'asset':
				$this->$name = (string) $value;
				break;

			case 'buttons':
				$value = (string) $value;

				if ($value == 'true' || $value == 'yes' || $value == '1')
				{
					$this->buttons = true;
				}
				elseif ($value == 'false' || $value == 'no' || $value == '0')
				{
					$this->buttons = false;
				}
				else
				{
					$this->buttons = explode(',', $value);
				}
				break;

			case 'hide':
				$value      = (string) $value;
				$this->hide = $value ? explode(',', $value) : array();
				break;

			case 'editorType':
				
				$this->editorType = explode('|', trim((string) $value));
				break;

			default:
				
				if (JUDownloadHelper::isJoomla3x())
				{
					parent::__set($name, $value);
				}
				else
				{
					$this->$name = $value;
				}
		}
	}

	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result == true)
		{
			$this->height      = $this->element['height'] ? (string) $this->element['height'] : '500';
			$this->width       = $this->element['width'] ? (string) $this->element['width'] : '100%';
			$this->assetField  = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
			$this->authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
			$this->asset       = $this->form->getValue($this->assetField) ? $this->form->getValue($this->assetField) : (string) $this->element['asset_id'];

			$buttons    = (string) $this->element['buttons'];
			$hide       = (string) $this->element['hide'];
			$editorType = (string) $this->element['editor'];

			if ($buttons == 'true' || $buttons == 'yes' || $buttons == '1')
			{
				$this->buttons = true;
			}
			elseif ($buttons == 'false' || $buttons == 'no' || $buttons == '0')
			{
				$this->buttons = false;
			}
			else
			{
				$this->buttons = !empty($hide) ? explode(',', $buttons) : array();
			}

			$this->hide       = !empty($hide) ? explode(',', (string) $this->element['hide']) : array();
			$this->editorType = !empty($editorType) ? explode('|', trim($editorType)) : array();
		}

		return $result;
	}

	
	protected function getInput()
	{
		JHtml::_('behavior.framework');
		JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/codemirror.js', false, false, false, false);
		JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/fullscreen.js', false, false, false, false);
		JHtml::_('stylesheet', 'administrator/components/com_judownload/assets/editors/codemirror/css/codemirror.css');
		JHtml::_('stylesheet', 'administrator/components/com_judownload/assets/editors/codemirror/css/configuration.css');

		
		$name    = $this->name;
		$id      = empty($this->id) ? $this->name : $this->id;
		$col     = $this->cols;
		$row     = $this->rows;
		$content = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		$this->params = new JRegistry;
		$this->params->set('syntax', (string) $this->element['syntax']);
		$this->params->set('lineNumbers', 1);
		$this->params->set('lineWrapping', 1);
		$this->params->set('matchTags', 1);
		$this->params->set('matchBrackets', 1);
		$this->params->set('marker-gutter', 1);
		$this->params->set('autoCloseTags', 1);
		$this->params->set('autoCloseBrackets', 1);
		$this->params->set('autoFocus', 1);
		$this->params->set('theme', 'default');
		$this->params->set('tabmode', 'indent');

		
		$syntax = $this->params->get('syntax', 'php');

		if ($syntax)
		{
			switch ($syntax)
			{
				case 'css':
					$parserFile        = array('css.js', 'closebrackets.js');
					$mode              = 'text/css';
					$autoCloseBrackets = true;
					$autoCloseTags     = false;
					$fold              = true;
					$matchTags         = false;
					$matchBrackets     = true;
					JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/brace-fold.js', false, false, false, false);
					break;

				case 'ini':
					$parserFile        = array('css.js');
					$mode              = 'text/css';
					$autoCloseBrackets = false;
					$autoCloseTags     = false;
					$fold              = false;
					$matchTags         = false;
					$matchBrackets     = false;
					break;

				case 'xml':
					$parserFile        = array('xml.js', 'closetag.js');
					$mode              = 'application/xml';
					$fold              = true;
					$autoCloseBrackets = false;
					$autoCloseTags     = true;
					$matchTags         = true;
					$matchBrackets     = false;
					JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/xml-fold.js', false, false, false, false);
					break;

				case 'js':
					$parserFile        = array('javascript.js', 'closebrackets.js');
					$mode              = 'text/javascript';
					$autoCloseBrackets = true;
					$autoCloseTags     = false;
					$fold              = true;
					$matchTags         = false;
					$matchBrackets     = true;
					JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/brace-fold.js', false, false, false, false);
					break;

				case 'less':
					$parserFile        = array('less.js', 'css.js', 'closebrackets.js');
					$mode              = 'text/x-less';
					$autoCloseBrackets = true;
					$autoCloseTags     = false;
					$fold              = true;
					$matchTags         = false;
					$matchBrackets     = true;
					JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/brace-fold.js', false, false, false, false);
					break;

				case 'php':
					$parserFile        = array('xml.js', 'clike.js', 'css.js', 'javascript.js', 'htmlmixed.js', 'php.js', 'closebrackets.js', 'closetag.js');
					$mode              = 'application/x-httpd-php';
					$autoCloseBrackets = true;
					$autoCloseTags     = true;
					$fold              = true;
					$matchTags         = true;
					$matchBrackets     = true;
					JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/brace-fold.js', false, false, false, false);
					JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/xml-fold.js', false, false, false, false);
					break;

				default:
					$parserFile        = false;
					$mode              = 'text/plain';
					$autoCloseBrackets = false;
					$autoCloseTags     = false;
					$fold              = false;
					$matchTags         = false;
					$matchBrackets     = false;
					break;
			}
		}

		if ($parserFile)
		{
			foreach ($parserFile AS $file)
			{
				JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/' . $file, false, false, false, false);
			}
		}

		$options = new stdClass;

		$options->mode        = $mode;
		$options->smartIndent = true;

		
		if ($this->params->get('lineNumbers') == "1")
		{
			$options->lineNumbers = true;
		}

		if ($this->params->get('autoFocus') == "1")
		{
			$options->autofocus = true;
		}

		if ($this->params->get('autoCloseBrackets') == "1")
		{
			$options->autoCloseBrackets = $autoCloseBrackets;
		}

		if ($this->params->get('autoCloseTags') == "1")
		{
			$options->autoCloseTags = $autoCloseTags;
		}

		if ($this->params->get('matchTags') == "1")
		{
			$options->matchTags = $matchTags;
			JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/matchtags.js', false, false, false, false);
		}

		if ($this->params->get('matchBrackets') == "1")
		{
			$options->matchBrackets = $matchBrackets;
			JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/matchbrackets.js', false, false, false, false);
		}

		if ($this->params->get('marker-gutter') == "1")
		{
			$options->foldGutter = $fold;
			$options->gutters    = array('CodeMirror-linenumbers', 'CodeMirror-foldgutter', 'breakpoints');
			JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/foldcode.js', false, false, false, false);
			JHtml::_('script', 'administrator/components/com_judownload/assets/editors/codemirror/js/foldgutter.js', false, false, false, false);
		}

		if ($this->params->get('theme', '') == 'ambiance')
		{
			$options->theme = 'ambiance';
			JHtml::_('stylesheet', 'administrator/components/com_judownload/assets/editors/codemirror/css/ambiance.css');
		}

		if ($this->params->get('lineWrapping') == "1")
		{
			$options->lineWrapping = true;
		}

		if ($this->params->get('tabmode', '') == 'shift')
		{
			$options->tabMode = 'shift';
		}

		$html   = array();
		$html[] = "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = '<script type="text/javascript">';
		$html[] = 'var judlEditor;';
		$html[] = '(function() {';
		$html[] = '		var editor = CodeMirror.fromTextArea(document.getElementById("' . $id . '"), ' . json_encode($options) . ');';
		$html[] = '		editor.setOption("extraKeys", {';
		$html[] = '			"Ctrl-Q": function(cm) {';
		$html[] = '				setFullScreen(cm, !isFullScreen(cm));';
		$html[] = '			},';
		$html[] = '			"Esc": function(cm) {';
		$html[] = '				if (isFullScreen(cm)) setFullScreen(cm, false);';
		$html[] = '			}';
		$html[] = '		});';
		$html[] = '		editor.on("gutterClick", function(cm, n) {';
		$html[] = '			var info = cm.lineInfo(n)';
		$html[] = '			cm.setGutterMarker(n, "breakpoints", info.gutterMarkers ? null : makeMarker())';
		$html[] = '		})';
		$html[] = '		function makeMarker() {';
		$html[] = '			var marker = document.createElement("div")';
		$html[] = '			marker.style.color = "#822"';
		$html[] = '			marker.innerHTML = "●"';
		$html[] = '			return marker';
		$html[] = '		}';
		$html[] = '		judlEditor = editor';
		$html[] = '})()';
		$html[] = '</script>';

		return implode("\n", $html);
	}

}
