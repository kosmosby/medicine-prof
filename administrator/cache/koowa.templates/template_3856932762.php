<?php defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load'); ?>
<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.modal'); ?>
<?php echo $this->helper('behavior.keepalive'); ?>
<?php echo $this->helper('behavior.validator'); ?>
<?php echo $this->helper('translator.script', array('strings' => array(
    'Folder names can only contain letters, numbers, dash, underscore or colons',
    'Audio files',
    'Archive files',
    'Documents',
    'Images',
    'Video files',
    'Add another extension...'
))); ?>

<ktml:script src="media://com_docman/js/jquery.tagsinput.js" />
<ktml:script src="media://com_docman/js/admin/config.default.js" />

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="Options" icon="config icon-equalizer">
</ktml:module>

<?php // Used for the extensions presets list ?>
<ul id="config-group" class="hide">
    <li>
        <span></span>
        <div class="btn-group">
            <a href="#" class="btn btn-mini btn-group add koowa-text-icon-button">+</a>
            <a href="#" class="btn btn-mini btn-group remove koowa-text-icon-button">-</a>
        </div>
    <li>
</ul>

<div class="docman_form_layout">
    <form action="" method="post" class="-koowa-form">
        <div class="koowa_container">
            <div class="koowa_grid__row">
                <div class="koowa_grid__item two-thirds">
                    <fieldset>
                        <legend><?php echo $this->translate('General settings') ?></legend>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item two-thirds">
                               <label for="document_path" class="control-label"><?php echo $this->translate('Store files in') ?></label>
                                <div class="controls">
                                    <div class="input-group">
                                        <input disabled required data-rule-storagepath class="input-block-level input-group-form-control" type="text"
                                               value="<?php echo $this->escape($config->document_path) ?>" id="document_path" name="document_path" />
                                        <div class="input-group-btn">
                                            <button class="btn edit_document_path" type="button"><?php echo $this->translate('Edit'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item two-thirds">
                                <label for="maximum_size" class="control-label"><?php echo $this->translate('File size limit');?></label>
                                <div class="controls">
                                    <label class="checkbox file_size_checkbox">
                                        <input type="checkbox" <?php echo $config->maximum_size == 0 ? 'checked' : '' ?> />
                                        <?php echo $this->translate('Unlimited') ?>
                                    </label>
                                </div>
                                <div class="controls">
                                    <div class="input-group">
                                        <input class="input-block-level input-group-form-control" type="text" id="maximum_size"
                                               value="<?php echo floor($config->maximum_size/1048576); ?>"
                                               data-maximum="<?php echo $upload_max_filesize; ?>" />
                                        <span class="input-group-addon"><?php echo $this->translate('MB'); ?></span>
                                    </div>
                                    <span class="help-block"><?php echo $this->translate('File size limit message', array(
                                            'link' => 'http://www.joomlatools.com/support/forums/topic/3369-does-docman-have-any-filesize-limitations',
                                            'size' => floor($upload_max_filesize/1048576)-1)); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?php echo $this->translate('Create thumbnails from uploaded images');?></label>
                                <div class="controls radio btn-group">
                                    <?php echo $this->helper('select.booleanlist', array('name' => 'thumbnails', 'selected' => $config->thumbnails)); ?>
                                </div>
                                <?php if (!$thumbnails_available): ?>
                                    <span class="help-block"><?php echo $this->translate('DOCman requires GD to be installed on your server for generating thumbnails')?></span>
                                <?php endif ?>
                            </div>
                        </div>

                        <legend><?php echo $this->translate('Allowed file extensions'); ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label" for="allowed_extensions_tag"><?php echo $this->translate('Select from presets'); ?></label>
                                <ul id="extension_groups"></ul>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <div class="controls">
                                    <input type="text" class="input-block-level" name="allowed_extensions" id="allowed_extensions"
                                           value="<?php echo implode(',', KObjectConfig::unbox($config->allowed_extensions)); ?>"
                                           data-filetypes="<?php echo htmlentities(json_encode($filetypes)); ?>" />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="koowa_grid__item one-third">
                    <fieldset>
                        <legend><?php echo $this->translate('Global permissions') ?></legend>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?php echo $this->translate('Users can edit their own documents and categories');?></label>
                                <div class="controls radio btn-group">
                                    <?php echo $this->helper('select.booleanlist', array('name' => 'can_edit_own', 'selected' => $config->can_edit_own)); ?>
                                </div>
                                <p class="help-block">
                                    <?php echo $this->translate('You can override this per menu item in menu parameters.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?php echo $this->translate('Users can delete their own documents and categories');?></label>
                                <div class="controls radio btn-group">
                                    <?php echo $this->helper('select.booleanlist', array('name' => 'can_delete_own', 'selected' => $config->can_delete_own)); ?>
                                </div>
                                <p class="help-block">
                                    <?php echo $this->translate('You can override this per menu item in menu parameters.'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="mfp-hide well" id="advanced-permissions" style="max-width: 800px; margin: 10% auto; position: relative;">
                            <?php echo $this->helper('access.rules'); ?>
                        </div>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?php echo $this->translate('Action');?></label>
                                <a class="btn" id="advanced-permissions-toggle" href="#advanced-permissions">
                                    <?php echo $this->translate('Change action permissions')?>
                                </a>
                                <p class="help-block">
                                    <?php echo $this->translate('For advanced use only'); ?>
                                </p>
                                <p class="help-block">
                                    <?php echo $this->translate('If you would like to restrict actions like downloading a document, editing a category based on the user groups, you can use the Advanced Permissions screen.'); ?>
                                </p>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </form>
</div>

<?php if (!$thumbnails_available): ?>
    <script>
        kQuery(function($) {
            var thumbnails = $('input[name="thumbnails"]').first().parents('.btn-group');
            thumbnails.addClass('disabled');
            $('label', thumbnails).unbind('click');
            $('input', thumbnails).attr('disabled', 'disabled');
        });
    </script>
<?php endif ?>