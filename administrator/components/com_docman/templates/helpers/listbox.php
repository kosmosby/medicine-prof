<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperListbox extends ComDefaultTemplateHelperListbox
{
    /**
     * Generates an HTML access listbox
     *
     * @param   array   An optional array with configuration options
     * @return string Html
     */
    public function access($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'inheritable' => false,
            'name'      => 'access',
            'attribs'   => array(),
            'deselect'  => true,
        ))->append(array(
            'prompt'    => '- '.($config->inheritable ? $this->translate('Inherit') : $this->translate('Select')).' -',
            'selected'  => $config->{$config->name}
        ));

        $prompt = false;
        if ($config->deselect) {
            $prompt = array((object) array('value' => $config->inheritable ? '-1' : '', 'text'  => $config->prompt));
        }

        $html = JHtml::_('access.level', $config->name, $config->selected, $config->attribs->toArray(), $prompt);

        return $html;
    }
    /**
     * Rendering a simple day range select list, specialized for bootstrap
     */
    public function day_range( $config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name'      => 'day_range'
        ))->append(array(
            'attribs'   => array('id' => $config->name, 'style' => 'width: auto'),
            'selected'  => $config->{$config->name}
        ));

        $options = array();
        $options[] = $this->option(array('text' => $this->translate('1 day'), 'value' => 1));
        $options[] = $this->option(array('text' => $this->translate('3 days') , 'value' => 3));
        $options[] = $this->option(array('text' => $this->translate('1 week'), 'value' => 7));
        $options[] = $this->option(array('text' => $this->translate('2 weeks') , 'value' => 14));
        $options[] = $this->option(array('text' => $this->translate('1 month'), 'value' => 30));

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    public function folders($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'model' => 'com://admin/files.model.folders',
            'filter' => array('container' => 'docman-files', 'tree' => true),
            'text' => 'path',
            'value' => 'path',
            'name' => 'folder',
            'attribs'	  => array(),
               'deselect'    => true,
               'prompt'      => $this->translate('Root Folder'),
            'selected'   => $config->{$config->name},
        ));

        $list = $this->getService($config->model)->set($config->filter)->getList();

        //Compose the options array
        $options   = array();
        if ($config->deselect) {
            $options[] = $this->option(array('text' => $config->prompt));
        }

        foreach ($list as $item) {
            $options[] = $this->option(array('text' => '- '.$item->{$config->text}, 'value' => $item->{$config->value}));
            $this->_recurseChildFolders($item, $options, $config);
        }

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    protected function _recurseChildFolders($item, &$options, $config)
    {
        static $level = 1;

        $level++;
        foreach ($item->getChildren() as $child) {
            $options[] = $this->option(array('text' => str_repeat('-', $level).' '.$child->{$config->text}, 'value' => $child->{$config->value}));
            if ($child->hasChildren()) {
                $this->_recurseChildFolders($child, $options, $config);
            }
        }
        $level--;
    }

    public function documents($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'model'		=> 'documents',
            'value'		=> 'id',
            'text'		=> 'name'
        ));

        return $this->_render($config);
    }

    public function documents_ajax($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name' => '',
            'selected' => '',
            'link' => JRoute::_('index.php?option=com_docman&view=documents&format=json&sort=title', false),
            'placeholder' => $this->translate('Search for a document')
        ))->append(array(
            'id' => $config->name
        ));

        $html = '';

        $html .= $this->getTemplate()->renderHelper('behavior.bootstrap');
        $html .= $this->getTemplate()->renderHelper('behavior.jquery');
        $html .= $this->getTemplate()->renderHelper('behavior.select2');

        $html .= "
        <style src=\"media://com_docman/bootstrap/css/select2.css\" />
        <input value=\"{$config->selected}\" id=\"{$config->id}\" name=\"{$config->name}\" style=\"width: 250px\" />
        <script>
        jQuery(function($) {
            $('#{$config->id}').select2({
                placeholder: '{$config->placeholder}',
                minimumInputLength: 2,
                ajax: {
                    url: '{$config->link}',
                    quietMillis: 100,
                    data: function (term, page) { // page is the one-based page number tracked by Select2
                        return {
                            search: term, //search term
                            limit: 10, // page size
                            offset: (page-1)*10
                        };
                    },
                    results: function (data, page) {
                        var results = [],
                            more = (page * 10) < data.documents.total; // whether or not there are more results available

                        $.each(data.documents.items, function(i, document) {
                            results.push(document.data);
                        });

                        // notice we return the value of more so Select2 knows if more results can be loaded
                        return {results: results, more: more};
                    }
                },
                initSelection: function(element, callback) {
                    var id=$(element).val();
                    if (id!=='') {
                        $.ajax('{$config->link}', {
                            data: {
                                view: 'document',
                                slug: id
                            }
                        }).done(function(data) { callback(data.data); });
                    }
                },
                formatResult: function (item) { return item.title; },
                formatSelection: function (item) { return item.title; },
                id: 'slug'
            });
        });

        </script>";

        return $html;
    }

    public function storage_types($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'key'		=> 'value',
            'text'		=> 'text',
            'methods'	=> array('file', 'remote')
        ));

        $strings = array(
            'file' => $this->translate('Local File'),
            'remote' => $this->translate('Remote Link')
        );

        $options = array();
        foreach ($config->methods as $method) {
            $text = isset($strings[$method]) ? $strings[$method] : $method;
            $options[] = $this->option(array('text' => $text, 'value' => $method));
        }

        $config->options = $options;

        return $this->optionlist($config);
    }

    public function users($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name'		=> '',
            'state' 	=> null,
            'attribs'	=> array(),
        ))->append(array(
            'value'		=> $config->name,
            'selected'  => $config->{$config->name}
        ))->append(array(
            'text'		=> $config->value,
            'column'    => $config->value,
            'deselect'  => true
        ));

        $table = $this->getService('com://admin/users.database.table.users', array('name' => 'users'));
        $model = $this->getService('com://admin/docman.model.users', array('table' => $table));
        $list = $model->getList();

        $options = array();
         if ($config->deselect) {
             $options[] = $this->option(array('text' => '- '.$this->translate('Select').' -'));
        }
        foreach ($list as $item) {
            $options[] =  $this->option(array('text' => $item->name.' ('.$item->username.')', 'value' => $item->id));
        }

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    public function categories($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'model'    => 'categories',
            'value'    => 'id',
            'text'     => 'title',
            'prompt'   => '',
            'required' => false,
            'attribs' => array('data-placeholder' => $this->translate('Select a category&hellip;'), 'class' => 'select2-listbox'),
            'behaviors' => array('select2' => array('element' => '.select2-listbox'))
        ));

        return $this->_treelistbox($config);
    }

     protected function _treelistbox($config = array())
     {
         $config = new KConfig($config);
         $config->append(array(
                 'name'		   => '',
                 'attribs'	   => array(),
                 'model'		   => KInflector::pluralize($this->getIdentifier()->package),
                 'deselect'     => true,
                 'prompt'       => '- '.$this->translate('Select').' -',
                 'unique'	   => false, // Overridden since there can be categories in different levels with the same name
                 'check_access' => false,
                 'behaviors'   => array(), // Behaviors like select2,
         ))->append(array(
                'indent'    => '&nbsp;&nbsp;&nbsp;',
                 'ignore' 	 => array(),
                 'value'		 => $config->name,
                 'selected'   => $config->{$config->name},
                 'identifier' => 'com://'.$this->getIdentifier()->application.'/'.$this->getIdentifier()->package.'.model.'.KInflector::pluralize($config->model)
                 ))->append(array(
                         'text'		=> $config->value,
                 ))->append(array(
                         'filter' 	=> array('sort' => $config->text),
                 ));

         if ($config->required) {
             $config->attribs->class .= ' required';
         }

         if ($config->deselect) {
             if (in_array('select2', array_keys(KConfig::unbox($config->behaviors)))) {
                 $config->behaviors->select2->append(array('options' => array('allowClear' => true)));
             }
         }

         $list = $this->getService($config->identifier)->set($config->filter)->getList();

         //Get the list of items
         $items = $list->getColumn($config->value);
         if ($config->unique) {
             $items = array_unique($items);
         }

         //Compose the options array
         $options = array();
         if ($config->deselect) {
             $options[] = $this->option(array('text' => $config->prompt));
         }

         $ignore = KConfig::unbox($config->ignore);
         foreach ($items as $key => $value) {
             $item = $list->find($key);

             if ($config->check_access && $item->isAclable() && !$item->canPerform('add')) {
                 continue;
             }

             if (in_array($item->id, $ignore)) {
                 continue;
             }

             $options[] =  $this->option(array('text' => str_repeat($config->indent, $item->level) . $item->{$config->text}, 'value' => $item->{$config->value}));
         }

         //Add the options to the config object
         $config->options = $options;

         $html = $this->optionlist($config);

         if ($this->getTemplate()) {
             foreach ($config->behaviors as $behavior => $options) {
                 $html .= $this->getTemplate()->renderHelper('behavior.'.$behavior, KConfig::unbox($options));
             }
         }

         return $html;
     }

     /**
      * Overridden to fix the Bootstrap problem with size=1 select boxes
      *
      * @see KTemplateHelperSelect::optionlist()
      */
     public function optionlist($config = array())
     {
         $html = parent::optionlist($config);

         $html = preg_replace('#size="1"#', '', $html, 1);

         return $html;
     }
}
