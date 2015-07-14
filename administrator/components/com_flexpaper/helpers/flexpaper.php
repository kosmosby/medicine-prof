<?php
// No direct access to this file
defined('_JEXEC') or die;
 
/**
 * HelloWorld component helper.
 */
abstract class flexpaperHelper
{

    public static function getCreationDateOptions()
    {
        $options = array();

        $options[0]->value=7;
        $options[0]->text=JText::_('COM_FLEXPAPER_THIS_WEEK');

        $options[1]->value=14;
        $options[1]->text=JText::_('COM_FLEXPAPER_LAST_WEEK');

        $options[2]->value=30;
        $options[2]->text=JText::_('COM_FLEXPAPER_LAST_MONTH');

        $options[3]->value=90;
        $options[3]->text=JText::_('COM_FLEXPAPER_3_MONTH');

        $options[4]->value=180;
        $options[4]->text=JText::_('COM_FLEXPAPER_6_MONTH');

        $options[5]->value=810;
        $options[5]->text=JText::_('COM_FLEXPAPER_9_MONTH');

        $options[6]->value=365;
        $options[6]->text=JText::_('COM_FLEXPAPER_LAST_YEAR');

        return $options;
    }


	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_DOCUMENTS'),
		                         'index.php?option=com_flexpaper',$submenu == 'flexpapers');
//        JSubMenuHelper::addEntry(JText::_(COM_FLEXPAPER_CATEGORIES_DOCUMENTS),
//             					'index.php?option=com_categories&view=categories&extension=com_flexpaper&level=2',
//                                  $submenu == 'courses-categories');
        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_COURSES'),
            'index.php?option=com_flexpaper&task=courses&view=courses',
            					 $submenu == 'courses');

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_CATEGORIES_COURSES'),
		    'index.php?option=com_categories&view=categories&extension=com_flexpaper',
		                         $submenu == 'categories');

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_BUNDLES'),
            'index.php?option=com_flexpaper&task=bundles&view=bundles',
            $submenu == 'bundles');

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_QUIZES'),
            'index.php?option=com_flexpaper&task=quizes&view=quizes',
            $submenu == 'quizes');

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_TESTS'),
            'index.php?option=com_flexpaper&task=tests&view=tests',
            $submenu == 'tests');

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_QUESTIONS'),
            'index.php?option=com_flexpaper&task=questions&view=questions',
            $submenu == 'questions');

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_CERTIFICATES'),
            'index.php?option=com_flexpaper&task=certificates&view=certificates',
            $submenu == 'certificates');

        JSubMenuHelper::addEntry(JText::_('COM_FLEXPAPER_VIDEO'),
            'index.php?option=com_flexpaper&task=videos&view=videos',
            $submenu == 'videos');


        // set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-flexpaper ' .
		                               '{background-image: url(../media/com_flexpaper/images/tux-48x48.png);}');
		if ($submenu == 'categories') 
		{
			$document->setTitle(JText::_('Courses Categories'));
		}
	}
}