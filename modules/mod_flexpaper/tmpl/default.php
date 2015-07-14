<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_custom
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>


<p>Lütfen sorgulamak istediğiniz sertifikanın sol alt köşesinde bulunan sertifika numarasını ilgili yere giriniz ve sorgula butonuna basınız...</p>

<div class="sertifika_sorgula_alt">
	<form id="sertifika_form" action="javascript:;">
		<input id="sertifika_no" class="sertifika_sorgula_no" 
			onfocus="if(this.value==this.defaultValue)this.value='';" 
			onblur="if(this.value=='')this.value=this.defaultValue;" 
			type="text" name="sertifika_no" value="Sertifika Numarası" 
		/> 
		<input class="sertifika_sorgula_buton" id="sertificate_click"  
		type="submit" value="" />
	</form>
</div>

<div id="live_site" style="display: none;"><?php echo JURI::base();?></div>
