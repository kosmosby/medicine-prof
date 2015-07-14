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
 * @subpackage: smarty
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class JTheFactorySmartyView extends JViewLegacy
{
    var $smarty=null;
    function __construct()
    {
        parent::__construct();
        $this->smarty=new JTheFactorySmarty();
    }
    
    public function display($tmpl = null)
    {
		$app	= JFactory::getApplication();
		$params = $app->getParams();
		if (!$this->smarty->get_template_vars('page_title') && $params->get('show_page_title', 1))
        {
			$page_title = $this->escape($params->get('page_title'));
			if($page_title)
            {
                $this->smarty->assign("page_title", $page_title);
            }
		}
       
		//add alternate feed link
		if($params->get('show_feed_link', 1) && file_exists(dirname(__FILE__).'view.feed.php'))
		{
			$document = JFactory::getDocument();
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

        $MyApp = JTheFactoryApplication::getInstance();
        if($MyApp->getIniValue('use_custom_fields')) {
            JHtml::addIncludePath($MyApp->app_path_admin.DS."fields".DS."html");
        }

        $this->smarty->display($tmpl);
    }
    public function assign()
    {
		// get the arguments; there may be 1 or 2.
		$arg0 = @func_get_arg(0);
		$arg1 = @func_get_arg(1);

		// assign by object
		if (is_object($arg0))
		{
			// assign public properties
			foreach (get_object_vars($arg0) as $key => $val)
			{
				if (substr($key, 0, 1) != '_') {
					$this->smarty->assign($key , $val);
				}
			}
			return true;
		}

		// assign by associative array
		if (is_array($arg0))
		{
			foreach ($arg0 as $key => $val)
			{
				if (substr($key, 0, 1) != '_') {
					$this->smarty->assign($key , $val);
				}
			}
			return true;
		}

		// assign by string name and mixed value.

		// we use array_key_exists() instead of isset() becuase isset()
		// fails if the value is set to null.
		if (is_string($arg0) && substr($arg0, 0, 1) != '_' && func_num_args() > 1)
		{
			$this->smarty->assign($arg0  , $arg1);
			return true;
		}

		// $arg0 was not object, array, or string.
		return false;
        
    }
	public function assignRef($key, &$val)
	{
		if (is_string($key) && substr($key, 0, 1) != '_')
		{
			$this->smarty->assign_by_ref($key , $val);
			return true;
		}

		return false;
	}
    
}

?>
