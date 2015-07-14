<?php
/**
 * 			JOOOID Controller
 * @version			1.0.0
 * @package			JOOOID for Joomla!
 * @copyright			Copyright (C) 2007-2011 Joomler!.net. All rights reserved.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * @author			Yoshiki Kozaki : joomlers@gmail.com
 * @link			http://www.joomler.net/
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

function joooid_log($data){
}



if(class_exists('JControllerLegacy')) {
	class Joooid_JController extends JControllerLegacy {}
} else {
	class Joooid_JController extends JController {}
}

class JOOOIDController extends Joooid_JController{

	public function service()
	{
		try{
			//error_reporting(0);
			$app = JFactory::getApplication();

			$params = JComponentHelper::getParams('com_joooid');

			$GLOBALS['joooid_debug']=$params->get('debug', JDEBUG);
			joooid_log("########## ".date(DATE_RFC822)."\nRichiesta:\n ###########\n");

			$plugin = $params->get('plugin', 'movabletype');

			JPluginHelper::importPlugin('xmlrpc');
			$allCalls = $app->triggerEvent('onGetWebServices');
			if(count($allCalls) < 1){
				JError::raiseError(404, JText::_('COM_JOOOID_SERVICE_WAS_NOT_FOUND'));
			}

			$methodsArray = array();

			foreach ($allCalls as $calls) {
				$methodsArray = array_merge($methodsArray, $calls);
			}

			@mb_regex_encoding('UTF-8');
			@mb_internal_encoding('UTF-8');

			require_once dirname(__FILE__).JOOOID_DS.'libraries'.JOOOID_DS.'phpxmlrpc'.JOOOID_DS.'xmlrpc.php';
			require_once dirname(__FILE__).JOOOID_DS.'libraries'.JOOOID_DS.'phpxmlrpc'.JOOOID_DS.'xmlrpcs.php';
			require_once (JPATH_SITE.JOOOID_DS.'components'.JOOOID_DS.'com_content'.JOOOID_DS.'helpers'.JOOOID_DS.'route.php');
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_content/tables');

			$xmlrpc = new xmlrpc_server($methodsArray, false);
			$xmlrpc->functions_parameters_type = 'phpvals';

			$encoding = 'UTF-8';

			$xmlrpc->xml_header($encoding);
			$GLOBALS['xmlrpc_internalencoding'] = $encoding;
			$xmlrpc->setDebug($params->get('debug', JDEBUG));

			$data = file_get_contents('php://input');
			

			if (count($_FILES)>0&& isset($_POST['request'])){
				$data = $_POST['request'];
				
			}
			else if(empty($data)){
				JError::raiseError(403, JText::_('COM_JOOOID_INVALID_REQUEST'));
			}

			$xmlrpc->service($data);
			$this->clearSessions();
		}
		catch (Exception $e){
				joooid_log($e->getMessage());
				return $this->response($e->getMessage());
		}

		jexit();
	}

	protected function clearSessions(){
		$session = JFactory::getSession();
		$db = JFactory::getDBO();
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__session') .
			'WHERE session_id ="'.$session->getId().'"'
		);
		$db->execute();
	 }

	public function weblayout($preview=false)
	{
		require_once (JPATH_SITE.JOOOID_DS.'components'.JOOOID_DS.'com_content'.JOOOID_DS.'helpers'.JOOOID_DS.'route.php');

		$model = $this->getModel('Template');
		$this->addViewPath(JPATH_SITE.'/components/com_content/views');
		$view = $this->getView('Article', 'html', 'ContentView');
		$view->setModel($model, true);
		$doc = JFactory::getDocument();
		$view->assignRef('document', $doc);
		$view->addTemplatePath(JPATH_SITE.'/components/com_content/views/article/tmpl');
		$view->display();
		$view->document->setMetaData('title', '');
		return;
	}

	public function webpreview()
	{
		$this->weblayout(true);
	}
	

	protected function response($msg)
	{
		global $xmlrpcerruser;
		$trace = debug_backtrace();
		$caller = $trace;

		return new xmlrpcresp(0, $xmlrpcerruser + 1, $msg);
	}
}
