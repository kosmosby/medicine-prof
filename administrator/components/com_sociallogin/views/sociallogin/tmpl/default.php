<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// load tooltip behavior
JHtml::_('behavior.tooltip');

?>

<form action="<?php echo JRoute::_('index.php?option=com_sociallogin&view=sociallogin&layout=default'); ?>" method="post" name="adminForm">

        <fieldset class="batch" style="width: 628px; margin: 0; padding: 0; border: 0;">
            <?php
            if (isset($this->settings ['oa_social_login_api_settings_verified']) && $this->settings ['oa_social_login_api_settings_verified'] !== '1')
            {
                ?>

                <div class="oa_container oa_container_welcome" style="margin-bottom: 0px;">
                    <h3>
                        Make your blog social!
                    </h3>
                    <div class="oa_container_body">
                        <p>
                            Allow your visitors to comment, login and register with social networks like Twitter, Facebook, LinkedIn, Hyves, Вконтакте, Google or Yahoo.
                            <strong>Draw a larger audience and increase user engagement in a  few simple steps.</strong>
                        </p>
                        <p>
                            To be able to use this plugin you first of all need to create a free account at <a href="https://app.oneall.com/signup/" target="_blank">http://www.oneall.com</a>
                            and setup a Site. After having created your account and setup your Site, please enter the Site settings in the form below.
                        </p>
                        <h3>You are in good company, 10000+ websites already trust us!</h3>
                        <p>
                            <a class="button-secondary" href="https://app.oneall.com/signup/" target="_blank"><strong>Create your free account now!</strong></a>
                        </p>
                    </div>
                </div>
                <?php
            }
            else
            {
                ?>
                <div class="oa_container oa_container_welcome" style="margin-bottom: 0px;">
                    <h3>
                        Your API Account is setup correctly
                    </h3>
                    <div class="oa_container_body" >
                        <p>
                            <a href="https://app.oneall.com/signin/" target="_blank">Login to your account</a> to manage your providers and access your <a href="https://app.oneall.com/insights/"  target="_blank">Social Insights</a>.
                            Determine which social networks are popular amongst your users and tailor your registration experience to increase your users' engagement.
                        </p>
                        <p>
                            <a class="button-secondary" href="https://app.oneall.com/signin/" target="_blank"><strong>Signin to your account</strong></a>
                        </p>
                    </div>
                </div>
                <?php
            }
            ?>


        </fieldset>



        <fieldset class="batch" style="width: 628px;background-color: #FFFFE0;">
            <legend><?php echo JText::_('Help, Updates &amp; Documentation');?></legend>
                 <ul>
                    <li><a target="_blank" href="http://www.twitter.com/oneall">Follow us on Twitter</a> to stay informed about updates;</li>
                    <li><a target="_blank" href="http://docs.oneall.com/plugins/guide/social-login-wordpress/">Read the online documentation</a> for more information about this plugin;</li>
                    <li><a target="_blank" href="http://www.oneall.com/company/contact-us/">Contact us</a> if you have feedback or need assistance.</li>
                </ul>
        </fieldset>


        <fieldset class="batch" style="width: 628px;">
            <legend> API Settings</legend>
                <div class="clr"> </div>

                <div>
                        <a href="https://app.oneall.com/applications/" target="_blank">Click here to create and view your API Credentials</a>
                </div>
            <div class="clr"> </div>
                <div>
                        <label for="oneall_api_subdomain"  style="width: 200px;">API Subdomain:</label>
                </div>
                <div>
                        <input type="text" id="oa_social_login_settings_api_subdomain" name="oa_social_login_settings[api_subdomain]" size="60" value="<?php echo (isset ($this->settings ['api_subdomain']) ? htmlspecialchars ($this->settings ['api_subdomain']) : ''); ?>" />
                </div>
            <div class="clr"> </div>
                <div>
                        <label for="oneall_api_public_key" style="width: 200px;">API Public Key:</label>
                </div>
                <div>
                        <input type="text" id="oa_social_login_settings_api_key" name="oa_social_login_settings[api_key]" size="60" value="<?php echo (isset ($this->settings ['api_key']) ? htmlspecialchars ($this->settings ['api_key']) : ''); ?>" />
                </div>
            <div class="clr"> </div>
                <div>
                        <label for="oneall_api_private_key" style="width: 200px;">API Private Key:</label>
                </div>
                <div>
                        <input type="text" id="oa_social_login_settings_api_secret"  name="oa_social_login_settings[api_secret]" size="60" value="<?php echo (isset ($this->settings ['api_secret']) ? htmlspecialchars ($this->settings ['api_secret']) : ''); ?>" />
                </div>
            <div class="clr"> </div>
                <div>

                    <div class="button2-left">
                        <div class="blank" style="float: left;">
                            <a  href="#" id="oa_social_login_test_api_settings">
                                Verify API Settings</a>
                        </div>
                    </div>
                    <div id="oa_social_login_api_test_result" style="float: left; padding-left: 98px;"></div>
                        <!--<a class="modal_jform_created_by" id="oa_social_login_test_api_settings" href="#">Verify API Settings</a>-->
                </div>
            <div class="clr"> </div>


        </fieldset>

        <fieldset class="batch" style="width: 648px; padding-left: 0px; padding-right: 0px;">
            <legend style="margin-left: 10px;">Enable the social networks/identity providers of your choice</legend>
            <?php
            $i = 0;
            foreach ($this->oa_social_login_providers AS $key => $provider_data)
            {
                ?>
                <div class="<?php echo ((($i++) % 2) == 0) ? 'row_even' : 'row_odd' ?> row_provider">
                    <div class="row">

                        <div style="float: left; padding-left: 10px;">
                            <label for="oneall_social_login_provider_<?php echo $key; ?>"><span class="oa_provider oa_provider_<?php echo $key; ?>" title="<?php echo htmlspecialchars ($provider_data['name']); ?>"><?php echo htmlspecialchars ($provider_data['name']); ?></span></label>
                        </div>
                        <div style="float: left; padding-top: 10px; padding-left: 10px;">
                            <input type="checkbox" id="oneall_social_login_provider_<?php echo $key; ?>" name="oa_social_login_settings[providers][<?php echo $key; ?>]" value="1"  <?php if(isset($this->settings ['providers'] [$key]) && $this->settings ['providers'] [$key]) echo "checked"; ?> />
                        </div>
                        <div style="float: left;  padding-top: 10px;">
                            <label for="oneall_social_login_provider_<?php echo $key; ?>"><?php echo htmlspecialchars ($provider_data['name']); ?></label>
                        </div>
                        <div style="float: left; padding-top: 16px;">
                            <?php echo (isset($provider_data['note']) ? ('&nbsp;('.$provider_data['note'].')') : ''); ?>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                <?php
            }
            ?>
        </fieldset>

        <fieldset class="batch" style="width: 628px;">
            <legend>Basic Settings</legend>
                <div>Enter the caption to be displayed above the social network login buttons: </div>
                <div>
                    <input type="text"  name="oa_social_login_settings[plugin_caption]" size="118" value="<?php echo (isset ($this->settings ['plugin_caption']) ? htmlspecialchars ($this->settings ['plugin_caption']) : 'Connect with:'); ?>" /></div>
        </fieldset>


    <input type="hidden" name="task" value="" />


</form>