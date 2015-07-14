<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Smarty
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class BidsSmarty extends JTheFactorySmarty 
{
    function __construct()
    {
        parent::__construct();

        $this->register_function('print_price', array($this,'smarty_print_price'));
		$this->register_function('printdate',array($this,'smarty_printdate'));
		$this->register_function('set_css',array($this,'smarty_set_css'));
		$this->register_function('jroute',array($this,'smarty_jroute'));
        $cfg=BidsHelperTools::getConfig();
		if($cfg->theme) {
            $this->template_dir = JPATH_COMPONENT_SITE . DS . 'templates' . DS . $cfg->theme . DS;
        }

        $this->assign_by_ref('bidCfg',$cfg);
        $this->assign('AUCTION_PICTURES',AUCTION_PICTURES);
        $this->assign('AUCTION_TYPES',array(
            'AUCTION_TYPE_PUBLIC'=> AUCTION_TYPE_PUBLIC,
            'AUCTION_TYPE_PRIVATE'=>AUCTION_TYPE_PRIVATE,
            'AUCTION_TYPE_BIN_ONLY'=> AUCTION_TYPE_BIN_ONLY
        ));
        $this->assign('TEMPLATE_IMAGES',JURI::root().'components/'.APP_EXTENSION.'/templates/'.$cfg->theme.'/images/');
        $this->assign('links',new BidsHelperRoute() );

        $this->config_dir =JPATH_COMPONENT_SITE.DS.'templates'.DS.'configs'.DS;

        $userProfile = BidsHelperTools::getUserProfileObject();
        $has_profile = $userProfile->checkProfile();
        $this->assign('has_profile', $has_profile);

        $jdoc = JFactory::getDocument();
        $this->assign('document_type', $jdoc->getType()); // from 1.7.0

        $arr_dateformat = array(
            'Y-m-d' => '%Y-%m-%d',
            'Y-d-m' => '%Y-%d-%m',
            'm/d/Y' => '%m/%d/%Y',
            'd/m/Y' => '%d/%m/%Y',
            'd.m.Y' => '%d.%m.%Y',
            'D, F d Y' => '%Y-%m-%d');
        $this->assign('opt_date_format', $arr_dateformat[$cfg->bid_opt_date_format]);


    }
    
    function display($tpl_name, $cache_id = null, $compile_id = null){
//    var image_link_dir='/components/com_rbids/images/';
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("var image_link_dir='".
            JURI::root().'components/'.APP_EXTENSION.'/images/'
        ."';");
                
        parent::display($tpl_name, $cache_id = null, $compile_id = null);
    }

    function smarty_printdate($params, &$smarty) {
        $res='';
        if (!empty($params['date']) && substr($params['date'],0,4)!='0000') {
            $usehour=empty($params['use_hour'])?$params['use_hour']: null;
            $res = BidsHelperAuction::formatDate($params['date'],$usehour);
        }
        return $res;
    }
    //Used for cloaking emails
    function smarty_print_encoded_email($params, &$smarty){
        $extra = '';
    
        if (empty($params['address'])) {
            return "";
        } else {
            $address = $params['address'];
        }
    
        $text = $address;
        $encode = (empty($params['encode'])) ? 'none' : $params['encode'];
        
        if (!in_array($encode,array('javascript','javascript_charcode','hex','none')) ) {
            $smarty->trigger_error("print_encoded: 'encode' parameter must be none, javascript or hex");
            return;
        }
    
        if ($encode == 'javascript' ) {
            $string = 'document.write(\''.$text.'\');';
    
            $js_encode = '';
            for ($x=0; $x < strlen($string); $x++) {
                $js_encode .= '%' . bin2hex($string[$x]);
            }
    
            return '<script type="text/javascript">eval(unescape(\''.$js_encode.'\'))</script>';
    
        } elseif ($encode == 'javascript_charcode' ) {
            $string = $text;
    
            for($x = 0, $y = strlen($string); $x < $y; $x++ ) {
                $ord[] = ord($string[$x]);   
            }
    
            $_ret = "<script type=\"text/javascript\" language=\"javascript\">\n";
            $_ret .= "<!--\n";
            $_ret .= "{document.write(String.fromCharCode(";
            $_ret .= implode(',',$ord);
            $_ret .= "))";
            $_ret .= "}\n";
            $_ret .= "//-->\n";
            $_ret .= "</script>\n";
            
            return $_ret;
            
            
        } elseif ($encode == 'hex') {
    
            preg_match('!^(.*)(\?.*)$!',$address,$match);
            if(!empty($match[2])) {
                $smarty->trigger_error("mailto: hex encoding does not work with extra attributes. Try javascript.");
                return;
            }
            $text_encode = '';
            for ($x=0; $x < strlen($text); $x++) {
                $text_encode .= '&#x' . bin2hex($text[$x]).';';
            }
    
            $mailto = "&#109;&#97;&#105;&#108;&#116;&#111;&#58;";
            return $text_encode;
    
        } else {
            // no encoding
            return $text;
    
        }
    
    	
    }
    
    function smarty_set_css() {
        $cfg = BidsHelperTools::getConfig();
    	$doc = JFactory::getDocument();
    	if ($cfg->theme!="" && file_exists(JPATH_ROOT."/components/".APP_EXTENSION."/templates/".strtolower($cfg->theme)."/bid_template.css") )
    		$doc->addStyleSheet(JURI::root()."components/".APP_EXTENSION."/templates/".strtolower($cfg->theme)."/bid_template.css");
    	else	
    		$doc->addStyleSheet(JURI::root()."components/".APP_EXTENSION."/templates/default/bid_template.css");
    }
    
    function smarty_print_price($params) {
        $auction = $params['auction'];
        $price = $params['price'];
        $currency = '';
        if (is_object($auction)) {
            $currency = $auction->currency;
        }

        $nocss = isset($params['nocss']) && $params['nocss'];
        $cssPrice = isset($params['cssprice']) ? $params['cssprice'] : 'bids_price';
        $cssCurrency = isset($params['csscurrency']) ? $params['csscurrency'] : 'bids_currency';

        $p = ($nocss ? '' : '<span class="'.$cssPrice.'">') . BidsHelperAuction::formatPrice($price). ($nocss ? '' :
        '</span>');
        if ($p) {
            return $p . '&nbsp;' . ($nocss ? '' : '<span class="'.$cssCurrency.'">') .$currency. ($nocss ? '' :
                    '</span>');
        } else {
            return '-';
        }
    }

    function smarty_jroute($params) {

        $url = $params['url'];
        $xhtml = isset($params['xhtml']) ? $params['url'] : true;

        return JRoute::_($url,$xhtml);
    }
        
}
