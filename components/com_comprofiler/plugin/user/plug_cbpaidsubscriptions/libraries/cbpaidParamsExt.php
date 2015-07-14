<?php
/**
* @version $Id: cbpaidParamsExt.php 1608 2012-12-29 04:12:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Core GUI for Paid Subscriptions backend
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\AhaWow\Model\XmlQuery;
use CBLib\AhaWow\View\RegistryEditView;
use CBLib\Database\Table\TableInterface;
use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class that allows to extend CB 1.x XML to AHA-WOW
 */
class cbpaidParamsExt {
	/**
	 * @var string  data table for function datalist
	 */
	private $table	=	null;
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="datalist">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function datalist( $value, &$pluginParams, /** @noinspection PhpUnusedParameterInspection */ $name,
							  &$param, /** @noinspection PhpUnusedParameterInspection */ $control_name, $control_name_name,
							  $view, &$modelOfData, /** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRows,
							  /** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRowsNumber )
	{
		global $_CB_database;

		//TBD	$multi					=	( $param->attributes( 'multiple' ) == 'true' );
		$data					=	$param->getElementByPath( 'data' );
		if ( $data ) {
			$dataTable			=	$data->attributes( 'table' );
			if ( ! $dataTable ) {
				if ( isset( $this->table ) ) {
					$dataTable	=	$this->table;
				} elseif ( is_object( $modelOfData ) && ( $modelOfData instanceof TableInterface ) ) {
					$dataTable	=	$modelOfData->getTableName();
				} elseif ( is_object( $modelOfData ) && isset( $modelOfData->_tbl ) ) {
					$dataTable	=	$modelOfData->_tbl;
				} else {
					$dataTable	=	null;
				}
			}

			$xmlsql				=	new XmlQuery( $_CB_database, $dataTable, $pluginParams );
			$xmlsql->setExternalDataTypeValues( 'modelofdata', $modelOfData );
			$xmlsql->process_orderby( $data->getElementByPath( 'orderby') );							// <data><orderby><field> fields
			$xmlsql->process_fields( $data->getElementByPath( 'rows') );								// <data><rows><field> fields
			$xmlsql->process_where( $data->getElementByPath( 'where') );								// <data><where><column> fields
			$groupby			=	$data->getElementByPath( 'groupby' );
			if ( ! $groupby ) {
				$groupby		=	'value';
			}
			if ( $data->attributes( 'dogroupby' ) != 'false' ) {
				$xmlsql->process_groupby( $groupby );
			}
			$fieldValuesInDb	=	$xmlsql->queryLoadObjectsList( $data );			// get the records
			if ( $view ) {
				if ( is_array( $fieldValuesInDb ) ) {
					foreach ( $fieldValuesInDb as $v ) {
						if( $v->value == $value ) {
							$value	=	$v->text;
							break;
						}
					}
				}
				return htmlspecialchars( $value );
			} else {
				// check if value is in possible values:
				if ( $value != $param->attributes( 'default' ) && is_array( $fieldValuesInDb ) ) {
					$setToDefault			=	true;
					foreach ( $fieldValuesInDb as $v ) {
						if( $v->value == $value ) {
							$setToDefault	=	false;
							break;
						}
					}
					if ( $setToDefault ) {
						$value		=	$param->attributes( 'default' );	
					}
				}
				if ( ( $param->attributes( 'blanktext' ) ) && ( ( $param->attributes( 'hideblanktext' ) != 'true' ) || ( $value == $param->attributes( 'default' ) ) ) ) {
					$default		=	(string) $param->attributes( 'default' );
					array_unshift( $fieldValuesInDb, moscomprofilerHTML::makeOption( $default, CBPTXT::T( $param->attributes( 'blanktext' ) ) ) );
				}
				//TBD	$selected			=	explode( '|*|', $value );
				$classes			=	'class="' . RegistryEditView::buildClasses( $param, array( 'form-control' ) ) . '"';
				return moscomprofilerHTML::selectList( $fieldValuesInDb, $control_name_name, $classes . $this->_title( $param ), 'value', 'text', $value, 2 );
				// return $this->selectList( $fieldValuesInDb, $param, $control_name, $name, $selected, $multi );
			}
		}
		return null;
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="data">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function data( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams,
						  /** @noinspection PhpUnusedParameterInspection */ $name, &$param,
						  /** @noinspection PhpUnusedParameterInspection */ $control_name, $control_name_name, $view, &$modelOfData,
						  /** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRows,
						  /** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRowsNumber )
	{
		global $_CB_database;

		$data					=	$param->getElementByPath( 'data' );
		if ( $data ) {
			$dataTable			=	$data->attributes( 'table' );
			if ( ! $dataTable ) {
				if ( is_object( $modelOfData ) && ( $modelOfData instanceof TableInterface ) ) {
					$dataTable	=	$modelOfData->getTableName();
				} elseif ( is_object( $modelOfData ) && isset( $modelOfData->_tbl ) ) {
					$dataTable	=	$modelOfData->_tbl;
				} else {
					$dataTable	=	null;
				}
			}

			$xmlsql				=	new XmlQuery( $_CB_database, $dataTable, $pluginParams );
			$xmlsql->setExternalDataTypeValues( 'modelofdata', $modelOfData );
			$xmlsql->process_orderby( $data->getElementByPath( 'orderby') );							// <data><orderby><field> fields
			$xmlsql->process_fields( $param );								// <data><rows><field> fields
			$xmlsql->process_where( $data->getElementByPath( 'where') );								// <data><where><column> fields
			$value				=	$xmlsql->queryloadResult();						// get the value
			if ( $view ) {
				if ( $value === null ) {
					$value		=	$param->attributes( 'default' );
				}
				return htmlspecialchars( $value );
			} else {
				return '<input name="'. $control_name_name . '" type="text" id="' . $control_name_name . '" value="' . htmlspecialchars( $value ) . '"' . $this->_title( $param ) . ' />';
			}
		}
		return null;
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="fileupload">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function fileupload( $value, /** @noinspection PhpUnusedParameterInspection */ &$pluginParams,
								/** @noinspection PhpUnusedParameterInspection */ $name, &$param,
								/** @noinspection PhpUnusedParameterInspection */ $control_name, $control_name_name, $view,
								/** @noinspection PhpUnusedParameterInspection */ &$modelOfData,
								/** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRows,
								/** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRowsNumber )
	{
		if ( $view ) {
			return htmlspecialchars( $value );
		} else {
	 		$size 		=	$param->attributes( 'size' );
	 		if ( ! $size ) {
	 			$size	=	'70';
	 		}
	 		return '<input name="'. $control_name_name . '" type="file" size="' . $size . '" class="inputbox" id="' . $control_name_name . '"' . $this->_title( $param ) . ' />';
		}
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkAllSubscriptions">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkAllSubscriptions( /** @noinspection PhpUnusedParameterInspection */ $value,
										   /** @noinspection PhpUnusedParameterInspection */ &$pluginParams,
										   /** @noinspection PhpUnusedParameterInspection */ $name,
										   &$param,
										   /** @noinspection PhpUnusedParameterInspection */ $control_name,
										   /** @noinspection PhpUnusedParameterInspection */ $control_name_name,
										   /** @noinspection PhpUnusedParameterInspection */ $view,
										   /** @noinspection PhpUnusedParameterInspection */ &$modelOfData,
										   /** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRows,
										   /** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRowsNumber )
	{
		$size				=	$param->attributes( 'size' );
		if ( $size == '' ) {
			$size			=	100;
		}

		$cbsubsParams		=	cbpaidApp::settingsParams();
		if ( $cbsubsParams->get( 'massexpirymethod' ) < 3 ) {
			$plansMgr		=&	cbpaidPlansMgr::getInstance();
			$total			=	$plansMgr->checkAllSubscriptions( (int) $size );
			if ( $total == $size ) {
				$total		.=	' (' . CBPTXT::T("reload page for more mass expiries") . ')';
			}
		} else {
			$total			=	'0 (' . CBPTXT::T("no mass expiry from admin area, Settings-Global-Massexpiry is only by cron tasks") . ')';
		}

		$basketsMgr			=&	cbpaidOrdersMgr::getInstance();
		$expBaskets			=	$basketsMgr->timeoutUnusedBaskets( null, (int) $size );
		return $total . ' / ' . $expBaskets;
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="httpspoststatus">...
	 *
	 * param  string              $value                  Stored Data of Model Value associated with the element
	 * param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * param  string              $name                   Name attribute
	 * param  CBSimpleXMLElement  $param                  This XML node
	 * param  string              $control_name           Name of the control
	 * param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * param  boolean             $view                   TRUE: view, FALSE: edit
	 * param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function httpspoststatus( /* $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber */ ) {
		$return				=	'';

		/*	$curlLoaded			=	( extension_loaded('curl') && is_callable( 'curl_init' ) );	*/

		$fsockopenOK		=	is_callable( 'fsockopen' );

		$openSSLloaded		=	extension_loaded( 'openssl' ) && defined( 'OPENSSL_VERSION_TEXT' );
		$php430				=	version_compare( phpversion(), '4.3.0', '>' );
		$fsockopenUsableSSL	=	$fsockopenOK && $php430 && $openSSLloaded;

		$curl_found			=	false;		// warning: this is also in base class function _httpsRequest for use
		$path				=	null;
		if ( ! $fsockopenUsableSSL ) {
			if(function_exists('is_executable')) {
				$paths = array( '/usr/bin/curl', '/usr/local/bin/curl', 'curl' );	// IN SNOOPY ALREADY: '/usr/local/bin/curl'
				foreach ($paths as $path) {
					if ( @is_executable( $path ) ) {
						$curl_found = true;
						break;
					}
				}
			}
		}
		$curl_version		=	null;
		if ( $curl_found ) {
			$curl_cmd		=	$path . ' -V';
	
			$descriptors	=	array(	0 => array('pipe', 'r'),
										1 => array('pipe', 'w'),
										2 => array('pipe', 'w') );
	
			$pipes			=	null;
			$process		=	@proc_open( $curl_cmd, $descriptors, $pipes );				// PHP 4.3.0 required for this !
	
			if (is_resource($process)) {
				//	@fwrite( $pipes[0], $cleartext );
				//	@fflush( $pipes[0] );
				@fclose( $pipes[0] );

				$output		=	'';
				while ( ! feof( $pipes[1] ) ) {
					$output	.=	@fgets( $pipes[1] );
				}
				$error		=	'';
				while ( ! feof( $pipes[2] ) ) {
					$error	.=	@fgets( $pipes[2] );
				}
				$error		=	trim( $error );

				@fclose( $pipes[1] );
				@fclose( $pipes[2] );
				@proc_close( $process );
				
				$curl_version	=	trim( str_replace( "\n", ', ', $output ) );
			} else {
				$curl_found		=	false;
				$error				=	"proc_open failed on " . $curl_cmd;
			}

		} else {
			$error				=	null;
		}
		

/*		if ( $curlLoaded ) {
			$return			.=	$this->_outputGreenRed( "CURL library", $curlLoaded );
		} elseif */
		if ( $fsockopenUsableSSL ) {
			$return			.=	$this->_outputGreenRed( "fsockopen", $fsockopenOK, "is available with openSSL extension and OpenSSL version: " . OPENSSL_VERSION_TEXT );
		} elseif ( $curl_found ) {
			$return			.=	$this->_outputGreenRed( "curl executable", $curl_found, "has been found at" . ' ' . $path . ", and could be executed: curl version: " . $curl_version );
		} else {
/*			$return			.=	'<div>--- ' . "this:" . ' ---</div>';
			$return			.=	$this->_outputGreenRed( "CURL library", $curlLoaded, "loaded", "not available" . ':' . "see PHP manual page for " . '<a href="http://www.php.net/curl" target="_blank">CURL module</a>' );
			$return			.=	'<div>--- ' . "or all of that:" . ' ---</div>';
*/
			if ( $error ) {
				$return		.=	'<div style="color:red;">Error on trying to get CURL version: ' . htmlspecialchars( $error ) . '</div>';
			}
			$return			.=	'<div>--- ' . "All of this:" . ' ---</div>';
			$return			.=	$this->_outputGreenRed( "fsockopen", $fsockopenOK, "is available" );
			$return			.=	$this->_outputGreenRed( "PHP version is " . phpversion(), $php430, '', "which does not allow fsockopen with ssl:// for https" );
			$return			.=	$this->_outputGreenRed( "PHP openSSL module", extension_loaded( 'openssl' ), "loaded", "not available" . ':' . "see PHP manual page for " . '<a href="http://www.php.net/openssl" target="_blank">OpenSSL module</a>' );
			$return			.=	$this->_outputGreenRed( "openSSL application library", $openSSLloaded, "found: " . ( defined( 'OPENSSL_VERSION_TEXT' ) ? constant( 'OPENSSL_VERSION_TEXT' ) : "but unknown version" ), "not available or not configured. Link for OpenSSL application library: " . '<a href="http://www.openssl.org/" target="_blank">www.openssl.org</a>.' . "See also PHP manual page for " . '<a href="http://www.php.net/openssl" target="_blank">configuring OpenSSL module</a>' );
//			$return			.=	'<div>--- ' . "or finally this:" . ' ---</div>';
			$return			.=	'<div>--- ' . "or this:" . ' ---</div>';
			$return			.=	$this->_outputGreenRed( "curl executable", $curl_found, 'found', "not available or not configured. Link for download: " . '<a href="http://curl.haxx.se/download.html" target="_blank">http://curl.haxx.se/download.html</a>.' );
			$return			.=	'<div>--- ' . "should all be green for posts to payment gateways to be operating encrypted via HTTPS/SSL (on most gateways, it will still be working, but without encryption, which is less secure):" . ' ---</div>';
//			$return			.=	'<div class="cbDisabled">' . "To run with payment processors you need either 1) the CURL PHP library, or 2) fsockopen with OpenSSL PHP module loaded, openssl application configured, and php 4.3.0 at least, or 3) a curl executable. None of the 3 supported possibilities has been found on this server. Please contact your hoster." . '</div>';
			$return			.=	'<div class="cbDisabled">'
							.	'<p>' . "To communicate in an encrypted way over https with payment processors instead of in cleartext with http you need either:" . '</p>'
							.	'<ol>'
							.	'<li>' . "fsockopen function enabled with ssl type support and with OpenSSL PHP module loaded, openssl application configured, and php 4.3.0 at least, or" . '</li>'
							.	'<li>' . "a curl executable." . '</li>'
							.	'</ol>'
							.	'<p>' . "None of the 2 supported possibilities has been found on this server. Please contact your hoster if you need or want to communicate securely with your payment processor." . '</p>'
							.	'<p>' . "Note: this is not required for some payment processors like Paypal express checkout used here, as sensitive payment information doesn't transit, as well as for authorize.net test server, however when credit-card information is inputed on your website, like with authorize.net production server, it is required." . '</p>'
							.	'</div>';
		}
		return $return;
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="opensslstatus">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function opensslstatus( /** @noinspection PhpUnusedParameterInspection */ $value,
								   &$pluginParams,
									/** @noinspection PhpUnusedParameterInspection */ $name,
									/** @noinspection PhpUnusedParameterInspection */ &$param,
									/** @noinspection PhpUnusedParameterInspection */ $control_name,
									/** @noinspection PhpUnusedParameterInspection */ $control_name_name,
									/** @noinspection PhpUnusedParameterInspection */ $view,
									/** @noinspection PhpUnusedParameterInspection */ &$modelOfData,
									/** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRows,
									/** @noinspection PhpUnusedParameterInspection */ &$modelOfDataRowsNumber )
	{
		$return				=	'';

		$openSSLloaded		=	extension_loaded( 'openssl' ) && defined( 'OPENSSL_VERSION_TEXT' );

		$php430				=	version_compare( phpversion(), '4.3.0', '>' );

		$openssl_found		=	false;			// warning: this is also in base class function _httpsRequest for use
		$path				=	null;
		if ( $php430 && ! $openSSLloaded ) {
			if(function_exists('is_executable')) {
				$configPath	=	$pluginParams->get( 'openssl_exec_path', '/usr/bin/openssl' );
				$paths = array( '/usr/bin/openssl', '/usr/local/bin/openssl', 'openssl' );
				if ( $configPath ) {
					array_unshift( $paths, $configPath );
				}
				foreach ($paths as $path) {
					if ( @is_executable( $path ) ) {
						$openssl_found = true;
						break;
					}
				}
			}
		}

		$error				=	null;
		$openssl_version	=	null;
		if ( $openssl_found ) {
			$openssl_cmd	=	$path . ' version';
	
			$descriptors	=	array(	0 => array('pipe', 'r'),
										1 => array('pipe', 'w'),
										2 => array('pipe', 'w') );
	
			$pipes			=	null;
			$process		=	@proc_open( $openssl_cmd, $descriptors, $pipes );				// PHP 4.3.0 required for this !
	
			if (is_resource($process)) {
				//	@fwrite( $pipes[0], $cleartext );
				//	@fflush( $pipes[0] );
				@fclose( $pipes[0] );
	
				$output		=	'';
				while ( ! feof( $pipes[1] ) ) {
					$output	.=	@fgets( $pipes[1] );
				}
				$error		=	'';
				while ( ! feof( $pipes[2] ) ) {
					$error	.=	@fgets( $pipes[2] );
				}
				$error		=	trim( $error );

				@fclose( $pipes[1] );
				@fclose( $pipes[2] );
				@proc_close( $process );
				
				$openssl_version	=	trim( $output );
			} else {
				$openssl_found		=	false;
				$error				=	"proc_open failed on " . $openssl_cmd;
			}

		}

		if ( $openSSLloaded ) {
			$return			.=	$this->_outputGreenRed( "OpenSSL PHP module is available with openSSL extension and OpenSSL version: " . OPENSSL_VERSION_TEXT, $openSSLloaded );
		} elseif ( $php430 && $openssl_found ) {
			$return			.=	$this->_outputGreenRed( sprintf( "openssl executable found at %s, and could be executed: openssl version: %s.", $path, $openssl_version ), true, '' );
			if ( $error ) {
				$return		.=	$this->_outputGreenRed( sprintf( "Error during openssl version execution: %s.", $error ), false, '', '' );
			}
		} else {
			$return			.=	'<div>--- ' . "All of this:" . ' ---</div>';
			$return			.=	$this->_outputGreenRed( "PHP openSSL module", extension_loaded( 'openssl' ), "loaded", "not available" . ':' . "see PHP manual page for " . '<a href="http://www.php.net/openssl" target="_blank">OpenSSL module</a>' );
			$return			.=	$this->_outputGreenRed( "openSSL application library", $openSSLloaded, "found: " . ( defined( 'OPENSSL_VERSION_TEXT' ) ? constant( 'OPENSSL_VERSION_TEXT' ) : "but unknown version" ), "not available or not configured. Link for OpenSSL application library: " . '<a href="http://www.openssl.org/" target="_blank">www.openssl.org</a>.' . "See also PHP manual page for " . '<a href="http://www.php.net/openssl" target="_blank">configuring OpenSSL module</a>' );
			$return			.=	'<div>--- ' . "or this:" . ' ---</div>';
			$return			.=	$this->_outputGreenRed( "PHP version is " . phpversion(), $php430, '', "which does not allow proc_open() for executing openssl" );
			$return			.=	$this->_outputGreenRed( "openssl executable", $openssl_found, 'found', "not available or not configured. Link for download: " . '<a href="http://www.openssl.org/" target="_blank">http://www.openssl.org/</a>.' );
			$return			.=	'<div>--- ' . "should all be green, for encrypted and signed PayPal payment buttons using X.509 certificates (it will still be working and is not a major security issue)" . ' ---</div>';
		}
		return $return;
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="getcfg">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function getcfg( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber )
	{
		global $_CB_framework;

		$default			=	$param->attributes( 'default' );
		$cfg				=	$_CB_framework->getCfg( $default );
		return htmlspecialchars( $cfg );
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="lastlicensestate">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function configstatetext(  /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber )
	{
		$lastSavedVersion		=	cbpaidApp::settingsParams()->get( 'lastsavedversion' );
		if ( $lastSavedVersion === cbpaidApp::version() ) {
			return null;
		} elseif ( $lastSavedVersion ) {
			return '<span class="cbDisabled">' . CBPTXT::Th("Not yet saved with this version") . '</span>';
		} else {
			return '<span class="cbDisabled">' . CBPTXT::Th("Settings not yet set") . '</span>';
		}
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="versionlicensecheck">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function versionlicensecheck( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		$paramValue				=	$param->attributes( 'value' );

		$return					=	array();
		$paramValuesArray		=	explode( ' ', $paramValue );
		foreach ( $paramValuesArray as $v ) {
			$return[]			=	cbpaidVersionMgr::getVersionAttr( $v, true );
		}
		return implode( ' ', $return );
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="ajaxversioncheck">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function ajaxversioncheck( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		global $_CB_framework, $ueConfig, $_REQUEST;

		$paramDefault			=	$param->attributes( 'default' );		// silent, always, or '' = depending on CB version check param (default)
		$paramAlign				=	$param->attributes( 'align' );
		if ( $paramAlign ) {
			$styleOnly			=	'text-align:' . $paramAlign . ';';
			$style				=	' style="' . $styleOnly . '"';
		} else {
			$styleOnly			=	'';
			$style				=	'';
		}

		ob_start();
		if ( ( $paramDefault == '' ) && isset( $ueConfig['noVersionCheck'] ) && ( $ueConfig['noVersionCheck'] == '1' ) ) {
			?><div id="cbLatestVersion"<?php echo $style; ?>><a href="check_now" onclick="return cbCheckVersion();" style="cursor: pointer; text-decoration:underline;">check latest version now</a></div><?php
		} elseif ( $paramDefault == 'silent' ) {
			?><div id="cbLatestVersion"<?php echo $style; ?>></div><?php
		} else {
			?><div id="cbLatestVersion" style="color:#CCC;<?php echo $styleOnly; ?>">...</div><?php
		}

		$ret					=	ob_get_contents();
		ob_end_clean();

		$baseClass				=&	cbpaidApp::getBaseClass();
		$cid					=	(int) $baseClass->getPluginId();
		$url					=	'index.php?option=com_comprofiler&task=pluginmenu&pluginid=' . $cid . '&menu=ajversion';		// &start_debug=1';
		if ( $paramDefault == 'silent' ) {
			$url				.=	'&mode=updatesonly';
		} else {			// if ( $paramDefault == 'always' ) {
			$url				.=	'&mode=allinfo';
		}
		$url					=	$_CB_framework->backendUrl( $url, false, 'raw' );

		$errorText				=	( $paramDefault != 'silent' ? "There was a problem with the request." : '' );
		$js						=	<<<EOT
	function cbCheckVersion() {
		document.getElementById('cbLatestVersion').innerHTML = 'Checking latest version now...';
		CBmakeHttpRequest('$url', 'cbLatestVersion', 'There was a problem with the request.', null);
		return false;
	}
	function cbInitAjax() {
		CBmakeHttpRequest('$url', 'cbLatestVersion', '$errorText', null);
	}

EOT
;
		if (!( ( $paramDefault == '' ) && isset($ueConfig['noVersionCheck']) && $ueConfig['noVersionCheck'] == '1')) {
			$js					.=	"\tcbInitAjax();\n";
	    }
		$_CB_framework->outputCbJQuery( $js );
		return $ret;
	}
	/**
	 * Internal utility function to output JS for the Ajax content for function currencyconvertercheck() to update currencies
	 *
	 * @param  string  $ajaxUrl
	 * @param  string  $cssSelectorReply
	 * @return void
	 */
	protected function _ajaxContent( $ajaxUrl, $cssSelectorReply ) {
		global $_CB_framework;

		$cbSpoofField			=	cbSpoofField();
		$cbSpoofString			=	cbSpoofString( null, 'guiajax' );
		$regAntiSpamFieldName	=	cbGetRegAntiSpamFieldName();
		$regAntiSpamValues		=	cbGetRegAntiSpams();
		cbGetRegAntiSpamInputTag( $regAntiSpamValues );		// sets the cookie
		$regAntiSpZ				=	$regAntiSpamValues[0];

		//$errorText				=	addslashes( $errorText );

		$_CB_framework->outputCbJQuery( <<<EOT
	$.ajax( {	type: 'POST',
				url:  '$ajaxUrl',
				data: '$cbSpoofField=' + encodeURIComponent('$cbSpoofString') + '&$regAntiSpamFieldName=' + encodeURIComponent('$regAntiSpZ'),
				success: function(response) {
					$('$cssSelectorReply').hide().html(response).fadeIn('fast');
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$('$cssSelectorReply').hide().html(errorThrown ? errorThrown.message : textStatus).fadeIn('fast');
				},
				dataType: 'html'
	});
EOT
			);
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="currencyconvertercheck">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function currencyconvertercheck( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		global $_CB_framework;

		$baseClass				=&	cbpaidApp::getBaseClass();
		$cid					=	(int) $baseClass->getPluginId();
		$url					=	$_CB_framework->backendUrl( 'index.php?option=com_comprofiler&task=pluginmenu&pluginid=' . $cid . '&menu=curconvcheck', false, 'raw' );		// &start_debug=1';
		$id						=	'chkcur' . $param->attributes( 'name' );
		$this->_ajaxContent( $url, '#' . $id );
		return '<div id="' . $id . '" style="display:none;text-align:center;"> </div>';
/*
		$ret					=	null;
		$_CBPAY_CURRENCIES		=&	cbpaidApp::getCurrenciesConverter();
		$secondaryPrice			=	$_CBPAY_CURRENCIES->convertCurrency( 'EUR', 'USD', 1.0 );
		if ( $secondaryPrice === null ) {
			$ret				=	$this->_outputGreenRed( '', false, '', $_CBPAY_CURRENCIES->getError() );
		}
		return $ret;
*/
	}
	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkSystemsVersions">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkSystemsVersions( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		$return				=	null;
		// This works only on j1.5, as mambo and j1.0 does not include plugins in backend:
		if ( defined( '_CBSUBS_BOT_VERSION' ) ) {
			$version		=	cbpaidApp::version();
			if ( $version != _CBSUBS_BOT_VERSION ) {
				$return		.=	'<div class="cbWarning">' . sprintf( CBPTXT::T("The version %s of cbsubsbot does not match CBSubs plugin version %s"), htmlspecialchars( _CBSUBS_BOT_VERSION ), htmlspecialchars( $version ) ) . '</div>';
			}
		}
		return $return;
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkifpluginInstalled">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkifpluginInstalled( $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		global $_CB_framework, $_CB_database;

		$return				=	'';

		$botname			=	$param->attributes( 'value' );
		if ( $botname ) {
			$filePath		=	$_CB_framework->getCfg( 'absolute_path' ) . ( checkJversion() >= 1 ? '/plugins' : '/mambots' ) . '/system/' . ( checkJversion() >= 2 ? $botname . '/' : '' ) . $botname . '.php';	
			$readable		=	( @file_exists( $filePath ) && @is_readable( $filePath ) );
			if ( $readable ) {
				$sql		=	"SELECT enabled FROM #__extensions WHERE `type` = 'plugin' AND element = " . $_CB_database->Quote( $botname );
				$object		=	null;
				$_CB_database->setQuery( $sql, 0, 1 );
				if ( $_CB_database->loadResult( $object ) ) {
					return $this->checkSystemsVersions( $value, $pluginParams, $name, $param, $control_name, $control_name_name, $view, $modelOfData, $modelOfDataRows, $modelOfDataRowsNumber );
				} else {
					return '<div class="cbWarning">' . sprintf( CBPTXT::Th("The needed mambot/plugin '%s' is installed but not published."), htmlspecialchars( $botname ) ) . '</div>';
				}
			}
			$return			=	'<div class="cbWarning">' . sprintf( CBPTXT::T("The needed mambot/plugin '%s' is not installed."), htmlspecialchars( $botname ) ) . '</div>';
		} else {
			$return			.=	$this->_outputGreenRed( '', false, '', "Error: value of plugin to check missing." );
		}
		return $return;
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkPluginsPublished">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkPluginsPublished( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		global $_PLUGINS;

		$groups								=	explode( ',', $param->attributes( 'groups' ) );
		$action								=	$param->attributes( 'action' );
		$path								=	$param->attributes( 'path' );

		$version							=	cbpaidApp::version();

		$html								=	null;
		foreach ($groups as $group ) {
			$matches						=	null;
			if ( preg_match( '/^([^\[]+)\[(.+)\]$/', $group, $matches ) ) {
				$classId					=	$matches[2];
				$group						=	$matches[1];
			} else {
				$classId					=	null;
			}
			$_PLUGINS->loadPluginGroup( $group, $classId, 0 );
			$loadedPlugins					=&	$_PLUGINS->getLoadedPluginGroup( $group );
			foreach ( $loadedPlugins as /* $id => */ $plugin ) {
				if ( ( ! $classId ) || ( ( substr( $classId, -1 ) == '.' ) && substr( $plugin->element, 0, strlen( $classId ) ) == $classId ) || ( $plugin->element == $classId ) ) {
					$element					=	$_PLUGINS->loadPluginXML( 'action', $action, $plugin->id );
					$viewModel					=	$element->getElementByPath( $path );
					if ( ( ! $path ) || $viewModel ) {
						if ( $plugin->published == 0 ) {
							$html				.=	'<div class="cbWarning">' . sprintf( CBPTXT::Th("The integration plugin '%s' is installed but not published."), htmlspecialchars( $plugin->name ) ) . '</div>';
						}
						$cbsubsv				=	$element->getElementByPath( 'cbsubsversion' );
						if ( $cbsubsv ) {
							if ( ! cbStartOfStringMatch( $version, $cbsubsv->attributes( 'version' ) ) ) {
								$html			.=	'<div class="cbWarning">' . sprintf( CBPTXT::T("The CBSubs integration plugin '%s' is for another CBSubs version %s."), htmlspecialchars( $plugin->name ), htmlspecialchars( $cbsubsv->attributes( 'version' ) ) ) . '</div>';
							}
						} else {
							$html				.=	'<div class="cbWarning">' . sprintf( CBPTXT::T("The CBSubs integration plugin '%s' has no CBSubs version information in XML."), htmlspecialchars( $plugin->name ) ) . '</div>';
						}
						
					}
				}
			}					
		}
/*
		if ( $html ) {
			$html			=	'<div class="cbDisabled">'
							.	CBPTXT::Th("Following CBSubs integration CB plugins are installed but not published (so not active in front-end)")
							.	':'
							.	'</div>'
							.	$html
							;
		}
*/
		return $html;
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkifexecutable">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkifexecutable( $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		$default			=	$param->attributes( 'default' );
		
		$return				=	'';

		$filePath			=	( isset( $modelOfData->$default ) ? $modelOfData->$default : null );			//->get( 'default' );
		
		if ( $filePath ) {
			if( function_exists( 'is_executable' )) {
				$executable	=	( @is_executable( $filePath ) );
				$return		.=	$this->_outputGreenRed( $filePath, $executable, "is executable", "is not found or not executable" );
			} else {
				$return		.=	$this->_outputGreenRed( $filePath, false, '', "can not be checked because of SafeMode enabled or is_executable function disabled." );
			}
		} else {
			$return			.=	$this->_outputGreenRed( '', false, '', "No path defined yet. Please define then apply setting to get result of check." );
		}
		
		// $openSSLloaded	=	extension_loaded( 'openssl' );
		// $return			.=	$this->_outputGreenRed( "openSSL library", $openSSLloaded );

		if ( ! cbStartOfStringMatch( $return, '<div class="cbEnabled">' ) ) {
			if ( $default == 'openssl_exec_path' ) {
				$resultOpenssl	=	$this->opensslstatus( $value, $pluginParams, $name, $param, $control_name, $control_name_name, $view, $modelOfData, $modelOfDataRows, $modelOfDataRowsNumber );
				if ( cbStartOfStringMatch( $resultOpenssl, '<div class="cbEnabled">' ) ) {
					$return		=	'<div class="cbEnabled">' . CBPTXT::Th("Not needed, as OpenSSL PHP module is loaded") . '</div>';
					return $return;
				}
			}
		}
		
		return $return;
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkiffilereadable">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkiffilereadable( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		$return				=	'';

		$default			=	$param->attributes( 'default' );
		$writable_arg		=	( $param->attributes( 'writable' ) == 'true' );
		$relativepath		=	( $param->attributes( 'relativepath' ) == 'true' );
		$filePath			=	( isset( $modelOfData->$default ) ? $modelOfData->$default : null );			//->get( 'default' );
		if ( $relativepath ) {
			global $_CB_framework;
			$filePath		=	$_CB_framework->getCfg( 'absolute_path' ) . ( substr( $filePath, 0, 1 ) == '/' ? '' : '/' ) . $filePath;
		}
		if ( $filePath ) {
			$readable		=	( @file_exists( $filePath ) && @is_readable( $filePath ) );
			$writable		=	( $writable_arg && $readable && @is_writable( $filePath ) );
			if ( $writable_arg && $readable ) {
				$return		.=	$this->_outputGreenRed( $filePath, $writable, "exists and is writable.", "exists but is not writable by webserver process." );
			} else {
				$return		.=	$this->_outputGreenRed( $filePath, $readable, "exists and is readable.", "does not exist or is not readable by webserver process." );
			}
		} else {
			$return		.=	$this->_outputGreenRed( '', false, '', "No path defined yet. Please define then apply setting to get result of check." );
		}
		
		// $openSSLloaded	=	extension_loaded( 'openssl' );
		// $return			.=	$this->_outputGreenRed( "openSSL library", $openSSLloaded );

		return $return;
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkcertificate">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkcertificate( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		$return						=	'';

		$default					=	$param->attributes( 'default' );
		$filePath					=	( isset( $modelOfData->$default ) ? $modelOfData->$default : null );			//->get( 'default' );

		$ok							=	false;
		if ( $filePath ) {
			$readable				=	( @file_exists( $filePath ) && @is_readable( $filePath ) );
			if ( $readable ) {
				$certificate		=	@openssl_x509_read( file_get_contents( $filePath ) );
				if ( $certificate !== false ) {
					$details		=	@openssl_x509_parse( $certificate, false );
					if ( $details !== false ) {
						/*
						foreach ( $details as $k => $v ) {
							$return	.=	$k . ': ' . $v . '<br />';
						}
						$return		.=	var_export( $details, true ) . '<br />';
						*/
						$return 	.=	isset( $details['name'] ) ?				"Name: " . $details['name'] . '<br />' : '';
						$return 	.=	isset( $details['validFrom_time_t'] ) ?	"Valid from: " . date( 'Y-m-d H:i:s', $details['validFrom_time_t'] ) . '<br />' : '';
						$return 	.=	isset( $details['validTo_time_t'] ) ?	"Valid until: " . date( 'Y-m-d H:i:s', $details['validTo_time_t'] ) . '<br />' : '';
						$ok			=	true;
						if ( isset( $details['validTo_time_t'] ) && ( $details['validTo_time_t'] < time() ) ) {
							$return	.=	'<br /><span class="cbSmallWarning">' . "Certificate has expired !" . '</span>';
							$ok	=	false;
						} elseif ( isset( $details['validFrom_time_t'] ) && ( $details['validFrom_time_t'] > time() ) ) {
							$return	.=	'<br /><span class="cbSmallWarning">' . "Certificate is not yet valid !" . '</span>';
							$ok	=	false;
						} else {
							$return	.=	"Certificate appears valid";
						}
					} else {
						$return		=	"File is not a X509 certificate (public key)";
					}
				} else {
					$return			=	sprintf( "File %s is readable but can not be opened as a X509 certificate (openssl_x509_read failed on public key cert)", $filePath );
				}
			} else {
				$return				=	sprintf( "File %s does not exist or is not readable", $filePath );
			}
		} else {
			$return					=	"Filename not set";
		}
		return $this->_outputGreenRed( '', $ok, $return, $return );
	}

	/**
	 * View for <param  type="private" class="cbpaidParamsExt" method="checkprivatekey">...
	 *
	 * @param  string              $value                  Stored Data of Model Value associated with the element
	 * @param  ParamsInterface     $pluginParams           Main settigns parameters of the plugin
	 * @param  string              $name                   Name attribute
	 * @param  CBSimpleXMLElement  $param                  This XML node
	 * @param  string              $control_name           Name of the control
	 * @param  string              $control_name_name      css id-encode of the names of the controls surrounding this node
	 * @param  boolean             $view                   TRUE: view, FALSE: edit
	 * @param  cbpaidTable         $modelOfData            Data of the Model corresponding to this View
	 * @param  cbpaidTable[]       $modelOfDataRows        Displayed Rows if it is a table
	 * @param  int                 $modelOfDataRowsNumber  Total Number of rows
	 * @return null|string
	 */
	public function checkprivatekey( /** @noinspection PhpUnusedParameterInspection */ $value, &$pluginParams, $name, &$param, $control_name, $control_name_name, $view, &$modelOfData, &$modelOfDataRows, &$modelOfDataRowsNumber ) {
		$return				=	'';

		$default			=	$param->attributes( 'default' );
		$passphrase_field	=	$param->attributes( 'value' );
		$public_key_field	=	$param->attributes( 'directory' );
		$filePath			=	( isset( $modelOfData->$default ) ? $modelOfData->$default : null );
		$passphrase			=	( isset( $modelOfData->$passphrase_field ) ? $modelOfData->$passphrase_field : null );
		$public_key_path	=	( isset( $modelOfData->$public_key_field ) ? $modelOfData->$public_key_field : null );
		
		$ok					=	false;
		if ( $filePath ) {
			$readable		=	( @file_exists( $filePath ) && @is_readable( $filePath ) );
			if ( $readable ) {
				$privateKey		=	openssl_pkey_get_private( file_get_contents( $filePath ), $passphrase );
				if ( $privateKey !== false ) {
					$readableCert		=	( @file_exists( $public_key_path ) && @is_readable( $public_key_path ) );
					if ( $readableCert ) {
						$certificate	=	openssl_x509_read( file_get_contents( $public_key_path ) );
						if ( $certificate != false ) {
							$corresponds	=	openssl_x509_check_private_key( $certificate, $privateKey );
							if ( $corresponds ) {
								$return		.=	"Private and public keys are a pair matching each other.";
								$ok			=	true;
								if ( function_exists( 'openssl_pkey_get_details' ) ) {
									$details	=	openssl_pkey_get_details( $privateKey );
									if ( $details !== false ) {
										/*
										foreach ( $details as $k => $v ) {
											$return	.=	$k . ': ' . $v . '<br />';
										}
										$return	.=	var_export( $details, true ) . '<br />';
										*/
										
										$return .=	isset( $details['bits'] ) ?	'<br />' . "Private key bits: " . $details['bits'] : '';
									}
								}
							} else {
								$return		=	sprintf( "Valid Private key File %s is not matching valid Public certificate File %s.", $filePath, $public_key_path );
							}
						} else {
							$return		=	sprintf( "Public certificate File %s is not a X509 certificate, can't check matching with private key.", $public_key_path );
						}
					} else {
						$return		=	sprintf( "Public certificate File %s is not existing or not readable by web server process, can't check matching with private key.", $filePath );
					}
				} else {
					$return		=	sprintf( "Private key File %s is either not a private key or the password provided with this key is not valid.", $filePath );
				}
			} else {
				$return		=	sprintf( "Private key File %s is not existing or not readable by web server process.", $filePath );
			}
		} else {
			$return		=	"Private key file is not defined.";
		}
		return $this->_outputGreenRed( '', $ok, $return, $return );
	}
	/**
	 * Utility to output text green or red with css
	 *
	 * @param  string   $text
	 * @param  boolean  $condition
	 * @param  string   $textOK
	 * @param  string   $KOtext
	 * @return string
	 */
	function _outputGreenRed( $text, $condition, $textOK = "loaded", $KOtext = "not available" ) {
		if ( $condition ) {
			$return	=	'<div class="cbEnabled">' . htmlspecialchars( $text ) . ' ' . $textOK . '</div>';
		} else {
			$return	=	'<div class="cbDisabled">' . htmlspecialchars( $text ) . ' ' . $KOtext . '</div>';
		}
		return $return;
	}
	/**
	 * Utility function to return title with description as hover
	 *
	 * @param  CBSimpleXMLElement  $node
	 * @return null|string
	 */
	function _title( &$node ) {
		$description		=	$node->attributes( 'description' );
		if ( $description ) {
			$description	=	CBPTXT::T( $description );
			$name			=	$node->attributes( 'name' );
			return ' title="' . htmlspecialchars( $name . '|' . $description ) .'"';
		}
		return null;
	}
/* was a test-case, unused for now and probably unneeded
	function viewintegrations( &$data, &$params, $control_name, $tabs, $viewType, $htmlFormatting ) {
		global $_PLUGINS;

		$html				=	'';
		$group				=	'user/plug_cbpaidsubscriptions/plugin';
		$_PLUGINS->loadPluginGroup( $group );
		$_PLUGINS->trigger( 'onCPayBeforeBackendPlanDisplay', array( ) );

		$loadedPlugins		=&	$_PLUGINS->getLoadedPluginGroup( $group );
		foreach ( $loadedPlugins as $id => $plugin ) {
			$element		=	$_PLUGINS->loadPluginXML( 'integration', 'plans', $plugin->id );
			$integration	=	$element->getElementByPath( 'payintegration' );
			$paramsEditor	=	new cbParamsEditorController( $data, $element, $element, $plugin );
			$html			.=	$paramsEditor->draw( 'payintegration', null, null, null, null, $control_name, false, $viewType, $htmlFormatting );
			$html			.=	"\n";
		}
		
		return "<tr><td colspan='3'>CALLED !</td></tr>" . $html;
	}
*/
}	// class cbpaidParamsExt
