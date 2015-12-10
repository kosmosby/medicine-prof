<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<?php

//echo "<pre>";
//print_r($this->items); die;

$string = '';

for($i=0;$i<count($this->items);$i++) {

    if($i>0 && $i!=count($this->items))
        $string .= ",{";
    else
        $string .= "{";
    $string .= "text: '".$this->items[$i]->name."',";
    $string .= "href: '#parent1',";
    $string .= "image: '".JUri::base() ."images/comprofiler/tn".$this->items[$i]->avatar."',";
    $string .= "tags: ['".count($this->items[$i]->nodes)."']";

        if(count($this->items[$i]->nodes)) {
            $string .= ", nodes: [";
            for($j=0;$j<count($this->items[$i]->nodes);$j++) {
                        if($j>0 && $j!=count($this->items[$i]->nodes))
                            $string .= ",{";
                        else
                            $string .= "{";
                        $string .= "text: '".$this->items[$i]->nodes[$j]->title."',";
                        //$string .= "href: '".$this->items[$i]->nodes[$j]->url."'";
                        $string .= "tags: ['".count($this->items[$i]->nodes[$j]->nodes)."']";

                            if(count($this->items[$i]->nodes[$j]->nodes)) {
                                $string .= ", nodes: [";
                                for($k=0;$k<count($this->items[$i]->nodes[$j]->nodes);$k++) {
                                    if($k>0 && $k!=count($this->items[$i]->nodes[$j]->nodes))
                                        $string .= ",{";
                                    else
                                        $string .= "{";
                                    $string .= "text: '".$this->items[$i]->nodes[$j]->nodes[$k]->title."',";
                                    //$string .= "href: '".$this->items[$i]->nodes[$j]->nodes[$k]->url."'";
                                    $string .= "tags: ['".count($this->items[$i]->nodes[$j]->nodes[$k]->nodes)."']";

                                                        if(count($this->items[$i]->nodes[$j]->nodes[$k]->nodes)) {
                                                            $string .= ", nodes: [";
                                                            for($m=0;$m<count($this->items[$i]->nodes[$j]->nodes[$k]->nodes);$m++) {
                                                                if($m>0 && $m!=count($this->items[$i]->nodes[$j]->nodes[$k]->nodes))
                                                                    $string .= ",{";
                                                                else
                                                                    $string .= "{";
                                                                $string .= "text: '".$this->items[$i]->nodes[$j]->nodes[$k]->nodes[$m]->name."',";
                                                                $string .= "image: '".JUri::base() ."images/comprofiler/tn".$this->items[$i]->nodes[$j]->nodes[$k]->nodes[$m]->avatar."'";
                                                                $string .= "}";
                                                            }
                                                        $string .= "]";
                                                        }

                                    $string .="}";
                                }
                                $string .="]";
                            }
                        $string .= "}";
            }
            $string .="]";
        }

    $string .= "}";
}

$document = JFactory::getDocument();
$document->addScriptDeclaration("
    var defaultData = [
            ".$string."
        ];
");


    //echo $this->item->greeting.(($this->item->category and $this->item->params->get('show_category'))
      //                                ? (' ('.$this->item->category.')') : ''); ?>





<div class="row virtualmed cb_template">
    <hr>
    <h2>Виртуальная медицина</h2>
     <div class="col-sm-4">
        <h3>Структура</h3>
        <div id="treeview-selectable" class=""></div>
    </div>
    <div class="col-sm-3">
        <h3>Поиск</h3>
        <div class="form-group">
            <label for="input-select-node" class="sr-only">Search Tree:</label>
            <input type="input" class="form-control" id="input-select-node" placeholder="Слово для поиска..." value="">
        </div>
    </div>

    <div class="col-sm-5 streamItem">
        <div id="selectable-output"></div>
    </div>
</div>



