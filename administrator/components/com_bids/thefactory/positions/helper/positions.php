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
 * @subpackage: positions
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryPositionsHelper
{
    function htmlPageSelect($pages,$selectedpage=null)
    {

        $pageoptions=array();
        for($i=0;$i<count($pages);$i++)
            $pageoptions[]=JHtml::_('select.option',$pages[$i]->description,$pages[$i]->name);

        return JHtml::_('select.genericlist',$pageoptions,'page',"onchange='this.form.submit();'",'text','value',$selectedpage);

    }
    function htmlPositionSelect($positions,$selectedposition=null)
    {

        $options=array();
        for($i=0;$i<count($positions);$i++)
            $options[]=JHtml::_('select.option',$positions[$i]->name,$positions[$i]->name);

        return JHtml::_('select.genericlist',$options,'position',"onchange='this.form.submit();'",'text','value',$selectedposition);

    }
    function htmlFieldsMultiselect($fields,$selectname,$attrib='',$selected=null,$excludelist=null)
    {
        $options=array();
        for($i=0;$i<count($fields);$i++)
        {
            if ($excludelist){
                $exclude=false;
                for($j=0;$j<count($excludelist);$j++)
                    if($excludelist[$j]->name==$fields[$i]->name)
                    {
                        $exclude=true;
                        break;
                    }
                if ($exclude) continue;
            }
            $options[]=JHtml::_('select.option',$fields[$i]->name,$fields[$i]->id);
        }
        return JHtml::_('select.genericlist',$options,$selectname,"multiple size='10' style='width:200px;' ".$attrib,'text','value',$selected);
        
    }
}


?>
