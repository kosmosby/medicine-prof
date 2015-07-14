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

<?php echo JHtml::_('bootstrap.startTabSet', 'document-other-' . $this->item->id, array('active' => 'publishing')); ?>

<?php
if($this->params->get('submit_form_show_tab_publishing', 0))
{
    echo JHtml::_('bootstrap.addTab', 'document-other-' . $this->item->id, 'publishing', JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'));
    if ($this->fieldsetPublishing)
    {
        foreach ($this->fieldsetPublishing AS $field)
        {
            //Parse field by JUDL Field
            if (is_object($field))
            {
                echo '<div class="control-group ">';
                //Modified and modified_by need to show together, only when modified_by has value
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
                //Approved_time and approved_by need to show together, only when approved_by has value
                elseif ($field->field_name == "approved_by" || $field->field_name == "approved_time")
                {
                    if ($this->item->approved_by)
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
                else
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
                echo "</div>";
            }
            //Parse field by Joomla
            else
            {
                $_field = $this->form->getField($field);
                //Modified and modified_by need to show together, only when modified_by has value
                if ($field == "modified" || $field == "modified_by")
                {
                    if ($this->item->modified_by)
                    {
                        echo $_field->getControlGroup();
                    }
                }
                //Approved_time and approved_by need to show together, only when approved_by has value
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
    echo JHtml::_('bootstrap.endTab');
}
?>

<?php
if($this->params->get('submit_form_show_tab_style', 0))
{
    echo JHtml::_('bootstrap.addTab', 'document-other-' . $this->item->id, 'style-layout', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_STYLE'));
    if ($this->fieldsetTemplateStyleAndLayout)
    {
        foreach ($this->fieldsetTemplateStyleAndLayout AS $field)
        {
            // Load field from DB
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
            // Load field as XML joomla field
            else
            {
                $field = $this->form->getField($field);
                echo $field->getControlGroup();
            }
            echo '</div>';
        }
    }
    echo JHtml::_('bootstrap.endTab');

    $fields = $this->form->getFieldSet('template_params');
    if ($fields)
    {
        echo JHtml::_('bootstrap.addTab', 'document-other-' . $this->item->id, 'template-params', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_PARAMS'));
        foreach ($fields AS $name => $field)
        {
            echo $field->getControlGroup();
        }
        echo JHtml::_('bootstrap.endTab');
    }
}
?>

<?php
if($this->params->get('submit_form_show_tab_meta_data', 0))
{
    echo JHtml::_('bootstrap.addTab', 'document-other-' . $this->item->id, 'metadata', JText::_('COM_JUDOWNLOAD_FIELD_SET_METADATA'));
    foreach ($this->form->getFieldset('metadata') AS $field)
    {
        echo $field->getControlGroup();
    }
    echo JHtml::_('bootstrap.endTab');
}
?>

<?php
if($this->params->get('submit_form_show_tab_params', 0))
{
    echo JHtml::_('bootstrap.addTab', 'document-other-' . $this->item->id, 'params', JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'));
    foreach ($this->form->getFieldset('params') AS $field)
    {
        echo $field->getControlGroup();
    }
    echo JHtml::_('bootstrap.endTab');
}
?>

<?php
if($this->params->get('submit_form_show_tab_permissions', 0))
{
    if ($this->canDo->get('core.admin') || (isset($this->approvalForm) && $this->approvalForm))
    {
        echo JHtml::_('bootstrap.addTab', 'document-other-' . $this->item->id, 'permissions', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS'));
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