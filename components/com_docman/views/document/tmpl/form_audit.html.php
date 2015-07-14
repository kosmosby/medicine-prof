<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<? // Download count field ?>
<div class="koowa_grid__row">
    <div class="control-group koowa_grid__item one-whole">
        <label class="control-label"><?= translate('Downloads'); ?></label>
        <div class="controls" id="hits-container">
            <p class="help-inline">
                <?= $document->hits; ?>
            </p>
            <? if ($document->hits): ?>
                <a href="#" class="btn btn-default btn-small"><?= translate('Reset'); ?></a>
            <? endif; ?>
        </div>
    </div>
</div>

<? // Modified by field ?>
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