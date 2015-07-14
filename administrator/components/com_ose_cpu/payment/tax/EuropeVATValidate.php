<?php
defined('_JEXEC') or die(";)");

class Vat_checker extends oseObject
{
    var $CI;
	var $config = array();
    /**
     * Contructor: Loads curl library and vat_checker config
     *
     * @return void
     */
    function Vat_checker()
    {
    	$taxConfig = oseRegistry::call('msc')->getConfig('tax','obj');
    	//$threeConfig = oseRegistry::call('msc')->getConfig('thirdparty','obj');

        $this->config['requesterMs']  = $taxConfig->vat_checker_requester_iso;
        $this->config['requesterIso'] = $taxConfig->vat_checker_requester_iso;
        $this->config['requesterVat'] = $taxConfig->vat_number;

    }

    /**
     * Validates againt http://ec.europa.eu/taxation_customs/vies/
     * if vat number is valid. If it's valid
     * returns Consultation Number for tracking porpuses
     *
     * @param string $vat
     * @param string $country_iso
     * @return string or boolean
     */
    function is_valid_europa($vat, $country_iso)
    {
        $country_iso = strtoupper($country_iso);
        $vat = str_replace($country_iso, '', $vat);
		$post= array(
        'ms' =>  $country_iso,
        'iso' =>  $country_iso,
        'vat' => $vat,
        'requesterMs' =>  $this->config['requesterMs'],
        'requesterIso' => $this->config['requesterIso'],
        'requesterVat' => str_replace($this->config['requesterIso'],"", $this->config['requesterVat']),
        'BtnSubmitVat' => 'Verify'
        );

		oseRegistry::call('remote')->getClientBridge('curl');
		$host = 'ec.europa.eu';
		$path = '/taxation_customs/vies/viesquer.do';
		$connect = new OSERemoteConnector();
		/*
		foreach ($post as $key => $value)
        {
        	$data[]=$key ."=". urlencode($value);
        }
        $post = implode("&", $data);
*/
		//$resp = $connect->send_httprequest_via_fsockopen($host, $path, $post,'urlencoded');
$resp = $connect->send_request_via_curl($host, $path, $post,false,'urlencoded');
       //$resp = $this->CI->curl->simple_post('http://ec.europa.eu/taxation_customs/vies/viesquer.do', $post);

        if (preg_match('/\svalid\sVAT\s/',$resp))
        {
            preg_match_all('/(WAPIAAAA.*)/', $resp, $match);
            return trim($match[0][0]);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Based on rules in http://ec.europa.eu/taxation_customs/vies/faqvies.do
     * check if nif is valid
     *
     * @param string $vat
     * @param string $country_iso
     * @return boolean
     */

    function is_valid_regex($vat, $country_iso)
    {

        $country_iso = strtoupper($country_iso);
        $regex = '';

        switch ($country_iso)
        {
            case 'AT':
                $regex = '/^U[0-9]{8}$/';
                break;
            case 'BE':
                $regex = '/^0?[0-9]{*}$/';
                break;
            case 'CZ':
                $regex = '/^[0-9]{8,10}$/';
                break;
            case 'DE':
                $regex = '/^[0-9]{9}$/';
                break;
            case 'CY':
                $regex = '/^[0-9]{8}[A-Z]$/';
                break;
            case 'DK':
                $regex = '/^[0-9]{8}$/';
                break;
            case 'EE':
                $regex = '/^[0-9]{9}$/';
                break;
            case 'GR':
                $regex = '/^[0-9]{9}$/';
                break;
            case 'ES':
                $regex = '/^[0-9A-Z][0-9]{7}[0-9A-Z]$/';
                break;
            case 'FI':
                $regex = '/^[0-9]{8}$/';
                break;
            case 'FR':
                $regex = '/^[0-9A-Z]{2}[0-9]{9}$/';
                break;
            case 'GB':
                $regex = '/^([0-9]{9}|[0-9]{12})~(GD|HA)[0-9]{3}$/';
                break;
            case 'HU':
                $regex = '/^[0-9]{8}$/';
                break;
            case 'IE':
                $regex = '/^[0-9][A-Z0-9\\+\\*][0-9]{5}[A-Z]$/';
                break;
            case 'IT':
                $regex = '/^[0-9]{11}$/';
                break;
            case 'LT':
                $regex = '/^([0-9]{9}|[0-9]{12})$/';
                break;
            case 'LU':
                $regex = '/^[0-9]{8}$/';
                break;
            case 'LV':
                $regex = '/^[0-9]{11}$/';
                break;
            case 'MT':
                $regex = '/^[0-9]{8}$/';
                break;
            case 'NL':
                $regex = '/^[0-9]{9}B[0-9]{2}$/';
                break;
            case 'PL':
                $regex = '/^[0-9]{10}$/';
                break;
            case 'PT':
                $regex = '/^[0-9]{9}$/';
                break;
            case 'SE':
                $regex = '/^[0-9]{12}$/';
                break;
            case 'SI':
                $regex = '/^[0-9]{8}$/';
                break;
            case 'SK':
                $regex = '/^[0-9]{10}$/';
                break;
            default:
                return FALSE;
                break;
        }

        $vat = str_replace($country_iso, '', $vat);
        return (preg_match($regex,$vat));
    }
}
?>
