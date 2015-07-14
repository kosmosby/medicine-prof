<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class='width-100 fltlft'>
    <fieldset class="adminform">
        <legend><?php echo JText::_( 'FACTORY_FIELD_BEHAVIOUR' ); ?></legend>
    <table class="paramlist admintable" border="0">
        <tr>
            <td class="paramlist_key" align="right" width="200">
                <?php echo Jtext::_("Compulsory Field"),JHTML::tooltip(JText::_("FACTORY_F_COMPULSORY_HELP")); ?>
            </td>
            <td class="paramlist_value">
                <?php echo $this->lists->get("compulsory"); ?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right">
                <?php echo JText::_("FACTORY_FIELD_VALIDATOR"); ?>:
            </td>
            <td class="paramlist_value">
                <?php echo $this->lists->get("validate_type"); ?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right">
                <?php echo JText::_("FACTORY_SEARCHABLE_FIELD"), JHTML::tooltip(JText::_("FACTORY_F_SEARCH_HELP" ));?>:
            </td>
            <td class="paramlist_value">
                <?php echo $this->lists->get("search"); ?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right" >
                <?php echo JText::_("FACTORY_ORDERING");?>:
            </td>
            <td class="paramlist_value">
                <input type="text" name="ordering" style="width:20px;" value="<?php echo $this->field->ordering; ?>" />
            </td>
        </tr>
    </table>
    </fieldset>
</div>
