<?php
/**
 * @version		$Id: default.php 21543 2011-06-15 22:48:00Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<?php

//Providers selected?
if (isset($this->settings) && count ($this->settings['providers']) > 0)
{
    //Random integer
    $rand = mt_rand (99999, 9999999);
    ?>
<div style="padding: 0 5px;">
    <fieldset style="border: 1px solid #DDDDDD;  margin: 10px 0 15px; padding: 15px;">
        <legend>Connect with</legend>
                <div class="oneall_social_login" style="overflow-y: hidden; height: 49px;" >



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
                <div id="branding" style="font-size: 10px;">
                    Powered&nbsp;by <a target="_blank" href="http://www.oneall.com">OneAll</a> <a href="http://www.oneall.com/services/single-sign-on/?utm_source=wordpress-test_login_frame&utm_medium=banner&utm_campaign=branding">Social&nbsp;Login</span></a>
                </div>
    </fieldset>
</div>

<?php
} ?>


<div class="registration<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate">
<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($fieldset->name);?>
	<?php if (count($fields)):?>
		<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
			<legend><?php echo JText::_($fieldset->label);?></legend>
		<?php endif;?>
			<dl>
		<?php foreach($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:?>
				<dt>
				<?php echo $field->label; ?>
				<?php if (!$field->required && $field->type != 'Spacer'): ?>
					<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
				<?php endif; ?>
				</dt>
				<dd><?php echo $field->input;?></dd>
			<?php endif;?>
		<?php endforeach;?>
			</dl>
		</fieldset>
	<?php endif;?>
<?php endforeach;?>



		<div>
			<button type="submit" class="validate"><?php echo JText::_('JREGISTER');?></button>
			<?php echo JText::_('or');?>
			<a href="<?php echo JRoute::_('');?>" title="<?php echo JText::_('JCANCEL');?>"><?php echo JText::_('JCANCEL');?></a>
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="registration.register" />
			<?php echo JHtml::_('form.token');?>
		</div>
	</form>
</div>
