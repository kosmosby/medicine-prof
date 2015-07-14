<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanTemplateHelperListbox extends KTemplateHelperListbox
{
    public function users($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
                'autocomplete' => true,
                'validate'     => false,
    			'model'		=> 'users',
        		'url' => JRoute::_('index.php?option=com_logman&view=users&format=json', false),
                'attribs'      => array('size' => 30, 'class' => 'span2')
        ));

        //@TODO : Fix - Forcing config option because of name collisions
        $config->name = 'user';
        $config->text = 'name';
        //@TODO : Note - Future support for matching by email + username will need a autocomplete.js refactor/workaround
        //               as Meio.Autocomplete.Filter._getValueFromKeys() isn't working as advertised.
        //$config->text = 'email.username.name';
        $config->sort = 'name';

        /* the str_replace is necessary as no line breaks or white space can be between the icon and the input or you'll have an ugly gap */
        return str_replace(array('	', "\n"), '', parent::_render($config));
    }
}