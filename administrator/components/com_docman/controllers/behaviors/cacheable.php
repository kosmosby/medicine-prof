<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorCacheable extends ComFilesControllerBehaviorCacheable
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'group' => 'com_docman.files'
        ));

        parent::_initialize($config);
    }

    public function execute($name, KCommandContext $context)
    {
        $container = $this->getRequest()->container;
        if (is_object($container)) {
            $container = $container->slug;
        }

        if ($container === 'docman-files') {
            return parent::execute($name, $context);
        }

        return true;
    }
}
