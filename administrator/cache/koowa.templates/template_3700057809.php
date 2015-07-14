<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('behavior.category_tree', array(
    'element'  => '#categories-tree',
    'list'     => $all_categories,
    'selected' => $this->parameters()->parent_id,
    'options'  => array(
        'state' => 'parent_id'
    )
))
?>

<?php echo $this->helper('behavior.sidebar', array(
    'sidebar'   => '#documents-sidebar',
    'target'    => '#categories-tree',
    'affix'     => true,
    'minHeight' => 100
)) ?>


<div id="documents-sidebar" class="fltlft">
    <div class="sidebar-inner">
        <h3><?php echo $this->translate('Favorites'); ?></h3>
        <ul class="sidebar-nav favorites">
            <li class="<?php echo $this->parameters()->created_by == $this->object('user')->getId() ? 'active' : ''; ?>">
                <a href="<?php echo $this->route('created_by='.($this->parameters()->created_by == 0 ? $this->object('user')->getId() : '')) ?>">
                    <?php echo $this->translate('My Categories') ?>
                </a>
            </li>
        </ul>
        <h3><?php echo $this->translate('Categories'); ?></h3>
        <div id="categories-tree"></div>
        <div class="documents-filters">
            <h3><?php echo $this->translate('Find categories by owner') ?></h3>
            <div class="sidebar-panel">
                <div class="controls find-by-owner">
                    <?php echo $this->helper('listbox.users',
                        array('name'    => 'created_by',
                              'attribs' => array('id' => 'owner_filter', 'onchange' => 'this.form.submit()'))) ?>
                </div>
            </div>
            <h3><?php echo $this->translate('Find categories by access') ?></h3>
            <div class="sidebar-panel">
                <div class="controls find-by-access">
                    <?php echo $this->helper('listbox.access', array(
                        'attribs' => array(
                            'onchange' => 'this.form.submit()',
                            'class' => 'input-block-level'
                        )
                    )); ?>
                </div>
            </div>
        </div>
    </div>
</div>