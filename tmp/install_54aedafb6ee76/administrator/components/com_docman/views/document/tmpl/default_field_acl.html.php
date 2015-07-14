<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<? // Status field ?>
<? if ($document->isPermissible() && $document->canPerform('admin')): ?>
<div class="mfp-hide mfp-holder" id="advanced-permissions">
    <button title="Close (Esc)" type="button" class="mfp-close">×</button>
    <div class="mfp-inline">
        <?= helper('access.rules', array(
            'section' => 'document',
            'asset' => $document->getAssetName(),
            'asset_id' => $document->asset_id
        )); ?>
    </div>
</div>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?= translate('Action');?></label>
        <div class="controls">
            <a class="btn" id="advanced-permissions-toggle" href="#advanced-permissions">
                <?= translate('Change action permissions')?>
            </a>
            <p class="help-block">
                <?= translate('For advanced use only'); ?>
            </p>
        </div>
    </div>
</div>
<? endif; ?>