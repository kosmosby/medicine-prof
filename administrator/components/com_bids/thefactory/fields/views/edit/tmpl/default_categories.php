<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class='width-100 fltlft'>
    <fieldset class="adminform">
    <legend><?php echo JText::_( 'FACTORY_CATEGORY_AVAILABILITY' ); ?></legend>
    <div id="categories_overlay" <?php echo $this->category_selection_style;?>>
        <?php echo JText::_("FACTORY_THIS_FIELD_PAGE_HAS_NO_CATEGORY_SELECTION");?>
    </div>
    <table class="paramlist admintable" border="0">
        <tr>
            <td class="paramlist_key" align="right" width="200" valign="top">
                <?php echo JText::_("FACTORY_ENABLE_FIELD_JUST_FOR_SPECIFIC_CATEGORIES");?>:
            </td>
            <td>
                <?php echo JHTML::_("select.booleanlist",'categoryfilter','',$this->field->categoryfilter);?>
            </td>
        </tr>
        <tr>
            <td class="paramlist_key" align="right" valign="top">
                <?php echo JText::_("FACTORY_AVAILABLE_FOR_CATEGORIES");?>
            </td>
            <td>
                   <a href="javascript:CustomFields.selectAllCategories();"><?php echo JText::_("FACTORY_SELECT_ALL");?></a> |
                   <a href="javascript:CustomFields.selectNoneCategories();"><?php echo JText::_("FACTORY_SELECT_NONE");?></a>
                <br />
                <?php echo $this->lists->get("category"); ?>
            </td>
        </tr>
    </table>
    </fieldset>
</div>
