<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('behavior.tooltip') ?>
<?= helper('translator.script', array('strings' => array(
    'You cannot delete a category while it still has documents'
))); ?>

<ktml:script src="media://com_docman/js/toolbar.js" />
<ktml:script src="media://com_docman/js/footable.js"/>
<ktml:script src="media://com_docman/js/admin/categories.default.js"/>

<? if (version_compare(JVERSION, '3.0', 'ge')): ?>
<script>
kQuery(function($){
    //Quick j3 sidebar layout fix
    $('#submenu').prependTo('#documents-sidebar .sidebar-inner').addClass('docman-main-nav');
});
</script>
<? endif ?>

<ktml:module position="submenu">
    <ktml:toolbar type="menubar">
</ktml:module>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="COM_DOCMAN_SUBMENU_CATEGORIES" icon="categories icon-folder">
</ktml:module>

<div class="docman-container">
    <form action="" method="get" class="-koowa-grid categories-grid">

        <? // Including the sidebar ?>
        <?= import('default_sidebar.html')?>

        <div class="docman_admin_list_grid">
            <div class="scopebar">
                <div class="scopebar-group">
                    <a class="<?= is_null(parameters()->enabled) ? 'active' : ''; ?>"
                       href="<?= route('enabled=') ?>">
                        <?= translate('All') ?>
                    </a>
                </div>
                <div class="scopebar-group last">
                    <a class="<?= parameters()->enabled === 0 ? 'active' : ''; ?>"
                       href="<?= route('enabled='.(parameters()->enabled === 0 ? '' : '0' )) ?>">
                        <?= translate('Unpublished') ?>
                    </a>
                    <a class="<?= parameters()->enabled === 1 ? 'active' : ''; ?>"
                       href="<?= route('enabled='.(parameters()->enabled === 1 ? '' : '1' )) ?>">
                        <?= translate('Published') ?>
                    </a>
                </div>
                <div class="scopebar-search">
                    <?= helper('grid.search', array('submit_on_clear' => true)) ?>
                </div>
            </div>
            <div class="footable_wrapper">
                <div class="docman_table_container">
                    <table class="table table-striped docman_categories_table footable">
                        <thead>
                            <tr>
                                <th style="text-align: center;" width="1">
                                    <?= helper('grid.checkall')?>
                                </th>
                                <th width="0" data-class="expand" data-toggle="true"></th>
                                <th class="docman_table__title_field">
                                    <?= helper('grid.sort', array('column' => 'title', 'title' => 'Title', 'direction' => 'asc')) ?>
                                </th>
                                <th width="5%" data-hide="phone,phablet">
                                    <?= translate('Status') ?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?= translate('Access')?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?= translate('Owner')?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?= translate('Date'); ?>
                                </th>
                                <th width="5%" data-hide="phone,phablet,tablet">
                                    <?= translate('Document count')?>
                                </th>
                                <th style="text-align: right;" width="7%" data-hide="phone">
                                    <?= helper('grid.sort', array('column' => 'custom', 'title' => 'Ordering', 'direction' => 'asc')) ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <? foreach($categories as $category):
                                $category->isPermissible();
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?= helper('grid.checkbox', array('entity'=> $category, 'attribs' => array(
                                        'data-document-count' => $category->document_count,
                                        'data-permissions' => htmlentities(json_encode($category->getPermissions()))
                                    ))); ?>
                                </td>
                                <td></td>
                                <td class="docman_table__title_field level<?= $category->level;?>">
                                    <div class="koowa_wrapped_content">
                                        <div class="whitespace_preserver">
                                            <?= import('com://site/docman.document.icon.html', array('icon' => $category->icon)) ?>
                                            <a class="docman_category_title" href="<?= route('view=category&id='.$category->id)?>">
                                                <?= escape($category->title) ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= helper('grid.state', array('entity' => $category, 'clickable' => $category->canPerform('edit'))) ?>
                                </td>
                                <td class="hidden-tablet hidden-phone">
                                    <span style="
                                        display: inline-block;
                                        text-overflow: ellipsis;
                                        overflow: hidden;
                                        max-width: 100px;
                                    ">
                                        <?= escape($category->access_title) ?>
                                        <? if ($category->access_raw == 0): ?>
                                            <br /><small><?= $category->level > 1 ? translate('Inherited') : translate('Default') ?></small>
                                        <? endif ?>
                                    </span>
                                </td>
                                <td>
                                    <?= escape($category->getAuthor()->getName()); ?>
                                </td>
                                <td>
                                    <?= helper('date.format', array('date' => $category->created_on)); ?>
                                </td>
                                <td class="hidden-phone" style="text-align: center">
                                    <a href="<?= route('view=documents&category='.$category->id)?>">
                                        <?= $category->document_count; ?>
                                    </a>
                                </td>
                                <td style="text-align: right;">
                                    <?= helper('grid.order', array(
                                        'entity' => $category,
                                        'total' => parameters()->total,
                                        'sort' => parameters()->sort === 'custom' ? 'ordering' : parameters()->sort)) ?>
                                </td>
                            </tr>
                            <? endforeach ?>
                            <? if(!count($categories)) : ?>
                            <tr>
                                <td colspan="20" style="text-align: center;">
                                    <?= translate('No categories found.') ?>
                                </td>
                            </tr>
                            <? endif ?>
                        </tbody>

                        <? if (count($categories)): ?>
                        <tfoot>
                            <tr>
                                <td colspan="20">
                                    <?= helper('paginator.pagination') ?>
                                </td>
                            </tr>
                        </tfoot>
                        <? endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
