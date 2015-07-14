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

class JTheFactoryIntegrationComponent extends JTheFactoryIntegration
{
    var $mode="component";
    var $table=null;
    var $keyfield='userid';

    function __construct()
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $this->table=$MyApp->getIniValue("table","extended-profile");
        $this->keyfield=$MyApp->getIniValue("field_userid","extended-profile");
    }
    function detectIntegration()
    {
	    return true;//Native Integration is always available
    }
    function getUserProfile($userid=0) {
        if (!$userid){
            $user=JFactory::getUser();
            $userid=$user->id;
        }
        $db=JFactory::getDbo();
        $db->setQuery("select * from `".$this->table."` where `".$this->keyfield."`='$userid'");
        $userdata = $db->loadObject(); 
        if (!$userdata){
            $fields=$db->getTableColumns($this->table);
            foreach($fields as $field=>$type) {
                $fields[$field]='';
            }
            return $fields;
        }else
            return get_object_vars($userdata);
    }

    function getIntegrationArray()
    {
        //Native profile has simetric integration array
        $flist=self::getIntegrationFields();
        $intarray=array();
        foreach($flist as $k=>$v)
        {
            $intarray[$v]=$v;
        }

        return $intarray;
    }
    function getProfileLink($userid)
    {
        //abstract
        return null;
    }
    function _buildWhere($filters)
    {
        $integrationArray=$this->getIntegrationArray();
		$fields = CustomFieldsFactory::getSearchableFieldsList("user_profile");
/*
    HUH?
        for($i=0;$i<count($fields);$i++)
            $integrationArray[$fields[$i]["row_id"]]=$fields[$i]["row_id"];
            
*/
        $where=array();
        if (count($filters))
            foreach($filters as $key=>$value)
                if (is_array($value))
                {
                    foreach ($value as $k=>$v)
                    {
                        if(isset($integrationArray[$k]) && $integrationArray[$k]){
                        if (strpos($v,'%')!==FALSE)
                            $w[]='profile.'.$integrationArray[$k]." LIKE '$v'";
                        else
                            $w[]='profile.'.$integrationArray[$k]."='$v'";
                        }
                        if($k=='username')
                        {
                            if (strpos($v,'%')!==FALSE)
                                $w[]="u.username LIKE '$v'";
                            else
                                $w[]="u.username='$v'";
                        }
                    }
                    if (count($w))
                        $where[]='('.implode(' OR ',$w).')';

                }else
                {
                    if(isset($integrationArray[$key]) &&  $integrationArray[$key]){
                        if (strpos($value,'%')!==FALSE)
                            $where[]='profile.'.$integrationArray[$key]." LIKE '$value'";
                        else
                            $where[]='profile.'.$integrationArray[$key]."='$value'";
                    }
                    if($key=='username')
                    {
                        if (strpos($v,'%')!==FALSE)
                            $w[]="u.username LIKE '$v'";
                        else
                            $w[]="u.username='$v'";
                    }
                }
        return $where;
    }

    function getUserList($limitstart=0,$limit=30,$filters=null,$ordering=null)
    {
        $db= JFactory::getDBO();

        $integrationArray=$this->getIntegrationArray();

        $colums=array('u.username','profile.*');

        $query="select ".implode(',',$colums);

        $query.=" from #__users u ";
        $query.=" left join `{$this->table}` profile on u.id=profile.`{$this->keyfield}` ";

        $where=$this->_buildWhere($filters);
        if (count($where))
            $query.=" WHERE ".implode(' AND ',$where);

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
        $query.=" left join `{$this->table}` profile on u.id=profile.`{$this->keyfield}` ";

        $where=$this->_buildWhere($filters);

        if (count($where))
            $query.=" WHERE ".implode(' AND ',$where);

        $db->setQuery($query);
        $total=$db->loadResult();

        return $total;
    }
    function checkProfile($userid=null)
    {

        if (!$userid){
            $user= JFactory::getUser();
            $userid=$user->id;
        }

        $db= JFactory::getDBO();
        $query="select count(*) ";
        $query.="from `{$this->table}` profile where profile.`{$this->keyfield}`='{$userid}'";

        $db->setQuery($query);
        $total=$db->loadResult();

        return $total;

    }

}
