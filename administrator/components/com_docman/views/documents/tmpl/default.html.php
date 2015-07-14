<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load', array('javascript' => true)); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('behavior.modal'); ?>

<ktml:script src="media://com_docman/js/toolbar.js" />
<ktml:script src="media://com_docman/js/footable.js"/>
<ktml:script src="media://com_docman/js/admin/documents.default.js"/>

<ktml:module position="submenu">
    <ktml:toolbar type="menubar">
</ktml:module>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="COM_DOCMAN_SUBMENU_DOCUMENTS" icon="article icon-stack">
</ktml:module>

<? if (count($categories) === 0): ?>
    <div class="alert alert-info alert-block">
        <p><?= translate('It seems like you don\'t have any categories yet. You first need to create one in the <a href="{link}">category manager</a> before adding documents.', array(
            'link' => route('option=com_docman&view=categories')
        )); ?>
        </p>
        <p>
            <a class="btn" href="<?= route('option=com_docman&view=categories') ?>">
                <i class="icon-folder-open"></i>
                <?= translate('Go to Category Manager')?>
            </a>
        </p>
    </div>
    <script>
    kQuery(function($){
        $('#toolbar-new').find('a').addClass('disabled').on('click', function(){ return false; });
    });
    </script>
<? endif; ?>

<div class="docman-container">

    <?php // Including the sidebar ?>
    <?= import('default_sidebar.html')?>

    <div class="docman_admin_list_grid">
        <form action="" method="get" class="-koowa-grid">
            <div class="scopebar">
                <div class="scopebar-group hidden-tablet hidden-phone">
                    <a class="<?= is_null(parameters()->enabled) && is_null(parameters()->status) ? 'active' : ''; ?>"
                       href="<?= route('enabled=&search=&status=' ) ?>">
                        <?= translate('All') ?>
                    </a>
                </div>
                <div class="scopebar-group last hidden-tablet hidden-phone">
                    <a class="<?= parameters()->enabled === 0 ? 'active' : ''; ?>"
                       href="<?= route('status=&enabled='.(parameters()->enabled === 0 ? '' : '0')) ?>">
                        <?= translate('Unpublished') ?>
                    </a>
                    <a class="<?= parameters()->status === 'published' ? 'active' : ''; ?>"
                       href="<?= route(parameters()->status === 'published' ? 'enabled=&status=' : 'enabled=1&status=published' ) ?>">
                        <?= translate('Published') ?>
                    </a>
                    <a class="<?= parameters()->status === 'pending' ? 'active' : ''; ?>"
                       href="<?= route(parameters()->status === 'pending' ? 'enabled=&status=' : 'enabled=1&status=pending' ) ?>">
                        <?= translate('Pending') ?>
                    </a>
                    <a class="<?= parameters()->status === 'expired' ? 'active' : ''; ?>"
                       href="<?= route(parameters()->status === 'expired' ? 'enabled=&status=' : 'enabled=1&status=expired' ) ?>">
                        <?= translate('Expired') ?>
                    </a>
                </div>
                <div class="scopebar-search">
                    <?= helper('grid.search', array('submit_on_clear' => true)) ?>
                </div>
            </div>
            <div class="docman_table_container">
                <table class="table table-striped footable">
                    <thead>
                        <tr>
                            <th style="text-align: center;" width="1">
                                <?= helper('grid.checkall')?>
                            </th>
                            <th width="10" data-class="expand" data-toggle="true"></th>
                            <th class="docman_table__title_field">
                                <?= helper('grid.sort', array('column' => 'title', 'title' => 'Title')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet">
                                <?= helper('grid.sort', array('column' => 'enabled', 'title' => 'Status')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet,tablet">
                                <?= helper('grid.sort', array('column' => 'access', 'title' => 'Access')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet,tablet">
                                <?= helper('grid.sort', array('column' => 'created_by', 'title' => 'Owner')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet">
                                <?= helper('grid.sort', array('column' => 'created_on', 'title' => 'Date')); ?>
                            </th>
                            <? if(!parameters()->category) : ?>
                            <th width="150" data-hide="phone">
                                <?= helper('grid.sort', array('column' => 'docman_category_id', 'title' => 'Category')); ?>
                            </th>
                            <? endif ?>
                            <th width="5%" data-hide="phone,phablet,tablet">
                                <?= helper('grid.sort', array('column' => 'hits', 'title' => '#downloads')); ?>
                            </th>
                            <th width="1" data-hide="phone,phablet" style="text-align: center;">
                                <i class="icon-download"></i>
                            </th>
                        </tr>
                    </thead>
                    <? if (count($documents)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="10">
                                <?= helper('paginator.pagination') ?>
                            </td>
                        </tr>
                    </tfoot>
                    <? endif; ?>
                    <tbody>
                        <? foreach ($documents as $document):
                            $document->isPermissible();
                            $location = false;
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <?= helper('grid.checkbox', array('entity' => $document, 'attribs' => array(
                                    'data-permissions' => htmlentities(json_encode($document->getPermissions()))
                                )))?>
                            </td>
                            <td style="text-align: left;">
                                <?= import('com://site/docman.document.icon.html', array('icon' => $document->icon)) ?>
                            </td>
                            <td class="docman_table__title_field">
                                <div class="koowa_wrapped_content">
                                    <div class="whitespace_preserver">
                                        <a href="<?= route('view=document&id='.$document->id); ?>">
                                            <?= escape($document->title); ?>
                                        </a>
                                        <? if ($document->storage_type == 'remote') : ?>
                                            <? $location = $document->storage_path; ?>
                                        <? elseif ($document->storage_type == 'file') : ?>
                                            <? $location = $document->storage_path; ?>
                                            <? if ($document->size): ?>
                                                <span>
                                                    <? $location .= ' - '.helper('string.humanize_filesize', array('size' => $document->size)); ?>
                                                </span>
                                            <? endif; ?>
                                        <? endif ?>
                                        <? if($location) : ?>
                                            <small title="<?= escape($location) ?>">
                                                <?= $location ?>
                                            </small>
                                        <? endif ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= helper('grid.state', array('entity' => $document, 'clickable' => $document->canPerform('edit'))) ?>
                            </td>
                            <td>
                                <?= escape($document->access_title) ?>
                                <? if ($document->access_raw == 0): ?>
                                    <br /><small><?= translate('Inherited') ?></small>
                                <? endif ?>
                            </td>
                            <td>
                                <?= escape($document->getAuthor()->getName()); ?>
                            </td>
                            <td>
                                <?= helper('date.format', array('date' => $document->created_on)); ?>
                            </td>
                            <? if(!parameters()->category) : ?>
                            <td class="docman_table__category_field">
                                <div class="koowa_wrapped_content">
                                    <div class="whitespace_preserver">
                                        <?= helper('grid.document_category', array('entity' => $document)) ?>
                                    </div>
                                </div>
                            </td>
                            <? endif ?>
                            <td style="text-align: center;">
                                <?= $document->hits; ?>
                            </td>
                            <td align="right">
                                <? if ($document->storage_type == 'remote'): ?>
                                    <? $location = $document->storage_path; ?>
                                <? else: ?>
                                    <? $location = route('view=file&routed=1&container=docman-files&folder='.($document->storage->folder === '.' ? '' : rawurlencode($document->storage->folder)).'&name='.rawurlencode($document->storage->name)); ?>
                                <? endif ?>
                                <a class="btn btn-mini" href="<?= $location; ?>" target="_blank"><i class="icon-download"></i></a>
                            </td>
                        </tr>
                        <? endforeach; ?>

                        <? if (!count($documents)) : ?>
                        <tr>
                            <td colspan="10" align="center" style="text-align: center;">
                                <?= translate('No documents found.') ?>
                            </td>
                        </tr>
                        <? endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<div id="document-move-modal" class="koowa mfp-hide" style="max-width: 600px; position: relative; width: auto; margin: 20px auto;">
    <form class="files-modal well">
        <div style="text-align: center;">
            <h3 style=" float: none">
                <?= translate('Move to') ?>
            </h3>
        </div>
        <div class="koowa_grid__row">
            <div class="control-group koowa_grid__item one-whole">
                <div class="controls">
                    <?= helper('listbox.categories', array(
                        'deselect' => true,
                        'check_access' => true,
                        'attribs' => array('id' => 'document_move_target'),
                        'selected' => null
                    )) ?>
                </div>
            </div>
        </div>
        <button class="btn btn-primary" disabled ><?= translate('Move'); ?></button>
    </form>
</div>
