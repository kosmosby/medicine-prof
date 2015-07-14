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
oseHTML::script("administrator/".OSEMSCFOLDER."/views/activation/js/script.js",'1.5');
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
				echo JText :: _('Due to unauthorized distribution of the software, we add the activation function into the software to have slightly higher protection of intellectual properties. We apologize for the inconvenience and wish you could understand it. Please enter your OSE ID and Password, and click the Activation Button to activate the component.');
				?>
			</div>
			
		<div id ='aboutose'>
			<table width ="100%">
			<tr>
				<td>
					OSE username: <input id="username" name="username" value="" />
				</td>
				<td>
				</td>
							
			</tr>
			<tr>
				<td>
					OSE password: <input id="password" name="password" type="password" value="" />
				</td>
				<td>
					
				</td>
						
			</tr>
			<tr>
				<td colspan ="2">
					<button class="button" id="activate" name="activate">Activate </button>
				</td>
				<td>
					
				</td>
						
			</tr>
			</table>
		</div>
		
		<div id ="keepupdated">
			<div class="mailbox">

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
	