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

$postString = array();
$postString['types']=array('Addons','MscIdOption','CountryState','Language');
$postString['option']='com_osemsc';
$postString['task']='generateJs';
$postString['addontype']='member';
oseHtml2::matchScript('index.php?'.http_build_query($postString),'msc');
?>


<script type="text/javascript">
if (!Ext.browser.is.WebKit) {
    alert("The current browser is unsupported.\n\nSupported browsers:\n" +
        "Google Chrome\n" +
        "Apple Safari\n" +
        "Mobile Safari (iOS)\n" +
        "Android Browser\n" +
        "BlackBerry Browser"
    );
}

var getCurrentUrl = function()	{
	return '<?php echo JURI::root();?>';
}

Ext.getBody().setHtml('');
Ext.getBody().removeCls('contentpane');
Ext.Loader.setConfig({
    enabled: true,
    paths:{
    	'MyApp': getCurrentUrl()+'components/com_osemsc/mobile/app'
    }
});

Ext.application({
	models: [
         'juser',
         'billinginfo',
         'countryModel',
         'stateModel',
         'mem.orderModel'
     ],
     stores: [
         'mem.orderStore'
     ],
     views: [
         'MemberPanel',
         'AddonView',
         'mem.juser',
         'mem.billinginfo',
         'mem.order',
         'mem.orderitem'
     ],
    name: 'MyApp',

    launch: function() {
    	Ext.apply(Ext.data.validations,{
            passwordMessage: ' is not equal Password',
            password: function(config, value,m) {
                
                if(value == m.get('password')){
                    return true;
                } else {
                    return false;
                }
            }
        });

    	
    	Ext.create('MyApp.view.MemberPanel', {
            fullscreen: true,
            id: 'oseRegForm'
        });
    }
});
</script>