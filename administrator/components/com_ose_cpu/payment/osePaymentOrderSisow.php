<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderSisow extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function SisowPostForm($orderInfo) 
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');

		$MerchantId = $pConfig->sisow_merchant_id;
		$testmode = $pConfig->sisow_testmode;
		$MerchantKey = $pConfig->sisow_merchant_key;
		$Shop_id = $pConfig->sisow_shop_id;
		
		
		$amount= round($orderInfo->payment_price * 100);
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$desc = self::generateDesc($order_id);
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$IssuerId = $orderInfoParams->sisow_issuerid;
		$payment = $orderInfoParams->sisow_payment;
		$returnUrl = urldecode($orderInfoParams->returnUrl);
		$returnUrl = $returnUrl?$returnUrl:JURI :: base()."index.php?option=com_osemsc&view=member";
		
		$sTransactionRequestUrl = 'ssl://www.sisow.nl:443/Sisow/iDeal/RestHandler.ashx/TransactionRequest';
		
		$aGetData = array();
		$aGetData['shopid'] = $Shop_id;
		$aGetData['payment'] = $payment;
		$aGetData['merchantid'] = $MerchantId;
		$aGetData['purchaseid'] = $order_id;
		$aGetData['entrancecode'] = $order_id;
		$aGetData['amount'] = $amount;
		$aGetData['description'] = $desc;
		$aGetData['issuerid'] = $IssuerId;
		$aGetData['returnurl'] = JURI :: base()."components/com_osemsc/ipn/sisow_notify.php";
		$aGetData['callbackurl'] = JURI :: base()."components/com_osemsc/ipn/sisow_notify.php";
		$aGetData['notifyurl'] = JURI :: base()."components/com_osemsc/ipn/sisow_notify.php";
		//$aGetData['r'] = '86';
		$aGetData['sha1'] = sha1($aGetData['purchaseid'] . $aGetData['entrancecode'] . $aGetData['amount'] . $aGetData['shopid'] . $aGetData['merchantid'] . $MerchantKey);
		$sXmlReply = $this->postToHost($sTransactionRequestUrl . '?' . http_build_query($aGetData), false, true, 30);
		//echo $aGetData['purchaseid'] . $aGetData['entrancecode'] . $aGetData['amount'] . $aGetData['shopid'] . $aGetData['merchantid'] . $MerchantKey;exit;
		//echo $sTransactionRequestUrl . '?' . http_build_query($aGetData);exit;
		if($sXmlReply)
		{
			if($this->parseFromXml('error', $sXmlReply))
			{
				$sErrorCode = $this->parseFromXml('errorcode', $sXmlReply);
				$sErrorMessage = $this->parseFromXml('errormessage', $sXmlReply);
				$result['success']= false;
				$result['title']= 'Error';
				$result['content']= 'Error Code:'.$sErrorCode.'Error Message:'.$sErrorMessage;
				$result['payment_method']= 'sisow';
				return $result;
			}
			elseif($this->parseFromXml('transaction', $sXmlReply))
			{
				$sTransactionId = $this->parseFromXml('trxid', $sXmlReply);
				$sTransactionUrl = $this->parseFromXml('issuerurl', $sXmlReply);
				$TransactionUrl = urldecode($sTransactionUrl);
				$sSignature = $this->parseFromXml('sha1', $sXmlReply);
				// Validate signature
				$sHash = sha1($sTransactionId . $sTransactionUrl . $MerchantId . $MerchantKey);
				//echo $sTransactionId . $sTransactionUrl . $MerchantId . $MerchantKey;echo ":";print_r($sXmlReply);exit;
				if(strcasecmp($sSignature, $sHash) !== 0)
				{
					$result['success']= false;
					$result['title']= 'Error';
					$result['content']= 'Transaction Request Error: Invalid signature.';
					$result['payment_method']= 'sisow';
					return $result;			
				}
			}
			else
			{
				$result['success']= false;
				$result['title']= 'Error';
				$result['content']= 'Transaction Request Error: Invalid response.';
				$result['payment_method']= 'sisow';
				return $result;		
			}
		}
		else
		{
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= 'Transaction Request Error: No response recieved.';
			$result['payment_method']= 'sisow';
			return $result;				
		}
		
		$result['success']= true;
		$result['url']= $TransactionUrl;
		$result['payment_method']= 'sisow';
		return $result;
	}
	
	function getSisowIssuerList()
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');

		$MerchantId = $pConfig->sisow_merchant_id;
		$testmode = $pConfig->sisow_testmode;
		$MerchantKey = $pConfig->sisow_merchant_key;
		$Shop_id = $pConfig->sisow_shop_id;
		
		$aIssuers = array();
		$aGetData = array();
		
		if($testmode)
		{
			$aGetData['test'] = 'true';
		}
		
		$sDirectoryRequestUrl = 'ssl://www.sisow.nl:443/Sisow/iDeal/RestHandler.ashx/DirectoryRequest';
		
		$sXmlReply = $this->postToHost($sDirectoryRequestUrl . '?' . http_build_query($aGetData), false, true, 30);
		if($sXmlReply)
		{
			if($this->parseFromXml('directory', $sXmlReply))
			{
				$a = explode('<issuer>', str_replace('</issuer>', '', $sXmlReply));
				foreach($a as $k => $v)
				{
					$sIssuerId = $this->parseFromXml('issuerid', $v);
					$sIssuerName = $this->parseFromXml('issuername', $v);
					if($sIssuerId && $sIssuerName)
					{
						$aIssuers[$sIssuerId] = $sIssuerName;
					}
				}
			}
			else
			{
				//$this->setError('Invalid reply on Issuer Request.', false, __FILE__, __LINE__);
			}
		}
		else
		{
			//$this->setError('No reply on Issuer Request.', false, __FILE__, __LINE__);
		}
		return $aIssuers;
	}
	
	function generateDesc($order_id)
	{
		$title = null;
        $db = oseDB::instance();
        $query = "SELECT * FROM `#__osemsc_order_item` WHERE `order_id` = '{$order_id}'";
        $db->setQuery($query);
        $obj = $db->loadObject();
        $params = oseJson::decode($obj->params);
        $msc_id = $obj->entry_id;
       
        $query = "SELECT title FROM `#__osemsc_acl` WHERE `id` = ".(int)$msc_id;
        $db->setQuery($query);
        $msc_name = $db->loadResult();
       
        $msc_option = $params->msc_option;
        $query = "SELECT params FROM `#__osemsc_ext` WHERE `type` = 'payment' AND `id` = ".(int)$msc_id;
        $db->setQuery($query);
        $result = oseJson::decode($db->loadResult());
        foreach($result as $key => $value)
        {
            if($msc_option == $key)
            {
                if($value->recurrence_mode == 'period')
                {
                    if($value->eternal)
                    {
                        $title = 'Life Time Membership';
                    }else{
                       
                        $title = $value->recurrence_num.' '.ucfirst($value->recurrence_unit).' Membership';
                    }
                }else{
                    $start_date = date("l,d F Y",strtotime($value->start_date));
                    $expired_date = date("l,d F Y",strtotime($value->expired_date));
                    $title  = $start_date.' - '. $expired_date.' Membership';
                }
               
            }
        }
        $title = $msc_name.' : '.$title;
        return $title;
	}
	
	function postToHost($url, $data, $timeout = 30)
	{
		$__url = $url;
		$idx = strrpos($url, ':');
		$host = substr($url, 0, $idx);
		$url = substr($url, $idx + 1);
		$idx = strpos($url, '/');
		$port = substr($url, 0, $idx);
		$path = substr($url, $idx);

		$fsp = fsockopen($host, $port, $errno, $errstr, $timeout);
		$res = '';
			
		if($fsp)
		{
			// echo "\n\nSEND DATA: \n\n" . $data . "\n\n";

			fputs($fsp, 'POST ' . $path . ' HTTP/1.0' . "\r\n");
			fputs($fsp, 'Host: ' . substr($host, 6) . "\r\n");
			fputs($fsp, 'Accept: text/html' . "\r\n");
			fputs($fsp, 'Accept: charset=ISO-8859-1' . "\r\n");
			fputs($fsp, 'Content-Length:' . strlen($data) . "\r\n");
			fputs($fsp, 'Content-Type: text/html; charset=ISO-8859-1' . "\r\n" . "\r\n");
			fputs($fsp, $data, strlen($data));

			while(!feof($fsp))
			{
				$res .= @fgets($fsp, 128);
			}

			fclose($fsp);

			// echo "\n\nRECIEVED DATA: \n\n" . $res . "\n\n";
		}
		else
		{
			//$this->setError('Error while connecting to ' . $__url, false, __FILE__, __LINE__);
		}

		return $res;
	}
	
	function parseFromXml($key, $xml)
	{
		$begin = 0;
		$end = 0;
		$begin = strpos($xml, '<' . $key . '>');
			
		if($begin === false)
		{
			return false;
		}

		$begin += strlen($key) + 2;
		$end = strpos($xml, '</' . $key . '>');

		if($end === false)
		{
			return false;
		}

		$result = substr($xml, $begin, $end - $begin);
		return $this->unescapeXml($result);
	}
	
	function unescapeXml($string)
	{
		return str_replace(array('&lt;', '&gt;', '&quot;', '&amp;'), array('<', '>', '"', '&'), utf8_decode($string));
	}
}
?>