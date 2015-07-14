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
$app = JFactory::getApplication();
$parent_cat_id = $app->input->getInt('parent_id', "");

if($this->item->id)
{
	echo $this->loadTemplate('custom_js');
}
else
{
	echo $this->loadTemplate('default_js');
}

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true).'/administrator/components/com_judownload/assets/fix_j25/fix.bootstrap.css');
?>

<div class="jubootstrap">

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate">
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
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('changeFieldGroupAction'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('changeFieldGroupAction'); ?>
					</div>
				</div>
			</div>

			<div id="warningCriteriaGroup">
				<div class="alert alert-block">
					<h4><?php echo JText::_('COM_JUDOWNLOAD_INHERITED_CRITERIA_GROUP_WILL_BE_CHANGED'); ?></h4>
					<div id="criteriaGroupMessage"></div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('changeCriteriaGroupAction'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('changeCriteriaGroupAction'); ?>
					</div>
				</div>
			</div>

			<div id="warningTemplateStyle">
				<div class="alert alert-block">
					<h4><?php echo JText::_('COM_JUDOWNLOAD_INHERITED_TEMPLATE_WILL_BE_CHANGED'); ?></h4>
					<div id="templateStyleMessage"></div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('changeTemplateStyleAction'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('changeTemplateStyleAction'); ?>
					</div>
				</div>
			</div>

		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_JUDOWNLOAD_CLOSE'); ?></button>
			<button id="acceptConfirm" class="btn btn-primary"><?php echo JText::_('COM_JUDOWNLOAD_CONFIRM_AND_SAVE'); ?></button>
		</div>
	</div>

	<div class="width-60 fltlft">
		<?php
		echo JHtml::_('tabs.start', 'category-tabs' . $this->item->id, array('useCookie' => 1));
		echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_EDIT_CATEGORY'), 'main');
		?>
		<fieldset class="adminform">
			<ul class="adminformlist">
				<?php
				foreach ($this->form->getFieldset('details') AS $field)
				{
					if ($field->name == 'jform[selected_criteriagroup]')
					{
						if(JUDownloadHelper::hasMultiRating())
						{
							?>
							<li>
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</li>
						<?php
						}
					}
					else
					{
						?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php
					}
				} ?>
			</ul>
		</fieldset>
		<?php
		if (!empty($this->plugins))
		{
			?>
			<?php echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PLUGIN_PARAMS_TAB'), 'plugin_params'); ?>
			<fieldset class="adminform">
				<div class="width-100 fltlft judl-plugin-params">
					<?php
					echo JHtml::_('tabs.start', 'plugin-params');
					$fieldSets = $this->form->getFieldsets('plugin_params');

					foreach ($this->plugins AS $pluginName => $pluginObj)
					{
						echo JHtml::_('tabs.panel', JText::_($pluginObj['label']), $pluginName . '-plugin-param');

						if ($pluginObj['total_fieldsets'] > 1)
						{
							echo JHtml::_('sliders.start', 'plugin-' . $pluginName);
						}

						foreach ($fieldSets AS $name => $fieldSet)
						{
							if ($fieldSet->plugin_name == $pluginName)
							{
								if ($pluginObj['total_fieldsets'] > 1)
								{
									$label = !empty($fieldSet->label) ? $fieldSet->label : $name;
									echo JHtml::_('sliders.panel', JText::_($label), 'plugin-' . $pluginName . '-' . $fieldSet->name);
								}

								$fields = $this->form->getFieldSet($fieldSet->name);
								if($fields)
								{
									echo "<fieldset class=\"panelform\">";
									$hidden_fields = '';
									echo "<ul class=\"adminformlist\">";
									foreach ($fields AS $field)
									{
										if (!$field->hidden)
										{
											echo "<li>";
											echo $field->label;
											echo $field->input;
											echo "</li>";
										}
										else
										{
											$hidden_fields .= $field->input;
										}
									}
									echo "</ul>";
									echo $hidden_fields;
									echo "</fieldset>";
								}
							}
						}

						if ($pluginObj['total_fieldsets'] > 1)
						{
							echo JHtml::_('sliders.end');
						}
					}

					echo JHtml::_('tabs.end');
					?>
				</div>
			</fieldset>
		<?php
		}

		echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_ORDERING_TAB'), 'field-ordering');
		?>
		<fieldset class="adminform">
			<ul class="adminformlist">
				<?php
				foreach ($this->form->getFieldset('fieldordering') AS $field)
				{
					?>
					<li>
						<?php echo $field->label; ?>
						<?php echo $field->input; ?>
					</li>
				<?php
				} ?>
			</ul>
		</fieldset>
		<?php
		$moderators = $this->model->getModeratorsManageCategory($this->item->id);
		if (count($moderators) > 0)
		{
			echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_MODERATORS_TAB'), 'moderators');
			$user = JFactory::getUser();
			?>
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
		}
		echo JHtml::_('tabs.end');
		?>
		</div>

		<div class="width-40 fltrt">
			<?php echo JHtml::_('sliders.start', 'category-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_GALLERY'), 'gallery'); ?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php
					foreach ($this->form->getFieldset('images') AS $key => $field)
					{
						?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php
					} ?>
				</ul>
			</fieldset>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'), 'publishing'); ?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php
					foreach ($this->form->getFieldset('publishing') AS $key => $field)
					{
						?>
						<li>
							<?php if ($key == "jform_modified" || $key == "jform_modified_by")
							{
								if ($this->item->modified_by)
								{
									echo $field->label;
									echo $field->input;
								}
							}
							else
							{
								echo $field->label;
								echo $field->input;
							} ?>
						</li>
					<?php
					} ?>
				</ul>
			</fieldset>

			<?php
			echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_STYLE'), 'style-layout');
			?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php
					foreach ($this->form->getFieldset('template_style') AS $field)
					{
						?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php
					} ?>
				</ul>
			</fieldset>

			<?php
			echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_PARAMS'), 'template-params');
			?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php
					foreach ($this->form->getGroup('template_params') AS $name => $field)
					{
						?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php
					} ?>
				</ul>
			</fieldset>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_METADATA'), 'metadata'); ?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php
					foreach ($this->form->getFieldset('metadata') AS $field)
					{ ?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php
					} ?>
				</ul>
			</fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'), 'params'); ?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php
					foreach ($this->form->getFieldset('params') AS $field)
					{ ?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php
					} ?>
				</ul>
			</fieldset>
			<?php echo JHtml::_('sliders.end'); ?>
		</div>

		<div class="clr"></div>

		<div class="config-tabs">
			<?php
			echo JHtml::_('tabs.start', 'config-' . $this->item->id, array('useCookie' => 1));
			?>
			<?php if ($this->canDo->get('core.admin')){
				echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS'), 'permissions');
				?>
				<div class="width-100">
					<fieldset class="panelform">
						<ul class="adminformlist">
							<?php
							if($this->item->level == 1){
								echo JHtml::_('tabs.start', 'top-category-acl-tab-' . $this->item->id, array('useCookie' => 1));
								echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_CATEGORY_LABEL'), 'category_category_permissions');
								foreach ($this->form->getFieldset('top_category_category_permissions') AS $field)
								{
									?>
									<li>
										<?php echo $field->input; ?>
									</li>
								<?php
								}
								echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_DOCUMENT_LABEL'), 'category_document_permissions');
								foreach ($this->form->getFieldset('top_category_document_permissions') AS $field)
								{
									?>
									<li>
										<?php echo $field->input; ?>
									</li>
								<?php
								}
								echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_COMMENT_LABEL'), 'category_comment_permissions');
								foreach ($this->form->getFieldset('top_category_comment_permissions') AS $field)
								{
									?>
									<li>
										<?php echo $field->input; ?>
									</li>
								<?php
								}
								echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_SINGLE_RATING_LABEL'), 'category_single_rating_permissions');
								foreach ($this->form->getFieldset('top_category_single_rating_permissions') AS $field)
								{
									?>
									<li>
										<?php echo $field->input; ?>
									</li>
								<?php
								}
								echo JHtml::_('tabs.end');
							}
							else
							{
								echo JHtml::_('tabs.start', 'category-acl-tab-' . $this->item->id, array('useCookie' => 1));
								echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_CATEGORY_LABEL'), 'category_permissions');
								foreach ($this->form->getFieldset('category_permissions') AS $field)
								{
									?>
									<li>
										<?php echo $field->input; ?>
									</li>
								<?php
								}
								echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_DOCUMENT_LABEL'), 'document_permissions');
								foreach ($this->form->getFieldset('document_permissions') AS $field)
								{
									?>
									<li>
										<?php echo $field->input; ?>
									</li>
								<?php
								}
								echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_COMMENT_LABEL'), 'comment_permissions');
								foreach ($this->form->getFieldset('comment_permissions') AS $field)
								{
									?>
									<li>
										<?php echo $field->input; ?>
									</li>
								<?php
								}
								echo JHtml::_('tabs.end');
							}
							?>
						</ul>
					</fieldset>
				</div>
			<?php
			} ?>

			<?php
			echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_GLOBAL_CONFIG'), 'global-config');
			?>

			<div id="judownload-config" class="judownload-config">
				<?php
				$fieldSets = $this->form->getFieldsets('config_params');
				if ($fieldSets)
				{
					echo JHtml::_('tabs.start', 'category-config-' . $this->item->id, array('useCookie' => 1));
					foreach ($fieldSets AS $name => $fieldSet)
					{
						echo JHtml::_('tabs.panel', JText::_($fieldSet->label), 'core_fields');
						?>
						<ul class="adminformlist">
							<?php
							foreach ($this->form->getFieldset($name) AS $field)
							{
								if(!$app->input->getInt('showhidden', 0) && $this->form->getFieldAttribute($field->fieldname, 'hiddenfield', null, $field->group) == 'true')
								{
									$class = 'class="hidden"';
								}
								elseif($this->form->getFieldAttribute($field->fieldname, 'hiddenfield', null, $field->group) == 'true')
								{
									$class = 'class="hiddenfield"';
								}
								else
								{
									$class = '';
								}
								?>
								<li <?php echo $class; ?>>
									<?php if (!in_array($field->type, array('JUFieldset', 'Spacer', 'Hidden')))
									{
										?>
										<?php if (isset($this->item->config_params_db[$field->fieldname]))
										{
											?>
											<input type="checkbox" class="juoverride overridden" checked="checked" />
										<?php
										}
										else
										{
											?>
											<input type="checkbox" class="juoverride" />
										<?php
										} ?>
									<?php
									} ?>
									<?php echo $field->label; ?>
									<?php echo $field->input; ?>
								</li>
							<?php
							} ?>
						</ul>
					<?php
					}
					echo JHtml::_('tabs.end');
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
		</div>

		<?php
		echo JHtml::_('tabs.end');
		?>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="parent_id" value="<?php echo $parent_cat_id; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>