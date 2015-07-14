<?php
/**
* @version $Id: cbsubs.content_access.php 428 2010-01-26 11:11:34Z brunner $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use Joomla\Registry\Registry as JRegistry;		// For php-doc comments only, as not J 2.5-compatible!

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CBSUBS_DEBUG;
if ( isset( $_CBSUBS_DEBUG ) && $_CBSUBS_DEBUG ) {
	global $_CB_PAIDBOT_OLD_DISPLAY_ERRORS;
	$_CB_PAIDBOT_OLD_DISPLAY_ERRORS		=	ini_get( 'display_errors' );
	ini_set('display_errors',true);
	global $_CB_PAIDBOT_OLD_ERRORS;
	$_CB_PAIDBOT_OLD_ERRORS				=	error_reporting(E_ALL);
}

$mainframe			=	JFactory::getApplication();

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.user.authorization' );
jimport( 'phpgacl.gacl' );
jimport( 'phpgacl.gacl_api' );

jimport('joomla.access.access');

class cbpaidFixAcl extends JAccess
{
}

$mainframe->registerEvent( 'onAfterRender', 'cbpaidsubsbot_onAfterRender');	// J1.6
$mainframe->registerEvent( 'onContentBeforeDisplay', 'cbpaidsubsbot_onContentBeforeDisplay');	// J1.6
$mainframe->registerEvent( 'onAfterInitialise', 'cbpaidsubsbot_onStart');
$mainframe->registerEvent( 'onAfterRoute', 'cbpaidsubsbot_onAfterStart');
$mainframe->registerEvent( 'onAfterDispatch', 'cbpaidsubsbot_onAfterDispatch_Alt');
// $mainframe->registerEvent( 'onPrepareContent', 'cbpaidsubsbot_onPrepareContent_Alt' );
$mainframe->registerEvent( 'onBeforeDisplayContent', 'cbpaidsubsbot_onBeforeDisplayContent');	//Same

/**
 * CBSubs ACL Class
 */
class cbpaidBotAclApi extends cbpaidFixAcl {
	public $cbContentAclRights;
	/**
	 * Instanciator
	 *
	 * @return cbpaidBotAclApi|JAccess
	 */
	public static function & getInstance( ) {
			/**
		 * Our ACL
		 * @var cbpaidBotAclApi
		 */
		static $_cbACL				=	null;
		if ( ! $_cbACL ) {

			/** @noinspection PhpUnusedLocalVariableInspection */
			static $universalAcl;

			$universalAcl	=	JFactory::getACL();

			$universalAcl		=	new cbpaidBotAclApi( $universalAcl );
			$_cbACL				=	$universalAcl;
		}
		return $_cbACL;
	}
	/**
	 * Constructor
	 *
	 * @param $previousAclApi
	 */
	public function __construct( &$previousAclApi ) {
		foreach (  array_keys( get_object_vars( $previousAclApi ) ) as $k ) {
			$this->$k				=&	$previousAclApi->$k;
		}
		$this->cbContentAclRights	=	array( 'canPublishContent', 'canEditAllContent', 'canEditOwnContent', 'canAddAllContent' );
		// global $option;
		// $this->_setAdminPaths( $option, $this->getCfg( 'absolute_path' ) );
	}
	/**
	 * Decodes ACL action
	 *
	 * @param  array            $aclArray
	 * @return int|null
	 */
	protected function _cb_decodeAclAction( $aclArray ) {
		global $_CB_framework;

		$cmsAcl				=	$_CB_framework->_cms_all_acl();
		$nAclArr			=	count( $aclArray );
		$n					=	0;
		$i					=	0;
		$k					=	null;

		foreach ( $cmsAcl as $k => $acl ) {
			$n				=	min( $nAclArr, count( $acl ) );
			for ( $i = 0; $i < $n; $i++ ) {
				if ( ( $acl[$i] != $aclArray[$i] ) && ( $acl[$i] !== null ) ) {
					break;
				}
			}
			if ( $i == $n ) {
				break;
			}
		}
		if ( $i == $n ) {
			return $k;
		} else {
			return null;
		}
	}
	/**
	 * Gets Section, Category of article
	 *
	 * @param  int       $articleId
	 * @param  int|null  $categoryId  OUT
	 * @param  int|null  $sectionId   OUT
	 * @return boolean
	 */
	protected function _cb_getSectionCategoryOfArticle( $articleId, &$categoryId, &$sectionId ) {
		global $_CB_database;

		static $cache					=	array();

		$articleId						=	(int) $articleId;
		if ( ! isset( $cache[$articleId] ) ) {
			$query					=	"SELECT `catid` FROM #__content WHERE id = " . (int) $articleId;
			$_CB_database->setQuery( $query );
			$result						=	null;
			if ( $_CB_database->loadObject( $result ) ) {
				/** @var StdClass $result */
				$result->sectionid		=	null;
				$cache[$articleId]		=	array( $result->sectionid, $result->catid );
			} else {
				$cache[$articleId]		=	false;
			}
		}
		if ( $cache[$articleId] == false ) {
			return false;
		} else {
			$sectionId					=	$cache[$articleId][0];
			$categoryId					=	$cache[$articleId][1];
			return true;
		}
	}
	/**
	 * Checks one access right (with caching)
	 *
	 * @param  int     $userId
	 * @param  string  $kind                    kind of access: 'contents', 'categories', 'sections'
	 * @param  int     $id
	 * @param  int     $level                   -1 = default = read access, otherwise for write-access, index in $this->cbContentAclRights[]
	 * @param  boolean $returnControllingPlans  if TRUE: return controlling plans instead of FALSE
	 * @return null
	 */
	protected function _cb_checkItemAcl( $userId, $kind, $id, $level = -1, $returnControllingPlans = false ) {
		static $cache				=	array();
		if ( $id ) {
			if ( ! isset( $cache[$userId][$kind][$id][$level][$returnControllingPlans] ) ) {
				if ( $level == -1 ) {
					$action			=	'';		// read-access
				} else {
					$action			=	'_' . $this->cbContentAclRights[$level];
				}
				$cache[$userId][$kind][$id][$level][$returnControllingPlans]	=	cbpaidBot::getInstance()->checkAccess( $userId, $id,  'cpaycontent_' . $kind . $action, null, $returnControllingPlans );
			}
			return $cache[$userId][$kind][$id][$level][$returnControllingPlans];
		}
		return null;
	}
	/**
	 * Checks multi-level ACL
	 *
	 * @param  int|null  $userId
	 * @param  int|null  $articleId
	 * @param  int|null  $categoryId
	 * @param  int|null  $sectionId
	 * @param  int|null  $contentAclLevel
	 * @return boolean|null
	 */
	public function _cb_checkMultiAcl( $userId, $articleId, $categoryId, $sectionId, $contentAclLevel ) {
		$access						=	null;
		$accessBlocked				=	false;

		for ( $i = min( 0, $contentAclLevel ); $i <= $contentAclLevel; $i++ ) {
			$access					=	$this->_cb_checkItemAcl( $userId, 'contents', $articleId, $i );
			if ( $access === true ) {
				break;
			}
			if ( $access === false ) {
				$accessBlocked	=	true;
			}
			if ( $articleId && ( ( ! $categoryId ) || ( ! $sectionId ) ) ) {
				// if articleId is set, but not category and section ids, get them from database:
				$this->_cb_getSectionCategoryOfArticle( $articleId, $categoryId, $sectionId );
			}
			$access					=	$this->_cb_checkItemAcl( $userId, 'categories', $categoryId, $i );
			if ( $access === true ) {
				break;
			}
			if ( $access === false ) {
				$accessBlocked	=	true;
			}
			$access					=	$this->_cb_checkItemAcl( $userId, 'sections', $sectionId, $i );
			if ( $access === true ) {
				break;
			}
			if ( $access === false ) {
				$accessBlocked	=	true;
			}
		}
		if ( $accessBlocked && ( $access === null ) ) {
			$access					=	false;
		}
		return $access;
	}
	/**
	 * Checks multi-level ACL and applicable plans
	 *
	 * @param  int|null  $userId
	 * @param  int|null  $articleId
	 * @param  int|null  $categoryId
	 * @param  int|null  $sectionId
	 * @param  int|null  $contentAclLevel
	 * @return array|null
	 */
	public function _cb_checkMultiAcl_Ok_or_Plans( $userId, $articleId, $categoryId, $sectionId, $contentAclLevel ) {
		$access						=	null;
		$accessBlocked				=	false;
		$plansToAccess				=	array();

		for ( $i = min( 0, $contentAclLevel ); $i <= $contentAclLevel; $i++ ) {
			$access					=	$this->_cb_checkItemAcl( $userId, 'contents', $articleId, $i, true );
			if ( $access === true ) {
				break;
			}
			if ( is_array( $access ) ) {
				$accessBlocked		=	true;
				$plansToAccess		=	array_merge( $plansToAccess, $access );
			}
			if ( $articleId && ( ( ! $categoryId ) || ( ! $sectionId ) ) ) {
				// if articleId is set, but not category and section ids, get them from database:
				$this->_cb_getSectionCategoryOfArticle( $articleId, $categoryId, $sectionId );
			}
			$access					=	$this->_cb_checkItemAcl( $userId, 'categories', $categoryId, $i, true );
			if ( $access === true ) {
				break;
			}
			if ( is_array( $access ) ) {
				$accessBlocked		=	true;
				$plansToAccess		=	array_merge( $plansToAccess, $access );
			}
			$access					=	$this->_cb_checkItemAcl( $userId, 'sections', $sectionId, $i, true );
			if ( $access === true ) {
				break;
			}
			if ( is_array( $access ) ) {
				$accessBlocked		=	true;
				$plansToAccess		=	array_merge( $plansToAccess, $access );
			}
		}
		if ( $accessBlocked && ( $access !== true ) ) {
			return $plansToAccess;
		}
		return $access;
	}
	/**
	 * Checks ACL based on $_REQUEST
	 *
	 * @param  string       $aco_section_value
	 * @param  string       $aco_value
	 * @param  string       $aro_section_value
	 * @param  string       $aro_value
	 * @param  string|null  $axo_section_value
	 * @param  string|null  $axo_value
	 * @return int
	 */
	public function acl_check( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value = null, $axo_value = null ) {
		global $_CB_framework, $_REQUEST, $_POST;

		$parentAcl						=	parent::acl_check( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value );
		if ( $parentAcl == 1 ) {
			return $parentAcl;
		}
		if ( cbpaidBot::getInstance()->paidsubsManager === null ) {
			return 0;
		}

		$action							=	$this->_cb_decodeAclAction( array( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value ) );

		$contentAclLevel				=	array_search( $action, $this->cbContentAclRights );

		if ( ( $contentAclLevel !== false )
/*		if (	( $aco_section_value == 'action' )
			&&	( ( $aco_value == 'edit' ) || ( $aco_value == 'publish' ) )
			&&	( $aro_section_value == 'users' )
			&&	( $axo_section_value == 'content' )
*/
			/* &&	( isset( $_REQUEST['task'] ) ) */
		)
		{
			$sectionId					=	null;
			$categoryId					=	null;
			$articleId					=	null;
			switch ( cbGetParam( $_REQUEST, 'task', '' ) ) {
				case '':
				case 'view':
				case 'edit':
		//BB1.5???			if ( cbGetParam( $_REQUEST, 'view', '' ) == 'category' ) {			//FIXME ???
		//BB1.5???				$categoryId		=	(int) cbGetParam( $_REQUEST, 'id', 0 );
		//BB1.5???			} else {
					$articleId			=	(int) cbGetParam( $_REQUEST, 'id', 0 );
		//BB1.5???			}
					$sectionId			=	0;
					break;
				case 'section':
					$sectionId			=	(int) cbGetParam( $_REQUEST, 'id', 0 );
					break;
				case 'category':
					$categoryId			=	(int) cbGetParam( $_REQUEST, 'id', 0 );
					$sectionId			=	(int) cbGetParam( $_REQUEST, 'sectionid', 0 );
					break;
				case 'new':
					$sectionId			=	(int) cbGetParam( $_REQUEST, 'sectionid', 0 );
					break;
				case 'save':
				case 'apply':
				case 'apply_new':
					$sectionId			=	(int) cbGetParam( $_POST, 'sectionid', 0 );
					$categoryId			=	(int) cbGetParam( $_POST, 'catid', 0 );
					$articleId			=	(int) cbGetParam( $_POST, 'id', 0 );
					break;
				case 'cancel':
				case 'blogsection':
				case 'blogcategorymulti':
				case 'blogcategory':
				case 'archivesection':
				case 'archivecategory':
				case 'emailform':
				case 'emailsend':
				case 'vote':
				default:
					break;
			}
			if ( $sectionId !== null ) {
				if ( $this->_cb_checkMultiAcl( $_CB_framework->myId(), $articleId, $categoryId, $sectionId, $contentAclLevel ) ) {
					return 1;
				}
			}
		}
		return 0;	// parent::acl_check( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value );
	}
}	// class cbpaidBotAclApi

// In 1.6+, there is a bug in ModuleHelper class as _load is called but assigned without reference, so we need to redo a wrapper class to call the protected function:
if ( class_exists( 'JModuleHelper' ) && ( ! is_callable( array( 'JModuleHelper', '_load' ) ) ) ) {
	/**
	 * Class to fix JModuleHelper in 1.6+
	 */
	class cbpaidAccessModuleHelper extends JModuleHelper {
		/**
		 * My own load function
		 *
		 * @return array
		 */
		public static function &myLoad( ) {
			return JModuleHelper::_load();
		}
	}
}

/**
 * Class cbpaidBotInput
 * Implements an Array to access RAW query variables (including JInput)
 */
class cbpaidBotInput implements \ArrayAccess
{
	/**
	 * @var array
	 */
	protected $get;
	/**
	 * @var array
	 */
	protected $post;
	/**
	 * @var array
	 */
	protected $request;
	/**
	 * @var \JInput
	 */
	protected $input;

	/**
	 * Constructor
	 * @param  array                        $get
	 * @param  array                        $post
	 * @param  \JInput|\Joomla\Input\Input  $input
	 */
	public function __construct( array $get, array $post, array $request, $input )
	{
		$this->get		=	$get;
		$this->post		=	$post;
		$this->request	=	$request;
		$this->input	=	$input;
	}

	/**
	 * Check if a value name exists.
	 *
	 * @param  string   $name  Value name
	 * @return boolean
	 */
	public function offsetExists( $name )
	{
		return isset( $this->get[$name] ) || isset( $this->post[$name] ) || isset( $this->request[$name] ) || ( $this->input->get( $name, null ) !== null );
	}

	/**
	 * Implements ArrayAccess: Gets a value from the input data.
	 *
	 * @param  string  $offset    Name of the value to get.
	 * @return mixed             The filtered input value.
	 */
	public function offsetGet( $offset )
	{
		if ( $this->input->get( $offset, null ) !== null ) {
			return $this->input->get( $offset, null, 'raw' );
		}

		if ( isset( $this->get[$offset] ) ) {
			return $this->get[$offset];
		}

		if ( isset( $this->post[$offset] ) ) {
			return $this->post[$offset];
		}

		if ( isset( $this->request[$offset] ) ) {
			return $this->request[$offset];
		}

		return null;
	}

	/**
	 * Implements ArrayAccess: Sets a value to the input data.
	 *
	 * @param  string  $offset
	 * @param  mixed  $value
	 */
	public function offsetSet( $offset, $value )
	{
		$this->get[$offset]		=	$value;
	}

	/**
	 * Implements ArrayAccess: Unsets a value to the input data.
	 *
	 * @param  string  $offset
	 */
	public function offsetUnset( $offset )
	{
		unset( $this->get[$offset] );
	}
}

/**
 * CBSubs bot implementation
 */
class cbpaidBot {
	/**
	 * Paid subscriptions manager
	 * @var cbpaidSubscriptionsMgr
	 */
	public $paidsubsManager			=	null;
	public $cbCmsVersion				=	null;
	public $option						=	null;
	public $task						=	null;
	public $view						=	null;
	public $taskPlugin					=	null;
	/**
	 * Constructor
	 */
	public function __construct( ) {
		$this->cbCmsVersion		=	1;
		$input					=	JFactory::getApplication()->input;
		$this->option			=	$input->getString('option', null);
		$this->task				=	$input->getString('task', null);
		$this->view				=	$input->getString('view', null);
		if ( ( $this->option == 'com_comprofiler' ) && ( $this->task == 'pluginclass' ) ) {
			$this->taskPlugin	=	$input->getString('plugin', null);
		}
		$this->paidsubsManager		=&	cbpaidSubscriptionsMgr::getInstance();
	}

	/**
	 * Self-Instanciator
	 *
	 * @return cbpaidBot
	 */
	public static function getInstance( ) {
		static $me	=	null;

		if ( $me === null ) {
			$me		=	new self();
		}
		return $me;
	}
	/**
	 * Event handler as soon as system started
	 *
	 * @return void
	 */
	public function onAlittleMoreAfterStart( ) {
		global $_CB_framework, $_GET, $_POST;

		if ( $this->paidsubsManager === null ) {
			return;
		}
		// already done in SysPlug: $this->_checkExpireMe();
		$userId							=	$_CB_framework->myId();

		$getPostArray					=	new cbpaidBotInput( $_GET, $_POST, $_REQUEST, JFactory::getApplication()->input );


		// redirection trick for joomla "Register to read more link":
		if ( ( ( $this->option == 'com_user' ) && ( ( $this->task == 'register' ) || ( $this->view == 'login' ) ) )		// 1.5
			|| ( ( $this->option == 'com_registration' ) && ( $this->task == 'register' ) ) )							// mambo & 1.0
		{
			cbRedirect( cbSef( 'index.php?option=com_comprofiler&task=registers', false ) );
		}

		$message						=	null;
		$allowAccess					=	$this->checkAccess( $userId, $this->option, 'cpaycontent_components' );
		if ( $allowAccess === false ) {
			$message					=	"Access to this component not allowed without %s";
			$redirectVars				=	array( 'accesstype' => 'components', 'accessvalue' => $this->option );
		} elseif ( isset( $getPostArray['Itemid'] ) ) {
			if ( ( $this->option == 'com_comprofiler' ) && ( in_array( strtolower( $this->task ), array( 'fieldclass', 'tabclass', 'lostpassword', 'sendnewpass', 'registers', 'saveregisters', 'login', 'logout', 'confirm', 'teamcredits', 'done', 'performcheckusername', 'performcheckemail' ) ) || ( ( $this->task == 'pluginclass' ) && ( $this->taskPlugin == 'cbpaidsubscriptions' ) ) ) ) {
				// legit CB or CBSubs access that should not be protected by menu !
			} else {
				$allowAccess			=	$this->checkAccess( $userId, (int) $getPostArray['Itemid'], 'cpaycontent_menus' );
				if ( $allowAccess === false ) {
					$message			=	"Access to this menu item not allowed without %s";
					$redirectVars		=	array( 'accesstype' => 'menus', 'accessvalue' => (int) $getPostArray['Itemid'] );
				}
			}
		}
		$postsMissingInGetToFindPlans =	array();
		if ( $allowAccess !== false ) {
			$allowAccess			=	$this->checkAccessUrl( $userId, $getPostArray, $_GET, $postsMissingInGetToFindPlans, 'cpaycontent_urls' );
			if ( $allowAccess === false ) {
				$message			=	"Access to this location not allowed without %s";
				$redirectVars		=	array( 'accesstype' => 'urls' );	// , 'accessvalue' => cbpaidsubsbot_encodeArrayUrl( $_GET ) );
			} else {
				$redirectVars		=	array();
				$allowAccess		=	$this->checkContentUrl( $userId, $getPostArray, 'cpaycontent_sections', 'cpaycontent_categories', 'cpaycontent_sections_list', 'cpaycontent_categories_list', $redirectVars );
				if ( $allowAccess === false ) {
					$message		=	"Access to this content list not allowed without %s";
					// done below $redirectVars['accessurl']	=	cbpaidsubsbot_encodeArrayUrl( $_GET );
				}
			}
		}
		// if ( ( $allowAccess === false ) && ( ! ( ( $this->option == 'com_comprofiler' ) && ( $this->task == 'pluginclass' ) && ( $this->taskPlugin == 'cbpaidsubscriptions' ) ) ) ) {
		if ( $allowAccess === false ) {
			$allowedComprofilerTasks	=	array( 'fieldclass', 'tabclass', 'lostpassword', 'sendnewpass', 'registers', 'saveregisters', 'login', 'logout', 'confirm', 'teamcredits', 'done', 'performcheckusername', 'performcheckemail' );
			if ( ( $this->option != 'com_comprofiler' ) || ( ! in_array( strtolower( $this->task ), $allowedComprofilerTasks ) ) || ! ( ( $this->task == 'pluginclass' ) && ( $this->taskPlugin == 'cbpaidsubscriptions' ) ) ) {
				$params							=&	cbpaidApp::settingsParams();

				// allow access to someone who is unrestricted:
				if ( ! $this->hasAccessAnyway( $userId ) ) {
					// not someone who is unrestricted:
					$redirectVars['accessurl']	=	cbpaidsubsbot_encodeArrayUrl( array_merge( $_GET, $postsMissingInGetToFindPlans ) );
					$redirectUrl				=	'index.php?option=com_comprofiler&task=pluginclass&plugin=cbpaidsubscriptions&do=accessdenied' . getCBprofileItemid( false);		// &Itemid= ???
					if ( is_array( $redirectVars ) ) {
						foreach ( $redirectVars as $k => $v ) {
							$redirectUrl		.=	'&' . urlencode( $k ) . '=' . urlencode( $v );
						}
					}
// if ( strlen( $redirectUrl ) > 1000 ) { echo $redirectUrl . '<br />'; var_dump( $this );exit; }
					// translate message:
					cbpaidApp::loadLang();
					$subscriptionText			=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
					$message					=	sprintf( CBPTXT::T( $message ), $subscriptionText );
					$_CB_framework->redirect( cbSef( $redirectUrl, false ), $message, 'warning' );
				}
			}
		}
	}

	/**
	 * Checks modules access
	 *
	 * @param  int      $userId
	 * @param  boolean  $initCallable
	 * @return void
	 */
	public function _checkModules( $userId, $initCallable ) {
		if ( $this->hasAccessAnyway( $userId ) ) {
			// user has access to all:
			return;
		}
		$modulesId					=	array();
		if ( class_exists( 'JModuleHelper' ) ) {
			// in backend, this class isn't loaded:

			// Re-load in case we switched to HTML in content.prepare use in IPNs:
			if ( ! is_callable( array( 'cbpaidAccessModuleHelper', 'myLoad' ) ) ) {
				include dirname( __FILE__ ) . '/cbsubs.content_helper.php';
			}

			$modules			=&	cbpaidAccessModuleHelper::myLoad();

			foreach ( $modules as $v ) {
				$modulesId[]		=	$v->id;
			}
		} else {
			// no modules to check: return
			return;
		}

		$alowedModules				=	$this->checkAccessArray( $userId, $modulesId, 'cpaycontent_modules' );

		if ( $initCallable ) {
			foreach ( $modules as $position => $modulesArray ) {
				foreach ( $modulesArray as $k => $v ) {
					if ( ! in_array( $v->id, $alowedModules ) ) {
						unset( $modules[$position][$k] );
					}
				}
			}
		} else {
			$remove					=	array();
			foreach ( $modules as $k => $v ) {
				if ( ! in_array( $v->id, $alowedModules ) ) {
					unset( $modules[$k] );
					$remove[]		=	$k;
				}
			}
			$this->_compactArray( $modules, $remove );
		}
	}
	/**
	 * Compacts the array $a, removing non-consecutive entries
	 *
	 * @param  array  $a
	 * @param  array  $remove
	 * @return void
	 */
	protected function _compactArray( &$a, $remove ) {
		if ( count( $remove ) > 0 ) {
			$i		=	0;
			foreach ( array_keys( $a ) as $k ) {
				if ( $i != $k ) {
					$a[$i]	=&	$a[$k];
					unset( $a[$k] );
				}
				$i++;
			}
		}			
	}
	/**
	 * Checks if needed to expire me
	 *
	 * @return void
	 */
	public function _checkExpireMe( ) {
		$this->paidsubsManager->checkExpireMe( 'system_mambot', null, true );
	}
	/**
	 * Finds the plans controlling an object
	 *
	 * @param  int          $object
	 * @param  string       $listName
	 * @param  string|null  $switchName
	 * @param  string       $forCause
	 * @return int[]
	 */
	protected function getControllingPlans( $object, $listName, $switchName = null, $forCause = 'any' ) {
		if ( $this->paidsubsManager === null ) {
			return array();
		}

		$plansMgr							=&	cbpaidPlansMgr::getInstance();
		// take any plans, not only those for this user:
		$plans								=&	$plansMgr->loadPublishedPlans( null, true, $forCause, null );

		$plansControlling					=	array();
		foreach ( $plans as $k => $plan ) {
			if ( ( $switchName == null ) || ( $plan->getParam( $switchName, null, 'integrations' ) == 1 ) ) {
				$cpaycontent_list			=	$plan->getParam( $listName, null, 'integrations' );
				if ( $cpaycontent_list ) {
					$controlledObjects		=	explode( '|*|', $cpaycontent_list );
					if ( in_array( $object, $controlledObjects ) ) {
						$plansControlling[]	=	$k;
					}
				}
			}
		}
		return $plansControlling;
	}
	/**
	 * Checks access of $userId to $object depending on $listName
	 * @param  int          $userId
	 * @param  int          $object
	 * @param  string       $listName
	 * @param  string|null  $switchName
	 * @param  boolean      $returnControllingPlans
	 * @return int[]|boolean|null
	 */
	public function checkAccess( $userId, $object, $listName, $switchName = null, $returnControllingPlans = false ) {
		if ( $this->paidsubsManager === null ) {
			return null;
		}

		$forCause							=	'any';
		// take any plans, not only those for this user:
		$plansControlling					=	$this->getControllingPlans( $object, $listName, $switchName, $forCause );

		$allowAccess						=	null;
		if ( count( $plansControlling ) ) {
			$allowAccess					=	false;
			$paidUserExtension				=	cbpaidUserExtension::getInstance( $userId );
			$subscriptions					=	$paidUserExtension->getUserSubscriptions( 'A', true );
			foreach ( $subscriptions as $sub ) {
				if ( in_array( $sub->plan_id, $plansControlling ) ) {
					$allowAccess			=	true;
					break;
				}
			}
		}
		if ( $allowAccess || ! $returnControllingPlans ) {
			return $allowAccess;
		} else {
			// we need to remove from the list of controlling plans the plans that cannot be upgraded to:
			$this->unsetPlansNotAllowedToBeProposed( $plansControlling, $forCause, $userId );
			return $plansControlling;
		}
	}

	/**
	 * Removes from the list of controlling plans $plansControlling the plans that cannot be upgraded to
	 * 
	 * @param  array  $plansControlling
	 * @param  string $forCause          for loadPublishedPlans
	 * @param  int    $userId            User id
	 */
	protected function unsetPlansNotAllowedToBeProposed( &$plansControlling, $forCause, $userId ) {
		global $_CB_framework;

		$plansMgr							=&	cbpaidPlansMgr::getInstance();
		$plans								=	$plansMgr->loadPublishedPlans( null, true, $forCause, null );
		foreach ( $plansControlling as $k => $planId ) {
			$resultTexts					=	array();
			if ( ! ( ( $_CB_framework->myId() ? ( ( $plans[$planId]->get( 'propose_upgrade' ) != 2 ) && $plans[$planId]->isPlanAllowingUpgradesToThis( $userId, $resultTexts ) ) : ( ( $plans[$planId]->get( 'propose_registration' ) != 2 ) && $plans[$planId]->isPlanAllowingRegistration() ) ) ) ) {
				unset( $plansControlling[$k] );
			}
		}
	}
	/**
	 * Checks access of array
	 *
	 * @param  int     $userId
	 * @param  int[]   $idsArray
	 * @param  string  $listName
	 * @return int[]
	 */
	protected function checkAccessArray( $userId, &$idsArray, $listName ) {
		$plansMgr							=&	cbpaidPlansMgr::getInstance();
		$forCause							=	'any';
		// take any plans, not only those for this user
		$plans								=&	$plansMgr->loadPublishedPlans( null, true, $forCause, null );

		$plansControlling					=	array();
		foreach ( $plans as $k => $plan ) {
			$cpaycontent_list				=	$plan->getParam( $listName, null, 'integrations' );
			if ( $cpaycontent_list ) {
				$controlledObjects			=	explode( '|*|', $cpaycontent_list );
				foreach ( $idsArray as $contId ) {
					if ( in_array( $contId, $controlledObjects ) ) {
						$plansControlling[$contId][]	=	$k;
					}
				}
			}
		}
	
		$allowedAccessIds					=	$idsArray;
		if ( count( $plansControlling ) ) {
			$activePlans					=	array();
			$paidUserExtension				=	cbpaidUserExtension::getInstance( $userId );
			$subscriptions					=	$paidUserExtension->getUserSubscriptions( 'A', true );
			foreach ( $subscriptions as $sub ) {
				$activePlans[]				=	$sub->plan_id;
			}

			foreach ( $plansControlling as $contId => $plansControllingThisId ) {
				if ( ! $this->_anyKeyInArray( $plansControllingThisId, $activePlans ) ) {			//TBD: Feature #1671 : this is the line where we could have positive object ids = show with plan, and negative ids = hide with plan, but we need a 3-cols UI for backend to do this.
					$k						=	array_search( $contId, $allowedAccessIds );
					if ( is_int( $k ) ) {
						unset( $allowedAccessIds[$k] );
					}
				}
			}
		}
		return $allowedAccessIds;
	}
	/**
	 * Checks access to URL
	 *
	 * @param  int      $userId
	 * @param  array    $getPostArray
	 * @param  array    $getArray
	 * @param  array    $postsMissingInGetToFindPlans  IN+OUT
	 * @param  string   $listName
	 * @param  boolean  $returnControllingPlans
	 * @return array|boolean|null
	 */
	public function checkAccessUrl( $userId, &$getPostArray, $getArray, &$postsMissingInGetToFindPlans, $listName, $returnControllingPlans = false ) {
		$plansMgr							=&	cbpaidPlansMgr::getInstance();
		$forCause							=	'any';
		// take any plans, not only those for this user:
		$plans								=&	$plansMgr->loadPublishedPlans( null, true, $forCause, null );

		$plansControlling					=	array();
		foreach ( $plans as $k => $plan ) {
			$cpaycontent_list				=	trim( $plan->getParam( $listName, null, 'integrations' ) );
			if ( $cpaycontent_list ) {
				$controlledObjects			=	explode( "\r\n", $cpaycontent_list );
				foreach ( $controlledObjects as $ctrlObj ) {
					$ctrlObjParts			=	array();
					self::_my_parse_str( $ctrlObj, $ctrlObjParts );
					if ( $this->_allKeysInArray( $ctrlObjParts, $getPostArray ) ) {
						$plansControlling[]	=	$k;
						// Now check if we needed a ley from the POST which was not in the GET to have this plan controlling it:
						if ( ! $this->_allKeysInArray( $ctrlObjParts, $getArray ) ) {
							$getArrayKeys	=	array_keys( $getArray );
							foreach ( $ctrlObjParts as $kk => $vv ) {
								if ( ! in_array( $kk, $getArrayKeys ) ) {
									// Indeed: list that key so we add it to the accessurl, so that on the redirect the plan can be found (fix for bug #2204):
									$postsMissingInGetToFindPlans[$kk]	=	$getPostArray[$kk];
								}
							}
						}
					}
				}
			}
		}
	
		$allowAccess						=	null;
		if ( count( $plansControlling ) ) {
			$allowAccess					=	false;
			$paidUserExtension				=	cbpaidUserExtension::getInstance( $userId );
			$subscriptions					=	$paidUserExtension->getUserSubscriptions( 'A', true );
			foreach ( $subscriptions as $sub ) {
				if ( in_array( $sub->plan_id, $plansControlling ) ) {
					$allowAccess			=	true;
					break;
				}
			}
		}
		if ( $allowAccess || ! $returnControllingPlans ) {
			return $allowAccess;
		} else {
			// we need to remove from the list of controlling plans the plans that cannot be upgraded to:
			$this->unsetPlansNotAllowedToBeProposed( $plansControlling, $forCause, $userId );
			return $plansControlling;
		}
	}
	/**
	 * Same as PHP's parse_str, but doesn't convert [] to arrays, and + to spaces (for regexes)
	 * 
	 * @param  string  $str
	 * @param  array   $arr (returned)
	 * @return void
	 */
	protected static function _my_parse_str( $str, &$arr ) {
		$arr				=	array();
		$pairs				=	explode("&", $str);
		foreach ($pairs as $pair) {
			list($k, $v)	=	explode( '=', $pair );
			$arr[$k]		=	$v;
		}
	}
	/**
	 * Checks access based on a content URL
	 *
	 * @param  int      $userId
	 * @param  array    $getArray
	 * @param  string   $listNameSection
	 * @param  string   $listNameCategory
	 * @param  string   $switchSection
	 * @param  string   $switchCategory
	 * @param  array    $redirectVars
	 * @return boolean|int[]|null
	 */
	protected function checkContentUrl( $userId, &$getArray, $listNameSection, $listNameCategory, $switchSection, $switchCategory, &$redirectVars ) {
		if (	( ! in_array( $this->option, array( 'com_content', 'content' ) ) )
			||	( ! isset( $getArray['task'] ) )
			||	( ! isset( $getArray['id'] ) )
		   )
		{
		   	return null;
		}
/* BB1.5???	//FIXME ?
		if ( $getArray['view'] == 'category' ) {
			$getArray['task'] = 'category';
		} elseif ( ! isset( $getArray['task'] ) ) {
			return null;
		}
*/
		switch ( trim( $getArray['task'] ) ) {
			case 'section':					// section list:
				$redirectVars['accesstype']		=	'sections';
				$redirectVars['accessvalue']	=	(int) $getArray['id'];
				$allowAccess					=	$this->checkAccess( $userId, (int) $getArray['id'], $listNameSection, $switchSection );
				break;
			case 'category':				// category list:
				$redirectVars['accesstype']		=	'categories';
				$redirectVars['accessvalue']	=	(int) $getArray['id'];
				$allowAccess					=	$this->checkAccess( $userId, (int) $getArray['id'], $listNameCategory, $switchCategory );
				if ( $allowAccess !== false ) {
					$section					=	$this->getSectionOfCategory( (int) $getArray['id'] );
					if ( $section && isset( $getArray['sectionid']) && ( $section == $getArray['sectionid']) ) {
						$redirectVars['accesstype']		=	'sections';
						$redirectVars['accessvalue']	=	(int) $section;
						$allowAccess			=	$this->checkAccess( $userId, (int) $section, $listNameSection, $switchSection );
					} else {
						$allowAccess			=	false;
					}
				}
				break;
			default:
				$allowAccess					=	null;
				break;
		}
		return $allowAccess;
	}
	/**
	 * Utility function to check if ANY of $keys is in $array
	 *
	 * @param  array  $keys
	 * @param  array  $array
	 * @return boolean
	 */
	protected function _anyKeyInArray( &$keys, &$array ) {
		foreach ( $keys as $k ) {
			if ( in_array( $k, $array ) ) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Utility function to check if ALL of $keys is in $array
	 *
	 * @param  array  $keys
	 * @param  array  $array
	 * @return boolean
	 */
	protected function _allKeysInArray( &$keys, &$array ) {
		foreach ( $keys as $k => $v ) {
			if ( substr( $v , 0, 1 ) == '/' ) {
				if ( ! ( ( isset( $array[$k] ) && ( preg_match( $v, $array[$k] ) ) ) ) ) {
					return false;
				}
			} else {
				if ( ! ( ( isset( $array[$k] ) && ( $v === $array[$k] ) ) ) ) {
					return false;
				}
			}
		}
		return true;
	}
	/**
	 * Gets the section of a category
	 *
	 * @param  int       $categoryId
	 * @return int|null
	 */
	public function getSectionOfCategory( $categoryId ) {
		global $_CB_database;

		$query		=	"SELECT `section` FROM #__categories WHERE id = " . (int) $categoryId;
		$_CB_database->setQuery( $query );
		$section	=	$_CB_database->loadResult();
		if ( $section == (int) $section ) {
			return (int) $section;
		} else {
			return null;
		}
	}
	/**
	 * Checks if $userId has anyway access because of his permissions
	 *
	 * @param  int  $userId
	 * @return int
	 */
	public function hasAccessAnyway( $userId ) {
		global $_CB_framework;
		// allow access to someone who is unrestricted:
		$params							=	cbpaidApp::settingsParams();
		$integration_full_access		=	cbToArrayOfInt( $params->get( 'integration_cpaycontent_access', $_CB_framework->acl->mapGroupNamesToValues( array( 'Administrator', 'Superadministrator' ) ) ) );
		if ( $userId ) {
			$myAclGids					=	Application::User( (int) $userId )->getAuthorisedGroups( false );
		} else {
			$myAclGids					=	array( $_CB_framework->acl->mapGroupNamesToValues( 'Public' ) );
		}
		return count( array_intersect( $myAclGids, $integration_full_access ) );
	}

	/**
	 * parses regex match callback plan substitutions
	 *
	 * @param  array   $matches
	 * @return string
	 */
	public function replacePlanSubstitutions( $matches ) {
		$planId					=	( isset( $matches[1] ) ? (int) $matches[1] : null );
		$text					=	( isset( $matches[2] ) ? $matches[2] : null );

		if ( $planId && $text ) {
			$user				=	cbUser::getMyUserDataInstance();
			$plansMgr			=	cbpaidPlansMgr::getInstance();
			$plan				=	$plansMgr->getPublishedPlan( $planId, $user );

			if ( $plan ) {
				// Output CSS for displaying the plan properly:
				cbpaidTemplateHandler::getViewer( '', null )->outputTemplateCss( 'cbpaidsubscriptions' );
				$plan->getTemplateOutoutCss();

				// Get substitutions:
				return $plan->getPersonalized( $text, $user->id, true, true, null, false );
			}
		}

		return null;
	}
}	// class cbpaidBot

/**
 * Encodes a $_GET array into a single get parameter value (base 64 or urlencoded parts)
 *
 * @param  array   $getArray   $_GET-type array
 * @param  string  $keyPrefix
 * @return string
 */
function cbpaidsubsbot_flattenArrayUrl( $getArray, $keyPrefix = '' ) {
	$result						=	array();
	if ( $keyPrefix ) {
		$pre					=	$keyPrefix . '[';
		$post					=	']';
	} else {
		$pre					=	'';
		$post					=	'';
	}
	
	foreach ( $getArray as $k => $v ) {
		if ( is_int( $k ) && $pre ) {
			$key 				=	$pre . $post;
		} else {
			$key				=	$pre . urlencode( $k ) . $post;
		}
		
		if ( is_array( $v ) ) {
			$result[]			=	cbpaidsubsbot_flattenArrayUrl( $v, $key );
		} else {
			$result[]			=	$key . '=' . urlencode( $v );
		}
	}
	return implode( '&', $result );
}
/**
 * Encodes a $_GET array into a single get parameter value (base 64 or urlencoded parts)
 *
 * @param  array   $getArray  $_GET-type array
 * @return string
 */
function cbpaidsubsbot_encodeArrayUrl( $getArray ) {
	return base64_encode( cbpaidsubsbot_flattenArrayUrl( $getArray ) );
}
/**
 * Event handler for onStart
 */
function cbpaidsubsbot_onStart( ) {
}
/**
 * Event handler for onAfterStart
 */
function cbpaidsubsbot_onAfterStart( ) {
	// done in SysPlug : $mainframe					=	new cbpaidBotMainframe( $mainframe );

	cbpaidBotAclApi::getInstance();					// initialize CBSubs ACL

	global $_CBSUBS_DEBUG;
	if ( isset( $_CBSUBS_DEBUG ) && $_CBSUBS_DEBUG ) {
		global $_CB_PAIDBOT_OLD_DISPLAY_ERRORS;
		ini_set('display_errors', $_CB_PAIDBOT_OLD_DISPLAY_ERRORS);
		global $_CB_PAIDBOT_OLD_ERRORS;
		error_reporting( $_CB_PAIDBOT_OLD_ERRORS );

		cbpaidErrorHandler::off();
	}
}
/**
 * Event handler for onAfterDispatch ON 1.5 ONLY
 */
function cbpaidsubsbot_onAfterDispatch_Alt( ) {
	global $_CB_framework;

	cbpaidErrorHandler::on();

	$cbpaidBot		=	cbpaidBot::getInstance();
	if ( $cbpaidBot->paidsubsManager !== null ) {
		$cbpaidBot->_checkModules( $_CB_framework->myId(), false );
	}

	cbpaidErrorHandler::off();
}

/**
 * J1.6 trigger for onContentBeforeDisplay
 *
 * @param  mixed      $part
 * @param  object     $row
 * @param  JRegistry  $articleParams
 * @param  int        $page
 * @return void
 */
function cbpaidsubsbot_onContentBeforeDisplay( /** @noinspection PhpUnusedParameterInspection */ $part, &$row, &$articleParams, $page = 0 ) {
	cbpaidsubsbot_onBeforeDisplayContent( $row, $articleParams, $page );
}
/**
 * Trigger for onBeforeDisplayContent
 * @param  object     $row
 * @param  JRegistry  $articleParams
 * @param  int        $page
 * @return void
 */
function cbpaidsubsbot_onBeforeDisplayContent( &$row, &$articleParams, /** @noinspection PhpUnusedParameterInspection */ $page ) {
	global $_CB_framework;

	$cbpaidBot					=	cbpaidBot::getInstance();

	if ( $cbpaidBot->paidsubsManager === null ) {
			return;
	}
	cbpaidErrorHandler::on();

	if ( isset( $row->id ) ) {
		$myId					=	$_CB_framework->myId();
		$_cbACL					=&	cbpaidBotAclApi::getInstance();
		$access					=	$_cbACL->_cb_checkMultiAcl( $myId, $row->id, isset( $row->catid ) ? $row->catid : null, isset( $row->sectionid ) ? $row->sectionid : null, -1 );

		if ( $access === false ) {

			if ( $_cbACL->_cb_checkMultiAcl( $myId, $row->id, isset( $row->catid ) ? $row->catid : null, isset( $row->sectionid ) ? $row->sectionid : null, count( $_cbACL->cbContentAclRights ) -1 ) !== true ) {

				// allow usage of <!-- !CBPAIDaccessCheck --> to NOT control the item
				if( ! ( isset( $row->text ) && strstr( $row->text, " !CBPAIDaccessCheck " ) ) ) {

					$params							=&	cbpaidApp::settingsParams();

					// allow access to someone who is unrestricted:
					if ( ! $cbpaidBot->hasAccessAnyway( $myId ) ) {

						// var_dump($row);
							// show/hides the intro text
						//	if ( $params->get( 'introtext'  ) ) {
						//		$row->text = $row->introtext. ( $params->get( 'intro_only' ) ? '' : chr(13) . chr(13) . $row->fulltext);
						//	} else {
						//		$row->text = $row->fulltext;
						//	}
						$redirectVars				=	array( 'accesstype' => 'contentdisplay', 'accessvalue' => (int) $row->id );
						$redirectVars['accessurl']	=	cbpaidsubsbot_encodeArrayUrl( $_GET );
			
						$redirectUrl				=	'index.php?option=com_comprofiler&task=pluginclass&plugin=cbpaidsubscriptions&do=accessdenied' . getCBprofileItemid( false);		// &Itemid= ???
						foreach ( $redirectVars as $k => $v ) {
							$redirectUrl			.=	'&' . urlencode( $k ) . '=' . urlencode( $v );
						}
			
						// translate message:
						cbpaidApp::loadLang();
						$subTxt						=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );

						$allowAccessToIntro			=	$params->get( 'integration_cpaycontent_allowIntro', '1' );
						// If access to intro is NO (0), or DEPENDS (2) but there is no main full text, then we do not allow to see anything:
						$disallowAccessToAnyText	=	( $allowAccessToIntro == 0 ) || ( ( $allowAccessToIntro == 2 ) && ! ( isset( $row->readmore ) ? $row->readmore != 0 : trim( strip_tags( $row->fulltext ) ) != '' ) );

						if ( $disallowAccessToAnyText ) {
							$messageTxt				=	'<span class="cbpaidContentAccessDenied">' . sprintf( CBPTXT::Th( 'To read this article, a %s is needed: Click here to subscribe' ), $subTxt ) . '</span>';
						} else {
							$messageTxt				=	'<span class="cbpaidContentAccessDenied">' . sprintf( CBPTXT::Th( 'To read more, a %s is needed: Click here to subscribe' ), $subTxt ) . '</span>';
						}

						if ( ( ($cbpaidBot->option == 'com_content' || $cbpaidBot->option == 'content') && ( ( $cbpaidBot->task == 'view' ) || ( $cbpaidBot->task == '' ) ) && ( $cbpaidBot->view != 'frontpage' ) && ( $cbpaidBot->view != 'featured' ) ) )  {
							// article view:
							if ( $disallowAccessToAnyText ) {
								// option=com_content&task=view
								cbRedirect( cbSef( $redirectUrl, false ), sprintf( CBPTXT::T("Access to this content is not allowed without %s"), $subTxt ) );
								return;
							}
							// 1.5: needed for title links:
							$row->readmore_link		=	cbSef( $redirectUrl );
							if ( ( $cbpaidBot->cbCmsVersion == 1 ) && ( in_array( $cbpaidBot->view, array( 'frontpage', 'section', 'category' ) ) ) ) {
								// 1.5: (section and category are for layout=blog (section blog), but as section layout just displays categories, it is ok to not check for that)
								$row->readmore_register	=	false;
								$articleParams->set( 'readmore', $messageTxt );
							} else {
								$row->text			=	$row->introtext;

								if ( trim( strip_tags( $row->fulltext ) ) ) {
									$row->text			.=	'<a href="' . cbSef( $redirectUrl ) . '">' . $messageTxt . '</a>';
								}
							}
						} else {
							//	$row->text			=	$row->text . $message;
							// 1.0:
							$row->link_text			=	$messageTxt;
							$row->link_on			=	cbSef( $redirectUrl );
							$articleParams->set( 'intro_only', 1 );
							if ( $cbpaidBot->cbCmsVersion == 1 ) {
								$articleParams->set( 'readmore', $messageTxt );
							} else {
								$articleParams->set( 'readmore', 1 );
							}
							// 1.5: needed for title links:
							$row->readmore_link		=	cbSef( $redirectUrl );
							if ( $disallowAccessToAnyText ) {
								$row->introtext		=	'';
								$row->text			=	'';
								$row->readmore		=	1;
							}
							if ( class_exists( 'JDatabaseQuery' ) ) {
								// 1.6:
								$row->alternative_readmore	=	'</a>' . '<a class="cbregPayToViewRedirectLink" href="' . cbSef( $redirectUrl ) . '">' . $messageTxt . ' ';		// Space is for case of article title in readmore link following. Ideally should be ': ' in that case only.
							}
						}
					}
				}
			}
		}

		// run cbsubs plan substitutions after access readmore access checks
		if ( isset( $row->title ) ) {
			$row->title			=	preg_replace_callback( '/\{cbsubs:plan:(\d+):(.*?)\}/s', array( $cbpaidBot, 'replacePlanSubstitutions' ), $row->title );
		}

		if ( isset( $row->introtext ) ) {
			$row->introtext		=	preg_replace_callback( '/\{cbsubs:plan:(\d+):(.*?)\}/s', array( $cbpaidBot, 'replacePlanSubstitutions' ), $row->introtext );
		}

		if ( isset( $row->fulltext ) ) {
			$row->fulltext		=	preg_replace_callback( '/\{cbsubs:plan:(\d+):(.*?)\}/s', array( $cbpaidBot, 'replacePlanSubstitutions' ), $row->fulltext );
		}

		if ( isset( $row->text ) ) {
			$row->text			=	preg_replace_callback( '/\{cbsubs:plan:(\d+):(.*?)\}/s', array( $cbpaidBot, 'replacePlanSubstitutions' ), $row->text );
		}
	}
	cbpaidErrorHandler::off();

}
/**
 * J1.6: need to fix the hardcoded MC in the VIEW of the "MVC" of 1.6:
 */
function cbpaidsubsbot_onAfterRender( ) {
	$text			=	JResponse::getBody();
	$text			=	preg_replace( '#<a [^>]+>\\s*</a>(<a class="cbregPayToViewRedirectLink" href=")#', '$1', $text );
	JResponse::setBody( $text );
}

/**
 * Trigger listener for OnAlittleMoreAfterStart.
 *
 * @return void
 */
function cbpaidContentOnAlittleMoreAfterStart( ) {
	cbpaidBot::getInstance()->onAlittleMoreAfterStart();
}
cbpaidSysPlugin::registerOnRealStart( 'cbpaidContentOnAlittleMoreAfterStart' );

cbpaidErrorHandler::off();
