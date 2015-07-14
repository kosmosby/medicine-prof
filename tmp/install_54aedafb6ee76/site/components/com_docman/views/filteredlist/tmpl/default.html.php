<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.koowa');?>


<? // RSS feed ?>
<link href="<?=route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />

<div class="docman_list_layout docman_list_layout--filtered_list">

    <? // Page Heading ?>
    <? if ($params->get('show_page_heading')): ?>
        <h1 class="docman_page_heading">
            <?= escape($params->get('page_heading')); ?>
        </h1>
    <? endif; ?>

    <? // Sorting ?>
    <? if ($params->show_document_sort_limit): ?>
    <div class="docman_block">
        <div class="docman_sorting btn-group form-search">
            <label for="sort-documents" class="control-label"><?= translate('Order by') ?></label>
            <?= helper('paginator.sort_documents', array(
                'attribs'   => array('class' => 'input-medium', 'id' => 'sort-documents')
            )); ?>
        </div>
    </div>
    <? endif; ?>

    <? // Documents & pagination  ?>
    <form action="" method="get" class="-koowa-grid">

        <? // Document list | Import child template from documents view ?>
        <?= import('com://site/docman.documents.list.html', array(
            'documents' => $documents,
            'params' => $params
        ))?>

        <? // Pagination ?>
        <? if ($params->show_pagination !== '0' && parameters()->total): ?>
            <?= helper('paginator.pagination', array(
                'show_limit' => (bool) $params->show_document_sort_limit
            )) ?>
        <? endif; ?>

    </form>
</div>
