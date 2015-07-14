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
			var message = '<?php echo JText::_('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_LEAVE_THIS_PAGE_ALL_DATA_YOU_ENTERED_WILL_BE_LOST'); ?>'; 
			if (!e) e = window.event;
			
			e.cancelBubble = true;
			e.returnValue = message;
			
			if (e.stopPropagation) {
				e.stopPropagation();
				e.preventDefault();
			}
			return message;
		}
	});

	Joomla.submitbutton = function (task) {
		buttonClicked = true;
		if (task == 'document.cancel' || task == 'pendingdocument.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<!-- New file uploaded template -->
<script id="file-template" type="text/x-handlebars-template">
	<li>
		<div class="file-item">
			<span class="move"><i class="icon-menu"></i></span>

			<div class="file-actions file-row">
				<span class="publish btn btn-mini" data-iconpublish="icon-publish" data-iconunpublish="icon-unpublish">
					<i class="icon-publish"></i><?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
				</span>
				<input type="hidden" name="judlfiles[{{key}}][published]" class="file-published-value" value="1" />

				<span class="remove btn btn-mini" data-iconremove="icon-trash" data-iconunremove="icon-undo">
					<i class="icon-trash"></i><?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?>
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
			<span class="move"><i class="icon-menu"></i></span>
			<div class="row-fluid">
				<div class="changelog-version span4">
					<input type="text" size="24" class="input-medium" name="changelogs[{{key}}][version]" value="" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FIELD_VERSION'); ?>" /></div>
				<div class="changelog-date span5">
					<div class="input-append" >
						<input type="text" title="" class="input-medium" name="changelogs[{{key}}][date]" id="{{calenderid}}" value="{{nowdate}}" size="25" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DATE'); ?>" />
						<span class="add-on icon-calendar" id="{{calenderid}}_img"></span>
					</div>
				</div>
				<div class="changelog-actions span3">
					<span class="unpublish btn btn-mini" data-iconunpublish="icon-unpublish" data-iconpublish="icon-publish">
						<i class="icon-publish"></i><?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
					</span>
					<input type="hidden" class="changelog-published-value" value="1" name="changelogs[{{key}}][published]" />

					<span class="btn btn-mini remove" data-iconunremove="icon-undo" data-iconremove="icon-trash">
						<i class="icon-trash"></i><?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?>
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

<div id="iframe-help"></div>

<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
<div id="alertChangeCategory" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="alertChangeCategoryLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="alertChangeCategoryLabel"><?php echo JText::_('COM_JUDOWNLOAD_CHANGE_MAIN_CATEGORY_WARNING'); ?></h3>
	</div>
	<div class="modal-body">
		<div id="messageChangeFieldGroup" class="alert alert-warning"></div>
		<div id="messageChangeTemplate" class="alert alert-warning"></div>
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
<div class="span8">
	<div class="form-horizontal">

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
		echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'files', JText::_('COM_JUDOWNLOAD_FILES_TAB'));
		echo $this->loadTemplate('files');
		echo JHtml::_('bootstrap.endTab');
		?>

		<?php
		echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'changelogs', JText::_('COM_JUDOWNLOAD_CHANGELOGS_TAB'));
		echo $this->loadTemplate('changelogs');
		echo JHtml::_('bootstrap.endTab');
		?>

		<?php
		echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'versions', JText::_('COM_JUDOWNLOAD_VERSIONS_TAB'));
		echo $this->loadTemplate('versions');
		echo JHtml::_('bootstrap.endTab');
		?>

		<?php
		echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'related-documents', JText::_('COM_JUDOWNLOAD_RELATED_DOCUMENTS_TAB'));
		echo $this->loadTemplate('rel_documents');
		echo JHtml::_('bootstrap.endTab');
		?>

		<?php
		if (!empty($this->plugins))
		{
			echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'plugin_params', JText::_('COM_JUDOWNLOAD_PLUGIN_PARAMS_TAB'));
			echo $this->loadTemplate('plugin_params');
			echo JHtml::_('bootstrap.endTab');
		} ?>

		<?php
		if ($this->app->isAdmin())
		{
			if ($this->canDo->get('core.admin'))
			{
				echo JHtml::_('bootstrap.addTab', 'document-' . $this->item->id, 'permissions', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS'));
				echo JHtml::_('bootstrap.startTabSet', 'document-permission', array('active' => 'document_permissions'));

				echo JHtml::_('bootstrap.addTab', 'document-permission', 'document_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_DOCUMENT_LABEL', true));
				foreach ($this->form->getFieldset('document_permissions') AS $field)
				{
					echo $field->input;
				}
				echo JHtml::_('bootstrap.endTab');
				echo JHtml::_('bootstrap.addTab', 'document-permission', 'comment_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_COMMENT_LABEL', true));
				foreach ($this->form->getFieldset('comment_permissions') AS $field)
				{
					echo $field->input;
				}
				echo JHtml::_('bootstrap.endTab');
				echo JHtml::_('bootstrap.endTabSet');
				echo JHtml::_('bootstrap.endTab');
			}
		}
		?>
		<?php echo JHtml::_('bootstrap.endTabset'); ?>
	</div>
</div>

<div class="span4">
	<?php echo $this->loadTemplate('gallery'); ?>
	<?php echo JHtml::_('bootstrap.startAccordion', 'document-sliders-' . $this->item->id, array('active' => 'publishing')); ?>

	<?php echo JHtml::_('bootstrap.addSlide', 'document-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'), 'publishing', 'publishing'); ?>
	<?php
	if ($this->fieldsetPublishing)
	{
		foreach ($this->fieldsetPublishing AS $field)
		{
			if(!JUDLPROVERSION)
			{
				if (is_object($field))
				{
					if ($field->field_name == "approved" || $field->field_name == "approved_by" || $field->field_name == "approved_time")
					{
						continue;
					}
				}
				else
				{
					if ($field == "approved" || $field == "approved_by" || $field == "approved_time")
					{
						continue;
					}
				}
			}
			
			
			if (is_object($field))
			{
				echo '<div class="control-group ">';
				
				if ($field->field_name == "modified" || $field->field_name == "modified_by")
				{
					if ($this->item->modified_by)
					{
						echo '<div class="control-label">';
						echo $field->getLabel();
						echo '</div>';
						echo '<div class="controls">';
						echo $field->getModPrefixText();
						echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
						echo $field->getModSuffixText();
						echo $field->getCountryFlag();
						echo '</div>';
					}
				}
				
				elseif ($field->field_name == "approved_by" || $field->field_name == "approved_time")
				{
					if ($this->item->approved_by)
					{
						echo '<div class="control-label">';
						echo $field->getLabel();
						echo "</div>";
						echo '<div class="controls">';
						echo $field->getModPrefixText();
						echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
						echo $field->getModSuffixText();
						echo $field->getCountryFlag();
						echo '</div>';
					}
				}
				else
				{
					echo '<div class="control-label">';
					echo $field->getLabel();
					echo "</div>";
					echo '<div class="controls">';
					echo $field->getModPrefixText();
					echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
					echo $field->getModSuffixText();
					echo $field->getCountryFlag();
					echo '</div>';
				}
				echo "</div>";
			}
			
			else
			{
				$_field = $this->form->getField($field);
				
				if ($field == "modified" || $field == "modified_by")
				{
					if ($this->item->modified_by)
					{
						echo $_field->getControlGroup();
					}
				}
				
				elseif ($field == "approved_by" || $field == "approved_time")
				{
					if ($this->item->approved_by)
					{
						echo $_field->getControlGroup();
					}
				}
				else
				{
					echo $_field->getControlGroup();
				}
			}
		}
	}
	?>

	<?php echo JHtml::_('bootstrap.endSlide'); ?>

	<?php echo JHtml::_('bootstrap.addSlide', 'document-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_STYLE'), 'style-layout', 'style-layout'); ?>
	<?php
	if ($this->fieldsetTemplateStyleAndLayout)
	{
		foreach ($this->fieldsetTemplateStyleAndLayout AS $field)
		{
			
			echo '<div class="control-group ">';
			if (is_object($field))
			{
				echo '<div class="control-label">';
				echo $field->getLabel();
				echo '</div>';
				echo '<div class="controls">';
				echo $field->getModPrefixText();
				echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
				echo $field->getModSuffixText();
				echo $field->getCountryFlag();
				echo '</div>';
			}
			
			else
			{
				$field = $this->form->getField($field);
				echo $field->getControlGroup();
			}
			echo '</div>';
		}
	}
	?>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>

	<?php
	$fields = $this->form->getFieldSet('template_params');
	if ($fields)
	{
		echo JHtml::_('bootstrap.addSlide', 'document-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_PARAMS'), 'template-params', 'template-params');
		foreach ($fields AS $name => $field) :
			echo $field->getControlGroup();
		endforeach;
		echo JHtml::_('bootstrap.endSlide');
	} ?>

	<?php
	echo JHtml::_('bootstrap.addSlide', 'document-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_METADATA'), 'metadata', 'metadata');
	foreach ($this->form->getFieldset('metadata') AS $field):
		echo $field->getControlGroup();
	endforeach;
	echo JHtml::_('bootstrap.endSlide');
	?>

	<?php
	echo JHtml::_('bootstrap.addSlide', 'document-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_NOTES'), 'notes', 'notes');
	foreach ($this->form->getFieldset('notes') AS $field):
		echo $field->getControlGroup();
	endforeach;
	echo JHtml::_('bootstrap.endSlide');
	?>

	<?php
	echo JHtml::_('bootstrap.addSlide', 'document-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'), 'params', 'params');
	foreach ($this->form->getFieldset('params') AS $field):
		echo $field->getControlGroup();
	endforeach;
	echo JHtml::_('bootstrap.endSlide');
	?>

	<?php echo JHtml::_('bootstrap.endAccordion'); ?>
</div>
</div>

<div>
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>

<script src="<?php echo JUri::root()?>administrator/components/com_judownload/assets/js/document-fix-editor.js" type="text/javascript"></script>