<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Bootstrap Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComDocmanTemplateHelperBootstrap extends ComKoowaTemplateHelperBootstrap
{
    /**
     * Loads necessary Bootstrap files
     *
     * {@inheritdoc}
     */
    public function load($config = array())
    {
        $config = new KObjectConfigJson($config);

        if ($menu = JFactory::getApplication()->getMenu()->getActive())
        {
            if ($suffix = htmlspecialchars($menu->params->get('pageclass_sfx')))
            {
                $config->append(array(
                    'class' => array($suffix)
                ));
            }
        }

        return parent::load($config);
    }
}