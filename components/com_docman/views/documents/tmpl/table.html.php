<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2012 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<? if ($params->track_downloads): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>

<? // Documents header & sorting ?>
<div class="docman_block">
    <? if ($params->show_documents_header): ?>
    <div class="docman_block__item">
        <h4 class="koowa_header"><?= translate('Documents')?></h4>
    </div>
    <? endif; ?>
    <? if ($params->show_document_sort_limit): ?>
        <div class="docman_block__item docman_sorting btn-group form-search">
            <label for="sort-documents" class="control-label"><?= translate('Order by') ?></label>
            <?= helper('paginator.sort_documents', array(
                'attribs'   => array('class' => 'input-medium', 'id' => 'sort-documents')
            )); ?>
        </div>
    <? endif; ?>
</div>


<? // Table ?>
<table class="table table-striped koowa_table koowa_table--documents">
    <tbody>
    <? foreach ($documents as $document): ?>
        <tr itemscope itemtype="http://schema.org/CreativeWork">
            <? // Title and labels ?>
            <td>
                <span class="koowa_header">
                    <? // Icon ?>
                    <? if ($document->icon && $params->show_document_icon): ?>
                        <span class="koowa_header__item koowa_header__item--image_container">
                            <? if ($params->document_title_link): ?>
                                <a href="<?= ($document->title_link) ?>"
                                <?= $params->download_in_blank_page && $params->document_title_link === 'download'  ? 'target="_blank"' : ''; ?>
                                >
                            <? endif; ?>

                            <?= import('com://site/docman.document.icon.html', array('icon' => $document->icon)) ?>

                            <? if ($params->document_title_link): ?>
                                </a>
                            <? endif; ?>
                        </span>
                    <? endif ?>

                    <? // Title ?>
                    <span class="koowa_header__item">
                        <span class="koowa_wrapped_content">
                            <span class="whitespace_preserver">
                                <? if ($params->document_title_link): ?>
                                    <a href="<?= ($document->title_link) ?>" title="<?= escape($document->storage->name);?>"
                                        <?= $params->download_in_blank_page && $params->document_title_link === 'download'  ? 'target="_blank"' : ''; ?>
                                    ><span itemprop="name"><?= escape($document->title);?></span><!--
                                        --><? if ($document->title_link === $document->download_link): ?>
                                            <? // Filetype and Filesize  ?>
                                            <? if (($params->show_document_size && $document->size) || ($document->storage_type == 'file' && $params->show_document_extension)): ?>
                                                <span class="docman_download__info">(
                                                    <? if ($document->storage_type == 'file' && $params->show_document_extension): ?>
                                                        <?= escape($document->extension . ($params->show_document_size && $document->size ? ', ':'')) ?>
                                                    <? endif ?>
                                                    <? if ($params->show_document_size && $document->size): ?>
                                                        <?= helper('string.humanize_filesize', array('size' => $document->size)) ?>
                                                    <? endif ?>
                                                )</span>
                                            <? endif; ?>
                                        <? endif ?><!--
                                    --></a>
                                <? else: ?>
                                    <span title="<?= escape($document->storage->name);?>">
                                        <span itemprop="name"><?= escape($document->title);?></span>
                                        <? if ($document->title_link === $document->download_link
                                            && ($params->show_document_size && $document->size || $document->storage_type == 'file' && $params->show_document_extension)): ?>
                                            (<?= $document->extension ? $document->extension.', ' : '' ?><?= helper('string.humanize_filesize', array('size' => $document->size)); ?>)
                                        <? endif; ?>
                                    </span>
                                <? endif; ?>

                                <? // Document hits ?>
                                <? if ($params->show_document_hits && $document->hits): ?>
                                    <meta itemprop="interactionCount" content=”UserDownloads:<?= $document->hits ?>">
                                    <span class="detail-label">(<?= object('translator')->choose(array('{number} download', '{number} downloads'), $document->hits, array('number' => $document->hits)) ?>)</span>
                                <? endif; ?>

                                <? // Label new ?>
                                <? if ($params->show_document_recent && isRecent($document)): ?>
                                    <span class="label label-success"><?= translate('New'); ?></span>
                                <? endif; ?>

                                <? // Label locked ?>
                                <? if ($document->canPerform('edit') && $document->isLockable() && $document->isLocked()): ?>
                                    <span class="label label-warning"><?= translate('Locked'); ?></span>
                                <? endif; ?>

                                <? // Label status ?>
                                <? if (!$document->isPublished() || !$document->enabled): ?>
                                    <? $status = $document->enabled ? translate($document->status) : translate('Draft'); ?>
                                    <span class="label label-<?= $document->enabled ? $document->status : 'draft' ?>"><?= ucfirst($status); ?></span>
                                <? endif; ?>

                                <? // Label owner ?>
                                <? if ($params->get('show_document_owner_label', 1) && object('user')->getId() == $document->created_by): ?>
                                    <span class="label label-success"><?= translate('Owner'); ?></span>
                                <? endif; ?>

                                <? // Label popular ?>
                                <? if ($params->show_document_popular && ($document->hits >= $params->hits_for_popular)): ?>
                                    <span class="label label-important"><?= translate('Popular') ?></span>
                                <? endif ?>

                                <? // Anchor ?>
                                <? if (!$params->document_title_link): ?>
                                    <span id="document-<?= escape($document->slug) ?>" class="koowa_anchor">
                                    <a href="#document-<?= escape($document->slug) ?>">#</a>
                                </span>
                                <? endif; ?>
                            </span>
                        </span>
                    </span>
                </span>
            </td>

            <? // Date ?>
            <td width="5" class="koowa_table__dates">
            <? if ($params->show_document_created): ?>
                <time itemprop="datePublished"
                      datetime="<?= parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date ?>"
                >
                    <?= helper('date.format', array(
                        'date' => parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date,
                        'format' => 'd M Y')); ?>
                </time>
            <? endif; ?>
            </td>

            <? // Download ?>
            <? if ($params->document_title_link !== 'download'): ?>
            <td width="5" class="koowa_table__download">
                <div class="btn-group">
                    <a class="btn btn-default btn-mini docman_download__button" href="<?= $document->download_link; ?>"
                        <?= $params->download_in_blank_page ? 'target="_blank"' : ''; ?>
                        >
                        <? // Text  ?>
                        <?= translate('Download'); ?>

                        <? // Filetype and Filesize  ?>
                        <? if (($params->show_document_size && $document->size) || ($document->storage_type == 'file' && $params->show_document_extension)): ?>
                            <span class="docman_download__info docman_download__info--inline">(<!--
                                --><? if ($document->storage_type == 'file' && $params->show_document_extension): ?><!--
                                    --><?= escape($document->extension . ($params->show_document_size && $document->size ? ', ':'')) ?><!--
                                --><? endif ?><!--
                                --><? if ($params->show_document_size && $document->size): ?><!--
                                    --><?= helper('string.humanize_filesize', array('size' => $document->size)) ?><!--
                                --><? endif ?><!--
                                -->)</span>
                        <? endif; ?>
                    </a>
                </div>
            </td>
            <? endif; ?>

            <? // Edit buttons ?>
            <? if ($document->canPerform('edit') || $document->canPerform('delete')): ?>
            <td width="5" class="koowa_table__manage">
                <? // Manage | Import partial template from document view ?>
                <?= import('com://site/docman.document.manage.html', array(
                    'document' => $document,
                    'button_size' => 'mini'
                )) ?>
            </td>
            <? endif; ?>
        </tr>
    <? endforeach ?>
    </tbody>
</table>