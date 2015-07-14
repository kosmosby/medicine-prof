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
echo $this->initJS();
oseHTML::script(oseMscMethods::getJsModPath('global','config'),'1.6');
oseHTML::script(oseMscMethods::getJsModPath('payment','config'),'1.6');
oseHTML::script(oseMscMethods::getJsModPath('email','config'),'1.6');
oseHTML::script(oseMscMethods::getJsModPath('register','config'),'1.6');
oseHTML::script(oseMscMethods::getJsModPath('currency','config'),'1.6');
oseHTML::script(oseMscMethods::getJsModPath('3rdparty','config'),'1.6');
oseHTML::script(oseMscMethods::getJsModPath('tax','config'),'1.6');
oseHTML::script(oseMscMethods::getJsModPath('locale','config'),'1.6');

$db = oseDB::instance();
$query = " SELECT id,subject,type FROM `#__osemsc_email`"
		;
$db->SetQUery($query);
$list = oseDB::loadList();
$result = array('total'=>count($list),'results'=>$list);
$document = JFactory::getDocument();
$document->addScriptDeclaration("var getEmails = function()	{return ".oseJson::encode($result)."};");
?>
<script type="text/javascript">
Ext.onReady(function(){
	oseMsc.config.globalInit = new oseMsc.config.globalInit();
	oseMsc.config.globalInit = oseMsc.config.globalInit.init();
	oseMsc.config.paymentInit = new oseMsc.config.paymentInit();
	oseMsc.config.paymentInit = oseMsc.config.paymentInit.init();
	oseMsc.config.emailInit = new oseMsc.config.emailInit();
	oseMsc.config.emailInit = oseMsc.config.emailInit.init();
	oseMsc.config.regFormInit = new oseMsc.config.regFormInit();
	oseMsc.config.regFormInit = oseMsc.config.regFormInit.init();
	oseMsc.config.currencyInit = new oseMsc.config.currencyInit();
	oseMsc.config.currencyInit = oseMsc.config.currencyInit.init();
	oseMsc.config.thirdPartyPanelInit = new oseMsc.config.thirdPartyPanelInit();
	oseMsc.config.thirdPartyPanelInit = oseMsc.config.thirdPartyPanelInit.init();
	
	Ext.QuickTips.init();
	oseMsc.config.tabs = new Ext.TabPanel({
		title: 'Configurations'
		,border: false	
		,activeItem: 0
		,renderTo: 'osemsc-config'
		,width: Ext.get('osemsc-config').getWidth()
		,height: 880
		,items:[
			oseMsc.config.globalForm
			,oseMsc.config.paymentForm
			,oseMsc.config.emailForm
			,oseMsc.config.regForm
			,oseMsc.config.currency
			,oseMsc.config.thirdPartyPanel
			,oseMsc.config.tax.init()
			,oseMsc.config.locale.init()
		]

	});
});
</script>

<div id="oseheader">
	<div class="container">
		<div class="logo-labels">
			<h1>
				<a href="http://www.opensource-excellence.com" target="_blank"><?php echo JText::_("Open Source Excellence"); ?>
				</a>
			</h1>
			<?php
			echo $this->preview_menus;
			?>
		</div>
		<?php
		$this->OSESoftHelper->showmenu();
		?>
		<div class="section">
			<div id="sectionheader">
				<?php echo $this->title; ?>
			</div>
			<div class="grid-title">
				<?php
				echo JText :: _('PLEASE_MANAGE_YOUR_CONFIGURATION_HERE');
				?>
			</div>
				<div id="osemsc-config" class="com-content"></div>
		</div>
	</div>
</div>
<?php
echo oseSoftHelper::renderOSETM();
?>