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
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task)
    {
		if (task == 'csvprocess.back' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};

	jQuery(document).ready(function ($)
    {
		$("#jformcsv_cat_filter").change(function () {
			var catids = $(this).val().join(",");
			var search_sub_categories = 0;
			if ($("#sub_cat").prop('checked') == true) {
				search_sub_categories = 1;
			}
		});
	});

    jQuery(document).ready(function($)
    {
        $('input[name="checkall-toggle"]').click(function(){
            var c = $(this).attr('sub-checkbox');
            if($(this).prop('checked')==false){
                $('.'+c).prop('checked', false);
            }else{
                $('.'+c).prop('checked', true);
            }
        });
    });
</script>

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="#" method="post" name="adminForm" id="adminForm" class="form-validate row-fluid" enctype="multipart/form-data">

		<div class="span6">
			<fieldset class="adminform">
				<legend><?php echo JText::_("COM_JUDOWNLOAD_CSV_EXPORT_SETTINGS"); ?></legend>
				<ul class="adminformlist">
					<?php
					foreach ($this->exportForm->getFieldset('details') AS $field)
					{
						echo "<li>";
						echo $field->label;
						echo $field->input;
						echo "</li>";
					}
					?>
				</ul>
			</fieldset>
		</div>

		<div class="span6">
            <legend><?php echo JText::_("COM_JUDOWNLOAD_FIELDS_TO_EXPORT"); ?></legend>
            <?php
            $coreFieldsHaveFieldClass = $this->model->getFieldsHaveFieldClass('core');
			echo JHtml::_('tabs.start', 'csv-export', array('useCookie' => 1));
            ?>

            <?php
            
            if (isset($coreFieldsHaveFieldClass))
            {
	            echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_CORE_FIELDS_TAB'), 'core-fields');
            ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="1%">
                                <input type="checkbox" sub-checkbox="core_field" id="core_field_toggle" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" checked/>
                            </th>
                            <th class="center">
                                <?php echo JText::_('COM_JUDOWNLOAD_FIELD_NAME'); ?>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $index = 0;
                        foreach ($coreFieldsHaveFieldClass AS $key => $field)
                        {
                            ?>
                            <tr>
                                <td class="center">
                                    <input type="checkbox" class="core_field" checked onclick="Joomla.isChecked(this.checked);" value="<?php echo $field->id; ?>"  name="col[]" id="cb<?php echo $key; ?>" />
                                </td>
                                <td class="center">
                                    <label for="cb<?php echo $key; ?>"><?php echo ucfirst(str_replace('_', ' ', $field->caption)); ?></label>
                                </td>
                            </tr>
                        <?php
                            $index = $key;
                        }

                        $coreFieldsHaveNoFieldClass = $this->model->getDocumentTableFieldsName($coreFieldsHaveFieldClass);
                        if (!empty($coreFieldsHaveNoFieldClass))
                        {
                            foreach ($coreFieldsHaveNoFieldClass AS $field)
                            { ?>
                                <tr>
                                    <td class="center">
                                        <input type="checkbox" class="core_field" checked onclick="Joomla.isChecked(this.checked);" value="<?php echo $field; ?>"  name="col[]" id="cb<?php echo $index; ?>" />
                                    </td>
                                    <td class="center"><label for="cb<?php $index++; ?>"><?php echo ucfirst(str_replace('_', ' ', $field)); ?></label></td>
                                </tr>
                        <?php
                            }
                        } ?>

                        <tr>
                            <td class="center">
                                <input type="checkbox" class="core_field" checked onclick="Joomla.isChecked(this.checked);" value="main_cat"  name="col[]" id="cb<?php echo $index; ?>" />
                            </td>
                            <td class="center">
                                <label for="cb<?php echo $index++; ?>"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_MAIN_CAT'); ?></label>
                            </td>
                        </tr>

                        <tr>
                            <td class="center">
                                <input type="checkbox" class="core_field" checked onclick="Joomla.isChecked(this.checked);" value="gallery"  name="col[]" id="cb<?php echo $index; ?>" />
                            </td>
                            <td class="center">
                                <label for="cb<?php echo $index++; ?>"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_GALLERY'); ?></label>
                            </td>
                        </tr>

                        <tr>
                            <td class="center">
                                <input type="checkbox" class="core_field" checked onclick="Joomla.isChecked(this.checked);" value="secondary_cats"  name="col[]" id="cb<?php echo $index; ?>" />
                            </td>
                            <td class="center">
                                <label for="cb<?php echo $index++; ?>"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_SECONDARY_CATS'); ?></label>
                            </td>
                        </tr>

                        <tr>
                            <td class="center">
                                <input type="checkbox" class="core_field" checked onclick="Joomla.isChecked(this.checked);" value="related_docs"  name="col[]" id="cb<?php echo $index; ?>" />
                            </td>
                            <td class="center">
                                <label for="cb<?php echo $index++; ?>"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_RELATED_DOCUMENTS'); ?></label>
                            </td>
                        </tr>

                        <tr>
                            <td class="center">
                                <input type="checkbox" class="core_field" checked onclick="Joomla.isChecked(this.checked);" value="files"  name="col[]" id="cb<?php echo $index; ?>" />
                            </td>
                            <td class="center">
                                <label for="cb<?php echo $index++; ?>"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILES'); ?></label>
                            </td>
                        </tr>

                    </tbody>
                </table>
            <?php
            }
            ?>

            <?php
                $extraFields = $this->model->getFieldsHaveFieldClass('extra');

                $fieldGroups = array();

                foreach ($extraFields AS $field)
                {
                    if (isset($fieldGroups[$field->group_id]))
                    {
                        $fieldGroups[$field->group_id][] = $field;
                    }
                    else
                    {
                        $fieldGroups[$field->group_id] = array($field);
                    }
                }

                if (!empty($fieldGroups))
                {
                    foreach ($fieldGroups AS $groupId => $fields)
                    {
                        
                        $group = JUDownloadFrontHelperField::getFieldGroupById($groupId);
	                    echo JHtml::_('tabs.panel', $group->name, "fieldgroup-$group->id");
                    ?>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th width="1%">
                            <input type="checkbox" id="<?php echo $group->id; ?>" sub-checkbox="group_<?php echo $group->id; ?>" class="field"
                                   title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" />
                        </th>
                        <th class="center">
                            <?php echo JText::_('COM_JUDOWNLOAD_FIELD_NAME'); ?>
                        </th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                    foreach ($fields AS $field)
                    { ?>
                        <tr>
                            <td class="center">
                                <input type="checkbox" id="<?php echo $field->id; ?>" class="group_<?php echo $field->group_id; ?> field" onclick="Joomla.isChecked(this.checked);" value="<?php echo $field->id; ?>"  name="col[]" /></li>
                            </td>
                            <td class="center">
                                <label for="<?php echo $field->id; ?>"><?php echo ucfirst($field->caption); ?></label>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
                <?php
                    }
                }
            ?>

            <?php
            echo JHtml::_('tabs.end');
            ?>
		</div>

		<div class="clr clearfix"></div>

		<div>
			<input type="hidden" name="task" value="csvprocess.csvexport" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>