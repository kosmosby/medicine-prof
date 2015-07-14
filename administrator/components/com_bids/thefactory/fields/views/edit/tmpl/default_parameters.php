<?php defined('_JEXEC') or die('Restricted access'); ?>
<div id="cfield_parameters">
<?php
   echo JHtml::_('customfields.fieldtype_params',$this->lists->get("curent_type_params"),$this->field->params);
?>
</div>
<div id="cfield_option_list" class='width-100 fltlft'>
    <?php if (($this->lists->get("curent_type"))&&($this->lists->get("curent_type")->has_options)):?>
       <fieldset class="adminform">
       <legend><?php echo JText::_( 'FACTORY_CUSTOM_FIELD_OPTIONS_VALUES' ); ?>&nbsp;
                <img src="<?php echo JURI::root();?>/administrator/components/<?php echo APP_EXTENSION;?>/thefactory/fields/images/reload.gif" width="16" border="0" id="field-reload-options" /></legend>
       <table id="cfield_option_list_table">
           <thead>
               <tr>
                   <th class="title" valign="top">
                       <?php echo JText::_("FACTORY_OPTION_VALUE");?>
                   </th>
                   <th class="title" width="50">
                       <?php echo JText::_("FACTORY_EDIT");?>
                   </th>
                   <th class="title" width="50">
                       <?php echo JText::_("FACTORY_DELETE");?>
                   </th>
               </tr>
           </thead>
           <tbody>
               <?php  $k=1; ?>
               <?php foreach($this->lists->get("field_options") as $option):?>
                   <tr class="row<?php echo $k;?>" id="opt-row-<?php echo $option->id; ?>">
                       <td><span id="cfield_opt_<?php echo $option->id;?>"><?php echo $option->option_name;?></span></td>
                       <td align="center">
                               <img src="<?php echo JURI::root();?>/administrator/components/<?php echo APP_EXTENSION;?>/thefactory/fields/images/edit.png" width="15" border="0" class="fields-edit-button" row-id="<?php echo $option->id;?>"/>
                       </td>
                       <td align="center">
                               <img src="<?php echo JURI::root();?>/administrator/components/<?php echo APP_EXTENSION;?>/thefactory/fields/images/delete.png" border="0" class="fields-delete-button" row-id="<?php echo $option->id;?>"/>
                       </td>
                   </tr>
                   <?php $k=1-$k;  ?>
               <?php endforeach;?>

           </tbody>
           <tfoot>
               <tr>
                   <td colspan="3">
                       <a href="#" id="add_option_link"><?php echo JText::_("FACTORY_ADD_FIELD_OPTION");?></a>
                       <?php if ($this->field->id):?>
                           &nbsp;&nbsp;
                           <a href="#" id="save_options_link"><?php echo JText::_("FACTORY_SAVE_OPTIONS");?></a>
                       <?php endif;?>
                   </td>
               </tr>
           </tfoot>
       </table>
       </fieldset>
    <?php endif;?>
</div>
