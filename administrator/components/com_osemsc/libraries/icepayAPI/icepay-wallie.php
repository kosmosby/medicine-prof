<?php
/**
* ICEPAY Library for PHP
* (c) 2009 ICEPAY
*
* This file contains the ICEPAY Library for PHP.
*
* @author ICEPAY.eu (support@icepay.eu)
* @version 1.0.5
*/
class ICEPAY_Wallie extends ICEPAY
{
	public function Pay( $language = NULL, $currency = NULL, $amount = NULL, $description = NULL )
	{
		$this->issuer			= 'WALLIE';
		$this->country			= "00";
		$this->assignLanguage	( $language );
		$this->assignCurrency	( $currency );
		$this->assignAmount		( $amount );
		$this->description		= $description;
		$this->paymentMethod	= "WALLIE";

		return $this->postRequest( $this->basicMode(), $this->prepareParameters() );
	}	
}

?>