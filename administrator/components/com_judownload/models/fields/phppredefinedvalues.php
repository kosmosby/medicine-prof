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


class JFormFieldPHPPredefinedValues extends JFormField
{
	
	protected $type = 'PHPPredefinedValues';

	
	protected function getInput()
	{
		$token = JSession::getFormToken();

		$isJoomla3x = JUDownloadHelper::isJoomla3x();

		$script = '
		jQuery(document).ready(function($){';
		if ($isJoomla3x && $this->checkEditor('codemirror'))
		{
			$script .= '
			Joomla.editors.instances["jform_php_predefined_values"].on("change", function(){
				$("#jform_php_predefined_values").removeData("validPhp");
			});';
		}
		else
		{
			$script .= '
			$("#jform_php_predefined_values").on("change", function(){
				$(this).removeData("validPhp");
			});';
		}

		$script .= '
			$("button.testcode").click(function(){
				testPhpCode(false, "");

				return false;
			});

			testPhpCode = function(submitOnSuccess, task){
				var testCodeBtn = $("button.testcode");
					if(testCodeBtn.hasClass("disabled"))
					{
						return false;
					}

					testCodeBtn.addClass("disabled");
				var plugin_id = $("#jform_plugin_id").val();';

		if ($isJoomla3x && $this->checkEditor('codemirror'))
		{
			$script .= '
				var	php_code = Joomla.editors.instances["jform_php_predefined_values"].getValue();';
		}
		else
		{
			$script .= '
				var	php_code = $("#jform_php_predefined_values").val();';
		}

		$script .= '
				$.ajax({
						type: "POST",
						url: "index.php?option=com_judownload&task=field.testPhpCode",
						data: { field_id: ' . (int) $this->form->getValue('id') . ', plugin_id: plugin_id, php_predefined_values: php_code, "' . $token . '": 1 }
					})
					.done(function( data ) {
						$("code.result").html(data).parent().show();

						if(data.indexOf("<b>Fatal error</b>") !== -1)
						{
							$("#jform_php_predefined_values").data("validPhp", 0);

							if(submitOnSuccess)
							{
								alert("Predefined PHP values is invalid");
							}

							return false;
						}
						else
						{
							$("#jform_php_predefined_values").data("validPhp", 1);

							if(submitOnSuccess)
							{
								Joomla.submitbutton(task);
							}

							return true;
						}
					})
					.fail(function(jqXHR, textStatus) {
						$("#jform_php_predefined_values").data("validPhp", 0);
						alert("Error " + jqXHR.status + ": " + jqXHR.statusText);
					})
					.always(function() {
						testCodeBtn.removeClass("disabled");
					});
                }
            });';

		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script);

		$editor = JFactory::getEditor('codemirror');
		$html   = '<button class="btn testcode"><i class="icon-ok"></i> Test code</button>';
		$html .= '<div class="hide" style="clear: both; max-width: 100%; max-height: 300px; overflow: auto; margin: 10px 0;"><code class="result pull-left"></code></div>';
		$html .= '<div style="clear: both; color: #999; font-family: Monaco,Menlo,Consolas,\'Courier New\',monospace; margin-top: 10px;">';
		$html .= '<div>// Remember to not include the <b>&lt;?php</b> and <b>?&gt;</b> tags.</div>';
		$html .= '<div>// To highlight code, put the first line as: <b>//&lt;?php</b></div>';
		$html .= '</div>';
		if ($isJoomla3x && $this->checkEditor('codemirror'))
		{
			$html .= $editor->display($this->name, htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'), $this->element['width'],
				$this->element['height'], $this->element['cols'], $this->element['rows'], false, $this->id);
		}
		else
		{
			$html .= '<textarea style="';
			if ($this->element['width'])
			{
				$html .= 'width: ' . $this->element['width'] . 'px;';
			}

			if ($this->element['height'])
			{
				$html .= 'height: ' . $this->element['height'] . 'px;';
			}

			$html .= '"';

			if ($this->element['cols'])
			{
				$html .= ' cols="' . $this->element['cols'] . '"';
			}

			if ($this->element['rows'])
			{
				$html .= ' rows="' . $this->element['rows'] . '"';
			}

			$html .= ' name="' . $this->name . '"';
			$html .= ' id="' . $this->id . '"';
			$html .= '>' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
		}
		$html .= '<div style="clear: both; font-family: Monaco,Menlo,Consolas,\'Courier New\',monospace;">';
		$html .= '<span style="color: #999;">// End function - return appropriate value</span>';
		$html .= '</div>';

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
}