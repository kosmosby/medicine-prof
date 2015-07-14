<?php
/**
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

JFactory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR);
if (version_compare(JVERSION, '3.0', '>=')){
include_once('components/com_config/model/cms.php');
include_once('components/com_config/model/form.php');
}

include_once(JPATH_ADMINISTRATOR.'/components/com_config/models/application.php');

class JOOOIDModelConfig extends ConfigModelApplication
{

	/**
	 * NO METHODS, ALWAYS CALL SUPERCLASS
	 */

}



