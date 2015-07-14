<?php
/**
 * Joomla! 1.5 component awdjomalbum
 *
 * @version $Id: view.html.php 2011-01-31 06:01:25 svn $
 * @author zippyinfotek
 * @package Joomla
 * @subpackage awdjomalbum
 * @license GNU/GPL
 *
 * awd album
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport( 'joomla.application.component.view');
class AwdjomalbumViewDefault extends JViewLegacy {
    function display($tpl = null) {
	
//		global $mainframe, $option;
		global $app, $option;
		$db		=& JFactory::getDBO();
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('JomWALL') );
	    
	    JToolBarHelper::title(JText::_('JomWALL'), 'awdwallprogallery');
        parent::display($tpl);
    }
		function addIcon( $image , $url , $text , $newWindow = false )
	{
		$lang		=& JFactory::getLanguage();
		
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php //echo JHTML::_('image', 'administrator/components/com_awdjomalbum/images/' . $image , NULL, NULL, $text ); 
?>
					<img src="<?php echo JURI::root();?>administrator/components/com_awdjomalbum/images/<?php echo $image; ?>" alt="<?php echo $text; ?>" />
					
					<span><?php  echo $text; ?></span></a>
			</div>
		</div>
<?php
	}

}
?>