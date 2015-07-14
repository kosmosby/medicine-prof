<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    $result=new stdClass();
    $result->result=1;
    $result->params=JHtml::_('customfields.fieldtype_params',$this->field_type->get('_params'),$this->get('paramvalues'));
    $result->hasoptions=$this->field_type->has_options;
    $result->options='';
    if ($result->hasoptions){
        $result->options='
               <fieldset class="adminform">
               <legend>'.JText::_( 'FACTORY_CUSTOM_FIELD_OPTIONS_VALUES' ).'&nbsp;
                        <img src="'.JURI::root().'/administrator/components/'.APP_EXTENSION.'/thefactory/fields/images/reload.gif" width="16" border="0" id="field-reload-options" /></legend>
               <table id="cfield_option_list_table">
                   <thead>
                       <tr>
                           <th class="title" valign="top">
                               '.JText::_("FACTORY_OPTION_VALUE").'
                           </th>
                           <th class="title" width="50">
                               '.JText::_("FACTORY_EDIT").'
                           </th>
                           <th class="title" width="50">
                               '.JText::_("FACTORY_DELETE").'
                           </th>
                       </tr>
                   </thead>
                   <tbody>';

        $k=1;
        if($this->field){
            $field_option=$this->field->getOptions();
            foreach($field_option as $option){
                $result->options.=
                    '<tr class="row'.$k.'" id="opt-row-'.$option->id.'">
                        <td><span id="cfield_opt_'.$option->id.'">'.$option->option_name.'</span></td>
                        <td align="center">
                                <img src="'.JURI::root().'/administrator/components/'.APP_EXTENSION.'/thefactory/fields/images/edit.png" width="15" border="0" class="fields-edit-button" row-id="'.$option->id.'"/>
                        </td>
                        <td align="center">
                                <img src="'.JURI::root().'/administrator/components/'.APP_EXTENSION.'/thefactory/fields/images/delete.png" border="0" class="fields-delete-button" row-id="'.$option->id.'"/>
                        </td>
                    </tr>';
                $k=1-$k;
            }
        }
        $result->options.='
                   </tbody>
                   <tfoot>
                       <tr>
                           <td colspan="3">
                               <a href="#" id="add_option_link">'.JText::_("FACTORY_ADD_FIELD_OPTION").'</a>
                               &nbsp;&nbsp;
                               <a href="#" id="save_options_link">'.JText::_("FACTORY_SAVE_OPTIONS").'</a>
                           </td>
                       </tr>
                   </tfoot>
               </table>
               </fieldset>
        ';
    }

    $reg=new JRegistry($result);
    echo $reg->toString('JSON');

?>
