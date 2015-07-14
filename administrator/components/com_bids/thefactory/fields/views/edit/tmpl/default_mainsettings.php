<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class='width-100 fltlft'>
    <fieldset class="adminform">
    <legend><?php echo JText::_( 'FACTORY_MAIN_SETTINGS' ); ?></legend>
    <table class="paramlist admintable" border="0" width="100%">
        <tr>
            <td class="paramlist_key" align="right" width="250">
                <?php echo JText::_("FACTORY_FIELD_LABEL"),":"; ?>
            </td>
            <td class="paramlist_value">
                <input type="text" name="name" style="width:200px;" value="<?php echo $this->field->name; ?>" />
                <?php echo $this->img_required;?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right" >
                <?php echo JText::_("FACTORY_DATABASE_FIELD_NAME"),":",JHTML::tooltip(JText::_("FACTORY_F_FIELDDBNAME_HELP")); ?>
            </td>
            <td class="paramlist_value">
                <input type="text" name="db_name" style="width:200px;" value="<?php echo $this->field->db_name; ?>" <?php if($this->field->id){ ?> readonly="readonly" disabled="disabled" <?php } ?> />
                <?php echo $this->img_required;?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right">
                <?php echo JText::_("FACTORY_FIELD_TYPE");?>:
            </td>
            <td class="paramlist_value">
                <?php echo $this->lists->get("field_types_html"); ?>
                <?php echo $this->img_required;?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right">
                <?php echo JText::_("FACTORY_FIELD_SECTION");?>:
            </td>
            <td class="paramlist_value">
                 <?php echo $this->lists->get("field_pages"); ?>
                 <?php echo $this->img_required;?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right" >
                <?php echo JText::_("FACTORY_FIELD_DESCRIPTION_OR_HELP_TEXT_FOR_USERS");?>:
            </td>
            <td class="paramlist_value">
                <textarea name="help" style="width:250px;" ><?php echo $this->field->help; ?></textarea>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right">
                <?php echo JText::_("FACTORY_FIELD_ENABLED"),JHTML::tooltip(JText::_("FACTORY_F_ENABLED_HELP"));?>:
            </td>
            <td class="paramlist_value">
                <?php echo $this->lists->get("status"); ?>
            </td>
        </tr>
    </table>
    </fieldset>
</div>
