<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class='width-100 fltlft'>
    <fieldset class="adminform">
    <legend><?php echo JText::_( 'FACTORY_HTML_INPUT_FORM_DISPLAY_SETTINGS' ); ?></legend>
    <table class="paramlist admintable" border="0">
        <tr>
            <td class="paramlist_key" align="right" width="200">
                <?php echo JText::_("FACTORY_HTML_FIELD_ID"),JHTML::tooltip(JText::_("FACTORY_F_FIELDID_HELP"));?>
            </td>
            <td class="paramlist_value">
                <input type="text" name="field_id" style="width:200px;" readonly="readonly" value="<?php echo $this->field->getHTMLId(); ?>" />
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right">
                <?php echo JText::_("FACTORY_CSS_CLASS");?>:
            </td>
            <td class="paramlist_value">
                <input type="text" name="css_class" style="width:200px;" value="<?php echo $this->field->css_class; ?>" />
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right">
                <?php echo JText::_("FACTORY_INLINE_CSS_STYLE_ATTRIBUTES");?>:
            </td>
            <td class="paramlist_value">
                <textarea name="style_attr" style="width:250px;"><?php echo $this->field->style_attr; ?></textarea>
            </td>
        </tr>
    </table>
    </fieldset>
</div>
