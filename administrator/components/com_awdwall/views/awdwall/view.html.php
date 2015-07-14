<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

class awdwallViewawdwall extends JViewLegacy
{
	function display($tpl = null)
	{
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('JomWALL') );
	    
	   
	   
	   JToolBarHelper::title(JText::_('JomWALL'), 'awdwallprocpanel');
	  
	    
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
					<?php //echo JHTML::_('image', 'administrator/components/com_awdwall/images/' . $image , NULL, NULL, $text ); ?>
					<img src="<?php echo JURI::root();?>administrator/components/com_awdwall/images/<?php echo $image; ?>" alt="<?php echo $text; ?>" />

					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
}
?>
