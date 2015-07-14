<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load', array('javascript' => true)); ?>
<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.modal'); ?>

<ktml:script src="media://com_docman/js/toolbar.js" />
<ktml:script src="media://com_docman/js/footable.js"/>
<ktml:script src="media://com_docman/js/admin/documents.default.js"/>

<ktml:module position="submenu">
    <ktml:toolbar type="menubar">
</ktml:module>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="COM_DOCMAN_SUBMENU_DOCUMENTS" icon="article icon-stack">
</ktml:module>

<?php if (count($categories) === 0): ?>
    <div class="alert alert-info alert-block">
        <p><?php echo $this->translate('It seems like you don\'t have any categories yet. You first need to create one in the <a href="{link}">category manager</a> before adding documents.', array(
            'link' => $this->route('option=com_docman&view=categories')
        )); ?>
        </p>
        <p>
            <a class="btn" href="<?php echo $this->route('option=com_docman&view=categories') ?>">
                <i class="icon-folder-open"></i>
                <?php echo $this->translate('Go to Category Manager')?>
            </a>
        </p>
    </div>
    <script>
    kQuery(function($){
        $('#toolbar-new').find('a').addClass('disabled').on('click', function(){ return false; });
    });
    </script>
<?php endif; ?>

<div class="docman-container">

    <?php // Including the sidebar ?>
    <?php echo $this->import('default_sidebar.html')?>

    <div class="docman_admin_list_grid">
        <form action="" method="get" class="-koowa-grid">
            <div class="scopebar">
                <div class="scopebar-group hidden-tablet hidden-phone">
                    <a class="<?php echo is_null($this->parameters()->enabled) && is_null($this->parameters()->status) ? 'active' : ''; ?>"
                       href="<?php echo $this->route('enabled=&search=&status=' ) ?>">
                        <?php echo $this->translate('All') ?>
                    </a>
                </div>
                <div class="scopebar-group last hidden-tablet hidden-phone">
                    <a class="<?php echo $this->parameters()->enabled === 0 ? 'active' : ''; ?>"
                       href="<?php echo $this->route('status=&enabled='.($this->parameters()->enabled === 0 ? '' : '0')) ?>">
                        <?php echo $this->translate('Unpublished') ?>
                    </a>
                    <a class="<?php echo $this->parameters()->status === 'published' ? 'active' : ''; ?>"
                       href="<?php echo $this->route($this->parameters()->status === 'published' ? 'enabled=&status=' : 'enabled=1&status=published' ) ?>">
                        <?php echo $this->translate('Published') ?>
                    </a>
                    <a class="<?php echo $this->parameters()->status === 'pending' ? 'active' : ''; ?>"
                       href="<?php echo $this->route($this->parameters()->status === 'pending' ? 'enabled=&status=' : 'enabled=1&status=pending' ) ?>">
                        <?php echo $this->translate('Pending') ?>
                    </a>
                    <a class="<?php echo $this->parameters()->status === 'expired' ? 'active' : ''; ?>"
                       href="<?php echo $this->route($this->parameters()->status === 'expired' ? 'enabled=&status=' : 'enabled=1&status=expired' ) ?>">
                        <?php echo $this->translate('Expired') ?>
                    </a>
                </div>
                <div class="scopebar-search">
                    <?php echo $this->helper('grid.search', array('submit_on_clear' => true)) ?>
                </div>
            </div>
            <div class="docman_table_container">
                <table class="table table-striped footable">
                    <thead>
                        <tr>
                            <th style="text-align: center;" width="1">
                                <?php echo $this->helper('grid.checkall')?>
                            </th>
                            <th width="10" data-class="expand" data-toggle="true"></th>
                            <th class="docman_table__title_field">
                                <?php echo $this->helper('grid.sort', array('column' => 'title', 'title' => 'Title')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet">
                                <?php echo $this->helper('grid.sort', array('column' => 'enabled', 'title' => 'Status')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet,tablet">
                                <?php echo $this->helper('grid.sort', array('column' => 'access', 'title' => 'Access')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet,tablet">
                                <?php echo $this->helper('grid.sort', array('column' => 'created_by', 'title' => 'Owner')); ?>
                            </th>
                            <th width="5%" data-hide="phone,phablet">
                                <?php echo $this->helper('grid.sort', array('column' => 'created_on', 'title' => 'Date')); ?>
                            </th>
                            <?php if(!$this->parameters()->category) : ?>
                            <th width="150" data-hide="phone">
                                <?php echo $this->helper('grid.sort', array('column' => 'docman_category_id', 'title' => 'Category')); ?>
                            </th>
                            <?php endif ?>
                            <th width="5%" data-hide="phone,phablet,tablet">
                                <?php echo $this->helper('grid.sort', array('column' => 'hits', 'title' => '#downloads')); ?>
                            </th>
                            <th width="1" data-hide="phone,phablet" style="text-align: center;">
                                <i class="icon-download"></i>
                            </th>
                        </tr>
                    </thead>
                    <?php if (count($documents)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="10">
                                <?php echo $this->helper('paginator.pagination') ?>
                            </td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                    <tbody>
                        <?php foreach ($documents as $document):
                            $document->isPermissible();
                            $location = false;
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php echo $this->helper('grid.checkbox', array('entity' => $document, 'attribs' => array(
                                    'data-permissions' => htmlentities(json_encode($document->getPermissions()))
                                )))?>
                            </td>
                            <td style="text-align: left;">
                                <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $document->icon)) ?>
                            </td>
                            <td class="docman_table__title_field">
                                <div class="koowa_wrapped_content">
                                    <div class="whitespace_preserver">
                                        <a href="<?php echo $this->route('view=document&id='.$document->id); ?>">
                                            <?php echo $this->escape($document->title); ?>
                                        </a>
                                        <?php if ($document->storage_type == 'remote') : ?>
                                            <?php $location = $document->storage_path; ?>
                                        <?php elseif ($document->storage_type == 'file') : ?>
                                            <?php $location = $document->storage_path; ?>
                                            <?php if ($document->size): ?>
                                                <span>
                                                    <?php $location .= ' - '.$this->helper('string.humanize_filesize', array('size' => $document->size)); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif ?>
                                        <?php if($location) : ?>
                                            <small title="<?php echo $this->escape($location) ?>">
                                                <?php echo $location ?>
                                            </small>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo $this->helper('grid.state', array('entity' => $document, 'clickable' => $document->canPerform('edit'))) ?>
                            </td>
                            <td>
                                <?php echo $this->escape($document->access_title) ?>
                                <?php if ($document->access_raw == 0): ?>
                                    <br /><small><?php echo $this->translate('Inherited') ?></small>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php echo $this->escape($document->getAuthor()->getName()); ?>
                            </td>
                            <td>
                                <?php echo $this->helper('date.format', array('date' => $document->created_on)); ?>
                            </td>
                            <?php if(!$this->parameters()->category) : ?>
                            <td class="docman_table__category_field">
                                <div class="koowa_wrapped_content">
                                    <div class="whitespace_preserver">
                                        <?php echo $this->helper('grid.document_category', array('entity' => $document)) ?>
                                    </div>
                                </div>
                            </td>
                            <?php endif ?>
                            <td style="text-align: center;">
                                <?php echo $document->hits; ?>
                            </td>
                            <td align="right">
                                <?php if ($document->storage_type == 'remote'): ?>
                                    <?php $location = $document->storage_path; ?>
                                <?php else: ?>
                                    <?php $location = $this->route('view=file&routed=1&container=docman-files&folder='.($document->storage->folder === '.' ? '' : rawurlencode($document->storage->folder)).'&name='.rawurlencode($document->storage->name)); ?>
                                <?php endif ?>
                                <a class="btn btn-mini" href="<?php echo $location; ?>" target="_blank"><i class="icon-download"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (!count($documents)) : ?>
                        <tr>
                            <td colspan="10" align="center" style="text-align: center;">
                                <?php echo $this->translate('No documents found.') ?>
                            </td>
                        </tr>
                        <?php endif; ?>
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
                <?php echo $this->translate('Move to') ?>
            </h3>
        </div>
        <div class="koowa_grid__row">
            <div class="control-group koowa_grid__item one-whole">
                <div class="controls">
                    <?php echo $this->helper('listbox.categories', array(
                        'deselect' => true,
                        'check_access' => true,
                        'attribs' => array('id' => 'document_move_target'),
                        'selected' => null
                    )) ?>
                </div>
            </div>
        </div>
        <button class="btn btn-primary" disabled ><?php echo $this->translate('Move'); ?></button>
    </form>
</div>
