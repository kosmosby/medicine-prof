<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load'); ?>
<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.tooltip') ?>
<?php echo $this->helper('translator.script', array('strings' => array(
    'You cannot delete a category while it still has documents'
))); ?>

<ktml:script src="media://com_docman/js/toolbar.js" />
<ktml:script src="media://com_docman/js/footable.js"/>
<ktml:script src="media://com_docman/js/admin/categories.default.js"/>

<?php if (version_compare(JVERSION, '3.0', 'ge')): ?>
<script>
kQuery(function($){
    //Quick j3 sidebar layout fix
    $('#submenu').prependTo('#documents-sidebar .sidebar-inner').addClass('docman-main-nav');
});
</script>
<?php endif ?>

<ktml:module position="submenu">
    <ktml:toolbar type="menubar">
</ktml:module>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="COM_DOCMAN_SUBMENU_CATEGORIES" icon="categories icon-folder">
</ktml:module>

<div class="docman-container">
    <form action="" method="get" class="-koowa-grid categories-grid">

        <?php // Including the sidebar ?>
        <?php echo $this->import('default_sidebar.html')?>

        <div class="docman_admin_list_grid">
            <div class="scopebar">
                <div class="scopebar-group">
                    <a class="<?php echo is_null($this->parameters()->enabled) ? 'active' : ''; ?>"
                       href="<?php echo $this->route('enabled=') ?>">
                        <?php echo $this->translate('All') ?>
                    </a>
                </div>
                <div class="scopebar-group last">
                    <a class="<?php echo $this->parameters()->enabled === 0 ? 'active' : ''; ?>"
                       href="<?php echo $this->route('enabled='.($this->parameters()->enabled === 0 ? '' : '0' )) ?>">
                        <?php echo $this->translate('Unpublished') ?>
                    </a>
                    <a class="<?php echo $this->parameters()->enabled === 1 ? 'active' : ''; ?>"
                       href="<?php echo $this->route('enabled='.($this->parameters()->enabled === 1 ? '' : '1' )) ?>">
                        <?php echo $this->translate('Published') ?>
                    </a>
                </div>
                <div class="scopebar-search">
                    <?php echo $this->helper('grid.search', array('submit_on_clear' => true)) ?>
                </div>
            </div>
            <div class="footable_wrapper">
                <div class="docman_table_container">
                    <table class="table table-striped docman_categories_table footable">
                        <thead>
                            <tr>
                                <th style="text-align: center;" width="1">
                                    <?php echo $this->helper('grid.checkall')?>
                                </th>
                                <th width="0" data-class="expand" data-toggle="true"></th>
                                <th class="docman_table__title_field">
                                    <?php echo $this->helper('grid.sort', array('column' => 'title', 'title' => 'Title', 'direction' => 'asc')) ?>
                                </th>
                                <th width="5%" data-hide="phone,phablet">
                                    <?php echo $this->translate('Status') ?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?php echo $this->translate('Access')?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?php echo $this->translate('Owner')?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?php echo $this->translate('Date'); ?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?php echo $this->translate('Document count')?>
                                </th>
                                <th style="text-align: right;" width="7%" data-hide="phone">
                                    <?php echo $this->helper('grid.sort', array('column' => 'custom', 'title' => 'Ordering', 'direction' => 'asc')) ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $category):
                                $category->isPermissible();
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php echo $this->helper('grid.checkbox', array('entity'=> $category, 'attribs' => array(
                                        'data-document-count' => $category->document_count,
                                        'data-permissions' => htmlentities(json_encode($category->getPermissions()))
                                    ))); ?>
                                </td>
                                <td></td>
                                <td class="docman_table__title_field level<?php echo $category->level;?>">
                                    <div class="koowa_wrapped_content">
                                        <div class="whitespace_preserver">
                                            <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $category->icon)) ?>
                                            <a class="docman_category_title" href="<?php echo $this->route('view=category&id='.$category->id)?>">
                                                <?php echo $this->escape($category->title) ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo $this->helper('grid.state', array('entity' => $category, 'clickable' => $category->canPerform('edit'))) ?>
                                </td>
                                <td class="hidden-tablet hidden-phone">
                                    <span style="
                                        display: inline-block;
                                        text-overflow: ellipsis;
                                        overflow: hidden;
                                        max-width: 100px;
                                    ">
                                        <?php echo $this->escape($category->access_title) ?>
                                        <?php if ($category->access_raw == -1): ?>
                                            <br /><small><?php echo $category->level > 1 ? $this->translate('Inherited') : $this->translate('Default') ?></small>
                                        <?php endif ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $this->escape($category->getAuthor()->getName()); ?>
                                </td>
                                <td>
                                    <?php echo $this->helper('date.format', array('date' => $category->created_on)); ?>
                                </td>
                                <td class="hidden-phone" style="text-align: center">
                                    <a href="<?php echo $this->route('view=documents&category='.$category->id)?>">
                                        <?php echo $category->document_count; ?>
                                    </a>
                                </td>
                                <td style="text-align: right;">
                                    <?php echo $this->helper('grid.order', array(
                                        'entity' => $category,
                                        'total' => $this->parameters()->total,
                                        'sort' => $this->parameters()->sort === 'custom' ? 'ordering' : $this->parameters()->sort)) ?>
                                </td>
                            </tr>
                            <?php endforeach ?>
                            <?php if(!count($categories)) : ?>
                            <tr>
                                <td colspan="20" style="text-align: center;">
                                    <?php echo $this->translate('No categories found.') ?>
                                </td>
                            </tr>
                            <?php endif ?>
                        </tbody>

                        <?php if (count($categories)): ?>
                        <tfoot>
                            <tr>
                                <td colspan="20">
                                    <?php echo $this->helper('paginator.pagination') ?>
                                </td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
