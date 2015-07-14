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

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true).'/administrator/components/com_judownload/assets/fix_j25/fix.bootstrap.css');
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

<div class="jubootstrap">

<div id="iframe-help"></div>

<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-validate">
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

<div class="width-60 fltlft">
	<?php
	echo JHtml::_('tabs.start', 'document-' . $this->item->id, array('useCookie' => 1));
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_CORE_FIELDS_TAB'), 'main');
	echo $this->loadTemplate('main');
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_EXTRA_FIELDS_TAB'), 'fields');
	echo $this->loadTemplate('fields');
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FILES_TAB'), 'files');
	echo $this->loadTemplate('files');
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_CHANGELOGS_TAB'), 'changelogs');
	echo $this->loadTemplate('changelogs');
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_VERSIONS_TAB'), 'versions');
	echo $this->loadTemplate('versions');
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_RELATED_DOCUMENTS_TAB'), 'related-documents');
	echo $this->loadTemplate('rel_documents');
	if (!empty($this->plugins))
	{
		echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PLUGIN_PARAMS_TAB'), 'plugin_params');
		echo $this->loadTemplate('plugin_params');
	}
	echo JHtml::_('tabs.end');
	?>
</div>

<div class="width-40 fltrt">
	<?php echo $this->loadTemplate('gallery'); ?>
	<?php echo JHtml::_('sliders.start', 'document-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
	<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'), 'publishing'); ?>
	<fieldset class="adminform">
		<ul class="adminformlist">
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

					echo "<li>";
					
					if (is_object($field))
					{
						
						if ($field->field_name == "modified" || $field->field_name == "modified_by")
						{
							if ($this->item->modified_by)
							{
								echo $field->getLabel();
								echo $field->getModPrefixText();
								echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
								echo $field->getModSuffixText();
								echo $field->getCountryFlag();
							}
						}
						
						elseif ($field->field_name == "approved_by" || $field->field_name == "approved_time")
						{
							if ($this->item->approved_by)
							{
								echo $field->getLabel();
								echo $field->getModPrefixText();
								echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
								echo $field->getModSuffixText();
								echo $field->getCountryFlag();
							}
						}
						else
						{
							echo $field->getLabel();
							echo $field->getModPrefixText();
							echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
							echo $field->getModSuffixText();
							echo $field->getCountryFlag();
						}
					}
					
					else
					{
						$_field = $this->form->getField($field);
						
						if ($field == "modified" || $field == "modified_by")
						{
							if ($this->item->modified_by)
							{
								echo $_field->label;
								echo $_field->input;
							}
						}
						
						elseif ($field == "approved_by" || $field == "approved_time")
						{
							if ($this->item->approved_by)
							{
								echo $_field->label;
								echo $_field->input;
							}
						}
						else
						{
							echo $_field->label;
							echo $_field->input;

						}
					}
					echo "</li>";
				}
			}
			?>
		</ul>
	</fieldset>
	<?php
	echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_STYLE'), 'style-layout');
	?>
	<fieldset class="adminform">
		<ul class="adminformlist">
			<?php
			if ($this->fieldsetTemplateStyleAndLayout)
			{
				foreach ($this->fieldsetTemplateStyleAndLayout AS $field)
				{
					
					echo "<li>";
					if (is_object($field))
					{
						echo $field->getLabel();
						echo $field->getModPrefixText();
						echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
						echo $field->getModSuffixText();
						echo $field->getCountryFlag();
					}
					
					else
					{
						$field = $this->form->getField($field);
						echo $field->label;
						echo $field->input;
					}
					echo "</li>";
				}
			}
			?>
		</ul>
	</fieldset>

	<?php
	$fields = $this->form->getFieldSet('template_params');
	if ($fields)
	{
		echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_PARAMS'), 'template-params');
		?>
		<fieldset class="adminform">
			<ul class="adminformlist">
				<?php
				foreach ($fields AS $name => $field) : ?>
					<li>
						<?php echo $field->label; ?>
						<?php echo $field->input; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php
	} ?>

	<?php
	echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_METADATA'), 'metadata');
	?>
	<fieldset class="adminform">
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('metadata') AS $field): ?>
				<li>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php
	echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_NOTES'), 'notes');
	?>
	<fieldset class="adminform">
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('notes') AS $field): ?>
				<li>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php
	echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'), 'params');
	?>
	<fieldset class="adminform">
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('params') AS $field): ?>
				<li>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php echo JHtml::_('sliders.end'); ?>
</div>

<div class="clr"></div>

<?php if ($this->canDo->get('core.admin')): ?>
	<?php
	echo JHtml::_('tabs.start', 'document-acl-tab-' . $this->item->id, array('useCookie' => 1));
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_DOCUMENT_LABEL'), 'document');
	?>
	<div class="width-100 fltlft">
		<?php echo JHtml::_('sliders.start', 'document-permissions-sliders-document' . $this->item->id, array('useCookie' => 1)); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS'), 'permissions-document'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getInput('rules'); ?>
				</li>
			</ul>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>
	<div class="clr"></div>
	<?php
	echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_COMMENT_LABEL'), 'comment');
	?>
	<div class="width-100 fltlft">
		<?php echo JHtml::_('sliders.start', 'document-permissions-sliders-comment' . $this->item->id, array('useCookie' => 1)); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS'), 'permissions-comment'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getInput('comment_permissions'); ?>
				</li>
			</ul>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>
	<div class="clr"></div>
	<?php
	echo JHtml::_('tabs.end');
	?>
<?php endif; ?>

<div class="clr"></div>

<div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
</div>
