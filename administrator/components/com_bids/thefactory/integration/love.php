<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: integration
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryIntegrationLove extends JTheFactoryIntegration{
    var $mode="love";
    var $table="#__lovefactory_profiles";
    var $keyfield="user_id";
    
	
    function detectIntegration()
	{
		$database =  JFactory::getDBO();
	    $database->setQuery("SELECT count(*) FROM #__extensions WHERE `element`='com_lovefactory'");
	    return $database->loadResult()>0;
	}
    function getUserProfile($userid=0)
    {
		$database =  JFactory::getDBO();
	    $q = ' SELECT p.*, u.username, m.title, m.title AS testering, m.ordering,'
		. '   u.lastvisitDate, COUNT(se.userid) AS loggedin,'
	    . '   IF (g.lat IS NOT NULL, g.lat, 0) AS googleX,'
	    . '   IF (g.lng IS NOT NULL, g.lng, 0) AS googleY'
        . ' FROM #__lovefactory_profiles p'
		. ' LEFT JOIN #__users u ON u.id = p.user_id'
        . ' LEFT JOIN #__lovefactory_memberships_sold s ON s.id = p.membership_sold_id'
        . ' LEFT JOIN #__lovefactory_memberships m ON m.id = s.membership_id'
        . ' LEFT JOIN #__session se ON se.userid = p.user_id AND se.client_id = 0'
        . ' LEFT JOIN #__lovefactory_geo g ON g.user_id = p.user_id'
        . ' WHERE p.user_id = ' . $userid
        . ' GROUP BY p.user_id';
         $database->setQuery($q);
        $userdata = $database->loadObject();

		return get_object_vars($userdata);
    }

    function getIntegrationArray()
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $tablename=$MyApp->getIniValue("field_map_table","profile-integration");
	    $fieldmap=array();
        
		$database =  JFactory::getDBO();
    	$database->setQuery("SELECT field,assoc_field FROM `".$tablename."`");
    	$r=$database->loadAssocList();
    	for($i=0;$i<count($r);$i++){
    	    $fieldmap[$r[$i]['field']]=$r[$i]['assoc_field'];
    	}
        return $fieldmap;
    }
    function getProfileLink($userid)
    {
        $link =  JURI::root()."index.php?option=com_lovefactory&view=profile";
        if ($userid)
            $link = JURI::root()."&id=$userid";
    }
    function _buildWhere($filters)
    {
        $integrationArray=$this->getIntegrationArray();
        $where=array();
        if (count($filters))
            foreach($filters as $key=>$value)
            {
                if (is_array($value))
                {
                    foreach ($value as $k=>$v)
                        if($integrationArray[$k]){
                        if (strpos($v,'%')!==FALSE)
                            $w[]='profile.'.$integrationArray[$k]." LIKE '$v'";
                        else
                            $w[]='profile.'.$integrationArray[$k]."='$v'";
                        }
                    if (count($w))
                        $where[]='('.implode(' OR ',$w).')';        
                    
                }elseif($integrationArray[$key]){
                    if (strpos($value,'%')!==FALSE)
                        $where[]='profile.'.$integrationArray[$key]." LIKE '$value'";
                    else
                        $where[]='profile.'.$integrationArray[$key]."='$value'";
                }
            }
        return $where;
    }
    function getUserList($limitstart=0,$limit=30,$filters=null,$ordering=null)
    {
        $db= JFactory::getDBO();
        $integrationArray=$this->getIntegrationArray();
        $integrationFields=$this->getIntegrationFields();
        
        $colums=array('u.username','profile.*');
        
        foreach($integrationFields as $field)
        {
            if ($key=='googleX' || $key=='googleY') 
                continue; 
            if ($integrationArray[$field])
                $colums[]='profile.'.$integrationArray[$field]." as `{$field}`";
            else
                $colums[]="'' as `{$field}`"; //field not defined, thus provided empty
        }
		$colums[] = " g.lat as googleX";
		$colums[] = " g.lng as googleY";
                 
        $query="select ".implode(',',$colums);
        
        $query.=" from #__users u ";
        $query.=" left join #__lovefactory_profiles profile on u.id=profile.user_id ";
        $query.=" left join #__lovefactory_geo g on u.id=g.user_id ";

        $where=$this->_buildWhere($filters);
        
        if (count($where))
            $query.=" WHERE ".implode(',',$where);
        
        if (count($ordering))
            $query.=" ORDER BY ".implode(',',$ordering);
            
        $db->setQuery($query,$limitstart,$limit);
        $list=$db->loadObjectList();
        return $list;
        
    }
    function getUserListCount($filters=null)
    {
        $db= JFactory::getDBO();
        $query="select count(*) ";
        
        $query.=" from #__users u ";
        $query.=" left join #__lovefactory_profiles profile on u.id=profile.user_id ";

        $where=$this->_buildWhere($filters);
        
        if (count($where))
            $query.=" WHERE ".implode(',',$where);
        
        $db->setQuery($query);
        $total=$db->loadResult();
        
        return $total;
    }	
    function checkProfile($userid=null)
    {
        return true;
    }    
	
}

?>
