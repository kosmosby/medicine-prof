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

<?php echo JHtml::_('tabs.start', 'document-tabs-' . $this->item->id, array('useCookie' => 1)); ?>
<?php
if($this->params->get('submit_form_show_tab_publishing', 0))
{
    echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'), 'publishing');
    if ($this->fieldsetPublishing)
    {
        foreach ($this->fieldsetPublishing AS $field)
        {
            //Parse field by JUDL Field
            if (is_object($field))
            {
                //Modified and modified_by need to show together, only when modified_by has value
                if ($field->field_name == "modified" || $field->field_name == "modified_by")
                {
                    if ($this->item->modified_by)
                    {
                        ?>
                        <div class="form-group">
                            <?php echo $field->getLabel(); ?>
                            <div class="col-sm-10">
                                <?php
                                echo $field->getModPrefixText();
                                echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
                                echo $field->getModSuffixText();
                                echo $field->getCountryFlag();
                                ?>
                            </div>
                        </div>
                    <?php
                    }
                }
                //Approved_time and approved_by need to show together, only when approved_by has value
                elseif ($field->field_name == "approved_by" || $field->field_name == "approved_time")
                {
                    if ($this->item->approved_by)
                    {
                        ?>
                        <div class="form-group">
                            <?php echo $field->getLabel(); ?>
                            <div class="col-sm-10">
                                <?php
                                echo $field->getModPrefixText();
                                echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
                                echo $field->getModSuffixText();
                                echo $field->getCountryFlag();
                                ?>
                            </div>
                        </div>
                    <?php
                    }
                }
                else
                {
                    ?>
                    <div class="form-group">
                        <?php echo $field->getLabel(); ?>
                        <div class="col-sm-10">
                            <?php
                            echo $field->getModPrefixText();
                            echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
                            echo $field->getModSuffixText();
                            echo $field->getCountryFlag();
                            ?>
                        </div>
                    </div>
                <?php
                }
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
                        ?>
                        <div class="form-group">
                            <?php echo $_field->label; ?>
                            <div class="col-sm-10">
                                <?php
                                echo $_field->input;
                                ?>
                            </div>
                        </div>
                    <?php
                    }
                }
                //Approved_time and approved_by need to show together, only when approved_by has value
                elseif ($field == "approved_by" || $field == "approved_time")
                {
                    if ($this->item->approved_by)
                    {
                        ?>
                        <div class="form-group">
                            <?php echo $_field->label; ?>
                            <div class="col-sm-10">
                                <?php
                                echo $_field->input;
                                ?>
                            </div>
                        </div>
                    <?php
                    }
                }
                else
                {
                    ?>
                    <div class="form-group">
                        <?php echo $_field->label; ?>
                        <div class="col-sm-10">
                            <?php
                            echo $_field->input;
                            ?>
                        </div>
                    </div>
                <?php
                }
            }
        }
    }
}
?>
<?php
if($this->params->get('submit_form_show_tab_style', 0))
{
    echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_STYLE'), 'style');

    if ($this->fieldsetTemplateStyleAndLayout)
    {
        foreach ($this->fieldsetTemplateStyleAndLayout AS $field)
        {
            // Load field from DB

            if (is_object($field))
            {
                ?>
                <div class="form-group">
                    <?php echo $field->getLabel(); ?>
                    <div class="col-sm-10">
                        <?php
                        echo $field->getModPrefixText();
                        echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
                        echo $field->getModSuffixText();
                        echo $field->getCountryFlag();
                        ?>
                    </div>
                </div>
            <?php
            }
            // Load field as XML joomla field
            else
            {
                $field = $this->form->getField($field);
                ?>
                <div class="form-group">
                    <?php echo $field->label; ?>
                    <div class="col-sm-10">
                        <?php echo $field->input; ?>
                    </div>
                </div>
            <?php
            }
        }
    }

    $templateParams = $this->form->getFieldSet('template_params');
    if ($templateParams)
    {
        echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_TEMPLATE_PARAMS'), 'template-params');
        foreach ($templateParams AS $name => $field)
        {
            ?>
            <div class="form-group">
                <?php echo $field->label; ?>
                <div class="col-sm-10"><?php echo $field->input; ?></div>
            </div>
        <?php
        } ?>
    <?php
    } ?>
<?php
} ?>

<?php
if($this->params->get('submit_form_show_tab_meta_data', 0))
{
    echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_METADATA'), 'metadata');
    foreach ($this->form->getFieldset('metadata') AS $field)
    {
        ?>
        <div class="form-group">
            <?php echo $field->label; ?>
            <div class="col-sm-10"><?php echo $field->input; ?></div>
        </div>
    <?php
    }
}
?>

<?php
if($this->params->get('submit_form_show_tab_params', 0))
{
    echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'), 'params');
    foreach ($this->form->getFieldset('params') AS $field)
    {
        ?>
        <div class="form-group">
            <?php echo $field->label; ?>
            <div class="col-sm-10"><?php echo $field->input; ?></div>
        </div>
    <?php
    }
}
?>
<?php
if($this->params->get('submit_form_show_tab_permissions', 0))
{
    echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS'), 'permissions');
    if ($this->canDo->get('core.admin') || (isset($this->approvalForm) && $this->approvalForm))
    {
        echo JHtml::_('tabs.start', 'document-acl-tab-' . $this->item->id, array('useCookie' => 1));
        echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_DOCUMENT_LABEL'), 'document');
        ?>
        <div class="width-100 fltlft">
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li>
                        <?php echo $this->form->getInput('rules'); ?>
                    </li>
                </ul>
            </fieldset>
        </div>

        <?php
        echo JHtml::_('tabs.panel', JText::_('COM_JUDOWNLOAD_PERMISSION_COMMENT_LABEL'), 'comment');
        ?>
        <div class="width-100 fltlft">
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li>
                        <?php echo $this->form->getInput('comment_permissions'); ?>
                    </li>
                </ul>
            </fieldset>
        </div>
        <div class="clr"></div>
        <?php
        echo JHtml::_('tabs.end');
    }
}

echo JHtml::_('tabs.end');
?>
