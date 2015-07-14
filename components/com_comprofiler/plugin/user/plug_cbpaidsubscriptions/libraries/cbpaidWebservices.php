<?php
/**
 * @version $Id: cbpaidWebservices.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CBSubs HTTPS remote web-services requests class
 */
class cbpaidWebservices {
	/**
	 * Posts a POST form by https if available, otherwise by http and gets result.
	 *
	 * @param  string  $urlNoHttpsPrefix  URL without https:// in front (but works also with http:// or https:// in front, but it's ignored.
	 * @param  array|string  $formvars          Variables in form to post
	 * @param  int     $timeout           Timeout of the access
	 * @param  string  $result            RETURNING: the fetched access
	 * @param  int     $status            RETURNING: the status of the access (e.g. 200 is normal)
	 * @param  string  $getPostType       'post' (default) or 'get'
	 * @param  string  $contentType       'normal' (default) or 'xml' ($formvars['xml']=xml content) or 'json' (application/json)
	 * @param  string  $acceptType        '* / *' (default) or 'application/xml' or 'application/json'
	 * @param  boolean $https             SSL protocol (default: true)
	 * @param  int     $port              port number (default: 443)
	 * @param  string  $username          HTTP username authentication
	 * @param  string  $password          HTTP password authentication
	 * @param  boolean $allowHttpFallback Allow fallback to http if https not available locally (default: false)
	 * @param  string  $referer           referrer
	 * @return int     $error             error-code of access (0 for ok)
	 */
	public static function httpsRequest( $urlNoHttpsPrefix, $formvars, $timeout, &$result, &$status, $getPostType = 'post', $contentType='normal', $acceptType='*/*', $https = true, $port = 443, $username = '', $password = '', $allowHttpFallback = false, $referer = null ) {
		$urlNoHttpsPrefix	=	preg_replace( '/^https?:\/\//', '', $urlNoHttpsPrefix );

		/* This is broken on both KreativMedia servers, don't use it until proven to work: on Nick's it's php-maxexectime-timeouting, and on Janus, it's returning nothing and no error.
				if ( extension_loaded('curl') && is_callable( 'curl_init' ) ) {

					// try CURL library:

					$posturl		=	( $https ? 'https://' : 'http://' ) . $urlNoHttpsPrefix;
					$ch = curl_init();
					if ( $ch !== false ) {
						curl_setopt( $ch, CURLOPT_URL,				$posturl );
						curl_setopt( $ch, CURLOPT_PORT,				$port );
						curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT,	$timeout );
						curl_setopt( $ch, CURLOPT_RETURNTRANSFER,	1 );
						if ( $https ) {
						//	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,	true );		//TBD: check this!
						//	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,	2 );
							curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,	false );
						} else {
							curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,	false );
						}
						if ( $getPostType == 'post' ) {
							curl_setopt($ch, CURLOPT_POST,			1 );
						}

						if ( $contentType == 'normal' )  {
							curl_setopt( $ch, CURLOPT_POSTFIELDS,	$formvars );
						} elseif ( $contentType == 'xml' )  {
							$headers		=	array( 'Content-Type: text/xml' );
			REMOVE THAT SPACE!!!				if ( $acceptType != '* /*' ) {
								$headers[]	=	'Accept: ' . $acceptType;
							}
							curl_setopt( $ch, CURLOPT_HTTPHEADER,	$headers );
							curl_setopt( $ch, CURLOPT_HEADER,		0 );
							curl_setopt( $ch, CURLOPT_POSTFIELDS,	$formvars );
						} elseif ( $contentType == 'json' )  {
							$headers		=	array( 'Content-Type: text/json' );
			REMOVE THAT SPACE!!!				if ( $acceptType != '* /*' ) {
								$headers[]	=	'Accept: ' . $acceptType;
							}
							curl_setopt( $ch, CURLOPT_HTTPHEADER,	$headers );
							//? curl_setopt( $ch, CURLOPT_HEADER,		0 );
							curl_setopt( $ch, CURLOPT_POSTFIELDS,	$formvars );
						}
						if ( $referer ) {
							curl_setopt( $ch, CURLOPT_REFERER,		$referer );
						}

						if ( $username || $password ) {
							curl_setopt( $ch, CURLOPT_HTTPAUTH,		CURLAUTH_ANY );
							curl_setopt( $ch, CURLOPT_USERPWD,		$username . ':' . $password );
						}
						$result	= curl_exec( $ch );
						$error	= curl_error( $ch );
						$status	= curl_getinfo( $ch, CURLINFO_HTTP_CODE );
						curl_close( $ch );

						if ( $result !== false ) {
							return $error;
						}
					}

				}
		*/
		if ( is_callable( 'fsockopen' ) && ( ( ! $https ) || ( version_compare( phpversion(), '4.3.0', '>' ) && extension_loaded( 'openssl' ) && defined( 'OPENSSL_VERSION_TEXT' ) ) ) ) {

			// otherwise try fsockopen:

			if ( is_array( $formvars ) ) {
				$formUrl			=	array();
				foreach ( $formvars as $k => $v ) {
					$formUrl[$k]	=	urlencode( $k ) . '=' . urlencode( $v );
				}
				$formUrl			=	implode( '&', $formUrl );
			} else {
				$formUrl			=	$formvars;
			}
			$urlParts				=	explode( '/', $urlNoHttpsPrefix, 2 );

			$posturl				=	( $https ? 'ssl://' : 'tcp://' ) . $urlParts[0];

			if ( $getPostType == 'post' ) {
				$header				=	'POST';
			} else {
				$header				=	'GET';
			}
			$header					.=	' /' . ( count( $urlParts ) == 2 ? $urlParts[1] : '' ) . " HTTP/1.0\r\n";
			$header					.=	'Host: ' . $urlParts[0] . "\r\n";
			if ( $username || $password ) {
				$header				.=	'Authorization: Basic ' . base64_encode( $username . ':' . $password ) . "\r\n";
			}
			$header					.=	'User-Agent: PHP Script' . "\r\n";
			if ( $referer ) {
				$header				.=	'Referer: ' . $referer . "\r\n";
			}
			if ( $contentType == 'xml' ) {
				$header				.=	'Content-Type: application/xml' . "\r\n";
			} elseif ( $contentType == 'json' ) {
				$header				.=	'Content-Type: application/json' . "\r\n";
			} else {
				$header				.=	'Content-Type: application/x-www-form-urlencoded' . "\r\n";			//TODO: 				$header				.=	'Content-Type: application/x-www-form-urlencoded;charset=\"utf-8\"' . "\r\n";
			}
			if ( $acceptType != '*/*' ) {
				$header				.=	'Accept: ' . $acceptType . "\r\n";
			}
			$header					.=	'Content-Length: ' . strlen( $formUrl ) . "\r\n\r\n";
			$error					=	null;
			$errstr					=	null;
			$status					=	100;

			$fp						=	@fsockopen ( $posturl, $port, $error, $errstr, $timeout );
			if ( $fp ) {
				if ( is_callable( 'stream_set_timeout' ) ) {
					stream_set_timeout( $fp, $timeout );
				}
				$bytesWritten		=	@fwrite( $fp, $header . $formUrl );
				if ( $bytesWritten !== false ) {
					$response		=	array();
					$result			=	'';
					while (!feof($fp)) {
						$line		=	@fgets ( $fp, 8096 );
						if ( trim( $line ) == '' ) {
							break;
						}
						$response[]	=	$line;
					}
					while (!feof($fp)) {
						$result		.=	@fgets ( $fp, 8096 );
					}
					@fclose ( $fp );

					if ( count( $response ) > 0 ) {
						$parts				=	explode( ' ', $response[0], 3 );
						if ( count( $parts ) == 3 ) {
							if ( ( trim( $parts[2] ) == 'OK' ) || ( preg_match( '/^\s*\d{3}\s*/', $parts[1] ) ) ) {
								$status		=	(int) $parts[1];	// 200 hopefully.
							}
						}
					}
					return $error;
				} else {
					fclose( $fp );
				}
			}

		}
		{
			// then try using curl executable:

			cbimport( 'cb.snoopy' );

			$curl_found				=	false;
			$path					=	null;
			if(function_exists('is_executable')) {
				$paths = array( '/usr/bin/curl', '/usr/local/bin/curl', 'curl' );	// IN SNOOPY ALREADY: '/usr/local/bin/curl'
				foreach ($paths as $path) {
					if ( @is_executable( $path ) ) {
						$curl_found = true;
						break;
					}
				}
			}

			//		if ( $curl_found ) {		// we will do http as last resort without using curl if curl_found == false:

			$s					=	new CBSnoopy();
			$s->curl_path = $path;

			if ( ! is_array( $formvars ) ) {
				if ( $contentType == 'xml' ) {
					$formvars	=	array( 'xml' => $formvars );
				} else {
					$formarr	=	explode( '&', $formvars );
					$formvars	=	array();
					foreach ( $formarr as $v ) {
						$p		=	explode( '=', $v, 2 );
						if ( count( $p ) == 2 ) {
							$formvars[$p[0]]	=	urldecode( $p[1] );
						}
					}
				}
			}

			$s->read_timeout	=	$timeout;

			if ( $username || $password ) {
				$s->user		=	$username;
				$s->pass		=	$password;
			}

			if ($contentType == 'xml' ) {
				$s->set_submit_xml();
			} elseif ($contentType == 'json' ) {
				// available after CB 1.2.1 :
				if ( is_callable( array( $s, 'set_submit_json' ) ) ) {
					$s->set_submit_json();
				}
			}
			if ( $acceptType ) {
				$s->accept		=	$acceptType;
			}

			if ( ( (int) $port ) && ( ( $https & ( $port != 443 ) ) || ( ( ! $https ) & ( $port != 80 ) ) ) ) {
				$portPostfix	=	':' . (int) $port;
			} else {
				$portPostfix	=	'';
			}
			if ( (int) $port ){
				$s->port		=	$port;
			}
			$posturl		=	( ( $https && ( $curl_found || ! $allowHttpFallback ) ) ? 'https://' : 'http://' ) . $urlNoHttpsPrefix . $portPostfix;
			/* $return = */
			if ( $referer ) {
				$s->referer	=	$referer;
			}
			@$s->submit( $posturl, $formvars);
			$status = $s->status;
			$error = $s->error;
			$result = $s->results;

			//		}

		}
		return $error;
	}
}