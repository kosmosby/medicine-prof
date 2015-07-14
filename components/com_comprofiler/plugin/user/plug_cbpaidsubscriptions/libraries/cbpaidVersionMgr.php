<?php
/**
 * @version $Id: cbpaidVersionMgr.php 1577 2012-12-24 01:53:42Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CBSubs Maintenance manager class
 */
class cbpaidVersionMgr {
	// product-specific variables:
	public $product			=	'cbpaidsubscriptions';
	public $version			=	null;	// '1.0.0';
	public $versionminor	=	null;	// '' or 'b14';

	private $url			=	'versions.joomlapolis.com/versions/cbsubs/cbsubsversion3.php';
	private $https			=	true;
	private $port			=	443;
	/**
	 * Version check responses cache
	 * @var string[]
	 */
	private $responses		=	null;

	/**
	 * Gets a single instance of the cbpaidSomethingMgr class
	 *
	 * @return cbpaidVersionMgr
	 */
	public static function getInstance( ) {
		static $singleInstance	=	null;
		if ( $singleInstance === null ) {
			$singleInstance		=	new self();
		}
		return $singleInstance;
	}
	/**
	 * Fetch latest version and licensing information from versions server
	 *
	 * @param  boolean     $detailed  Gives detailed latest version description ?
	 * @return array|NULL             errorText  NULL if the key record could be fetched and stored, otherwise major error string
	 */
	public function fetchVersion( $detailed = false ) {
		global $_CB_framework;

		$return							=	null;
		if ( ! isset( $this->responses[$detailed] ) ) {
			$cbsubsVersion				=	explode( ' ', cbpaidApp::version() );
			$this->version				=	$cbsubsVersion[0];
			$this->versionminor			=	( isset( $cbsubsVersion[1] ) ? $cbsubsVersion[1] : '' );

			$formvars		=	array(	'task'					=>	'version',
										'type'					=>	'3',
										'version'				=>	'300',
										'product'				=>	$this->product,
										'productversion'		=>	$this->version,
										'productversionminor'	=>	$this->versionminor,
										'lang'					=>	$_CB_framework->getCfg( 'lang_tag' ),
										'info'					=>	( $detailed ? 'latestversiondetailed' : 'latestversionsummary' )
			);
			$random						=	sprintf( '%08x', mt_rand() );
			$formvars['sign']			=	$random . '-' . md5( $random . implode( '&', $formvars ) );

			$result						=	null;
			$status						=	null;
			$timeout					=	90;
			$live_site					=	$_CB_framework->getCfg( 'live_site' );
			$error						=	cbpaidWebservices::httpsRequest( $this->url, $formvars, $timeout, $result, $status, 'post', 'normal', '*/*', $this->https, $this->port, '', '', false, $live_site );

			$this->responses[$detailed]	=	array();
			if ($error || ( $status != 200) ) {
				$return					=	CBPTXT::T("Connection to update server failed") . ': ' . CBPTXT::T("Error") . ': ' . $error . ($status == -100 ? CBPTXT::T("Timeout") : $status);
			} else {
				$resultArray			=	explode( '-', $result );
				if ( count( $resultArray ) == 3 ) {
					$md5hash			=	md5( $resultArray[1] . $resultArray[0] );
					if ( $md5hash == $resultArray[2] ) {
						$result			=	base64_decode( $resultArray[0] );
						$arr			=	explode( '&', $result );
						$this->responses[$detailed]						=	array();
						foreach ( $arr as $v ) {
							$parts										=	explode( '=', $v );
							if ( count( $parts ) == 2 ) {
								$this->responses[$detailed][$parts[0]]	=	rawurldecode( $parts[1] );
							}
						}
						$return			=	null;
					} else {
						$return			=	CBPTXT::T("Hash mismatch");
					}
				} else {
					// echo $result;
					$return				=	CBPTXT::T("Malformed version server response");	// . $result;
				}
			}
		}
		if ( $return === null ) {
			return $this->responses[$detailed];
		}
		return $return;
	}
	/**
	 * Gets license attribute $licenseValue in HTML
	 * @param  string   $key
	 * @param  boolean  $forcereload
	 * @return string
	 */
	public static function getVersionAttr( $key, /** @noinspection PhpUnusedParameterInspection */ $forcereload = false )
	{
		$result						=	'---';

		$versionMgr					=	self::getInstance();
		$versionInfo				=	$versionMgr->fetchVersion( true );
		if ( is_array( $versionInfo ) ) {

			if ( in_array( $key, array( 'version', 'versionminor' ) ) ) {
				$value				=	$versionMgr->$key;
				$refValueKey		=	'latest' . $key;
				if ( isset( $versionInfo[$refValueKey] ) ) {
					$valueLatest	=	$versionInfo[$refValueKey];
					if ( $value == $valueLatest ) {
						$class		=	'cbEnabled';
					} else {
						$class		=	'cbDisabled';
					}
				} else {
					$class			=	'cbSpecial';
				}
			} elseif ( in_array( $key, array( 'latestversion', 'latestversionminor', 'latestversionmessage' ) ) && isset( $versionInfo[$key] ) ) {
				$value				=	$versionInfo[$key];
				$class				=	'cbpayLatestVersion';
			} else {
				if ( isset( $versionInfo[$key] ) ) {
					$value			=	$versionInfo[$key];
					$class			=	'cbEnabled';
				} else {
					$value			=	'?';
					$class			=	'cbDisabled';
				}
			}
			$ret					=	'<span class="' . $class . '">' . $value . '</span>';
		} else {
			$ret					=	'<span class="cbDisabled">' . $result . '</span>';
		}
		return $ret;
	}

	/**
	 * This is the handler for Ajax call:
	 * Ajax: administrator/index3.php?option=com_comprofiler&task=latestVersion&no_html=1&format=raw : administrator/index3.php?option=com_comprofiler&task=pluginmenu&cid=566&menu=ajversion&no_html=1&format=raw
	 *
	 * @param  boolean  $silent
	 * @return string
	 */
	public static function latestVersion( $silent = false ){
		$versionMgr				=	self::getInstance();
		$result					=	$versionMgr->fetchVersion();
		if ( is_array( $result ) ) {
			if ( ( $result['latestversion'] == $versionMgr->version ) && ( $result['latestversionminor'] == $versionMgr->versionminor ) ) {
				$class			=	'cbEnabled';
				if ( $silent ) {
					$value		=	null;
				} else {
					$value		=	CBPTXT::T("You have the latest version") . ' ' . $versionMgr->version . ' ' . $versionMgr->versionminor . '.';
				}
			} else {
				$class			=	'cbDisabled';
				$value			=	sprintf( CBPTXT::T("Your version %s %s is not the latest version %s %s: %s"), $versionMgr->version, $versionMgr->versionminor, $result['latestversion'], $result['latestversionminor'], '' );
			}
			if ( $value ) {
				$versionText	=	'<div class="' . $class . '">' . htmlspecialchars( $value ) . $result['latestversionmessage'] . '</div>';
			} else {
				$versionText	=	null;
			}
			$ret				=	$versionText;
		} else {
			$ret				=	'<div class="cbDisabled">' . $result . '</div>';
		}
		return $ret;
	}
	/**
	 * This is the handler for current version
	 *
	 * @return string
	 */
	public static function currentVersion( ){
		$licenseMgr				=&	self::getInstance();
		return CBPTXT::T("Version") . ' ' . $licenseMgr->version . ' ' . $licenseMgr->versionminor . '.';
	}

}	// class cbpaidVersioMgr
