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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');
JHtml::_('behavior.calendar');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select:not(.browse_cat)');

JFactory::getDocument()->addScript(JUri::root() . "components/com_judownload/assets/js/judl-tabs-state.js");
?>

<script type="text/javascript">
	var buttonClicked = false;
	jQuery(window).on('beforeunload', function (e) {
		if (!buttonClicked) {
			var message = '<?php echo JText::_('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_LEAVE_THIS_PAGE_ALL_DATA_YOU_ENTERED_WILL_BE_LOST'); ?>'; //This is displayed on the dialog
			if (!e) e = window.event;
			//e.cancelBubble is supported by IE - this will kill the bubbling process.
			e.cancelBubble = true;
			e.returnValue = message;
			//e.stopPropagation works in Firefox.
			if (e.stopPropagation) {
				e.stopPropagation();
				e.preventDefault();
			}
			return message;
		}
	});

	Joomla.submitbutton = function (task) {
		buttonClicked = true;
		if (task == 'form.cancel' || task == 'modpendingdocument.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<!-- New file uploaded template -->
<script id="file-template" type="text/x-handlebars-template">
	<li>
		<div class="file-item">
			<span class="move"><i class="fa fa-ellipsis-v"></i></span>

			<div class="file-actions file-row">
				<span class="publish btn btn-mini" data-iconpublish="fa fa-check" data-iconunpublish="fa fa-close">
					<i class="fa fa-check"></i> <?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
				</span>
				<input type="hidden" name="judlfiles[{{key}}][published]" class="file-published-value" value="1" />

				<span class="remove btn btn-mini" data-iconremove="fa fa-trash-o" data-iconunremove="fa fa-undo">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?>
				</span>
				<input type="hidden" name="judlfiles[{{key}}][remove]" class="file-remove-value" value="0" />
			</div>

			<div class="file-row">
				<div class="file-name-info input-append">
					<input name="judlfiles[{{key}}][rename]" id="file-name-{{key}}" class="file-name validate-filename required" type="text" value="{{file.name}}" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILE_NAME'); ?>"/>
					<span class="file-size add-on" title="<?php echo JText::_('COM_JUDOWNLOAD_MIME_TYPE'); ?>: {{file.type}}">{{formatted_file_size}}</span>
					<input type="hidden" name="judlfiles[{{key}}][size]" class="file-size-value" value="{{file.size}}" />
				</div>
				<input type="hidden" name="judlfiles[{{key}}][mime_type]" class="file-mimetype" value="{{file.type}}" />
				<label style="display: none" for="file-name-{{key}}"><?php echo JText::_('COM_JUDOWNLOAD_INVALID_FILE_NAME'); ?></label>
			</div>

			<div class="file-row">
				<input name="judlfiles[{{key}}][title]" class="file-title" type="text" value="" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILE_TITLE'); ?>"/>
				<input name="judlfiles[{{key}}][downloads]" class="file-downloads" type="text" value="0" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOADS'); ?>" title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOADS'); ?>"/>
			</div>

			<div class="file-row">
				<textarea class="file-description" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_DESCRIPTION'); ?>" name="judlfiles[{{key}}][description]" rows="2"></textarea>
			</div>
			<input type="hidden" class="file-filename" value="{{file.target_name}}" name="judlfiles[{{key}}][file_name]"/>
			<input type="hidden" class="file-id" value="0" name="judlfiles[{{key}}][id]"/>
		</div>
	</li>
</script>

<!-- Change log item template -->
<script id="changelog-template" type="text/x-handlebars-template">
	<li>
		<div class="changelog-item">
			<span class="move"><i class="fa fa-ellipsis-v"></i></span>
			<div class="row-fluid">
				<div class="changelog-version span4">
					<input type="text" size="24" class="input-medium" name="changelogs[{{key}}][version]" value="" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FIELD_VERSION'); ?>" /></div>
				<div class="changelog-date span5">
					<div class="input-append" >
						<input type="text" title="" class="input-medium" name="changelogs[{{key}}][date]" id="{{calenderid}}" value="{{nowdate}}" size="25" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DATE'); ?>" />
						<span class="add-on fa fa-calendar" id="{{calenderid}}_img"></span>
					</div>
				</div>
				<div class="changelog-actions span3">
					<span class="unpublish btn btn-mini" data-iconunpublish="fa fa-close" data-iconpublish="fa fa-check">
						<i class="fa fa-check"></i> <?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
					</span>
					<input type="hidden" class="changelog-published-value" value="1" name="changelogs[{{key}}][published]" />

					<span class="btn btn-mini remove" data-iconunremove="fa fa-undo" data-iconremove="fa fa-trash-o">
						<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?>
					</span>
					<input type="hidden" class="changelog-remove-value" value="0" name="changelogs[{{key}}][remove]" />
				</div>
			</div>
			<div class="row-fluid">
				<div class="changelog-description span12">
					<textarea placeholder="<?php echo JText::_('COM_JUDOWNLOAD_DESCRIPTION', 'Description'); ?>" rows="5" cols="50" name="changelogs[{{key}}][description]"></textarea></div>
			</div>
		</div>
		<input type="hidden" class="changelog-id-value" value="0" name="changelogs[{{key}}][id]" />
	</li>
</script>

<div id="judl-container" class="jubootstrap component judl-container view-form judl-form">
	<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>"
	      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
	<div id="alertChangeCategory" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="alertChangeCategoryLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="alertChangeCategoryLabel"><?php echo JText::_('COM_JUDOWNLOAD_CHANGE_MAIN_CATEGORY_WARNING'); ?></h3>
		</div>
		<div class="modal-body">
			<div id="messageChangeFieldGroup"></div>
			<div id="messageChangeTemplate"></div>
		</div>
		<div class="modal-footer">
			<button id="noConfirmChangeCat" class="btn"><?php echo JText::_('COM_JUDOWNLOAD_CLOSE'); ?></button>
			<button id="confirmChangeCat" class="btn btn-primary"><?php echo JText::_('COM_JUDOWNLOAD_CONFIRM_AND_CHANGE'); ?></button>
		</div>
	</div>
	<?php
	echo $this->loadTemplate('btn_group_control');
	?>
	<div class="row-fluid">
	<?php echo JHtml::_('bootstrap.startTabSet', 'document-' . $this->item->id, array('active' => 'details')); ?>

	<?php
	echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'details', JText::_('COM_JUDOWNLOAD_CORE_FIELDS_TAB'));
	echo $this->loadTemplate('main');
	echo JHtml::_('bootstrap.endTab');
	?>

	<?php
	echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'fields', JText::_('COM_JUDOWNLOAD_EXTRA_FIELDS_TAB'));
	echo $this->loadTemplate('fields');
	echo JHtml::_('bootstrap.endTab');
	?>

	<?php
    if($this->params->get('submit_form_show_tab_file', 1))
    {
        echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'files', JText::_('COM_JUDOWNLOAD_FILES_TAB'));
        echo $this->loadTemplate('files');
        echo JHtml::_('bootstrap.endTab');
    }
	?>

	<?php
    if($this->params->get('submit_form_show_tab_changelog', 1))
    {
        echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'changelogs', JText::_('COM_JUDOWNLOAD_CHANGELOGS_TAB'));
        echo $this->loadTemplate('changelogs');
        echo JHtml::_('bootstrap.endTab');
    }
	?>

	<?php
    if($this->params->get('submit_form_show_tab_related', 0))
    {
        echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'related-documents', JText::_('COM_JUDOWNLOAD_RELATED_DOCUMENTS_TAB'));
        echo $this->loadTemplate('rel_documents');
        echo JHtml::_('bootstrap.endTab');
    }
	?>

	<?php
    if($this->params->get('submit_form_show_tab_plugin_params', 0))
    {
        if (!empty($this->plugins))
        {
            echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'plugin_params', JText::_('COM_JUDOWNLOAD_PLUGIN_PARAMS_TAB'));
            echo $this->loadTemplate('plugin_params');
            echo JHtml::_('bootstrap.endTab');
        }
    }
	?>

	<?php
	if ($this->fieldGallery)
	{
		echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'gallery', JText::_('COM_JUDOWNLOAD_GALLERY_TAB'));
		echo $this->fieldGallery->getInput();
		echo JHtml::_('bootstrap.endTab');
	}
	?>

	<?php
    if($this->params->get('submit_form_show_tab_publishing', 0) || $this->params->get('submit_form_show_tab_style', 0)
        || $this->params->get('submit_form_show_tab_meta_data', 0) || $this->params->get('submit_form_show_tab_params', 0)
        || $this->params->get('submit_form_show_tab_permissions', 0))
    {
        echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'others', JText::_('COM_JUDOWNLOAD_OTHERS_TAB'));
        echo $this->loadTemplate('others');
        echo JHtml::_('bootstrap.endTab');
    }
	?>

	<?php echo JHtml::_('bootstrap.endTabset'); ?>
	</div>

	<div>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
	</form>
</div>

<script src="<?php echo JUri::root()?>administrator/components/com_judownload/assets/js/document-fix-editor.js" type="text/javascript"></script>