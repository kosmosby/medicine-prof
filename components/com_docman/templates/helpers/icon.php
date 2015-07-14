<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanTemplateHelperIcon extends KTemplateHelperAbstract
{
    /**
     * Used for giving class names to the icons
     * @var int
     */
    protected static $_count = 0;

    public function path($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'icon' => null
        ));

        $icon = $config->icon;

        if (substr($icon, 0, 5) === 'icon:') {
            $icon = 'icon://'.substr($icon, 5);
        } else {
            $icon = 'media://com_docman/images/icons/'.$icon;
        }

        return $icon;
    }

    public function css($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'icon' => null
        ));

        $icon = $this->path($config);

        $class = ++self::$_count;

        $html = sprintf('
        <style>
            .category-icon-%d {
                background-image: url(%s);
            }
        </style>', $class, $icon);

        return $html;
    }

    public function getCount($config = array())
    {
        return self::$_count;
    }
}
