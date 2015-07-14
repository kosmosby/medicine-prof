<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load', array('javascript' => true)); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>
<?= helper('behavior.icon_map'); ?>

<ktml:script src="media://com_docman/js/document.js" />

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" icon="article-add icon-pencil-2">
</ktml:module>

<div class="docman_form_layout">
    <form action="" method="post" class="-koowa-form">
        <div class="koowa_container">
            <div class="koowa_grid__row">
                <div class="koowa_grid__item two-thirds">

                    <legend><?= translate('Details') ?></legend>

                    <div class="koowa_grid__row">
                        <div class="control-group koowa_grid__item two-thirds">
                            <label class="control-label" for="docman_form_title"><?= translate('Title') ?></label>
                            <div class="controls">
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <?= helper('behavior.icon', array(
                                            'name'  => 'parameters[icon]',
                                            'id' => 'params_icon',
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

                    <?= import('default_field_file.html'); ?>

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


                    <legend><?= translate('Description') ?></legend>

                    <div class="koowa_grid description_container">
                        <div class="control-group koowa_grid__item one-whole">
                            <div class="controls">
                                <?= helper('editor.display', array(
                                    'name' => 'description',
                                    'value' => $document->description,
                                    'id'   => 'description',
                                    'width' => '100%',
                                    'height' => '341',
                                    'cols' => '100',
                                    'rows' => '20',
                                    'buttons' => array('pagebreak')
                                )); ?>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="koowa_grid__item one-third">

                    <legend><?= translate('Publishing') ?></legend>

                    <div class="koowa_grid__row">
                        <div class="control-group koowa_grid__item one-whole">
                            <label class="control-label"><?= translate('Status'); ?></label>
                            <div class="controls radio btn-group">
                                <?= helper('select.booleanlist', array(
                                    'name' => 'enabled',
                                    'selected' => $document->enabled,
                                    'true' => translate('Published'),
                                    'false' => translate('Unpublished')
                                )); ?>
                            </div>
                        </div>
                    </div>

                    <div class="koowa_grid__row">
                        <div class="control-group koowa_grid__item one-whole">
                            <label class="control-label"><?= translate('Date'); ?></label>
                            <div class="controls">
                                <?= helper('behavior.calendar', array(
                                    'name' => 'created_on',
                                    'id' => 'created_on',
                                    'value' => $document->created_on,
                                    'format' => '%Y-%m-%d %H:%M:%S',
                                    'filter' => 'user_utc'
                                ))?>
                            </div>
                        </div>
                    </div>

                    <div class="koowa_grid__row">
                        <div class="control-group koowa_grid__item one-whole">
                            <label class="control-label"><?= translate('Start publishing on'); ?></label>
                            <div class="controls">
                                <? $datetime = new DateTime(null, new DateTimeZone('UTC')) ?>
                                <? $datetime->modify('-1 day'); ?>
                                <?= helper('behavior.calendar', array(
                                    'name' => 'publish_on',
                                    'id' => 'publish_on',
                                    'value' => $document->publish_on,
                                    'format' => '%Y-%m-%d %H:%M:%S',
                                    'filter' => 'user_utc',
                                    'options' => array(
                                        'clearBtn' => true,
                                        'startDate' => $datetime->format('Y-m-d H:i:s'),
                                        'todayBtn' => false
                                    )
                                ))?>
                            </div>
                        </div>
                    </div>
                    <div class="koowa_grid__row">
                        <div class="control-group koowa_grid__item one-whole">
                            <label class="control-label"><?= translate('Stop publishing on'); ?></label>
                            <div class="controls">
                                <?= helper('behavior.calendar', array(
                                    'name' => 'unpublish_on',
                                    'id' => 'unpublish_on',
                                    'value' => $document->unpublish_on,
                                    'format' => '%Y-%m-%d %H:%M:%S',
                                    'filter' => 'user_utc',
                                    'options' => array(
                                        'clearBtn' => true,
                                        'todayBtn' => false
                                    )
                                ))?>
                            </div>
                        </div>
                    </div>


                    <legend><?= translate('Permissions') ?></legend>

                    <div class="koowa_grid__row">
                        <div class="control-group koowa_grid__item one-whole">
                            <label class="control-label"><?= translate('Access'); ?></label>
                            <?= helper('access.access_box', array(
                                'entity' => $document
                            )); ?>
                        </div>
                    </div>

                    <div class="koowa_grid__row">
                        <div class="control-group koowa_grid__item one-whole">
                            <label class="control-label"><?= translate('Owner'); ?></label>
                            <div class="controls">
                                <?= helper('listbox.users', array(
                                    'name' => 'created_by',
                                    'selected' => $document->created_by ? $document->created_by : object('user')->getId(),
                                    'deselect' => false
                                )) ?>
                            </div>
                        </div>
                    </div>

                    <?= import('default_field_acl.html'); ?>

                </div>
            </div>
        </div>

        <div class="koowa_secondary_container">
            <div class="koowa_container">
                <div class="koowa_grid__row">
                    <div class="koowa_grid__item two-thirds">
                        <legend><?= translate('Image') ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <div class="controls">
                                    <?= helper('behavior.thumbnail', array(
                                        'automatic_path' => !$document->isNew() ? 'generated/' . md5($document->id) . '.png' : '',
                                        'value' => $document->image,
                                        'name'  => 'image',
                                        'id'  => 'image',
                                        'automatic_switch' => $document->isNew()
                                    )) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="koowa_grid__item one-third">
                        <legend><?= translate('Audit') ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Downloads'); ?></label>
                                <div class="controls" id="hits-container">
                                    <p class="help-inline">
                                        <?= $document->hits; ?>
                                    </p>
                                    <? if ($document->hits): ?>
                                        <a href="#" class="btn btn-small"><?= translate('Reset'); ?></a>
                                    <? endif; ?>
                                </div>
                            </div>
                        </div>

                        <? if ($document->modified_by): ?>
                            <div class="koowa_grid__row">
                                <div class="control-group koowa_grid__item one-whole">
                                    <label class="control-label"><?= translate('Modified by'); ?></label>
                                    <div class="controls">
                                        <span class="help-info">
                                        <?= object('user.provider')->load($document->modified_by)->getName(); ?>
                                        <?= translate('on') ?>
                                        <?= helper('date.format', array('date' => $document->modified_on)); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <? endif; ?>

                    </div>
                </div>
            </div>
        </div>

    </form>
</div>