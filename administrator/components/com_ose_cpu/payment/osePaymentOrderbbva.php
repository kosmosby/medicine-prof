<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderbbva extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function BBVAOneOffPostForm($orderInfo,$params=array()) {
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$html= array();
		$test_mode= $pConfig->bbva_testmode;
		if(empty($pConfig->bbva_clave) || empty($pConfig->bbva_comercio) || empty($pConfig->bbva_terminal) || empty($pConfig->bbva_currency)) {
			$html['form']= "";
			$html['url']= "";
			return $html;
		}
		if($test_mode == true) {
			$url= "https://w3.grupobbva.com/TLPV/tlpv/TLPV_pub_RecepOpModeloServidor";
		} else {
			$url= "https://w3.grupobbva.com/TLPV/tlpv/TLPV_pub_RecepOpModeloServidor";
		}

		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo= self::getBillingInfo($orderInfo->user_id);

		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;

		$user= & JFactory :: getUser($orderInfo->user_id);

		$desc = self::generateDesc($order_id);
		$msc_name = $desc;

		$obfuscated="88;87;1A;02;78;03;74;05;70;03;1F;XX;XX;XX;XX;XX;XX;XX;XX;XX";
		$clave=$pConfig->bbva_clave;
		$comercio=$pConfig->bbva_comercio;
		$terminal=$pConfig->bbva_terminal;
		$moneda=$pConfig->bbva_currency;
		$localizador="";
		$idioma="es";
		$pais="ES";
		$urlcomercio = JURI :: base()."components/com_osemsc/ipn/bbva_notify.php"; 
		$urlredir = JROUTE::_(JURI :: base()."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id);
		$des_key=$clave.substr($comercio,0,9)."***";
		$desobfuscated=self::desobfuscate($obfuscated,$des_key);
		$order = '000'.$order_id;
		$importe=$amount / 1;
		$importe1=number_format($importe,2,",",",");
		$importe_formatado=$importe * 100;
		$datos_firma = $terminal.$comercio.$order.$importe_formatado.$moneda.$localizador.$desobfuscated;
		$firma = strtoupper(sha1($datos_firma));
		$lt="&lt;";
		 $gt="&gt;";
		        $xml.=$lt."tpv".$gt;
		          $xml.=$lt."oppago".$gt;
		            $xml.=$lt."idterminal".$gt.$terminal.$lt."/idterminal".$gt;
		            $xml.=$lt."idcomercio".$gt.$comercio.$lt."/idcomercio".$gt;                    
		            $xml.=$lt."idtransaccion".$gt.$order.$lt."/idtransaccion".$gt;
		            $xml.=$lt."moneda".$gt.$moneda.$lt."/moneda".$gt;            
		            $xml.=$lt."importe".$gt.$importe1.$lt."/importe".$gt;                        
		            $xml.=$lt."urlcomercio".$gt.$urlcomercio.$lt."/urlcomercio".$gt;                                
		            $xml.=$lt."idioma".$gt.$idioma.$lt."/idioma".$gt;                        
		            $xml.=$lt."pais".$gt.$pais.$lt."/pais".$gt;              
		            $xml.=$lt."urlredir".$gt.$urlredir.$lt."/urlredir".$gt;                                             
		$xml.=$lt."localizador".$gt.$localizador.$lt."/localizador".$gt;                                                
		            $xml.=$lt."firma".$gt.$firma.$lt."/firma".$gt;                                                            
		          $xml.=$lt."/oppago".$gt;
				$xml.=$lt."/tpv".$gt;
				
		$vendor_image_url= "";
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$html['form']= '<form action="'.$url.'" method="post">';
		
		//if($orderInfo->payment_mode == 'm') {
			$html['form'] .= '<input type="hidden" name="peticion" value="'.$xml.'" />';
			$html['form'] .= '<input type="image" id="bbva_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with BBVA - it is fast, free and secure!').'" />';
		//}
		$html['form'] .= '</form>';
		return $html;
	}
	
	function desobfuscate($pal_sec_ofuscada,$clave_xor)
	{
	    $trozos = explode (";",$pal_sec_ofuscada);
	    $tope = count($trozos);
	    $res="";
	    for ($i=0; $i<$tope; $i++)
	    {
	        $x1=ord($clave_xor[$i]);
	        $x2=hexdec($trozos[$i]);
	        $r=$x1 ^ $x2;
	        $res.=chr($r);
	    }
	    return($res);
	}
}
?>