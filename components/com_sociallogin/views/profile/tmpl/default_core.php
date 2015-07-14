<?php
/**
 * @version		$Id: default_core.php 21020 2011-03-27 06:52:01Z infograf768 $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

jimport('joomla.user.helper');
?>

<fieldset id="users-profile-core">
	<legend>
		<?php echo JText::_('Profile'); ?>
	</legend>
	<dl>
		<dt>
			<?php echo JText::_('Name:'); ?>
		</dt>
		<dd>
			<?php echo $this->data->name; ?>
		</dd>
		<dt>
			<?php echo JText::_('Username:'); ?>
		</dt>
		<dd>
			<?php echo $this->data->username; ?>
		</dd>
		<dt>
			<?php echo JText::_('Registered Date'); ?>
		</dt>
		<dd>
			<?php echo JHtml::_('date',$this->data->registerDate); ?>
		</dd>
		<dt>
			<?php echo JText::_('Last visited date'); ?>
		</dt>

		<?php if ($this->data->lastvisitDate != '0000-00-00 00:00:00'){?>
			<dd>
				<?php echo JHtml::_('date',$this->data->lastvisitDate); ?>
			</dd>
		<?php }
		else {?>
			<dd>
				<?php echo JText::_('This is the first time you visit this site'); ?>
			</dd>
		<?php } ?>

	</dl>
</fieldset>
