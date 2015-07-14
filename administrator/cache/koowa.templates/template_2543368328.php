<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php // Description field ?>
<div class="docman_grid description_container">
    <div class="control-group docman_grid__item one-whole">
        <div class="controls">
            <?php echo $this->helper('editor.display', array(
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