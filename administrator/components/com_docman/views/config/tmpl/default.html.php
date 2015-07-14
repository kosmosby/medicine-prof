<? defined('KOOWA') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('behavior.modal'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>
<?= helper('translator.script', array('strings' => array(
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

<? // Used for the extensions presets list ?>
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
                        <legend><?= translate('General settings') ?></legend>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item two-thirds">
                               <label for="document_path" class="control-label"><?= translate('Store files in') ?></label>
                                <div class="controls">
                                    <div class="input-group">
                                        <input disabled required data-rule-storagepath class="input-block-level input-group-form-control" type="text"
                                               value="<?= escape($config->document_path) ?>" id="document_path" name="document_path" />
                                        <div class="input-group-btn">
                                            <button class="btn edit_document_path" type="button"><?= translate('Edit'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item two-thirds">
                                <label for="maximum_size" class="control-label"><?= translate('File size limit');?></label>
                                <div class="controls">
                                    <label class="checkbox file_size_checkbox">
                                        <input type="checkbox" <?= $config->maximum_size == 0 ? 'checked' : '' ?> />
                                        <?= translate('Unlimited') ?>
                                    </label>
                                </div>
                                <div class="controls">
                                    <div class="input-group">
                                        <input class="input-block-level input-group-form-control" type="text" id="maximum_size"
                                               value="<?= floor($config->maximum_size/1048576); ?>"
                                               data-maximum="<?= $upload_max_filesize; ?>" />
                                        <span class="input-group-addon"><?= translate('MB'); ?></span>
                                    </div>
                                    <span class="help-block"><?= translate('File size limit message', array(
                                            'link' => 'http://www.joomlatools.com/support/forums/topic/3369-does-docman-have-any-filesize-limitations',
                                            'size' => floor($upload_max_filesize/1048576)-1)); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Create thumbnails from uploaded images');?></label>
                                <div class="controls radio btn-group">
                                    <?= helper('select.booleanlist', array('name' => 'thumbnails', 'selected' => $config->thumbnails)); ?>
                                </div>
                                <? if (!$thumbnails_available): ?>
                                    <span class="help-block"><?=translate('DOCman requires GD to be installed on your server for generating thumbnails')?></span>
                                <? endif ?>
                            </div>
                        </div>

                        <legend><?= translate('Allowed file extensions'); ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label" for="allowed_extensions_tag"><?= translate('Select from presets'); ?></label>
                                <ul id="extension_groups"></ul>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <div class="controls">
                                    <input type="text" class="input-block-level" name="allowed_extensions" id="allowed_extensions"
                                           value="<?= implode(',', KObjectConfig::unbox($config->allowed_extensions)); ?>"
                                           data-filetypes="<?= htmlentities(json_encode($filetypes)); ?>" />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="koowa_grid__item one-third">
                    <fieldset>
                        <legend><?= translate('Global permissions') ?></legend>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Users can edit their own documents and categories');?></label>
                                <div class="controls radio btn-group">
                                    <?= helper('select.booleanlist', array('name' => 'can_edit_own', 'selected' => $config->can_edit_own)); ?>
                                </div>
                                <p class="help-block">
                                    <?= translate('You can override this per menu item in menu parameters.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Users can delete their own documents and categories');?></label>
                                <div class="controls radio btn-group">
                                    <?= helper('select.booleanlist', array('name' => 'can_delete_own', 'selected' => $config->can_delete_own)); ?>
                                </div>
                                <p class="help-block">
                                    <?= translate('You can override this per menu item in menu parameters.'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="mfp-hide well" id="advanced-permissions" style="max-width: 800px; margin: 10% auto; position: relative;">
                            <?= helper('access.rules'); ?>
                        </div>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Action');?></label>
                                <a class="btn" id="advanced-permissions-toggle" href="#advanced-permissions">
                                    <?= translate('Change action permissions')?>
                                </a>
                                <p class="help-block">
                                    <?= translate('For advanced use only'); ?>
                                </p>
                                <p class="help-block">
                                    <?= translate('If you would like to restrict actions like downloading a document, editing a category based on the user groups, you can use the Advanced Permissions screen.'); ?>
                                </p>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </form>
</div>

<? if (!$thumbnails_available): ?>
    <script>
        kQuery(function($) {
            var thumbnails = $('input[name="thumbnails"]').first().parents('.btn-group');
            thumbnails.addClass('disabled');
            $('label', thumbnails).unbind('click');
            $('input', thumbnails).attr('disabled', 'disabled');
        });
    </script>
<? endif ?>