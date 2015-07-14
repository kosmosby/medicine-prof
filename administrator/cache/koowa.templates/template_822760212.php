<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php // Image field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Image'); ?></label>
        <div class="controls">
            <?php echo $this->helper('behavior.thumbnail', array(
                'automatic_path' => $document->isNew() ? '' : 'generated/'.md5($document->id).'.png',
                'automatic_switch' => $document->isNew(),
                'value' => $document->image,
                'name'  => 'image',
                'id'  => 'image',
            )) ?>
        </div>
    </div>
</div>