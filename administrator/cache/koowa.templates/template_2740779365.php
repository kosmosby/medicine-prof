<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('behavior.category_tree', array('element' => '#categories-tree', 'list' => $categories, 'selected' => $this->parameters()->category)) ?>

<?php echo $this->helper('behavior.sidebar', array(
    'sidebar'   => '#documents-sidebar',
    'target'    => '#categories-tree',
    'affix'     => true,
    'minHeight' => 100
)) ?>

<script>
    kQuery(function($){
        <?php if (version_compare(JVERSION, '3.0', 'ge')): ?>
        //Quick j3 sidebar layout fix
        $('#submenu').prependTo('#documents-sidebar .sidebar-inner').addClass('docman-main-nav');
        <?php endif ?>

        $('#search_clear').on('click', function() {
            $('[name=search_date], [id=day_range]').val('');
            document.getElementById('day_range').value = '';
        });
    });
</script>

<div id="documents-sidebar" class="fltlft">
    <div class="sidebar-inner">
        <h3><?php echo $this->translate('Favorites'); ?></h3>
        <ul class="sidebar-nav favorites">
            <?php $user_id = $this->object('user')->getId(); ?>
            <li class="<?php echo $this->parameters()->created_by == $user_id ? 'active' : ''; ?>">
                <a href="<?php echo $this->route('created_by='.($this->parameters()->created_by == 0 || $this->parameters()->created_by != $user_id ? $user_id : '')) ?>">
                    <?php echo $this->translate('My Documents') ?>
                </a>
            </li>
            <li class="<?php echo $this->parameters()->sort === 'created_on' && $this->parameters()->direction === 'desc' ? 'active' : ''; ?>">
                <a href="<?php echo $this->route($this->parameters()->sort === 'created_on' && $this->parameters()->direction === 'desc' ? 'sort=&direction=&created_by=' : 'sort=created_on&direction=desc&created_by=') ?>">
                    <?php echo $this->translate('Recently Added') ?>
                </a>
            </li>
            <li class="<?php echo $this->parameters()->sort === 'modified_on' && $this->parameters()->direction === 'desc' ? 'active' : ''; ?>">
                <a href="<?php echo $this->route($this->parameters()->sort === 'modified_on' && $this->parameters()->direction === 'desc' ? 'sort=&direction=&created_by=' : 'sort=modified_on&direction=desc&created_by=') ?>">
                    <?php echo $this->translate('Recently Edited') ?>
                </a>
            </li>
            <li class="<?php echo $this->parameters()->sort === 'hits' && $this->parameters()->direction === 'desc' ? 'active' : ''; ?>">
                <a href="<?php echo $this->route($this->parameters()->sort === 'hits' && $this->parameters()->direction === 'desc' ? 'sort=&direction=&created_by=' : 'sort=hits&direction=desc&created_by=') ?>">
                    <?php echo $this->translate('Most Popular') ?>
                </a>
            </li>
        </ul>
        <h3><?php echo $this->translate('Categories'); ?></h3>
        <div id="categories-tree"></div>
        <div class="documents-filters">
            <form action="" method="get" class="-koowa-grid">
                <h3><?php echo $this->translate('Find documents by owner') ?></h3>
                <div class="sidebar-panel">
                    <div class="controls find-by-owner">
                        <?php echo $this->helper('listbox.users',
                            array('name'    => 'created_by',
                                'attribs' => array('id' => 'owner_filter', 'onchange' => 'this.form.submit()'))) ?>
                    </div>
                </div>
                <h3><?php echo $this->translate('Find documents by access') ?></h3>
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
                <h3><?php echo $this->translate('Find documents by date') ?></h3>
                <div class="sidebar-panel">
                    <div class="controls find-by-date">
                        <label for="day_range"><?php echo $this->translate('Within') ?></label>
                        <?php echo $this->helper('listbox.day_range') ?>

                        <label for="search_date"><?php echo $this->translate('days of') ?></label>
                        <?php echo $this->helper('behavior.calendar', array(
                            'name' => 'search_date',
                            'id'   => 'search_date',
                            'format' => '%Y-%m-%d',
                            'value' => $this->parameters()->search_date,
                            'attribs' => array('placeholder' => date('Y-m-d'))
                        )) ?>
                        <div style="text-align: center;">
                            <button id="search_submit" class="btn btn-primary"><i class="icon-search icon-white"></i></button>
                            <button id="search_clear" class="btn"><?php echo $this->translate('Clear'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>