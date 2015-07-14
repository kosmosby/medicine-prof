<?php
/**
 * @version     $Id: behavior.php 3364 2011-05-25 21:07:41Z johanjanssens $
 * @package     Nooku_Components
 * @subpackage  Default
 * @copyright   Copyright (C) 2007 - 2012 Johan Janssens. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Template Toolbar Helper
 *
 * @author      Johan Janssens <johan@nooku.org>
 * @package     Nooku_Components
 * @subpackage  Default
 */
class ComDocmanTemplateHelperToolbar extends ComDefaultTemplateHelperToolbar
{
    /**
     * Render a toolbar command
     *
     * @param   array   An optional array with configuration options
     * @return string Html
     */
    public function command($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'command' => NULL
        ));

        $command = $config->command;

        if ($command->is_allowed === false) {
            $command->attribs->title = $this->translate('You are not allowed to perform this action');
            $command->attribs->onclick = 'return false;';
            $command->attribs->rel = 'tooltip';
            $command->attribs->class->append(array('disabled', 'unauthorized'));
        }

        return parent::command($config);
    }
}
