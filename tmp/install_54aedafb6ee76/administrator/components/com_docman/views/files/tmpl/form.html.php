<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('translator.script', array('strings' => array(
    'You will lose all unsaved data. Are you sure?',
    'Continue editing this document: {document}'
))); ?>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar">
</ktml:module>

<ktml:script src="media://com_docman/js/admin/files.form.js" />

<div class="docman_form_layout">

<? if (empty($paths)): ?>
    <?= translate('You did not select any files. Please go back and select some files first.')?>
<? else: ?>
    <div class="docman_container docman_container--medium docman_container--island">
        <form class="-koowa-form" id="document-batch">
            <div class="docman_grid">
                <div class="docman_grid__item one-whole">
                    <fieldset>
                        <legend><?= translate('Batch Values'); ?></legend>

                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <label class="control-label"><?= translate('Category');?>:</label>
                                <div class="controls">
                                <?= helper('listbox.categories', array(
                                        'name' => 'docman_category_id',
                                        'deselect' => false,
                                        'attribs' => array('class' => 'required')
                                    ))?>
                                </div>
                            </div>
                        </div>
                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <label class="control-label"><?= translate('Status');?>:</label>
                                <div class="controls radio btn-group">
                                     <?= helper('select.booleanlist', array(
                                        'name' => 'enabled',
                                        'selected' => 1,
                                        'true' => 'Published',
                                        'false' => 'Unpublished'
                                      )); ?>
                                </div>
                            </div>
                        </div>
                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <div class="controls">
                                    <label for="humanized_titles">
                                        <input type="checkbox" id="humanized_titles" checked />
                                        <?= translate('Human readable titles'); ?>
                                        <small>(document-2013-07-08.pdf &raquo; Document 2013 07 08)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </form>
    </div>

    <div class="docman_secondary_container">
        <div class="docman_container docman_container--medium docman_container--island">
            <div class="docman_grid">
                <div class="docman_grid__item one-whole">

                    <legend><?= translate('Documents'); ?></legend>
                    <div id="document_list">
                        <div class="docman_grid">
                            <? $i=0;foreach ((array)KObjectConfig::unbox($paths) as $path): ?>
                            <div class="docman_grid__item one-third">
                                <form class="form-vertical document-form" method="post" id="form<?= $i?>" data-path="<?= escape($path) ?>">
                                    <div class="control-group">
                                        <button class="cancel btn btn-mini docman_tooltip" title="<?= translate('Remove this file from the list');?>"><i class="icon icon-minus-sign"></i></button>
                                    </div>

                                    <div class="control-group">
                                        <label class="control-label"><?= translate('File name');?>:</label>
                                        <div class="controls">
                                            <input class="disabled input-block-level file-name-input" type="text" value="<?= escape($path); ?>" disabled/>
                                        </div>
                                    </div>

                                    <div class="control-group">
                                        <label for="docman_title<?= $i?>" class="control-label"><?= translate('Title');?>:</label>
                                        <div class="controls">
                                            <input id="docman_title<?= $i?>" class="input-block-level" type="text" name="title" value="<?= escape(helper('string.humanize', array(
                                                'string' => $path, 'strip_extension' => true))); ?>" />
                                        </div>
                                    </div>

                                    <div class="control-group">
                                        <label for="docman_description<?= $i?>" class="control-label"><?= translate('Description');?>:</label>
                                        <div class="controls">
                                            <textarea id="docman_description<?= $i?>" class="input-block-level" style="resize:vertical;" name="description"></textarea>
                                        </div>
                                    </div>

                                    <div class="control-group">
                                        <label class="control-label"><?= translate('Category');?>:</label>
                                        <div class="controls">
                                            <?= helper('listbox.categories', array(
                                                'attribs' => array('id' => 'docman_category_id_'.$i),
                                                'name' => 'docman_category_id',
                                                'selected' => '',
                                                'prompt'   => translate('- Use Batch Value -')
                                            ))?>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label class="control-label"><?= translate('Status');?>:</label>
                                        <div class="controls">
                                            <?= helper('listbox.optionlist', array(
                                                'select2' => true,
                                                'name' => 'enabled',
                                                'prompt' => translate('- Use Batch Value -'),
                                                'attribs' => array('class' => 'input-block-level'),
                                                'deselect' => true,
                                                'options' => array(
                                                    array('value' => 1, 'label' => translate('Published')),
                                                    array('value' => 0, 'label' => translate('Unpublished'))
                                                )
                                            ))?>
                                        </div>
                                    </div>
                                    <input type="hidden" name="storage_path" value="<?= escape($path); ?>" />

                                    <input type="hidden" name="automatic_thumbnail" value="1" />
                                </form>
                            </div>
                            <? $i++;endforeach; ?>
                        </div>
                    </div>
                    <? endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
