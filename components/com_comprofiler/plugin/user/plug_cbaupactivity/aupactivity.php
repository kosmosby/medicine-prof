<?php
/**
* @version		$Id: alphauserpoints 2012-01-25 19:34:56Z $
* @package		Joomla
* @copyright	Copyright (C) migus Mike Gusev. All rights reserved.
* @license		GNU/GPLv3
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class getAUPActivityTab extends cbTabHandler {

	function getAUPActivityTab()
	{
		$this->cbTabHandler();
	}

	function getDisplayTab($tab,$user,$ui)
	{

		$params=$this->params;

		global $_CB_framework,$_CB_database;

		$livesite = JURI::base();
		JPlugin::loadLanguage( 'com_alphauserpoints', JPATH_SITE );
		$tableclass = $params->get('tableclass', 1);
		$count_activity = $params->get('count_activity', 20);
		$return ="";
		$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';

		if ( file_exists($api_AUP)) {
			require_once ($api_AUP);
			$listActivity = AlphaUserPointsHelper::getListActivity('all', $user->id, $count_activity);
		}
		
		if(count($listActivity) >0) {
			$return .='<table width="95%" cellspacing="0" border="0">';
			$return .= '<tr class=\'sectiontableheader\'><th>';
			$return .='</th><th width="20%">';
			$return .= JText::_( 'AUP_DATE' );
			$return .='</th><th width="20%">';
			$return .= JText::_( 'AUP_ACTION' );
			$return .='</th><th width="6%">';
			$return .= JText::_( 'AUP_POINTS_UPPER' );
			$return .='</th><th>';
			$return .= JText::_( 'AUP_DETAIL' );
			$return .='</th></tr>';
			$i=0;
			foreach ( $listActivity as $activity ) {
				$i++;
				if($i>2) $i=1;
				$return .='<tr';
				if($tableclass) $return .=' class="sectiontableentry'.$i.'"';
				$return .=' ><td>';
					$icon = ( $activity->category!='' ) ? '<img src="'.JURI::base(true).DS.'components'.DS.'com_alphauserpoints'.DS.'assets'.DS.'images'.DS.'categories'.DS.$activity->category.'.gif" alt="" />' : '';
				$return .= $icon;
				$return .='</td><td>';
				$return .= '<span style="color:#333;">'.JHTML::_('date', $activity->insert_date, JText::_('d.m.Y')).'</span>&nbsp;<span style="color:#777;font-style:oblique;">'.JHTML::_('date', $activity->insert_date, JText::_('H:i:s')).'</span>';
					$color = $activity->points>0 ? "#009900" : ($activity->points<0 ? "#ff0000" : ($activity->points==0.00 ? "#777" : "#777"));
				$return .='</td><td style="color:'. $color .';">';
				$return .= JText::_( $activity->rule_name );
				$return .='</td><td style="text-align: right; color:'. $color .';">';
				$return .= $activity->points;
				$return .='&nbsp;&nbsp;</td><td  style="color:#777;">';
				$return .= $activity->datareference;
				$return .='</td></tr>';
			}
			$return .='</table>';
			$return .= '<br />' . JHTML::_('date', 'now', JText::_('l d.m.Y H:i'));
		} else $return .='<div align="center"><p>'.JText::_( 'AUP_THIS_INFORMATION_HAS_NOT_BEEN_PROVIDED' ).'</p></div>';
		return $return;
	}
}
?>