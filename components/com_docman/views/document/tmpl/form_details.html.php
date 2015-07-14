<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<? // Title field ?>
<div class="koowa_grid__row">
    <div class="control-group koowa_grid__item two-thirds">
        <label class="control-label" for="docman_form_title"><?= translate('Title') ?></label>
        <div class="controls">
            <div class="input-group">
                <span class="input-group-btn">
                    <?= helper('behavior.icon', array(
                        'name'  => 'parameters[icon]',
                        'id'    => 'params_icon',
                        'value' => $document->getParameters()->get('icon', 'default'),
                        'link'  => route('option=com_docman&view=files&layout=select&tmpl=koowa&container=docman-icons&types[]=image')
                    ))?>
                </span>
                <input required class="input-group-form-control" id="docman_form_title" type="text" name="title" maxlength="255"
                       value="<?= escape($document->title); ?>" />
            </div>
        </div>
    </div>
    <div class="control-group koowa_grid__item one-third">
        <label class="control-label" for="docman_form_alias"><?= translate('Alias') ?></label>
        <div class="controls">
            <input id="docman_form_alias" type="text" class="input-block-level" name="slug" maxlength="255"
                   value="<?= escape($document->slug) ?>" />
        </div>
    </div>
</div>

<? // File field ?>
<?= import('com://admin/docman.document.default_field_file.html') ?>

<? // Category field ?>
<div class="koowa_grid__row">
    <div class="control-group koowa_grid__item one-whole">
        <label class="control-label"><?= translate('Category'); ?></label>
        <div class="controls">
            <?= helper('listbox.categories', array(
                'check_access' => true,
                'deselect' => false,
                'required' => true,
                'name' => 'docman_category_id',
                'attribs' => array(
                    'required' => true,
                    'id'    => 'docman_category_id'
                ),
                'selected' => $document->docman_category_id
            ))?>
        </div>
    </div>
</div>