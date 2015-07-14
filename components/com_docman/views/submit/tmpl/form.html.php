<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>
<?= helper('behavior.koowa');?>

<?= helper('translator.script', array('strings' => array(
    'Your link should either start with http:// or another protocol',
    'Invalid remote link. This link type is not supported by your server.'
))); ?>

<ktml:script src="media://com_docman/js/site/submit.default.js" />

<div class="docman_submit_layout">

    <? // Header ?>
    <? if ($params->get('show_page_heading')): ?>
        <h1>
            <?= escape($params->get('page_heading')); ?>
        </h1>
    <? endif; ?>

    <div class="koowa_toolbar">
        <? // Toolbar ?>
        <ktml:toolbar type="actionbar" title="false">
    </div>

    <? // Form ?>
    <div class="koowa_form">
        <form action="" method="post" class="-koowa-form" enctype="multipart/form-data">
            <div class="koowa boxed">
                <fieldset class="form-horizontal">

                    <legend><?= translate('Details'); ?></legend>

                    <div class="control-group submit_document__title_field">
                        <label for="title_field"><?= translate('Title'); ?></label>
                        <div class="input-group">
                            <input required id="title_field" type="text" class="title input-xxlarge" size="25" name="title"
                                   value="<?= escape($document->title); ?>" />
                        </div>
                    </div>

                    <? if ($show_categories): ?>
                    <div class="control-group submit_document__category_field">
                        <label><?= translate('Category') ?></label>
                        <?=
                        helper('listbox.categories', array(
                            'name'   => 'docman_category_id',
                            'prompt' => $category->title,
                            'deselect' => true,
                            'filter' => array(
                                'parent_id'    => $category->id,
                                'include_self' => true,
                                'access'       => object('user')->getRoles(),
                                'enabled'      => true
                            ))) ?>
                    </div>
                    <? endif ?>

                    <div class="control-group submit_document__document">
                        <ul class="nav nav-tabs">
                            <li>
                                <a href="#" class="upload-method" data-type="file">
                                    <?= translate('Upload a file')?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="upload-method" data-type="remote">
                                    <?= translate('Submit a link')?>
                                </a>
                            </li>
                        </ul>
                        <input type="hidden" name="storage_type" id="storage_type" />
                        <div class="upload-method-box" id="document-remote-path-row">
                            <input data-rule-streamwrapper
                                   data-rule-storage
                                   data-rule-scheme
                                   class="validate-storage submitlink input-xxlarge"
                                   data-type="remote"
                                   id="storage_path_remote"
                                   type="text"
                                   size="25"
                                   maxlength="512"
                                   placeholder="http://"
                                   data-streams="<?= htmlentities(json_encode($document->getSchemes())); ?>"
                                   name="storage_path_remote"
                                   value="<?= escape($document->storage_path); ?>"
                                />
                        </div>
                        <div class="control-group upload-method-box" id="document-file-path-row">
                            <input type="file" name="storage_path_file" class="input-file" />
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?= translate('Description'); ?></legend>
                    <?= helper('editor.display', array(
                        'name'    => 'description',
                        'value' => $document->description,
                        'width'   => '100%', 'height' => '200',
                        'rows'    => '20',
                        'buttons' => null
                    )); ?>
                </fieldset>
            </div>
        </form>
    </div>
    
</div>