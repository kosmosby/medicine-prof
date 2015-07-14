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


jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

// Don't change this constant if you don't know what are you doing, it can break your site
define('JUDLPROVERSION', false);


JLoader::register('JUDownloadFrontHelper', JPATH_SITE . '/components/com_judownload/helpers/helper.php');
JLoader::register('JUDownloadFrontHelperBreadcrumb', JPATH_SITE . '/components/com_judownload/helpers/breadcrumb.php');
JLoader::register('JUDownloadFrontHelperCaptcha', JPATH_SITE . '/components/com_judownload/helpers/captcha.php');
JLoader::register('JUDownloadFrontHelperCategory', JPATH_SITE . '/components/com_judownload/helpers/category.php');
JLoader::register('JUDownloadFrontHelperComment', JPATH_SITE . '/components/com_judownload/helpers/comment.php');
JLoader::register('JUDownloadFrontHelperCriteria', JPATH_SITE . '/components/com_judownload/helpers/criteria.php');
JLoader::register('JUDownloadFrontHelperDocument', JPATH_SITE . '/components/com_judownload/helpers/document.php');
JLoader::register('JUDownloadFrontHelperEditor', JPATH_SITE . '/components/com_judownload/helpers/editor.php');
JLoader::register('JUDownloadFrontHelperField', JPATH_SITE . '/components/com_judownload/helpers/field.php');
JLoader::register('JUDownloadFrontHelperLanguage', JPATH_SITE . '/components/com_judownload/helpers/language.php');
JLoader::register('JUDownloadFrontHelperLog', JPATH_SITE . '/components/com_judownload/helpers/log.php');
JLoader::register('JUDownloadFrontHelperMail', JPATH_SITE . '/components/com_judownload/helpers/mail.php');
JLoader::register('JUDownloadFrontHelperModerator', JPATH_SITE . '/components/com_judownload/helpers/moderator.php');
JLoader::register('JUDownloadFrontHelperPassword', JPATH_SITE . '/components/com_judownload/helpers/password.php');
JLoader::register('JUDownloadFrontHelperPermission', JPATH_SITE . '/components/com_judownload/helpers/permission.php');
JLoader::register('JUDownloadFrontHelperPluginParams', JPATH_SITE . '/components/com_judownload/helpers/pluginparams.php');
JLoader::register('JUDownloadFrontHelperRating', JPATH_SITE . '/components/com_judownload/helpers/rating.php');
JLoader::register('JUDownloadFrontHelperSeo', JPATH_SITE . '/components/com_judownload/helpers/seo.php');
JLoader::register('JUDownloadFrontHelperString', JPATH_SITE . '/components/com_judownload/helpers/string.php');
JLoader::register('JUDownloadFrontHelperTemplate', JPATH_SITE . '/components/com_judownload/helpers/template.php');

JLoader::register('JUDownloadHelperRoute', JPATH_SITE . '/components/com_judownload/helpers/route.php');


JLoader::register('JUDownloadHelper', JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/judownload.php');
JLoader::register('JUTimThumb', JPATH_ADMINISTRATOR . '/components/com_judownload/timthumb/timthumb.php');
JLoader::register('Watermark', JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/watermark.class.php');
JLoader::register('JUDLView', JPATH_SITE . '/components/com_judownload/helpers/judlview.php');
JLoader::register('JUDLPagination', JPATH_SITE . '/components/com_judownload/helpers/judlpagination.php');
JLoader::register('JUDLModelList', JPATH_SITE . '/components/com_judownload/helpers/judlmodellist.php');

JLoader::register('JUDownloadSearchHelper', JPATH_SITE . '/components/com_judownload/helpers/search.php');

spl_autoload_register(array('JUDownloadHelper', 'autoLoadFieldClass'));


JUDownloadFrontHelperLanguage::loadLanguageForTopLevelCat();


JUDownloadFrontHelperLanguage::loadLanguageFile("com_judownload.custom");

if (JUDownloadHelper::isJoomla3x())
{
	JHtml::_('script', 'system/core.js', false, true);
}

$app  = JFactory::getApplication();
$task = $app->input->get('task');

switch ($task)
{
	case 'captcha':
		$namespace = $app->input->getString('captcha_namespace', '');
		JUDownloadFrontHelperCaptcha::captchaSecurityImages($namespace);
		exit;
		break;

	case 'rawdata':
		$field_id = $app->input->getInt('field_id', 0);
		$doc_id   = $app->input->getInt('doc_id', 0);
		$fieldObj = JUDownloadFrontHelperField::getField($field_id, $doc_id);
		JUDownloadHelper::obCleanData();
		$fieldObj->getRawData();
		exit;
		break;

	case 'cron':
		
		JUDownloadFrontHelperMail::sendMailq();
		exit;
		break;

	default:
		$controller = JControllerLegacy::getInstance('judownload');

		
		$controller->execute($app->input->get('task'));

		
		$controller->redirect();
		break;
}


$params = JUDownloadHelper::getParams();
if ($params->get('send_mailqs_on_pageload', 0))
{
	JUDownloadFrontHelperMail::sendMailq();
}
