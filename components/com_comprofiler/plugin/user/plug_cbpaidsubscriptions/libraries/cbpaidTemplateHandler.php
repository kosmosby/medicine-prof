<?php
/**
 * @version $Id: cbpaidTemplateHandler.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * New CB 2.0 template handler class
 */
class cbSUPERTemplateHandler extends cbTemplateHandler {
	protected $_classPrefix		=	'cb';
	protected $_classPrefixLen		=	2;	// cb
	protected $_classPostfixLen		=	4;	// View
	protected $_viewsPath				=	'views';
	protected $_defaultTemplatePath	=	'plugin/templates';
	protected $_overrideSubFolder		=	'';
	protected $_tmplToUse				=	'default';
	protected $_tmplVersion			=	1;

	protected $viewName;
	protected $output;
	protected $_model;
	private $_tmplUsedPath;
	/**
	 * Chainable: Get the viewer class for template, view and output
	 *
	 * @param  string  $template
	 * @param  string  $view
	 * @param  string  $output
	 * @return cbpaidTemplateHandler  also for chaining
	 */
	public static function getViewer( $template, $view, $output = 'html' ) {
		$instance					=	new self();
		return $instance->_getViewer( $template, $view, $output );
	}
	/**
	 * DO NOT CALL: call getViewer
	 * Gets the viewer class for template, view and output
	 *
	 * @param  string  $template
	 * @param  string  $view
	 * @param  string  $output
	 * @return cbpaidTemplateHandler
	 */
	public function _getViewer( $template, $view, $output = 'html' ) {
		if ( $view ) {
			$saneViewName			=	strtolower( preg_replace( '/\W/', '', $view ) );

			$this->_loadView( $saneViewName );
			$viewerClass			=	$this->_classPrefix . $view . 'View';		// this class inherits from this one indirectly
		} else {
			$viewerClass			=	get_class( $this );
		}
		if ( ! class_exists( $viewerClass ) ) {
			trigger_error( sprintf( CBPTXT::T("Template %s View %s class %s does not exist or is not loaded."), $template, $view, $viewerClass ), E_USER_ERROR );
		}
		/** @var $viewer cbpaidTemplateHandler */
		$viewer						=	new $viewerClass( $this );
		$viewer->templateToUse( $template );
		$viewer->viewName			=	strtolower( preg_replace( '/\W/', '', $view ) );
		$viewer->output				=	$output;
		return $viewer;
	}
	/**
	 * Loads the viewer class
	 *
	 * @param  string $saneViewName
	 */
	protected function _loadView( $saneViewName ) {
		global $_CB_framework;

		$internalFile				=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/' . $this->_viewsPath . '/' . $saneViewName . '.php';
		if ( file_exists( $internalFile ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once $internalFile;
		} else {
			trigger_error( htmlspecialchars( sprintf( CBPTXT::T("File %s not found for view %s"), $internalFile, $saneViewName ) ), E_USER_NOTICE );
		}
	}
	/**
	 * Chainable: Sets model of view
	 *
	 * @param $model
	 * @return cbSUPERTemplateHandler  for chaining
	 */
	public function setModel( $model ) {
		$this->_model			=	$model;
		return $this;
	}
	/**
	 * Sets or gets template to use
	 *
	 * @param  string|null  $newTemplateToUse  string: set $newTemplateToUse, null: just returns which template is used
	 * @return string                          Template name which is in use
	 */
	public function templateToUse( $newTemplateToUse = null ) {
		if ( $newTemplateToUse ) {
			$saneTemplateName	=	preg_replace( '/\W/', '', $newTemplateToUse );
			$this->_tmplToUse	=	$saneTemplateName;
		}
		return $this->_tmplToUse;
	}
	/**
	 * Renders the view with $layout
	 *
	 * @param  string|null  $layout  Layout to render (null = default layout)
	 * @return string
	 */
	public function display( $layout = null ) {
		ob_start();
		$this->_renderView( $layout );
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	/**
	 * Renders by ECHO the plan selection view
	 *
	 *
	public function OLD_renderView( $layout = null ) {
	global $_CB_framework;

	$saneLayout						=	$layout ? preg_replace( '/\W/', '', strtolower( $layout ) ) : 'default';
	$cmsTemplate					=	$this->_cmsCurrentTemplate();
	$overrideFile					=	$_CB_framework->getCfg( 'absolute_path' ) . '/templates/' . $cmsTemplate . '/html/com_comprofiler/' . $this->_overrideSubFolder . '/' . $this->viewName . '/' . $saneLayout . '.php';
	if ( $cmsTemplate && file_exists( $overrideFile ) ) {
	$tmplVersion				=	null;
	include $overrideFile;
	if ( $tmplVersion != $this->_tmplVersion ) {
	trigger_error( sprintf( CBPTXT::T("Template %s has version %s instead of %s"), $overrideFile, $tmplVersion, $this->_tmplVersion ), E_USER_NOTICE );
	}
	} else {
	$internalFile				=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/' . $this->_defaultTemplatePath . '/' . $this->templateToUse() . '/' . $this->viewName . '/' . $saneLayout . '.php';
	if ( file_exists( $internalFile ) ) {
	include $internalFile;
	} else {
	$defaultInternalFile	=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/' . $this->_defaultTemplatePath . '/' . 'default' . '/' . $this->viewName . '/' . $saneLayout . '.php';
	if ( ( $this->templateToUse() != 'default' ) && file_exists( $defaultInternalFile ) ) {
	include $defaultInternalFile;
	} else {
	// $method					=	'_render' . $layout;
	// if ( is_callable( array( $this, $method ) ) ) {
	//	$this->$method();
	// } else {
	trigger_error( sprintf( CBPTXT::T("CB View %s Layout %s has no rendering file %s."), $this->viewName, $saneLayout, $internalFile ), E_USER_NOTICE );
	// }
	}
	}
	}
	}
	 */
	/**
	 * Renders by ECHO the plan selection view
	 *
	 * @param  string      $layout       one word name ('default' by default)
	 */
	protected function _renderView( $layout = null ) {
		$file							=	$this->_file_path( $layout, '.php' );
		if ( $file ) {
			$tmplVersion				=	null;
			/** @noinspection PhpIncludeInspection */
			include $file;
			if ( $tmplVersion != $this->_tmplVersion ) {
				trigger_error( sprintf( CBPTXT::T("Template %s has version %s instead of %s"), $file, $tmplVersion, $this->_tmplVersion ), E_USER_NOTICE );
			}
		}
	}
	/**
	 * Outputs template CSS file
	 *
	 * @param  string      $layout       one word name (main plugin name by default)
	 */
	public function outputTemplateCss( $layout ) {
		global $_CB_framework;

		$file							=	$this->_file_path( $layout, '.css' );
		if ( $file ) {
			$_CB_framework->document->addHeadStyleSheet( $file );
		}
	}
	/**
	 * Gets live_site url for image url if that file exists in the used template or template override
	 * Cleans the image url, and if it was unclean or file does not exist, returns null
	 *
	 * @param  string  $imgSrc  e.g. subfolder1/subfolder2/picture.gif
	 * @return string           e.g. http://www.example.com/components/com_comprofiler/....../images/subfolder1/subfolder2/picture.gif
	 */
	protected function getMediaUrl( $imgSrc ) {
		global $_CB_framework;

		$saneImgSrc						=	preg_replace( '/(\/\/|\.\.|[^-_.\/A-Za-z0-9])/', '', $imgSrc );
		if ( $saneImgSrc === $imgSrc ) {
			$imgPathFile				=	$this->_tmplUsedPath . 'images/' . $saneImgSrc;
			if ( file_exists( $_CB_framework->getCfg( 'absolute_path' ) . $imgPathFile ) ) {
				return $_CB_framework->getCfg( 'live_site' ) . $imgPathFile;
			}
		}
		return null;
	}
	/**
	 * Finds file and returns absolute file path
	 *
	 * @param  string      $layout       one word name
	 * @param  string      $extension    '.php' or '.css'
	 * @return string|null
	 */
	protected function _file_path( $layout, $extension ) {
		global $_CB_framework;

		$saneLayout						=	( $layout ? preg_replace( '/\W/', '', strtolower( $layout ) ) : 'default' ) . $extension;
		if ( $extension === '.php' ) {
			$absPrefix					=	$_CB_framework->getCfg( 'absolute_path' );
			$chkPrefix					=	'';
			$saneLayout					=	$this->viewName . '/' . $saneLayout;
		} else {
			$absPrefix					=	'';
			$chkPrefix					=	$_CB_framework->getCfg( 'absolute_path' );
		}
		$cmsTemplate					=	$this->_cmsCurrentTemplate();
		$overrideFilePath				=	'/templates/' . $cmsTemplate . '/html/com_comprofiler/' . $this->_overrideSubFolder . '/';
		if ( $cmsTemplate && file_exists( $chkPrefix . $absPrefix . $overrideFilePath . $saneLayout ) ) {
			$this->_tmplUsedPath		=	$overrideFilePath;
			return $absPrefix . $overrideFilePath . $saneLayout;
		} else {
			$internalFilePath			=	'/components/com_comprofiler/' . $this->_defaultTemplatePath . '/' . $this->templateToUse() . '/';
			if ( file_exists( $chkPrefix . $absPrefix . $internalFilePath . $saneLayout ) ) {
				$this->_tmplUsedPath	=	$internalFilePath;
				return $absPrefix . $internalFilePath . $saneLayout;
			} else {
				$defaultInternalFilePath =	'/components/com_comprofiler/' . $this->_defaultTemplatePath . '/' . 'default' . '/';
				if ( ( $this->templateToUse() != 'default' ) && file_exists( $chkPrefix . $absPrefix . $defaultInternalFilePath . $saneLayout ) ) {
					$this->_tmplUsedPath =	$defaultInternalFilePath;
					return $absPrefix . $defaultInternalFilePath . $saneLayout;
				} else {
					// $method					=	'_render' . $layout;
					// if ( is_callable( array( $this, $method ) ) ) {
					//	$this->$method();
					// } else {
					trigger_error( sprintf( CBPTXT::T("CB View %s Layout %s has no rendering file %s."), $this->viewName, $saneLayout, $absPrefix . $internalFilePath . $saneLayout ), E_USER_NOTICE );
					// }
				}
			}
		}
		return null;
	}
	/**
	 * Returns current CMS template
	 *
	 * @return string   name (and folder name) of current CMS template or NULL if CMS does not have templates or does not support template overrides
	 */
	protected function _cmsCurrentTemplate( ) {
		static $cur_template	=	null;
		if (  $cur_template === null ) {
			global $_CB_framework;
			$mainframe			=&	$_CB_framework->_baseFramework;
			$cur_template	 	=	$mainframe->getTemplate();
		}
		return $cur_template;
	}
}
/**
 * CBSubs-specific template handler, extends CB 2.0's template handler
 */
class cbpaidTemplateHandler extends cbSUPERTemplateHandler {
	protected $_classPrefix			=	'cbpaid';
	protected $_classPrefixLen		=	6;	// cbpaid
	// protected $_classPostfixLen		=	4;	// View
	protected $_viewsPath				=	'plugin/user/plug_cbpaidsubscriptions/views';
	protected $_defaultTemplatePath	=	'plugin/user/plug_cbpaidsubscriptions/templates';
	protected $_overrideSubFolder		=	'plugin/user/plug_cbpaidsubscriptions';
	protected $_tmplVersion			=	1;

	/**
	 * Chainable: Get the viewer class for template, view and output
	 *
	 * @param  string                 $template  Template to load
	 * @param  string                 $view      View
	 * @param  string                 $output    Output
	 * @return cbpaidTemplateHandler
	 */
	public static function getViewer( $template, $view, $output = 'html' ) {
		$instance				=	new self();
		if ( ! $template ) {
			$template			=	cbpaidApp::settingsParams()->get( 'template', 'default' );
		}
		return $instance->_getViewer( $template, $view, $output );
	}
	/**
	 * Gets live_site url for image url if that file exists in the used template or template override
	 * Cleans the image url, and if it was unclean or file does not exist, returns null
	 *
	 * @param  string  $imgSrc  e.g. subfolder1/subfolder2/picture.gif
	 * @return string           e.g. http://www.example.com/components/com_comprofiler/....../images/subfolder1/subfolder2/picture.gif
	 */
	protected function getMediaUrl( $imgSrc ) {
		$imgPath				=	parent::getMediaUrl( $imgSrc );
		if ( $imgPath ) {
			return $imgPath;
		} else {
			// image not in the template folder:
			global $_CB_framework;

			$saneImgSrc						=	preg_replace( '/(\/\/|\.\.|[^-_.\/A-Za-z0-9])/', '', $imgSrc );
			if ( $saneImgSrc === $imgSrc ) {
				// Tries default template images folder:
				$imgPathFile				=	'/components/com_comprofiler/' . $this->_defaultTemplatePath . '/' . 'default' . '/' . 'images/' . $saneImgSrc;
				if ( file_exists( $_CB_framework->getCfg( 'absolute_path' ) . $imgPathFile ) ) {
					return $_CB_framework->getCfg( 'live_site' ) . $imgPathFile;
				}
				// Tries plugin's global images folder as last resort:
				$imgPathFile				=	'/components/com_comprofiler/' . $this->_overrideSubFolder . '/' . 'images/' . $saneImgSrc;
				if ( file_exists( $_CB_framework->getCfg( 'absolute_path' ) . $imgPathFile ) ) {
					return $_CB_framework->getCfg( 'live_site' ) . $imgPathFile;
				}
			}
		}
		return null;
	}
	/**
	 * Gets the name input parameter for search and other functions
	 *
	 * @param  string  $name     name of parameter of plugin
	 * @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	 * @return string            value of the name input parameter
	 */
	protected function _getPagingParamName( $name="search", $postfix="" ) {
		return cbpaidApp::getBaseClass()->_getPagingParamName( $name, $postfix );
	}
	/**
	 * gets an ESCAPED and urldecoded request parameter for the plugin
	 * you need to call stripslashes to remove escapes, and htmlspecialchars before displaying.
	 *
	 * @param  string  $name     name of parameter in REQUEST URL
	 * @param  string  $def      default value of parameter in REQUEST URL if none found
	 * @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	 * @return string            value of the parameter (urldecode processed for international and special chars) and ESCAPED! and ALLOW HTML!
	 */
	protected function _getReqParam( $name, $def=null, $postfix="" ) {
		return cbpaidapp::getBaseClass()->_getReqParam( $name, $def, $postfix );
	}
}
