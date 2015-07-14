<?php
/**
  * @version       1.0 +
  * @package       Open Source Excellence Marketing Software
  * @subpackage    Open Source Excellence RSS - com_ose_rss
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 01-Oct-2011
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
?>
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
				echo JText :: _('Why not try e-Commerce software produced by OSE team and generate profits or reduce potential costs (e.g. being hacked)? OSE is experienced in producing e-Commerce solutions for your e-Commerce websites. The following software are a couple of renowned software produced by OSE Team.');
				?>
			</div>
			
		
		<div id ='aboutose'>
			<table width ="100%">
			<tr>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=419&oafid=62&obid=4" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/osemembership.png" alt="OSE Membership" title=""width="180" height="180" /></a>
		 			<br />
		 			OSE Membership
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=389&oafid=62&obid=5" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/osesecsuite.png" alt="OSE Security Suite" title=""width="180" height="180" /></a>
		 			<br />
		 			OSE Security Suite
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=438&oafid=62&obid=7" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/osefeaturetable.png" alt="OSE Feature Table" title=""width="180" height="180" /></a>
		 			<br />
		 			OSE Feature table
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=432&oafid=62&obid=8" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/osewebmailpls.png" alt="OSE Webmail Client" title=""width="180" height="180" /></a>
					<br />
		 			OSE Webmail PLUS
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/shop/category/19-virtuemart_payment_gateway.html?oafid=62&obid=10" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/googlecheckout.png" alt="OSE VirtueMart Payment Gateways" title=""width="180" height="180" /></a>
					<br />
		 			OSE VirtueMart Payment Gateways
				</td>			
			</tr>
			<tr>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=393&oafid=62&obid=11" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/oseantihacker.png" alt="OSE Anti Hacker" title=""width="180" height="180" /></a>
		 			<br />
		 			OSE Anti-Hacker
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=398&oafid=62&obid=12" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/oseantivirus.png" alt="OSE Anti Virus" title=""width="180" height="180" /></a>
		 			<br />
		 			OSE Anti-Virus
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=381&oafid=62&obid=13" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/osecredits.png" alt="OSE Credit" title=""width="180" height="180" /></a>
		 			<br />
		 			OSE Credits
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=385&oafid=62&obid=14" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/osedbman.png" alt="OSE Database Manager" title=""width="180" height="180" /></a>
					<br />
		 			OSE Database Manager
				</td>
				<td>
					<a href="http://www.opensource-excellence.com/index.php?option=com_ose_mart&view=item&id=471&oafid=62&obid=16" target="_top"><img src="http://www.opensource-excellence.com/components/com_ose_affiliates/assets/banner_image/osecloudmkt.png" alt="OSE Cloud Marketing" title=""width="180" height="180" /></a>
					<br />
		 			OSE Cloud Marketing
				</td>			
			</tr>
			</table>
		</div>
		
		<div id ="keepupdated">
			<div class="mailbox">
				<form onsubmit="window.open('http://www.feedmyinbox.com/', 'fmi', 'scrollbars=yes,width=520,height=490');return true;" target="fmi" id="fmi" method="post" action="http://www.feedmyinbox.com/feeds/verify/">
				<strong>Software Update News Letter</strong> &nbsp; <input type="text" name="email" value="" size ="40">
					<input type="hidden" name="feed" value="http://www.opensource-excellence.com/index.php?option=com_ose_cloudmkt&amp;view=feed&amp;feedID=2">
					<button onclick="this.form.submit()">Subscribe</button>
				</form>
			</div>
			
			<div class="mod-ose_social">
				<div class="ose_social_icons">
					<div class="socialicon"><a target="_blank" href="http://www.facebook.com/osexcellence"><img src="components/com_ose_cpu/assets/images/ose_social_fb.png"></a></div>
					<div class="socialicon"><a target="_blank" href="https://twitter.com/#!/osexcellence"><img src="components/com_ose_cpu/assets/images/ose_social_tw.png"></a></div>
					<div class="socialicon"><a target="_blank" href="http://www.linkedin.com/in/osexcellence"><img src="components/com_ose_cpu/assets/images/ose_social_in.png"></a></div>
				</div>
			</div>
		</div>
	
	</div>
	</div>
</div>
<?php
echo oseSoftHelper::renderOSETM();
?>