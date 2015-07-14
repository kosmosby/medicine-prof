<?php
/**
 * @version		$Id: default_login.php 22060 2011-09-12 14:14:55Z infograf768 $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<div class="login<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	<div class="login-description">
	<?php endif ; ?>

		<?php if($this->params->get('logindescription_show') == 1) : ?>
			<?php echo $this->params->get('login_description'); ?>
		<?php endif; ?>

		<?php if (($this->params->get('login_image')!='')) :?>
			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
		<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	</div>
	<?php endif ; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post">

		<fieldset>
			<?php foreach ($this->form->getFieldset('credentials') as $field): ?>
				<?php if (!$field->hidden): ?>
					<div class="login-fields"><?php echo $field->label; ?>
					<?php echo $field->input; ?></div>
				<?php endif; ?>
			<?php endforeach; ?>

            <?php
            //Providers selected?
            if (isset($this->settings) && count ($this->settings['providers']) > 0)
            {
                //Random integer
                $rand = mt_rand (99999, 9999999);
                ?>
                <!--<script type='text/javascript' src='http://wordpress-test.api.oneall.com/socialize/library.js?ver=3.3'></script>-->

                <div class="oneall_social_login">
                    <?php
                    if ($this->settings['show_title'])
                    {
                        ?>
                        <div style="margin-bottom: 3px;"><label><?php echo $this->settings['plugin_caption'];?></label></div>
                        <?php
                    }
                    ?>
                    <div class="oneall_social_login_providers" id="oneall_social_login_providers_<?php echo $rand; ?>"></div>
                    <script type="text/javascript">
                        oneall.api.plugins.social_login.build("oneall_social_login_providers_<?php echo $rand; ?>", {
                            'providers' :  ['<?php echo implode ("','", $this->settings['providers']); ?>'],
                            'callback_uri': (window.location.href + ((window.location.href.split('?')[1] ? '&':'?') + 'option=com_sociallogin&task=<?php echo $this->settings['source']; ?>')),
                            'css_theme_uri' : '<?php echo $this->settings['css_theme_uri']; ?>'
                        });
                    </script>
                </div>
                <?php
            } ?>


			<button type="submit" class="button"><?php echo JText::_('JLOGIN'); ?></button>
			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url',$this->form->getValue('return'))); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
<div>
	<ul>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
			<?php echo JText::_('Forgot your password?'); ?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
			<?php echo JText::_('Forgot your username?'); ?></a>
		</li>
		<?php
		$usersConfig = JComponentHelper::getParams('com_users');
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo JText::_('Don\'t have an account?'); ?></a>
		</li>
		<?php endif; ?>
	</ul>
</div>
