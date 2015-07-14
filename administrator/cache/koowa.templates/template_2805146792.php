<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php // Download count field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Downloads'); ?></label>
        <div class="controls" id="hits-container">
            <p class="help-inline">
                <?php echo $document->hits; ?>
            </p>
            <?php if ($document->hits): ?>
                <a href="#" class="btn btn-default btn-small"><?php echo $this->translate('Reset'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php // Modified by field ?>
<?php if ($document->modified_by): ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Modified by'); ?></label>
        <div class="controls">
            <span class="help-info">
            <?php echo $this->object('user.provider')->load($document->modified_by)->getName(); ?>
            <?php echo $this->translate('on') ?>
            <?php echo $this->helper('date.format', array('date' => $document->modified_on)); ?>
            </span>
        </div>
    </div>
</div>
<?php endif; ?>