<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperListbox extends ComKoowaTemplateHelperListbox
{
    /**
     * Generates an HTML access listbox
     *
     * @param   array $config  An optional array with configuration options
     * @return string Html
     */
    public function access($config = array())
    {
        $translator = $this->getObject('translator');

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'inheritable' => false,
        ))->append(array(
            'deselect_value' => $config->inheritable ? '-1' : '',
            'prompt'         => '- '.($config->inheritable ? $translator->translate('Inherit') : $translator->translate('Select')).' -',
        ));

        if ($config->inheritable) {
            $config->deselect = true;
        }

        return parent::access($config);
    }
    /**
     * Rendering a simple day range select list
     */
    public function day_range($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name'   => 'day_range',
            'values' => array(1, 3, 7, 14, 30)
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));

        $html   = array();
        $html[] = '<input type="hidden" id="day_range" name="'.$config->name.'" value="'.$config->value.'" '. $this->buildAttributes($config->attribs) .'>';
        $html[] = '<div class="input-group" data-active-class="active">';

        foreach($config->values as $value)
        {
            $button = new KObjectConfigJson(array(
                'text'    => $value,
                'value'   => $value,
                'attribs' => array()
            ));

            $button->attribs->type = 'button';
            $button->attribs->class = 'btn';

            if($config->selected && $value == $config->selected) {
                $button->attribs->class .= ' active';
            }

            $button->attribs->value = $button->value;

            $attributes = $this->buildAttributes($button->attribs);

            $html[] = '<span class="input-group-btn"><button '.$attributes.'>'.$button->text.'</button></span>';
        }

        $value = in_array($config->selected, KObjectConfig::unbox($config->values)) ? '' : $config->selected;
        $html[] = '<input value="'.$value.'" class="input-group-form-control custom_amount" type="text" placeholder="&hellip;" />';

        $html[] = '</div>';

        $html[] = $this->getTemplate()->helper('behavior.buttongroup', array(
            'element' => '#day_range'
        ));

        return implode(PHP_EOL, $html);
    }

    public function folders($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'model' => 'com:files.model.folders',
            'filter' => array('container' => 'docman-files', 'tree' => true),
            'label' => 'path',
            'value' => 'path',
            'name' => 'folder',
            'attribs'	  => array(),
               'deselect'    => true,
               'prompt'      => $this->getObject('translator')->translate('Root folder'),
            'selected'   => $config->{$config->name},
        ));

        try {
            $list = $this->getObject($config->model)->setState(KObjectConfig::unbox($config->filter))->fetch();
        } catch (Exception $e) {
            return $e->getMessage();
        }


        //Compose the options array
        $options   = array();
        if ($config->deselect) {
            $options[] = $this->option(array('label' => $config->prompt));
        }

        foreach ($list as $item)
        {
            $options[] = $this->option(array('label' => '- '.$item->{$config->label}, 'value' => $item->{$config->value}));
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
        foreach ($item->getChildren() as $child)
        {
            $options[] = $this->option(array('label' => str_repeat('-', $level).' '.$child->{$config->label}, 'value' => $child->{$config->value}));
            if ($child->hasChildren()) {
                $this->_recurseChildFolders($child, $options, $config);
            }
        }
        $level--;
    }

    public function documents($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'model'		=> 'documents',
            'value'		=> 'id',
            'label'		=> 'title'
        ));

        return $this->_render($config);
    }

    public function categories($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'model'    => 'categories',
            'value'    => 'id',
            'label'    => 'title',
            'select2'  => true
        ));

        return $this->_treelistbox($config);
    }

    public function pages($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'select2' => true,
            'options' => array(),
        ));

        $types = KObjectConfig::unbox($config->types);

        if (empty($types)) {
            $types = array('document', 'list', 'filteredlist', 'userlist');
        }

        $pages = $this->getObject('com://admin/docman.model.pages')
                    ->language('all')
                    ->view($types)
                    ->fetch();

        $options = array();
        foreach ($pages as $page)
        {
            if (!isset($options[$page->menutype])) {
                $options[$page->menutype] = array();
            }

            $options[$page->menutype][] = array('value' => $page->id, 'label' => $page->title);
        }

        $config->options->append($options);

        return $this->optionlist($config);
    }

    public function pagecategories($config = array())
    {
        $config = new KObjectConfigJson($config);

        $config->append(array(
            'model'   => 'categories',
            'select2' => true,
            'page'    => 'all',
            'value'   => 'id',
            'label'   => 'title',
            'filter'  => array('page' => $config->page),
            'tree'    => false
        ))->append(array(
            'identifier' => 'com://' . $this->getIdentifier()->domain . '/' .
                $this->getIdentifier()->package . '.model.' . KStringInflector::pluralize($config->model)
        ));

        if ($config->tree)
        {
            $config->indent = '- ';
            $list           = $this->_treelistbox($config);
        }
        else
        {
            $categories = $this->getObject($config->identifier)->setState(KObjectConfig::unbox($config->filter))->fetch();

            $options = array();

            foreach ($categories as $category)
            {
                $options[] = array('value' => $category->{$config->value}, 'label' => $category->{$config->label});
            }

            $config->options = $options;

            $list = parent::optionlist($config);
        }

        return $list;
    }

    protected function _treelistbox($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name'		    => '',
            'attribs'	    => array(),
            'model'		    => KStringInflector::pluralize($this->getIdentifier()->package),
            'deselect'      => true,
            'prompt'        => '- Select -',
            'unique'	    => false, // Overridden since there can be categories in different levels with the same name
            'check_access'  => false
        ))->append(array(
            'select2'         => false,
            'value'	  => $config->name,
            'selected'   => $config->{$config->name},
            'identifier' => 'com://'.$this->getIdentifier()->domain.'/'.$this->getIdentifier()->package.'.model.'.KStringInflector::pluralize($config->model)
        ))->append(array(
            'label'		=> $config->value,
        ))->append(array(
            'filter' 	=> array('sort' => $config->label),
        ))->append(array(
            'indent'     => '- ',
            'ignore' 	 => array(),
        ));

        $list = $this->getObject($config->identifier)->setState(KObjectConfig::unbox($config->filter))->fetch();

        //Get the list of items
        $items = array();
        foreach($list as $key => $item) {
            $items[$key] = $item->getProperty($config->value);
        }

        if ($config->unique) {
            $items = array_unique($items);
        }

        //Compose the options array
        $options = array();

        $ignore = KObjectConfig::unbox($config->ignore);
        foreach ($items as $key => $value)
        {
            $item = $list->find($key);

            if ($config->check_access && $item->isPermissible() && ($value != $config->selected) && !$item->canPerform('add')) {
                continue;
            }

            if (in_array($item->id, $ignore)) {
                continue;
            }

            $options[] =  $this->option(array('label' => str_repeat($config->indent, $item->level-1) . $item->{$config->label}, 'value' => $item->{$config->value}));
        }

        //Add the options to the config object
        $config->options = $options;

        $html = '';

        if($config->autocomplete) {
            $html .= $this->_autocomplete($config);
        } else {
            $html .= $this->optionlist($config);
        }

        return $html;
    }
}
