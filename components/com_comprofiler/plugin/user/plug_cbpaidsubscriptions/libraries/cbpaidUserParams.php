<?php
/**
* @version $Id: cbpaidUserParams.php 1541 2012-11-23 22:21:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage User params with encryption class
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to store safely user-specific parameters
 */
class cbpaidUserParams extends cbpaidConfig {
	/**
	 * Gets instance of user parameters for $user_id of type $type
	 *
	 * @param  int               $userId
	 * @param  string            $type
	 * @param  string            $prefix
	 * @return cbpaidUserParams
	 */
	public static function getUserParamsInstance( $userId, $type = 'params', /** @noinspection PhpUnusedParameterInspection */ $prefix = '' ) {
		static $cache							=	array();
		if ( ! isset( $cache[(string) $userId][$type] ) ) {
			$userParams							=	new cbpaidUserParams();
			if ( ! $userParams->loadUserParams( $userId, $type ) ) {
				$userParams->user_id			=	$userId;
				$userParams->type				=	$type;
			}
			$cache[(string) $userId][$type]		=	$userParams;
		}
		return $cache[(string) $userId][$type];
	}
	/**
	* loads the parameters of the user
	* Not cached
	* Used internally. Do not use, use getUserParam instead.
	*
	* @param  int                      $userId  User id
	* @param  string                   $type    Type of parametrs to load
	* @return ParamsInterface|boolean           Parameters or FALSE if database error or not found
	*/
	public function loadUserParams( $userId, $type = 'params' ) {
		$result				=	$this->loadThisMatching( array( 'user_id' => (int) $userId, 'type' => (string) $type ) );
		if ( $result ) {
			$result			=	new Registry( $this->params );
		}
		return $result;
	}
	/**
	 * Get an attribute of this stored object
	 *
	 * @param  string    $paramName     The name of the parameter
	 * @param  mixed     $default       The default value of the parameter
	 * @return mixed
	 */
	public function getUserParam( $paramName, $default = null ) {
		return $this->getParam( $paramName, $default, $this->type );
	}
	/**
	 * Sets an attribute of this stored object
	 *
	 * @param  string    $paramName     The name of the parameter
	 * @param  string    $value         The value of the parameter
	 * @return cbpaidUserParams         For chaining
	 */
	public function setUserParam( $paramName, $value ) {
		$this->setParam( $paramName, $value, $this->type );
		return $this;
	}
	/**
	 * Encrypts and signs a PHP datastructure as JSON with AES128
	 *
	 * @param  mixed   $cleartextData  Any json_encode() -able PHP data-structure 
	 * @param  string  $secretKeyPart  The secret key to encrypt (another part of the secret key is added in this function)
	 * @param  string  $signaturePart
	 * @return string
	 */
	public function encryptAliasInfo( $cleartextData, $secretKeyPart, $signaturePart ) {
		$dataToCrypt				=	json_encode( $cleartextData );

		$secret3					=	'ORm387ZuAqyjHF!L3CsqYaUr4*i9)uw(du24MnCsI' . $secretKeyPart;
		$secret4					=	'ThisIsJustToHash7425987jklnjnHOIUFHBHHbjhbsdldbvs' . $signaturePart;
		$random						=	base64_encode( mt_rand() );
		
		$aes						=	new cbpaidAES128();
		$aeskey						=	$aes->makeKey( $secret3 );
		$responseString				=	$aes->toHexString( $aes->encrypt( $dataToCrypt, $aeskey ) );

		return $responseString . '-' . $random . '-' . sha1( $random . $secret4 . $responseString );
	}
	/**
	 * Decrypts and verifies signature of a PHP datastructure encrypted with function encryptAliasInfo() as JSON with AES128
	 *
	 * @param  string  $cryptedAlias
	 * @param  string  $secretKeyPart
	 * @param  string  $signaturePart
	 * @return mixed|null
	 */
	public function decryptAliasInfo( $cryptedAlias, $secretKeyPart, $signaturePart ) {
		if ( $cryptedAlias && $secretKeyPart ) {
			$resultArray		=	explode( '-', $cryptedAlias );
			if ( count( $resultArray ) == 3 ) {
				$secret3			=	'ORm387ZuAqyjHF!L3CsqYaUr4*i9)uw(du24MnCsI' . $secretKeyPart;
				$secret4			=	'ThisIsJustToHash7425987jklnjnHOIUFHBHHbjhbsdldbvs' . $signaturePart;

				$aes				=	new cbpaidAES128();
				$aeskey				=	$aes->makeKey( $secret3 );
				$result				=	$aes->decrypt( $aes->fromHexString( $resultArray[0] ), $aeskey );
				$hash				=	sha1( $resultArray[1] . $secret4 . $resultArray[0] );
				if ( $hash == $resultArray[2] ) {
					return json_decode( $result, true );
				}
			}
		}
		return null;
	}
}
