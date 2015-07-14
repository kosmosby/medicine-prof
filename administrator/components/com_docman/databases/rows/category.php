<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseRowCategory extends ComDocmanDatabaseRowNode
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->mixin(clone $this->getTable()->getBehavior('orderable'));
        $this->mixin(clone $this->getTable()->getBehavior('aclable'));
    }

    public function save()
    {
        if (is_array($this->params) && empty($this->params['icon'])) {
            $this->params['icon'] = 'folder.png';
        }

        return parent::save();
    }

    public function __get($column)
    {
        if ($column == 'image_path') {
            return $this->image ? KRequest::root().'/joomlatools-files/docman-images/'.$this->image : null;
        }

        if ($column == 'icon')
        {
            if (!is_object($this->params)) {
                $this->params = json_decode($this->params);
            }
            $icon = $this->params->icon ? $this->params->icon : 'icon:folder.png';

            return $icon;
        }

        if ($column == 'icon_path')
        {
            $icon = $this->icon;
            if (substr($icon, 0, 5) === 'icon:') {
                $icon = '/joomlatools-files/docman-icons/'.substr($icon, 5);
            } else {
                $icon = '/media/com_docman/images/icons/'.$icon;
            }

            return KRequest::root().$icon;
        }

        if ($column === 'description_summary')
        {
            $description = $this->description;
            $position    = strpos($description, '<hr id="system-readmore" />');
            if ($position !== false) {
                return substr($description, 0, $position);
            }

            return $description;
        }

        if ($column === 'description_full') {
            return str_replace('<hr id="system-readmore" />', '', $this->description);
        }

        return parent::__get($column);
    }
}
