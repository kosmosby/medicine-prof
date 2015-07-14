<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php // Status field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Status'); ?></label>
        <div class="controls radio btn-group">
            <?php echo $this->helper('select.booleanlist', array(
                'name' => 'enabled',
                'selected' => $document->enabled,
                'true' => $this->translate('Published'),
                'false' => $this->translate('Unpublished')
            )); ?>
        </div>
    </div>
</div>

<?php // Created on field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Date'); ?></label>
        <div class="controls">
            <?php echo $this->helper('behavior.calendar', array(
                'name' => 'created_on',
                'id' => 'created_on',
                'value' => $document->created_on,
                'format' => '%Y-%m-%d %H:%M:%S',
                'filter' => 'user_utc'
            ))?>
        </div>
    </div>
</div>

<?php // Start publishing field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Start publishing on'); ?></label>
        <div class="controls">
            <?php $datetime = new DateTime(null, new DateTimeZone('UTC')) ?>
            <?php $datetime->modify('-1 day'); ?>
            <?php echo $this->helper('behavior.calendar', array(
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

<?php // Stop publishing field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Stop publishing on'); ?></label>
        <div class="controls">
            <?php echo $this->helper('behavior.calendar', array(
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

<?php // Access level field ?>
<legend><?php echo $this->translate('Permissions') ?></legend>

<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Access'); ?></label>
        <div class="controls">
            <?php echo $this->helper('listbox.access', array(
                'name' => 'access',
                'inheritable' => true,
                'attribs' => array('class' => 'input-block-level'),
                'selected' => $document->access_raw
            ))?>

            <?php $has_value = !$document->isNew() && $document->access_raw == -1;
            $default = '<span>'
                . ($has_value ? $document->access_title : '')
                .'</span>';
            ?>
            <p class="help-block current-access">
                <?php echo $this->translate('Calculated as {access}', array('access' => $default)); ?>
            </p>
        </div>
    </div>
</div>

<?php // Owner field ?>
<?php if (!isset($hide_owner_field)): ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Owner'); ?></label>
        <div class="controls">
            <?php echo $this->helper('listbox.users', array(
                'name' => 'created_by',
                'selected' => $document->created_by ? $document->created_by : $this->object('user')->getId(),
                'deselect' => false
            )) ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php // Status field ?>
<?php echo $this->import('com://admin/docman.document.default_field_acl.html'); ?>