<?php
/**
* @version $Id: validation.php 1619 2013-01-09 23:58:29Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/*
 * List of EU countries is below in $EUcountries, as well as in the XML file xml/edit.invoice.xml
 */
/*
//echo tep_verif_tva('DE266348510'); //down SOAP Fault: (faultcode: soapenv:Server, faultstring: { 'MS_UNAVAILABLE' })
//echo tep_verif_tva('FR67388237'); //no verif
//echo tep_verif_tva('FR70401437397'); //true
//echo tep_verif_tva('EL039416045'); //individual no verif
//echo tep_verif_tva('GB235323781'); //true
//echo tep_verif_tva('EL099936189'); //true WIND
//echo tep_verif_tva('EL094493766'); //true
echo tep_verif_tva('BE0459360623'); // Ogone true
echo tep_verif_tva('ATU63350288'); // Ogone true
echo tep_verif_tva('DE814551327'); // Ogone true
echo tep_verif_tva('GB943928390'); // Ogone true

//http://www.oup.com/uk/help/ordering/ :
echo tep_verif_tva('GB195275334'); // true
echo tep_verif_tva('BE0453521619');	// no_verif
echo tep_verif_tva('DK21832073');	// true
echo tep_verif_tva('FI15408594');	// true
echo tep_verif_tva('FR84350934675');	// true
echo tep_verif_tva('DE112144487');	// down -> true
echo tep_verif_tva('EL998160526');	// true
echo tep_verif_tva('IE9507061A');	// true
echo tep_verif_tva('IT11492960155');	//true
echo tep_verif_tva('NL817695783B01');	// true
echo tep_verif_tva('ESN0065351I');		//no_verif
echo tep_verif_tva('SE502052817901');	// true
echo tep_verif_tva('FR70401437397');	// true
 */

/**
* Paid Subscriptions Tab Class for handling the CB tab api
*/
class cbpaidValidate_tax_eu extends cbpaidValidate {
	protected $EUcountries	=	array('AT','BE','BG','CY','CZ','DE','DK','EE','GR','ES','FI','FR','GB','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK');
/*
	protected function tep_verif_tva($vat_number) {
		$countryCode	=	substr($vat_number, 0, 2);
		$vatNumber		=	substr($vat_number, 2);

		$client			=	new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", array('exceptions' => 0) );

// $params = array('countryCode' => $countryCode, 'vatNumber' => $vatNumber);
// $result = $client->checkVat($params);

// $params = array('countryCode' => $countryCode, 'vatNumber' => $vatNumber, 'traderName' => 'OXFORD UNIVERSITY PRESS' );
		$params			=	array('countryCode' => $countryCode, 'vatNumber' => $vatNumber, 'traderName' => 'OXFORD UNIVERSITY PRESS', 'traderCompanyType' => 'GB-1', 'traderStreet' => 'GREAT CLARENDON STREET', 'traderPostcode' => 'OX2 6DP' , 'traderCity' => 'OXFORD',  'requesterCountryCode' => 'FR', 'requesterVatNumber' => '70401437397' );

		$result			=	$client->checkVatApprox($params);
		if ( is_soap_fault( $result ) ) {
			trigger_error( "SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_NOTICE);
		}
		if ( ! $result->valid ) {
			return $vat_number . ': no_verif' . '<br />';
		} else {
			return $vat_number . ':true' . '<br />' . var_export($result, true) . '<br/><br/>';
		}
		return false;
	}
*/

	/**
	 * Encodes JSON the raw logs of the VAT verification
	 *
	 * @param  string  $status
	 * @param  string  $subStatus
	 * @param  array   $params
	 * @param  object  $result
	 * @return string
	 */
	protected function encodeVatVerification( $status, $subStatus, $params, $result ) {
		global $_CB_framework;

		$rawlogs					=	array( 'status' => $status, 'identifier' => $subStatus, 'request' => $params, 'result' => $result, 'time' => date( 'Y-m-d H:i:s', $_CB_framework->now() ) );
		return ( is_callable( 'json_encode' ) ? json_encode( $rawlogs ) : var_export( $rawlogs, true ) );
	}
	/**
	 * Checks an exact VAT number using EU VIES checkVat SOAP call
	 *
	 * @param  string  $vat_number
	 * @param  string  $userMessage  OUTPUT: String to customer
	 * @return boolean
	 */
	public function checkVatNumber( $vat_number, &$userMessage ) {
		$vatCountryCode				=	strtoupper( substr( $vat_number, 0, 2 ) );
		$cleanVatNumber				=	$this->cleanVatNumber( $vat_number );
		if ( ( $cleanVatNumber !== false ) && $this->checkCountryInEU( ( $vatCountryCode == 'EL' ? 'GR' : $vatCountryCode ) ) ) {
			try {
				$client				=	new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", array( 'exceptions' => true ) );
				$params				=	array('countryCode' => $vatCountryCode, 'vatNumber' => $cleanVatNumber);
				/** @noinspection PhpUndefinedMethodInspection */
				$result				=	$client->checkVat( $params );
				$checked			=	true;
			}
			catch ( \SoapFault $e ) {
				$result				=	$e->getMessage();
				$checked			=	false;
			}

			if ( ( ! $checked ) || is_soap_fault( $result ) ) {
				if ( ! $checked ) {
					$userMessage	=	CBPTXT::Th("EU VIES VAT number verification server unreachable. VAT number could not be checked. Proceed with VAT or try again later.");
					return false;
				} elseif ( isset( $result->faultstring ) && ( $result->faultstring == 'INVALID_INPUT' ) ) {
					$userMessage	=	CBPTXT::T("Invalid EU VAT Number. EU VAT numbers start with country code and must be valid.");
					return false;
				} else {
					$userMessage	=	CBPTXT::T("Could not check EU VAT Number, EU or country service not available now.");
					return null;
				}
			}
			/** @var StdClass $result */
			if ( $result->valid ) {
				$userMessage		=	CBPTXT::T("EU VAT Number is valid.");
			} else {
				$userMessage		=	CBPTXT::T("Invalid EU VAT Number. EU VAT numbers start with country code and must be valid.");;
			}
			return $result->valid;
		} else {
			$userMessage			=	CBPTXT::T("Invalid EU VAT Number. EU VAT numbers start with country code and must be valid.");
			return false;
		}				
	}
	/**
	 * Checks the VAT number using EU VIES checkVatApprox SOAP call
	 *
	 * @param  array                $params
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $vat_verification  OUTPUT: raw log of the verification for storage
	 * @return int                  1: Verification Passed, 0: not passed
	 */
	public function checkVatApprox( $params, $paymentBasket, &$vat_verification )
	{
		try {
			$client						=	new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", array( 'exceptions' => true ) );
			/** @noinspection PhpUndefinedMethodInspection */
			$result						=	$client->checkVatApprox($params);
			$checked					=	true;
		}
		catch ( \SoapFault $e ) {
			$result						=	$e->getMessage();
			$checked					=	false;
		}

		/** @var StdClass $result */

		// log result
		// echo 'Params: '; var_export( $params ); echo "<br />\nResult: "; var_export( $result );

		if ( ( ! $checked ) || is_soap_fault( $result ) ) {
			// FaultString can take the following specific values:
			// - INVALID_INPUT: The provided Country Code is invalid or the VAT number is empty;  This is the only final error state.
			// - SERVICE_UNAVAILABLE: The EU VIES SOAP service is unavailable, try again later;
			// - MS_UNAVAILABLE: The Member State service is unavailable at this time, try again later: http://ec.europa.eu/taxation_customs/vies/viesspec.do
			// - TIMEOUT: The Member State service could not be reached in time, try again later;
			// - SERVER_BUSY: The service can't process your request. Try again latter.
			if ( ! $checked ) {
				$vat_verification		=	$this->encodeVatVerification( 'SOAPSERVERFAULT', null, $params, $result );
				$userMessage			=	CBPTXT::Th("EU VIES VAT number verification server unreachable. VAT number could not be checked. Proceed with VAT or try again later.");
			} elseif ( isset( $result->faultstring ) ) {
				$vat_verification		=	$this->encodeVatVerification( $result->faultstring, $result->faultcode, $params, $result );
				$userMessage			=	( $result->faultstring == 'INVALID_INPUT' ? CBPTXT::T("Invalid EU VAT Number. EU VAT numbers start with country code and must be valid.") : null );
			} else {
				$vat_verification		=	$this->encodeVatVerification( 'SOAPFAULT', null, $params, $result );
				$userMessage			=	null;
			}
			cbpaidApp::getBaseClass()->setLogErrorMSG( 5, $paymentBasket, sprintf( CBPTXT::T('EU VAT VIES error condition: "%s" for request on VAT: "%s%s", faultcode: "%"'), $result->faultstring, $params['countryCode'], $params['vatNumber'], $result->faultcode ), $userMessage );
			return 0;
		}

		if ( ! $result->valid ) {
			$vat_verification			=	$this->encodeVatVerification( 'INVALID', $params['countryCode'] . $params['vatNumber'], $params, $result );
			$userMessage				=	CBPTXT::T("Invalid EU VAT Number. EU VAT numbers start with country code and must be valid.");
			cbpaidApp::getBaseClass()->_setErrorMSG( $userMessage );
			return 0;
		} else {
			$matchesToCheck					=	array( 'traderNameMatch', /* 'traderCompanyTypeMatch', */ 'traderStreetMatch', 'traderPostcodeMatch', 'traderCityMatch' );
			foreach ( $matchesToCheck as $match ) {
				// 1=VALID, 2=INVALID:
				if ( isset( $result->$match ) && ( $result->$match == 2 ) ) {
					$vat_verification	=	$this->encodeVatVerification( 'MISMATCH', strtoupper( substr( $match, 6, -5 ) ), $params, $result );
					return 0;
				}
			}
			// requestIdentifier, requestDate, valid
			// countryCode vatNumber
			// traderName traderCompanyType traderAddress traderStreet traderPostcode traderCity

			$vat_verification			=	$this->encodeVatVerification( 'VALID', ( $result->requestIdentifier ? $result->requestIdentifier : '-' ) . ' / ' . $result->requestDate, $params, $result );
			return 1;
		}
	}
	/**
	 * Checks if $country_code is in EUcountries
	 *
	 * @param  string  $country_code  2-letters ISO country code
	 * @return boolean                TRUE: EU-country, FALSE: Non-EU
	 */
	protected function checkCountryInEU( $country_code ) {
		return ( in_array( $country_code, $this->EUcountries ) );
	}
	/**
	 * Checks if $country_code matches the $vatCountryCode (taking in account the greek EL vs GR exception)
	 *
	 * @param  string  $country_code    2-letters ISO country code (GR for Greece)
	 * @param  string  $vatCountryCode  2-letters VAT country code (EL for Greece)
	 * @return boolean                  TRUE: Matches
	 */
	protected function checkCountryMatch( $country_code, $vatCountryCode ) {
		return ( ( $country_code == $vatCountryCode ) || ( ( $vatCountryCode == 'EL' ) && ( $country_code == 'GR' ) ) );
	}
	/**
	 * Cleans the VAT number to match EU VIES specification (removing , . - and spaces)
	 *
	 * @param  string          $fullVatNumber  2-12 digits VAT number
	 * @return string|boolean                  STRING: Cleaned VAT number, FALSE: not matching EU Format
	 */
	protected function cleanVatNumber( $fullVatNumber ) {
		// $cleanVat	=	substr( preg_replace( '/[^0-9a-zA-Z]/', '', $fullVatNumber ), 2 );
		$cleanVat		=	substr( preg_replace( "/[,\\.\\- ']/", '', $fullVatNumber ), 2 );
		if ( preg_match( '/^[0-9A-Za-z\+\*\.]{2,12}$/', $cleanVat ) ) {
			return $cleanVat;
		} else {
			return false;
		}
	}
	/**
	 * Validates and computes business status on payment invoice save 
	 *
	 * @param  cbpaidPaymentBasket          $paymentBasket
	 * @param  cbpaidsalestaxTotalizertype  $salestaxTotalizerType
	 */
	public function validateInvoiceAddress( $paymentBasket, $salestaxTotalizerType ) {
		static $cache				=	array();

		$country_code				=	$paymentBasket->address_country_code;
		$vatCountryCode				=	strtoupper( substr( $paymentBasket->vat_number, 0, 2 ) );
		$cleanVatNumber				=	$this->cleanVatNumber( $paymentBasket->vat_number );
		if ( $this->checkCountryInEU( $country_code ) ) {
			if ( ! ( $paymentBasket->payer_business_name && $paymentBasket->vat_number ) ) {
					$paymentBasket->is_business				=	0;
			} elseif ( $this->checkCountryMatch( $country_code, $vatCountryCode ) ) {
				if ( $cleanVatNumber !== false ) {
					$viesArgs		=	array(	'countryCode'		=>	$vatCountryCode,
												'vatNumber'			=>	$cleanVatNumber,
												'traderName'		=>	$paymentBasket->payer_business_name,
												// 'traderCompanyType'	=>	'GB-1',		// ?
												'traderStreet'		=>	$paymentBasket->address_street,
												'traderPostcode'	=>	$paymentBasket->address_zip,
												'traderCity'		=>	$paymentBasket->address_city,
										);
					// check if we want to transmit the seller VAT number (to get the requestIdentifier in return as proof of request):
					$seller_taxnumber						=	$salestaxTotalizerType->seller_taxnumber;
					if ( $seller_taxnumber ) {
						$viesArgs['requesterCountryCode']	=	substr( $seller_taxnumber, 0, 2 );
						$viesArgs['requesterVatNumber']		=	substr( $seller_taxnumber, 2 );
					}

					$k										=	implode( '|', $viesArgs );
					if ( ! array_key_exists( $k, $cache ) ) {
						$paymentBasket->is_business			=	$this->checkVatApprox( $viesArgs, $paymentBasket, $paymentBasket->vat_verification );
						$cache[$k]							=	$paymentBasket->is_business;
					}
					
				} else {
					// VAT number is not of correct format:
					$userMessage							=	CBPTXT::T("Invalid EU VAT Number. EU VAT numbers start with country code and must be valid.");
					cbpaidApp::getBaseClass()->_setErrorMSG( $userMessage );
					$paymentBasket->is_business				=	0;
				}
			} else {
				// country code and VAT number prefix missmatch:
				$userMessage								=	CBPTXT::T("Invalid VAT Number. VAT numbers start with country code and must match invoice address country.");
				cbpaidApp::getBaseClass()->_setErrorMSG( $userMessage );
				$paymentBasket->is_business					=	0;
			}
		} else {
			// country not in EU: do nothing here !
		}
	}
}
