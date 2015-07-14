<?php

defined('_JEXEC') or die('Restricted access');

class JFormFieldSelectPeriod extends JFormField{

    protected $type = 'InfoGDVersion';


    protected function getInput() {

        $input = '<input type="text" value="'.$this->value.'" name="'.$this->name.'" />';

        $opts = array();
        $timeUnits = array('second','minute','hour','day','month','year');
        foreach($timeUnits as $tu) {
            $opts[] = JHTML::_('select.option',$tu,ucfirst($tu).'s');
        }

        $cfg = new BidConfig;

        $selectedUnit = $cfg->{$this->name.'_type'};
        $selectTimeUnit = JHtml::_('select.genericlist',$opts,$this->name.'_type',null,'value','text',$selectedUnit);

        return $input.$selectTimeUnit;
    }
}
