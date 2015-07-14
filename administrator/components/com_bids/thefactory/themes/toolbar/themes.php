<?php
	/**------------------------------------------------------------------------
	thefactory - The Factory Class Library - v 2.0.0
	------------------------------------------------------------------------
	 * @author    TheFactory
	 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
	 * @license   - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
	 *            Websites: http://www.thefactory.ro
	 *            Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
	 * @build     : 01/04/2012
	 * @package   : thefactory
	 * @subpackage: themes
	-------------------------------------------------------------------------*/

	defined('_JEXEC') or die('Restricted access');

	class JTheFactoryThemesToolbar
	{
		function display($task = null)
		{
            $MyApp = JTheFactoryApplication::getInstance();
            $appname = $MyApp->getIniValue('name');

			JToolBarHelper::title(JText::_('FACTORY_THEME_MANAGEMENT'), $appname);
			JTheFactoryThemesSubmenu::subMenuListPages();
			switch ($task) {
				default:
				case 'listthemes':
					JToolBarHelper::custom('themes.upload', 'upload.png', 'upload_2.png', JText::_('FACTORY_UPLOAD_THEME_PACK'), false);
					JToolBarHelper::save2copy('themes.clonetheme', JText::_('FACTORY_CLONE_THEME'));
					JToolBarHelper::deleteList('', 'themes.delete', JText::_('FACTORY_DELETE_THEME'));
					JToolBarHelper::custom('settingsmanager', 'back', 'back', JText::_('FACTORY_BACK'), false);
					break;
				case 'clonetheme':
					JToolBarHelper::save('themes.doclone');
					JToolBarHelper::cancel('themes.cancel');
					break;
				case 'upload':
					JToolBarHelper::save('themes.doupload');
					JToolBarHelper::cancel('themes.cancel');
					break;
			}

		}
	}
