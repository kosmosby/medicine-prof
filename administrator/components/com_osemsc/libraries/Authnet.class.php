<?php
/**
* Abstraction class for interacting with the Authorize.net Advanced Integration API
*
* Simplifies working with the Authorize.net Advanced Integration API by wrapping
* it in an abstraction class. Changes to the API can be hidden by this class as
* well as simplified code on the client side.
*
* Sample usage:
*
* try
* {
*     $payment = Authnet::instance();
*     $payment -> setTransaction($creditcard, $expiration, $total);
*     $payment -> process();
* }
* catch (Exception $e)
* {
*     die($e->getTraceAsString());
* }
*
* @package Authnet
* @author John Conde
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
*/
class Authnet {
	// Set these variables prior to use
	private $params= array();
	private $results= array();
	private $approved= false;
	private $declined= false;
	private $error= true;
	private $fields;
	private $response;
	public $url;
	private static $instance;
	private function __construct() {
		$this->params['x_delim_data']= "TRUE";
		$this->params['x_delim_char']= "|";
		$this->params['x_relay_response']= "FALSE";
		$this->params['x_url']= "FALSE";
		$this->params['x_version']= "3.1";
		$this->params['x_method']= "CC";
		$this->params['x_type']= "AUTH_CAPTURE";
		$this->params['x_login']= '';
		$this->params['x_tran_key']= '';
	}
	public static function instance() {
		if(!self :: $instance) {
			self :: $instance= new self();
		}
		return self :: $instance;
	}
	public function __clone() {
		throw new Exception("Only one instance of Authnet should be running at one time.");
	}
	public function __toString() {
		if(!$this->params) {
			return (string) $this;
		}
		$output= "";
		$output .= '<table summary="Authnet Results" id="authnet">'."\n";
		$output .= '<tr>'."\n\t\t".'<th colspan="2"><b>Outgoing Parameters</b></th>'."\n".'</tr>'."\n";
		foreach($this->params as $key => $value) {
			$output .= "\t".'<tr>'."\n\t\t".'<td><b>'.$key.'</b></td>';
			$output .= '<td>'.$value.'</td>'."\n".'</tr>'."\n";
		}
		if($this->results) {
			$output .= '<tr>'."\n\t\t".'<th colspan="2"><b>Incomming Parameters</b></th>'."\n".'</tr>'."\n";
			$response= array("Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text", "Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description", "Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name", "Cardholder Last Name", "Company", "Billing Address", "City", "State", "Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name", "Ship to Company", "Ship to Address", "Ship to City", "Ship to State", "Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount", "Tax Exempt Flag", "PO Number", "MD5 Hash", "Card Code (CVV2/CVC2/CID) Response Code", "Cardholder Authentication Verification Value (CAVV) Response Code");
			foreach($this->results as $key => $value) {
				if($key > 40)
					break;
				$output .= "\t".'<tr>'."\n\t\t".'<td><b>'.$response[$key].'</b></td>';
				$output .= '<td>'.$value.'</td>'."\n".'</tr>'."\n";
			}
		}
		$output .= '</table>'."\n";
		return $output;
	}
	public function process($retries= 1) {
		$this->prepareParameters();
		//build the post string
		$poststring= '';
		foreach($this->params AS $key => $val) {
			if($key == "x_card_num") {
				$poststring .= urlencode($key)."=".$val."&";
			} else {
				$poststring .= urlencode($key)."=".urlencode($val)."&";
			}
		}
		$count= 0;
		while($count < $retries) {
			// strip off trailing ampersand
			$poststring= substr($poststring, 0, -1);
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
			$this->response= OSECONNECTOR :: send_request_via_fsockopen($this->url, '/gateway/transact.dll', $poststring);
			$this->parseResults();
			if($this->getResultResponseFull() == "Approved") {
				$this->approved= true;
				$this->declined= false;
				$this->error= false;
				break;
			} else
				if($this->getResultResponseFull() == "Declined") {
					$this->approved= false;
					$this->declined= true;
					$this->error= false;
					break;
				}
			$count++;
		}
	}
	private function prepareParameters() {
		foreach($this->params as $key => $value) {
			$this->fields .= "$key=".urlencode($value)."&";
		}
	}
	private function parseResults() {
		$c_mccomb= '|';
		$resultmm= '';
		$foundmm= 0;
		$iimm= 0;
		for($imm= 0; $imm < strlen($this->response); $imm++) {
			if(!$foundmm) {
				if($this->response[$imm] == $c_mccomb) {
					$foundmm= 1;
					$resultmm .= $this->response[$imm -1];
					$iimm++;
				}
			}
			if($foundmm) {
				$resultmm .= $this->response[$imm];
				$iimm++;
			}
		}
		$response= explode("|", $resultmm);
		// Strip off quotes from the first response field
		$response[0]= str_replace('"', '', $response[0]);
		$this->results= $response;
		//$this->results = explode("|", $this->response);
	}
	public function setTransaction($cardnum, $expiration, $amount, $cvv= null, $invoice= null, $tax= null) {
		$this->params['x_card_num']= (string) trim($cardnum);
		$this->params['x_exp_date']= (string) $expiration;
		$this->params['x_amount']= (float) $amount;
		$this->params['x_po_num']= (int) $invoice;
		$this->params['x_tax']= (float) $tax;
		$this->params['x_card_code']= (string) $cvv;
		if(empty($this->params['x_card_num']) || empty($this->params['x_exp_date']) || empty($this->params['x_amount'])) {
			throw new Exception("Required information for transaction processing omitted.");
		}
	}
	public function setParameter($field= "", $value= null) {
		$field=(is_string($field)) ? trim($field) : $field;
		$value=(is_string($value)) ? trim($value) : $value;
		if(!is_string($field)) {
			throw new Exception("setParameter() arg 1 must be a string or integer: ".gettype($field)." given.");
		}
		if(!is_string($value) && !is_numeric($value) && !is_bool($value)) {
			throw new Exception("setParameter() arg 2 must be a string, integer, or boolean value: ".gettype($value)." given.");
		}
		if(empty($field)) {
			throw new Exception("setParameter() requires a parameter field to be named.");
		}
		if($value === "") {
			throw new Exception("setParameter() requires a parameter value to be assigned: $field");
		}
		$this->params[$field]= $value;
	}
	public function setTransactionType($type= "") {
		$type= strtoupper(trim($type));
		$typeArray= array("AUTH_CAPTURE", "AUTH_ONLY", "PRIOR_AUTH_CAPTURE", "CREDIT", "CAPTURE_ONLY", "VOID");
		if(!in_array($type, $typeArray)) {
			throw new Exception("setTransactionType() requires a valid value to be assigned.");
		}
		$this->params['x_type']= $type;
	}
	public function getResultResponse() {
		return $this->results[0];
	}
	public function getResultResponseFull() {
		$response= array("", "Approved", "Declined", "Error");
		return $response[$this->results[0]];
	}
	public function isApproved() {
		return $this->approved;
	}
	public function isDeclined() {
		return $this->declined;
	}
	public function isError() {
		return $this->error;
	}
	public function getResponseSubcode() {
		return $this->results[1];
	}
	public function getResponseCode() {
		return $this->results[2];
	}
	public function getResponseText() {
		return $this->results[3];
	}
	public function getAuthCode() {
		return $this->results[4];
	}
	public function getAVSResponse() {
		return $this->results[5];
	}
	public function getTransactionID() {
		return $this->results[6];
	}
	public function getInvoiceNumber() {
		return $this->results[7];
	}
	public function getDescription() {
		return $this->results[8];
	}
	public function getAmount() {
		return $this->results[9];
	}
	public function getPaymentMethod() {
		return $this->results[10];
	}
	public function getTransactionType() {
		return $this->results[11];
	}
	public function getCustomerID() {
		return $this->results[12];
	}
	public function getCHFirstName() {
		return $this->results[13];
	}
	public function getCHLastName() {
		return $this->results[14];
	}
	public function getCompany() {
		return $this->results[15];
	}
	public function getBillingAddress() {
		return $this->results[16];
	}
	public function getBillingCity() {
		return $this->results[17];
	}
	public function getBillingState() {
		return $this->results[18];
	}
	public function getBillingZip() {
		return $this->results[19];
	}
	public function getBillingCountry() {
		return $this->results[20];
	}
	public function getPhone() {
		return $this->results[21];
	}
	public function getFax() {
		return $this->results[22];
	}
	public function getEmail() {
		return $this->results[23];
	}
	public function getShippingFirstName() {
		return $this->results[24];
	}
	public function getShippingLastName() {
		return $this->results[25];
	}
	public function getShippingCompany() {
		return $this->results[26];
	}
	public function getShippingAddress() {
		return $this->results[27];
	}
	public function getShippingCity() {
		return $this->results[28];
	}
	public function getShippingState() {
		return $this->results[29];
	}
	public function getShippingZip() {
		return $this->results[30];
	}
	public function getShippingCountry() {
		return $this->results[31];
	}
	public function getTaxAmount() {
		return $this->results[32];
	}
	public function getDutyAmount() {
		return $this->results[33];
	}
	public function getFreightAmount() {
		return $this->results[34];
	}
	public function getTaxExemptFlag() {
		return $this->results[35];
	}
	public function getPONumber() {
		return $this->results[36];
	}
	public function getMD5Hash() {
		return $this->results[37];
	}
	public function getCVVResponse() {
		return $this->results[38];
	}
	public function getCAVVResponse() {
		return $this->results[39];
	}
}
?>