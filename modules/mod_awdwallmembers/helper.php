<?php
/**
 * @package    JomWALL 
 * @subpackage  mod_awdwallmembers
 * @link http://www.AWDsolution.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class modAwdwallmembersHelper
{
    function getMembers( $params )
    {
		$db		=JFactory::getDBO();
		$ordering=$params->get('ordering',0);
		$count=$params->get('count',5);
		$showavatar=$params->get('showavatar',1);
		$where='';
		if($showavatar==1)
		{
			$where=" and avatar!='' ";
		}
		if($ordering==2)
		{
			$sql ='Select a.*,b.username,b.name'
				.' from #__awd_wall_users as a'
				.'  INNER JOIN #__users as b on a.user_id=b.id INNER JOIN #__session as s on s.userid = b.id'
				.'  where b.block=0 and s.client_id=0 '.$where.' limit '.$count;
				//echo $sql;
		}
		else
		{
			if($ordering==0)
			{
				$orderby=' order by b.id desc ';
			}
			else
			{
				$orderby=' order by RAND() ';
			}
			$sql ='Select a.*,b.username,b.name'
				.' from #__awd_wall_users as a'
				.'  INNER JOIN #__users as b on a.user_id=b.id'
				.'  where b.block=0 '.$where.$orderby.' limit '.$count;
		}
		
		$db->setQuery($sql);
		$rows=$db->loadObjectList();	
		
		return $rows;
    }
	function getComItemId()
	{
		$db 	= JFactory::getDBO();
		$query  = "SELECT id FROM #__menu WHERE link='index.php?option=com_awdwall&view=awdwall&layout=main' and published='1'";
		$db->setQuery($query);
		return $db->loadResult();
	}

}
?>
