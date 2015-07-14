<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<? // Image field ?>
<div class="koowa_grid__row">
    <div class="control-group koowa_grid__item one-whole">
        <label class="control-label"><?= translate('Image'); ?></label>
        <div class="controls">
            <?= helper('behavior.thumbnail', array(
                'automatic_path' => $document->isNew() ? '' : 'generated/'.md5($document->id).'.png',
                'automatic_switch' => $document->isNew(),
                'value' => $document->image,
                'name'  => 'image',
                'id'  => 'image',
            )) ?>
        </div>
    </div>
</div>