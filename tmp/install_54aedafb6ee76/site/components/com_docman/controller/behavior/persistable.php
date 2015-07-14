<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorPersistable extends ComKoowaControllerBehaviorPersistable
{
    protected function _beforeRender(KControllerContextInterface $context)
    {
        parent::_beforeBrowse($context);
    }

    protected function _afterRender(KControllerContextInterface $context)
    {
        parent::_afterBrowse($context);
    }
}
