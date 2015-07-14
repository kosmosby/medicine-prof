<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<? // Status field ?>
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

<? // Created on field ?>
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

<? // Start publishing field ?>
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

<? // Stop publishing field ?>
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
                    'startDate' => $datetime->format('Y-m-d H:i:s'),
                    'todayBtn' => false
                )
            ))?>
        </div>
    </div>
</div>

<? // Access level field ?>
<legend><?= translate('Permissions') ?></legend>

<div class="koowa_grid__row">
    <div class="control-group koowa_grid__item one-whole">
        <label class="control-label"><?= translate('Access'); ?></label>
        <?= helper('access.access_box', array(
            'entity' => $document
        )); ?>
    </div>
</div>

<? // Owner field ?>
<? if (!isset($hide_owner_field)): ?>
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
<? endif; ?>

<? // Status field ?>
<?= import('com://admin/docman.document.default_field_acl.html'); ?>