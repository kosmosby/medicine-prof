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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select:not(.browse_cat)');

$app = JFactory::getApplication();
$document = JFactory::getDocument();
$user = JFactory::getUser();

$document->addScript(JUri::root() . "components/com_judownload/assets/js/judl-tabs-state.js");

$parent_cat_id = $app->input->getInt('parent_id', "");

if ($this->item->id)
{
	echo $this->loadTemplate('custom_js');
}
else
{
	echo $this->loadTemplate('default_js');
}

?>

<div id="iframe-help"></div>

<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate form-horizontal">
<div id="confirmModal" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php echo JText::_('COM_JUDOWNLOAD_PARENT_CATEGORY_HAS_BEEN_CHANGED'); ?></h3>
	</div>
	<div class="modal-body">
		<div class="alert alert-block">
			<?php echo JText::_('COM_JUDOWNLOAD_PARENT_CATEGORY_CHANGE_WARNING'); ?>
		</div>

		<div id="warningFieldGroup">
			<div class="alert alert-block">
				<h4><?php echo JText::_('COM_JUDOWNLOAD_INHERITED_FIELD_GROUP_WILL_BE_CHANGED'); ?></h4>

				<div id="fieldGroupMessage"></div>
			</div>
			<?php echo $this->form->getControlGroup('changeFieldGroupAction'); ?>
		</div>

		<div id="warningCriteriaGroup">
			<div class="alert alert-block">
				<h4><?php echo JText::_('COM_JUDOWNLOAD_INHERITED_CRITERIA_GROUP_WILL_BE_CHANGED'); ?></h4>

				<div id="criteriaGroupMessage"></div>
			</div>
			<?php echo $this->form->getControlGroup('changeCriteriaGroupAction'); ?>
		</div>

		<div id="warningTemplateStyle">
			<div class="alert alert-block">
				<h4><?php echo JText::_('COM_JUDOWNLOAD_INHERITED_TEMPLATE_WILL_BE_CHANGED'); ?></h4>

				<div id="templateStyleMessage"></div>
			</div>
			<?php echo $this->form->getControlGroup('changeTemplateStyleAction'); ?>
		</div>

	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal"
		        aria-hidden="true"><?php echo JText::_('COM_JUDOWNLOAD_CLOSE'); ?></button>
		<button id="acceptConfirm"
		        class="btn btn-primary"><?php echo JText::_('COM_JUDOWNLOAD_CONFIRM_AND_SAVE'); ?></button>
	</div>
</div>
<div class="row-fluid">
<div class="span8">
<div class="form-horizontal">
<?php
echo JHtml::_('bootstrap.startTabSet', 'category', array('active' => 'details'));
echo JHtml::_('bootstrap.addTab', 'category', 'details', JText::_('COM_JUDOWNLOAD_EDIT_CATEGORY', true));
?>
<?php
foreach ($this->form->getFieldset('details') AS $field)
{
	if ($field->name == 'jform[selected_criteriagroup]')
	{
		if(JUDownloadHelper::hasMultiRating())
		{
			echo $field->getControlGroup();
		}
	}
	else
	{
		echo $field->getControlGroup();
	}
}

echo JHtml::_('bootstrap.endTab');

if (!empty($this->plugins))
{
	echo JHtml::_('bootstrap.addTab', 'category', 'plugin_params', JText::_('COM_JUDOWNLOAD_PLUGIN_PARAMS_TAB', true));
	?>
	<div class="judl-plugin-params">
		<?php
		$fieldSets = $this->form->getFieldsets('plugin_params');
		$fieldSetsNames = array_keys($fieldSets);
		echo JHtml::_('bootstrap.startTabSet', 'plugin-params', array('active' => $fieldSetsNames[0] . '-plugin-param'));

		foreach ($this->plugins AS $pluginName => $pluginObj)
		{
			echo JHtml::_('bootstrap.addTab', 'plugin-params', $pluginName . '-plugin-param', JText::_($pluginObj['label']));
			if ($pluginObj['total_fieldsets'] > 1)
			{
				echo JHtml::_('bootstrap.startAccordion', 'plugin-' . $pluginName);
			}

			foreach ($fieldSets AS $name => $fieldSet)
			{
				if ($fieldSet->plugin_name == $pluginName)
				{
					if ($pluginObj['total_fieldsets'] > 1)
					{
						echo JHtml::_('bootstrap.addSlide', 'plugin-' . $pluginName, JText::_($fieldSet->label ? $fieldSet->label : strtoupper('COM_JUDOWNLOAD_FIELDSET_' . $fieldSet->name)), 'plugin-' . $pluginName . '-' . $fieldSet->name, 'plugin-' . $pluginName . '-' . $fieldSet->name);
					}

					$fields = $this->form->getFieldSet($fieldSet->name);
					if ($fields)
					{
						foreach ($fields AS $field)
						{
							echo $field->getControlGroup();
						}
					}

					if ($pluginObj['total_fieldsets'] > 1)
					{
						echo JHtml::_('bootstrap.endSlide');
					}
				}
			}

			if ($pluginObj['total_fieldsets'] > 1)
			{
				echo JHtml::_('bootstrap.endAccordion');
			}

			echo JHtml::_('bootstrap.endTab');
		}
		echo JHtml::_('bootstrap.endTabSet');
		?>
	</div>
	<?php echo JHtml::_('bootstrap.endTab');
}

echo JHtml::_('bootstrap.addTab', 'category', 'field-ordering', JText::_('COM_JUDOWNLOAD_FIELD_ORDERING_TAB', true));

foreach ($this->form->getFieldset('fieldordering') AS $field)
{
	echo $field->input;
}

echo JHtml::_('bootstrap.endTab');

$moderators = $this->model->getModeratorsManageCategory($this->item->id);
if (count($moderators) > 0)
{
	?>
	<?php echo JHtml::_('bootstrap.addTab', 'category', 'moderators', JText::_('COM_JUDOWNLOAD_MODERATORS_TAB', true)); ?>

	<div style="overflow:auto; min-height: 300px;">
		<table class="table table-striped">
			<thead>
			<tr>
				<th>
					<?php echo JText::_("COM_JUDOWNLOAD_USER_NAME"); ?>
				</th>
				<th>
					<?php echo JText::_("COM_JUDOWNLOAD_NAME"); ?>
				</th>
				<th>
					<?php echo JText::_("COM_JUDOWNLOAD_USER_ID"); ?>
				</th>
				<th>
					<?php echo JText::_("COM_JUDOWNLOAD_MODERATOR_ID"); ?>
				</th>
				<th>
					<?php echo JText::_("COM_JUDOWNLOAD_STATUS"); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_VIEW_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_VIEW_UNPUBLISHED_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_CREATE_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_EDIT_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_EDIT_DOCUMENT_STATE'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_EDIT_OWN_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_DELETE_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_DELETE_OWN_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_APPROVE_DOCUMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_EDIT_COMMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_EDIT_COMMENT_STATE'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_DELETE_COMMENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_APPROVE_COMMENT'); ?>
				</th>
			</tr>
			</thead>

			<tbody>
			<?php
			foreach ($moderators AS $i => $moderator)
			{
				$canChangeModerator = $user->authorise('judl.moderator.edit.state', 'com_judownload.moderator.' . $moderator->id);
				$canEditModerator   = $user->authorise('judl.moderator.edit', 'com_judownload.moderator.' . $moderator->id);
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $moderator->username; ?>
					</td>
					<td>
						<?php echo $moderator->name; ?>
					</td>
					<td>
						<?php echo $moderator->user_id; ?>
					</td>
					<td>
						<?php echo $moderator->id; ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'published', false); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_view', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_view_unpublished', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_create', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_edit', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_edit_state', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_edit_own', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_delete', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_delete_own', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_download', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'document_approve', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'comment_edit', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'comment_edit_state', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'comment_delete', $canEditModerator); ?>
					</td>
					<td>
						<?php echo $this->model->getModeratorRightSelectOption($moderator, 'comment_approve', $canEditModerator); ?>
					</td>
				</tr>
			<?php
			} ?>
			</tbody>
		</table>
	</div>
	<?php
	echo JHtml::_('bootstrap.endTab');
}

if ($this->canDo->get('core.admin'))
{
	echo JHtml::_('bootstrap.addTab', 'category', 'permissions', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS', true));
	if ($this->item->level == 1)
	{
		echo JHtml::_('bootstrap.startTabSet', 'top-category-permission', array('active' => 'category_category_permissions'));
		echo JHtml::_('bootstrap.addTab', 'top-category-permission', 'category_category_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_CATEGORY_LABEL', true));
		foreach ($this->form->getFieldset('top_category_category_permissions') AS $field)
		{
			echo $field->input;
		}
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'top-category-permission', 'category_document_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_DOCUMENT_LABEL', true));
		foreach ($this->form->getFieldset('top_category_document_permissions') AS $field)
		{
			echo $field->input;
		}
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'top-category-permission', 'category_comment_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_COMMENT_LABEL', true));
		foreach ($this->form->getFieldset('top_category_comment_permissions') AS $field)
		{
			echo $field->input;
		}
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'top-category-permission', 'category_single_rating_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_SINGLE_RATING_LABEL', true));
		foreach ($this->form->getFieldset('top_category_single_rating_permissions') AS $field)
		{
			echo $field->input;
		}
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.endTabSet');
	}
	else
	{
		echo JHtml::_('bootstrap.startTabSet', 'category-permission', array('active' => 'category_permissions'));
		echo JHtml::_('bootstrap.addTab', 'category-permission', 'category_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_CATEGORY_LABEL', true));
		foreach ($this->form->getFieldset('category_permissions') AS $field)
		{
			echo $field->input;
		}
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'category-permission', 'document_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_DOCUMENT_LABEL', true));
		foreach ($this->form->getFieldset('document_permissions') AS $field)
		{
			echo $field->input;
		}
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'category-permission', 'comment_permissions', JText::_('COM_JUDOWNLOAD_PERMISSION_COMMENT_LABEL', true));
		foreach ($this->form->getFieldset('comment_permissions') AS $field)
		{
			echo $field->input;
		}
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.endTabSet');
	}
	echo JHtml::_('bootstrap.endTab');
}

echo JHtml::_('bootstrap.addTab', 'category', 'configs', JText::_('COM_JUDOWNLOAD_GLOBAL_CONFIG', true));
?>
<div id="judownload-config" class="judownload-config joomla3x">
	<?php
	$fieldSets = $this->form->getFieldsets('config_params');
	if ($fieldSets)
	{
		echo JHtml::_('bootstrap.startTabSet', 'category-config-' . $this->item->id, array('active' => 'config-general'));
		foreach ($fieldSets AS $name => $fieldSet)
		{
			echo JHtml::_('bootstrap.addTab', 'category-config-' . $this->item->id, $fieldSet->name, JText::_($fieldSet->label));

			$fields = $this->form->getFieldset($fieldSet->name);
			foreach ($fields AS $field)
			{
				if (!$app->input->getInt('showhidden', 0) && $this->form->getFieldAttribute($field->fieldname, 'hiddenfield', null, $field->group) == 'true')
				{
					$class = 'class="hidden"';
				}
				elseif ($this->form->getFieldAttribute($field->fieldname, 'hiddenfield', null, $field->group) == 'true')
				{
					$class = 'hiddenfield';
				}
				else
				{
					$class = '';
				}

				?>
				<div class="control-group <?php echo $class; ?>">
					<div class="control-label">
						<?php
						if (!in_array($field->type, array('JUFieldset', 'Spacer', 'Hidden')))
						{
							if (isset($this->item->config_params_db[$field->fieldname]))
							{
								?>
								<input type="checkbox" class="juoverride overridden" checked="checked"/>
							<?php
							}
							else
							{
								?>
								<input type="checkbox" class="juoverride"/>
							<?php
							}
						}
						?>
						<div style="display: inline-block">
							<?php echo $field->label; ?>
						</div>

					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php
			}
			echo JHtml::_('bootstrap.endTab');
		}

		echo JHtml::_('bootstrap.endTabSet');
	}
	else
	{
		echo '<div class="alert alert-success">';
		echo '<p>This section helps to override global config to create multi download sections.</p>';
		echo '<p>Please upgrade to <a href="http://www.joomultra.com/ju-download-comparison.html">Pro Version</a> to use this feature</p>';
		echo '</div>';
	}
	?>
</div>
<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.endTabSet');
?>
</div>
</div>

<div class="span4">
	<?php
	echo JHtml::_('bootstrap.startAccordion', 'category-sliders-' . $this->item->id, array('active' => 'gallery'));
	echo JHtml::_('bootstrap.addSlide', 'category-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_GALLERY'), 'gallery', 'gallery');
	foreach ($this->form->getFieldset('images') AS $key => $field)
	{
		echo $field->getControlGroup();
	}
	echo JHtml::_('bootstrap.endSlide');

	echo JHtml::_('bootstrap.addSlide', 'category-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'), 'publishing', 'publishing');
	foreach ($this->form->getFieldset('publishing') AS $key => $field)
	{
		if ($key == "jform_modified" || $key == "jform_modified_by")
		{
			if ($this->item->modified_by)
			{
				echo $field->getControlGroup();
			}
		}
		else
		{
			echo $field->getControlGroup();
		}
	}
	echo JHtml::_('bootstrap.endSlide');

	echo JHtml::_('bootstrap.addSlide', 'category-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_STYLE'), 'style-layout', 'style-layout');
	foreach ($this->form->getFieldset('template_style') AS $field)
	{
		echo $field->getControlGroup();
	}
	echo JHtml::_('bootstrap.endSlide');

	$fields = $this->form->getFieldset('template_params');
	if ($fields)
	{
		echo JHtml::_('bootstrap.addSlide', 'category-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_PARAMS'), 'template-params', 'template-params');
		foreach ($fields AS $name => $field)
		{
			echo $field->getControlGroup();
		}
		echo JHtml::_('bootstrap.endSlide');
	}

	echo JHtml::_('bootstrap.addSlide', 'category-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_METADATA'), 'metadata', 'metadata');
	foreach ($this->form->getFieldset('metadata') AS $field)
	{
		echo $field->getControlGroup();
	}
	echo JHtml::_('bootstrap.endSlide');

	echo JHtml::_('bootstrap.addSlide', 'category-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'), 'params', 'params');
	foreach ($this->form->getFieldset('params') AS $field)
	{
		echo $field->getControlGroup();
	}
	echo JHtml::_('bootstrap.endSlide');
	echo JHtml::_('bootstrap.endAccordion');
	?>
</div>
</div>

<div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="parent_id" value="<?php echo $parent_cat_id; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
