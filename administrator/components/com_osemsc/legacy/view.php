<?php
/**
 * @version     5.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author        Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
 */
defined('_JEXEC') or die("Direct Access Not Allowed");
jimport('joomla.application.component.view');
class osemscView extends JView {
	public function display($tpl= null) {

		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		$jversion = (JOOMLA16==true)?'1.6':'1.5';
		oseHTML :: script('media/system/js/core.js', $jversion);
		self :: initCss();
		parent :: display($tpl);
	}
	public function initCss()
	{
		$this->com_name= OSESoftHelper :: getExtensionName();
		oseHTML::stylesheet('components/com_ose_cpu/extjs/resources/css/ose-all.css');
		oseHTML :: stylesheet('administrator/components/com_ose_cpu/assets/css/old.css');
		oseHTML :: stylesheet('administrator/components/com_ose_cpu/assets/css/osesoft.css');
		oseHTML :: stylesheet('administrator/components/'.$this->com_name.'/assets/css/style.css');
	}
	public function loadViewJs($type=null)
	{
		oseHTML :: initScript($type);
		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		require_once(OSEMSC_B_PATH.DS.'helpers'.DS."extLanguage.php");
		if (JOOMLA16==true)
		{
			oseHTML :: script($com.'/ose/app.msg.js', '1.6');
			$lang = JRequest::getCmd('ose_lang','en');
			if(JFile::exists(OSECPU_F_PATH.DS.'locale'.DS."ext-lang-{$lang}.js"))
			{
				oseHTML :: script($com."/locale/ext-lang-{$lang}.js", '1.6');
			}
		}
		else
		{
			oseHTML :: script($com.'/ose/app.msg.js');
			$lang = JRequest::getCmd('lang','en');
			oseHTML :: script(OSEMSC_F_URL.'/libraries/joomla.core.js', '1.5');
			$strings = oseJson::encode(oseText::jsStrings());
			$document = JFactory::getDocument();
			$document->addScriptDeclaration('(function(){var strings='.$strings.';Joomla.JText.load(strings)})()');
		}
		oseHTML :: script($com.'/ose/func.js', '1.5');
		oseHTML :: script(OSEMSC_F_URL.'/libraries/init.js', '1.6');
	}

	public function loadGridJs()
	{
		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		oseHTML :: script($com.'/grid/limit.js');
		oseHTML :: script($com.'/grid/quickgrid.js');
		oseHTML :: script($com.'/grid/SearchField.js');
	}

	public function loadTinyMCE()
	{
		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		oseHTML :: script($com."/htmleditor/tiny_mce/tiny_mce.js", '1.6');
		oseHTML :: script($com."/htmleditor/TinyMCE.js", '1.6');
	}
	protected function generateCountryStateData()
	{
		$country = oseMscMethods::getCountry();
		$state = oseMscMethods::getState();
		$result = array();
		$result['country'] = $country;
		$result['state'] = $state;
		return $result;
	}
	public function loadMultiSelect()
	{
		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		oseHTML :: script($com.'/multiselect/MultiSelect.js');
		oseHTML :: stylesheet($com.'/multiselect/MultiSelect.css');
	}

	public function initJs()
	{
		$result1 = $this->generateCountryStateData();
		$result2 = $this->generateMscIdOption();
		$result = array_merge($result1,$result2);

		$config = oseRegistry::call('msc')->getConfig('locale','obj');
		$jsonCountry = oseObject::getValue($config,'default_country_json',false);

		if(!$jsonCountry)
		{
			$jsonCountry = oseJson::encode(array('code3'=>'USA','code2'=>'US'));
		}
		else
		{
			$jsonCountry = oseObject::getValue($config,'default_country_json');
		}

		$js = oseHTML::getInstance('js');
		$js->append('oseMsc.defaultSelectedCountry=Ext.decode(\''.$jsonCountry.'\');');
		$js->append('oseMsc.mscList = '.oseJson::encode($result['mscList']).';');
		$js->append('oseMsc.mscOptions = '.oseJson::encode($result['mscOptions']).';');
		$js->append('oseMsc.countryData = '.oseJson::encode($result['country']).';');
		$js->append('oseMsc.stateData = '.oseJson::encode($result['state']).';');
		$js->onReady();
		$js->wrap();
		return $js->toString();
	}

	protected function generateMscIdOption()
	{
		$list = oseMscPublic::getList();;
		$result = array();
		if (!empty($list))
		{
			$cart = oseMscPublic::getCart();
			$options = array();
			$msc = oseRegistry::call('msc');
			foreach($list as $key => $entry)
			{
				$msc_id = oseObject::getValue($entry,'id');
				$node = $msc->getInfo($msc_id,'obj');
				$paymentInfos = $msc->getExtInfo($msc_id,'payment');
				$osePaymentCurrency = $cart->get('currency');
				$option = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
				$options = array_merge($options,$option);
			}
			$mscList = array('total'=>count($list),'results' => $list);
			$mscOptions = array('total'=>count($options),'results' => $options);
			$result = array();
			$result['mscList'] = $mscList;
			$result['mscOptions'] = $mscOptions;
		}
		else
		{
			$result['mscList'] = array();
			$result['mscOptions'] = array();
		}
		return $result;
	}
}
?>