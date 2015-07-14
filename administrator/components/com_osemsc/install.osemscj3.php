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
defined('_JEXEC') or die('Restricted access');
/**
 * This file and method will automatically get called by Joomla
 * during the installation process
 **/
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
if (!class_exists('JURI')) {
	jimport('joomla.environment.uri');
}
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
class com_osemscInstallerScript {
	function update() {
		$this->install();
	}
	function install() {
	$destination= JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_osemsc'.DS;
	$buffer= "installing";
	if(!JFile :: write($destination.'installer.dummy.ini', $buffer))
	{
		ob_start();
?>
		<table width="100%" border="0">
			<tr>
				<td>
					There was an error while trying to create an installation file.
					Please ensure that the path <strong><?php echo $destination; ?></strong> has correct permissions and try again.
				</td>
			</tr>
		</table>
		<?php

		$html= ob_get_contents();
		@ ob_end_clean();
	}
	else
	{
		$phpVersion= floatval(phpversion());
		if($phpVersion >= 5.2)
		{
			if (JFile::exists(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php'))
			{
				JFile :: delete(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
			}
			$link= rtrim(JURI :: root(), '/').'/administrator/index.php?option=com_osemsc&task=install';
			ob_start();
?>
			<style type="text/css">
			.button-next
			{
				height: 34px;
				line-height: 34px;
				width: 220px;
				text-align: center;
				font-weight: bold;
				font-size: 12px;
				color: #333;
				background: #9c3;
				border: solid 1px #690;
				cursor: pointer;
			}
			</style>
			<table width="100%" border="0">
				<tr>
					<td>
						Thank you for choosing OSE Membership™ for Joomla!, please click on the following button to complete your installation.
					</td>
				</tr>
				<tr>
					<td>
						<input type="button" class="button-next" onclick="window.location = '<?php echo $link; ?>'" value="<?php echo JText::_('COMPLETE YOUR INSTALLATION');?>"/>
					</td>
				</tr>
			</table>
			<?php

			$html= ob_get_contents();
			@ ob_end_clean();
		}
		else
		{
			ob_start();
?>
			<table width="100%" border="0">
				<tr>
					<td style="color:red; font-weight:700">
						Installation Error.
					</td>
				</tr>
				<tr>
					<td>
						Installation could not proceed any further because we detected that your site is using an unsupported version of PHP
					</td>
				</tr>
				<tr>
					<td>
						OSE Membership™ only support <strong>PHP5.2</strong> and above. Please upgrade your PHP version and try again.
					</td>
				</tr>
			</table>
			<?php

			$html= ob_get_contents();
			@ ob_end_clean();
		}
	}
	echo $html;
}
}