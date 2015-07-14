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
 * @subpackage: mailman
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryMailmanController extends JTheFactoryController
{
    var $name='Mailman';
    var $_name='Mailman';
    
    function Mails()
    {
        $current_mail_type=JRequest::getVar('mail_type');
        
        $model= JModel::getInstance('Mailman','JTheFactoryModel');
        $rows=$model->getMailList();
        
        $current_mail=$rows[0];
        $options=array();
        foreach($rows as $row)
        {
            $options[]=JHtml::_('select.option',$row->mail_type,JText::_(APP_PREFIX.'_mail_'.$row->mail_type));
            if ($row->mail_type==$current_mail_type)
                $current_mail=$row;
        }
        $select_html=JHtml::_('select.genericlist',$options,'mail_type',"onchange='this.form.submit();'",'value','text',$current_mail_type);
       	
        $title= JText::_(APP_PREFIX.'_mail_'.$current_mail->mail_type);
        $help = JText::_(APP_PREFIX.'_mail_'.$current_mail->mail_type.'_help');
        $editor= JFactory::getEditor();
        
        $view=$this->getView('mails');  
        $view->assignRef('mail_type',$current_mail_type);
        $view->assignRef('rows',$rows);
        $view->assignRef('current_mail',$current_mail);
        $view->assignRef('title',$title);
        $view->assignRef('help',$help);
        $view->assignRef('mailtype_select',$select_html);
        $view->assignRef('editor',$editor);
        $view->assign('shortcuts',$model->getShortcuts());
        
        $view->display('mail');
        
        return;
        
    }
    function Save()
    {
		$mailtype = JRequest::getVar('mail_type');
		$subject=JRequest::getVar('subject','');
		$content=JRequest::getVar('mailbody','','default','none',JREQUEST_ALLOWRAW);
		$enabled=JRequest::getVar('enabled',0);
        
        $row= JTable::getInstance('MailmanTable','JTheFactory');
    
        if (!$enabled)
        {
            $row->load($mailtype);
            $row->enabled=0;
            
        }else
        {
            $row->bind(JRequest::get('post'));
            $row->content=JRequest::getVar('mailbody','','default','none',JREQUEST_ALLOWRAW);
        }
        $row->store();
        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=mailman.mails&mail_type='.$mailtype,JText::_("FACTORY_MAILTEXT_SAVED"));
        return;
    }
}

