<?php

defined('_JEXEC') or die('Restricted access');

class JFormFieldGDVersion extends JFormField{

    protected $type = 'gdVersion';

    protected function getLabel() {
        return 'GD Version';
    }

    protected function getInput() {
        $info = gd_info();

        return $info['GD Version'];
    }
}
