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

class JUDownloadFrontHelperCaptcha
{
	
	protected static $cache = array();

	
	protected static function initCaptcha($namespace = null, $check = false)
	{
		require_once JPATH_SITE . '/components/com_judownload/libs/securimage.php';
		$secureImage            = new Securimage();
		$secureImage->namespace = $namespace;
		if ($check == false)
		{
			$params                                    = JUDownloadHelper::getParams();
			$secureImage->image_width                  = $params->get('captcha_width', '155');
			$secureImage->image_height                 = $params->get('captcha_height', '50');
			$secureImage->font_ratio                   = null;
			$secureImage->image_type                   = $secureImage::SI_IMAGE_PNG;
			$secureImage->image_bg_color               = new Securimage_Color($params->get('captcha_bg_color', '#ffffff'));
			$secureImage->text_color                   = new Securimage_Color($params->get('captcha_color', '#050505'));
			$secureImage->line_color                   = new Securimage_Color($params->get('captcha_line_color', '#707070'));
			$secureImage->noise_color                  = new Securimage_Color($params->get('captcha_noise_color', '#707070'));
			$secureImage->use_transparent_text         = true;
			$secureImage->text_transparency_percentage = 20;
			$secureImage->code_length                  = $params->get('captcha_length', '6');
			$secureImage->case_sensitive               = false;
			$secureImage->charset                      = 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789';
			$secureImage->expiry_time                  = 900;
			$secureImage->session_name                 = null;
			$secureImage->perturbation                 = $params->get('captcha_perturbation', '5') / 10;
			$secureImage->num_lines                    = $params->get('captcha_num_lines', '3');
			$secureImage->noise_level                  = $params->get('captcha_noise_level', '2');
			$secureImage->image_signature              = '';
			$secureImage->signature_color              = new Securimage_Color('#707070');
			$secureImage->signature_font               = null;
			$secureImage->captcha_type                 = $secureImage::SI_CAPTCHA_STRING;
			$secureImage->ttf_file                     = 'components/com_judownload/libs/captcha_fonts/' . $params->get('captcha_font', 'AHGBold.ttf');
			$secureImage->use_wordlist                 = false;
			$secureImage->wordlist_file                = null;
			$secureImage->background_directory         = null;
		}

		return $secureImage;
	}

	
	public static function captchaSecurityImages($namespace = null)
	{
		$secureImage = JUDownloadFrontHelperCaptcha::initCaptcha($namespace);

		JUDownloadHelper::obCleanData();
		$secureImage->show();
		$secureImage->getCode();
	}

	
	public static function getCaptcha($hiddenCaptcha = false, $captchaNameSpaceValue = null, $label = true, $name = "security_code", $id = "security_code", $captchaNameSpaceName = "captcha_namespace")
	{
		$document = JFactory::getDocument();
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/captcha.js");
		$params = JUDownloadHelper::getParams();

		$captchaNameSpaceValue = !$captchaNameSpaceValue ? md5(time()) : $captchaNameSpaceValue;

		$html = '';

		if ($label)
		{
			$html .= '<div class="control-group">';
			$html .= '<label class="control-label" for="' . $id . '">' . JText::_('COM_JUDOWNLOAD_CAPTCHA') . '<span class="required" style="color: red">*</span></label>';
			$html .= '<div class="controls">';
		}

		$html .= '<div class="judl-captcha pull-left">';
		$html .= '<div class="clearfix">';

		if ($hiddenCaptcha == false)
		{
			$html .= '<img class="captcha-image" alt="' . JText::_('COM_JUDOWNLOAD_CAPTCHA') . '"
							src="' . JUri::root(true) . '/index.php?option=com_judownload&task=captcha&captcha_namespace=' . $captchaNameSpaceValue . '&tmpl=component"
							width="' . $params->get('captcha_width', '155') . 'px"  height="' . $params->get('captcha_height', '50') . 'px"/>';
		}
		else
		{
			$html .= '<img class="captcha-image" alt="' . JText::_('COM_JUDOWNLOAD_CAPTCHA') . '"
							src="" width="' . $params->get('captcha_width', '155') . 'px"  height="' . $params->get('captcha_height', '50') . 'px"/>';
		}
		$html .= '<input type="hidden" class="captcha-namespace" name="' . $captchaNameSpaceName . '" value="' . $captchaNameSpaceValue . '" />';
		$html .= '</div>';
		$html .= '<div class="input-group input-group-sm">';
		$html .= '<input type="text" id="' . $id . '" name="' . $name . '" class="security_code form-control required" autocomplete="off"/>';
		$html .= '<span class="input-group-addon btn btn-default reload-captcha" title="' . JText::_('COM_JUDOWNLOAD_RELOAD_CAPTCHA') . '"><i class="fa fa-refresh" ></i></span>';
		$html .= '</div>';
		$html .= '</div>';

		if ($label)
		{
			$html .= '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	
	public static function checkCaptcha($namespace = null, $captcha = '')
	{
		if (!$namespace)
		{
			$namespace = JFactory::getApplication()->input->getString('captcha_namespace', '');
		}

		if (!$captcha)
		{
			$captcha = JFactory::getApplication()->input->getString('security_code', '');
		}

		if ($captcha && $namespace)
		{
			$secureImage = JUDownloadFrontHelperCaptcha::initCaptcha($namespace);

			if ($secureImage->check($captcha, true) == true)
			{
				return true;
			}
		}

		return false;
	}

}