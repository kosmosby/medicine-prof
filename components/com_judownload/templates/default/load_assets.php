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

require_once "template_helper.php";

$app      = JFactory::getApplication();
$document = JFactory::getDocument();

// Name of current template that component using
//$this->template

// Name of this template
$self_template = basename(dirname(__FILE__));

$templateStyle = JUDownloadFrontHelperTemplate::getCurrentTemplateStyle();
$templateParams = $templateStyle->params;

//Load font awesome icon
$document->addStyleSheet(JUri::root(true) . '/components/com_judownload/assets/css/font-awesome.min.css');

JUDownloadFrontHelper::loadjQuery();
JUDownloadFrontHelper::loadBootstrap(3, $templateParams->get('load_bootstrap', '2'));

$JUDLTemplateDefaultHelper = new JUDLTemplateDefaultHelper($self_template);

$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/assets/css/reset.css");
$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/assets/css/core.css");
$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/common.css");

// Define a variable that allows google analytics tracks download (core.js)
$document->addScriptDeclaration("
	var google_analytics_track_download = " . $this->params->get("google_analytics_track_download", 0) . ";
");

// JText in core.js
JText::script('COM_JUDOWNLOAD_DOWNLOAD');
JText::script('COM_JUDOWNLOAD_DOCUMENT');
JText::script('COM_JUDOWNLOAD_DOCUMENTS');
JText::script('COM_JUDOWNLOAD_DOWNLOAD_N_DOCUMENT_1');
JText::script('COM_JUDOWNLOAD_DOWNLOAD_N_DOCUMENT');
JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_DOCUMENTS');
JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_PUBLISH_THESE_DOCUMENTS');
JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_UNPUBLISH_THESE_DOCUMENTS');

$view = $this->getName();
switch ($view)
{
	case 'advsearch':
		if ($app->input->getInt('advancedsearch', 0) || !is_null($app->input->get('limitstart')))
		{
			// Load primary stylesheet
			$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

			// Load primary javascript
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

			$JUDLTemplateDefaultHelper->loadTooltipster();

			// Load switch mode view
			if ($this->allow_user_select_view_mode)
			{
				$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
			}
		}
		else
		{
			$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.advsearch.css");
		}
		break;

	case 'categories':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.categories.css");
		break;

	case 'category':
		// Load primary stylesheet
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.category.css");
		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");
		if ($this->getLayout() != 'list')
		{
			$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

			$JUDLTemplateDefaultHelper->loadTooltipster();

			// Load switch mode view
			if ($this->allow_user_select_view_mode)
			{
				$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
			}
		}
		break;

	case 'collection' :
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.collection.css");

		if ($this->getLayout() == 'edit')
		{
			JUDownloadFrontHelper::loadjQueryUI();
			$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/assets/css/typeahead.collection.css");
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/handlebars.min.js");
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/typeahead.bundle.min.js");
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/jquery.dragsort.min.js");
			// JText in view.collection.js
			JText::script('COM_JUDOWNLOAD_PENDING_ADD');
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.collection.js");
		}
		else
		{
			$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

			// Load primary javascript
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

			$JUDLTemplateDefaultHelper->loadTooltipster();
		}

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'collections':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.collections.css");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/jquery.juvote.js");
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.collections.js");

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'commenttree':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.commenttree.css");

		$JUDLTemplateDefaultHelper->loadTooltipster();
		break;

	case 'contact' :
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.contact.css");
		break;

	case 'dashboard' :
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.dashboard.css");
		break;

	case 'documents':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.documents.css");
		break;

	case 'document':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document.css");

		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$script = 'var docId = ' . $this->item->id . ',
						token = "' . $this->token . '";';
		$document->addScriptDeclaration($script);

		// JText in view.document.js
		JText::script('COM_JUDOWNLOAD_PLEASE_RATING_BEFORE_SUBMIT_COMMENT');
		JText::script('COM_JUDOWNLOAD_THANK_YOU_FOR_VOTING');
		JText::script('COM_JUDOWNLOAD_DOWNLOAD_N_FILE_1');
		JText::script('COM_JUDOWNLOAD_DOWNLOAD_N_FILE');
		JText::script('COM_JUDOWNLOAD_INVALID_FIELD');
		JText::script('COM_JUDOWNLOAD_REQUIRED_FIELD');
		JText::script('COM_JUDOWNLOAD_YOU_HAVE_NOT_ENTERED_COLLECTION_NAME');
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.document.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

        if($this->_layout == 'default')
        {
            if ($this->item->comment->total_comments_no_filter)
            {
                // Vote comment
                $document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/jquery.juvote.js");

                // Readmore comment
                $document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/readmore.min.js");

                $readmoreCommentJS = "jQuery(document).ready(function($){
				            $('.comment-text .comment-content').readmore({
				                speed    : 300,
				                maxHeight: 150,
				                moreLink: '<span class=\"see-more\" title=\"" . JText::_('COM_JUDOWNLOAD_SEE_MORE') . "\" href=\"#\"><i class=\"fa fa-chevron-down\"></i></span>',
				                lessLink: '<span class=\"see-less\" title=\"" . JText::_('COM_JUDOWNLOAD_SEE_LESS') . "\" href=\"#\"><i class=\"fa fa-chevron-up\"></i></span>',
				                embedCSS: false
				            });
				        });";
                $document->addScriptDeclaration($readmoreCommentJS);
            }

            if ($this->params->get('comment_form_editor', 'wysibb') == 'wysibb' && $this->params->get('comment_system', 'default') == 'default')
            {
                JUDownloadFrontHelperEditor::getWysibbEditor('.comment-editor');
                // JText in comment-wysibb.js
                JText::script('COM_JUDOWNLOAD_UPDATE_COMMENT_ERROR');
                JText::script('COM_JUDOWNLOAD_PLEASE_ENTER_AT_LEAST_N_CHARACTERS');
                JText::script('COM_JUDOWNLOAD_CONTENT_LENGTH_REACH_MAX_N_CHARACTERS');
                $document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/comment-wysibb.js");
            }
        }

		break;

	case 'embeddocument':
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.embeddocument.js");
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/embeddocument.css");
		break;

	case 'downloaderror':
		break;

	case 'featured':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'form' :
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.form.css");

		JUDownloadFrontHelper::loadjQueryUI();
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/handlebars.min.js");
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/jquery.dragsort.min.js");
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/assets/plupload/css/jquery.plupload.queue.css");
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/plupload/js/plupload.full.min.js");
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/plupload/js/jquery.plupload.queue.min.js");

		JUDownloadHelper::formValidation();

		// JText in forms/document.js
		JText::script('COM_JUDOWNLOAD_INVALID_IMAGE');
		JText::script('COM_JUDOWNLOAD_INVALID_FILE_NAME');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_UPLOAD_FILE_BECAUSE_IT_IS_EMPTY');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_UPLOAD_THIS_FILE_PLEASE_RECHECK_MIMETYPE_FILE');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_TRANSFER_FILE');
		JText::script('COM_JUDOWNLOAD_OTHER_FILE_IS_UPLOADING_DO_YOU_WANT_TO_CANCEL_TO_UPLOAD_NEW_FILE');
		JText::script('COM_JUDOWNLOAD_YOU_HAVE_TO_UPLOAD_AT_LEAST_ONE_FILE');
		JText::script('COM_JUDOWNLOAD_YOU_HAVE_NOT_ENTERED_SOURCE_URL');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_UPLOAD_THIS_FILE_DOCUMENT_REACH_MAX_UPLOAD_FILES_N_FILES');
		JText::script('COM_JUDOWNLOAD_PLEASE_UPLOAD_A_FILE');
		JText::script('COM_JUDOWNLOAD_REMOVE');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_ADD_IMAGE_BECAUSE_MAX_NUMBER_OF_IMAGE_IS_N');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_RESTORE_THIS_FILE_DOCUMENT_REACH_MAX_UPLOAD_N_FILES');
		JText::script('COM_JUDOWNLOAD_TOGGLE_TO_PUBLISH');
		JText::script('COM_JUDOWNLOAD_TOGGLE_TO_UNPUBLISH');
		JText::script('COM_JUDOWNLOAD_CLICK_TO_REMOVE');
		JText::script('COM_JUDOWNLOAD_YOU_MUST_UPLOAD_AT_LEAST_ONE_IMAGE');
		JText::script('COM_JUDOWNLOAD_FILE_TITLE');
		JText::script('COM_JUDOWNLOAD_FILE_NAME');
		JText::script('COM_JUDOWNLOAD_DESCRIPTION');
		JText::script('COM_JUDOWNLOAD_FIELD_TITLE');
		JText::script('COM_JUDOWNLOAD_FIELD_DESCRIPTION');
		JText::script('COM_JUDOWNLOAD_FIELD_PUBLISHED');
		JText::script('COM_JUDOWNLOAD_UPDATE');
		JText::script('COM_JUDOWNLOAD_CANCEL');
		$document->addScript(JUri::root(true) . "/" . $this->script);
		break;

	case 'listall':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.listall.css");
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'listalpha':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.listalpha.css");
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'modcomment':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.modcomment.css");
		break;

	case 'modcomments':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.modcomments.css");

		// JText in comments.js
		JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_PUBLISH_THESE_COMMENTS');
		JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_UNPUBLISH_THESE_COMMENTS');
		JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_COMMENTS');
		JText::script('COM_JUDOWNLOAD_NO_ITEM_SELECTED');
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/comments.js");
		break;

	case 'moddocuments' :
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.moddocuments.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.moddocuments.js");
		break;

	case 'modpermission':
		break;

	case 'modpermissions' :
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");
		break;

	case 'modpendingcomments':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.modpendingcomments.css");

		// JText in comments.js
		JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_PUBLISH_THESE_COMMENTS');
		JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_UNPUBLISH_THESE_COMMENTS');
		JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_COMMENTS');
		JText::script('COM_JUDOWNLOAD_NO_ITEM_SELECTED');
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/comments.js");
		break;

	case 'modpendingdocuments' :
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.modpendingdocuments.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.modpendingdocuments.js");
		break;

	case 'profile':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.dashboard.css");
		break;

	case 'report':
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.report.js");
		break;

	case 'search':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'searchby':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'subscribe':
		break;

	case 'tag':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'tags':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.tags.css");
		break;

	case 'topcomments':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.topcomments.css");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Readmore comment
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/readmore.min.js");

		$readmoreCommentJS = "jQuery(document).ready(function($){
				            $('.comment-text .comment-content').readmore({
				                speed    : 300,
				                maxHeight: 150,
				                moreLink: '<span class=\"see-more\" title=\"" . JText::_('COM_JUDOWNLOAD_SEE_MORE') . "\" href=\"#\"><i class=\"fa fa-chevron-down\"></i></span>',
				                lessLink: '<span class=\"see-less\" title=\"" . JText::_('COM_JUDOWNLOAD_SEE_LESS') . "\" href=\"#\"><i class=\"fa fa-chevron-up\"></i></span>',
				                embedCSS: false
				            });
				        });";
		$document->addScriptDeclaration($readmoreCommentJS);
		break;

	case 'topdocuments':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		// JText in view.topdocuments.js
		JText::script('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_CLEAR_ALL_RECENTLY_VIEWED_DOCUMENTS');
		JText::script('COM_JUDOWNLOAD_CLEAR_ALL_RECENTLY_VIEWED_DOCUMENTS_SUCCESSFULLY');
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/view.topdocuments.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'tree':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.category.css");
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'usercomments':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.usercomments.css");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Readmore comment
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/readmore.min.js");

		$readmoreCommentJS = "jQuery(document).ready(function($){
				            $('.comment-text .comment-content').readmore({
				                speed    : 300,
				                maxHeight: 150,
				                moreLink: '<span class=\"see-more\" title=\"" . JText::_('COM_JUDOWNLOAD_SEE_MORE') . "\" href=\"#\"><i class=\"fa fa-chevron-down\"></i></span>',
				                lessLink: '<span class=\"see-less\" title=\"" . JText::_('COM_JUDOWNLOAD_SEE_LESS') . "\" href=\"#\"><i class=\"fa fa-chevron-up\"></i></span>',
				                embedCSS: false
				            });
				        });";
		$document->addScriptDeclaration($readmoreCommentJS);
		break;

	case 'userdocuments':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.document-list.css");

		// Load primary javascript
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/core.js");

		$JUDLTemplateDefaultHelper->loadTooltipster();

		// Load switch mode view
		if ($this->allow_user_select_view_mode)
		{
			$document->addScript(JUri::root(true) . "/components/com_judownload/assets/js/switch.js");
		}
		break;

	case 'usersubscriptions':
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/templates/" . $self_template . "/assets/css/view.usersubscriptions.css");
		break;

	default:
		break;
}
?>