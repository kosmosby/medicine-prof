<?php
/**
 * @version		$Id: default.php 21322 2011-05-11 01:10:29Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>

<?php
//Providers selected?

if (isset($settings) && count ($settings['providers']) > 0 && $type == 'login')
{
    //Random integer
    $rand = mt_rand (99999, 9999999);
    ?>
<!--<script type='text/javascript' src='http://wordpress-test.api.oneall.com/socialize/library.js?ver=3.3'></script>-->

<div class="oneall_social_login" style="overflow-y: hidden; height:30px;"  id="iframe_div" >
    <?php
    if ($settings['show_title'])
    {
        ?>
        <div style="margin-bottom: 3px;"><label><?php echo $settings['plugin_caption'];?></label></div>
        <?php
    }
    ?>
    <div class="oneall_social_login_providers" id="oneall_social_login_providers_<?php echo $rand; ?>"></div>


    <script type="text/javascript">
        oneall.api.plugins.social_login.build("oneall_social_login_providers_<?php echo $rand; ?>", {
            'providers' :  ['<?php echo implode ("','", $settings['providers']); ?>'],
            'callback_uri': (window.location.href + ((window.location.href.split('?')[1] ? '&':'?') + 'option=com_sociallogin&task=<?php echo $settings['source']; ?>')),
            'css_theme_uri' : (("https:" == document.location.protocol) ? "https" : "http") + '://oneallcdn.com/css/api/socialize/themes/phpbb/small.css'
        });

        $(document).ready(function() {
            function change_heigth() {
               var iframe_div = $('#iframe_div');
               iframe_div.css('height',"-=12");
            }
            setTimeout(change_heigth, 1000);
       });
    </script>


</div>
<div id="branding" style="font-size: 10px;">

</div>

<h3 style="width: 172px;"></h3>
<?php }