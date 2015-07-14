<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2012 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php if ($params->track_downloads): ?>
    <?php echo $this->helper('behavior.download_tracker'); ?>
<?php endif; ?>

<?php // Documents header & sorting ?>
<div class="docman_block">
    <?php if ($params->show_documents_header): ?>
    <div class="docman_block__item">
        <h4 class="koowa_header"><?php echo $this->translate('Documents')?></h4>
    </div>
    <?php endif; ?>
    <?php if ($params->show_document_sort_limit): ?>
        <div class="docman_block__item docman_sorting btn-group form-search">
            <label for="sort-documents" class="control-label"><?php echo $this->translate('Order by') ?></label>
            <?php echo $this->helper('paginator.sort_documents', array(
                'attribs'   => array('class' => 'input-medium', 'id' => 'sort-documents')
            )); ?>
        </div>
    <?php endif; ?>
</div>


<?php // Table ?>
<table class="table table-striped koowa_table koowa_table--documents">
    <tbody>
    <?php foreach ($documents as $document): ?>
        <tr itemscope itemtype="http://schema.org/CreativeWork">
            <?php // Title and labels ?>
            <td>
                <span class="koowa_header">
                    <?php // Icon ?>
                    <?php if ($document->icon && $params->show_document_icon): ?>
                        <span class="koowa_header__item koowa_header__item--image_container">
                            <?php if ($params->document_title_link): ?>
                                <a href="<?php echo ($document->title_link) ?>"
                                <?php echo $params->download_in_blank_page && $params->document_title_link === 'download'  ? 'target="_blank"' : ''; ?>
                                >
                            <?php endif; ?>

                            <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $document->icon)) ?>

                            <?php if ($params->document_title_link): ?>
                                </a>
                            <?php endif; ?>
                        </span>
                    <?php endif ?>

                    <?php // Title ?>
                    <span class="koowa_header__item">
                        <span class="koowa_wrapped_content">
                            <span class="whitespace_preserver">
                                <?php if ($params->document_title_link): ?>
                                    <a href="<?php echo ($document->title_link) ?>" title="<?php echo $this->escape($document->storage->name);?>"
                                        <?php echo $params->download_in_blank_page && $params->document_title_link === 'download'  ? 'target="_blank"' : ''; ?>
                                    ><span itemprop="name"><?php echo $this->escape($document->title);?></span><!--
                                        --><?php if ($document->title_link === $document->download_link): ?>
                                            <?php // Filetype and Filesize  ?>
                                            <?php if (($params->show_document_size && $document->size) || ($document->storage_type == 'file' && $params->show_document_extension)): ?>
                                                <span class="docman_download__info">(
                                                    <?php if ($document->storage_type == 'file' && $params->show_document_extension): ?>
                                                        <?php echo $this->escape($document->extension . ($params->show_document_size && $document->size ? ', ':'')) ?>
                                                    <?php endif ?>
                                                    <?php if ($params->show_document_size && $document->size): ?>
                                                        <?php echo $this->helper('string.humanize_filesize', array('size' => $document->size)) ?>
                                                    <?php endif ?>
                                                )</span>
                                            <?php endif; ?>
                                        <?php endif ?><!--
                                    --></a>
                                <?php else: ?>
                                    <span title="<?php echo $this->escape($document->storage->name);?>">
                                        <span itemprop="name"><?php echo $this->escape($document->title);?></span>
                                        <?php if ($document->title_link === $document->download_link
                                            && ($params->show_document_size && $document->size || $document->storage_type == 'file' && $params->show_document_extension)): ?>
                                            (<?php echo $document->extension ? $document->extension.', ' : '' ?><?php echo $this->helper('string.humanize_filesize', array('size' => $document->size)); ?>)
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <?php // Document hits ?>
                                <?php if ($params->show_document_hits && $document->hits): ?>
                                    <meta itemprop="interactionCount" content=”UserDownloads:<?php echo $document->hits ?>">
                                    <span class="detail-label">(<?php echo $this->object('translator')->choose(array('{number} download', '{number} downloads'), $document->hits, array('number' => $document->hits)) ?>)</span>
                                <?php endif; ?>

                                <?php // Label new ?>
                                <?php if ($params->show_document_recent && $this->isRecent($document)): ?>
                                    <span class="label label-success"><?php echo $this->translate('New'); ?></span>
                                <?php endif; ?>

                                <?php // Label locked ?>
                                <?php if ($document->canPerform('edit') && $document->isLockable() && $document->isLocked()): ?>
                                    <span class="label label-warning"><?php echo $this->translate('Locked'); ?></span>
                                <?php endif; ?>

                                <?php // Label status ?>
                                <?php if (!$document->isPublished() || !$document->enabled): ?>
                                    <?php $status = $document->enabled ? $this->translate($document->status) : $this->translate('Draft'); ?>
                                    <span class="label label-<?php echo $document->enabled ? $document->status : 'draft' ?>"><?php echo ucfirst($status); ?></span>
                                <?php endif; ?>

                                <?php // Label owner ?>
                                <?php if ($params->get('show_document_owner_label', 1) && $this->object('user')->getId() == $document->created_by): ?>
                                    <span class="label label-success"><?php echo $this->translate('Owner'); ?></span>
                                <?php endif; ?>

                                <?php // Label popular ?>
                                <?php if ($params->show_document_popular && ($document->hits >= $params->hits_for_popular)): ?>
                                    <span class="label label-important"><?php echo $this->translate('Popular') ?></span>
                                <?php endif ?>

                                <?php // Anchor ?>
                                <?php if (!$params->document_title_link): ?>
                                    <span id="document-<?php echo $this->escape($document->slug) ?>" class="koowa_anchor">
                                    <a href="#document-<?php echo $this->escape($document->slug) ?>">#</a>
                                </span>
                                <?php endif; ?>
                            </span>
                        </span>
                    </span>
                </span>
            </td>

            <?php // Date ?>
            <td width="5" class="koowa_table__dates">
            <?php if ($params->show_document_created): ?>
                <time itemprop="datePublished"
                      datetime="<?php echo $this->parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date ?>"
                >
                    <?php echo $this->helper('date.format', array(
                        'date' => $this->parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date,
                        'format' => 'd M Y')); ?>
                </time>
            <?php endif; ?>
            </td>

            <?php // Download ?>
            <?php if ($params->document_title_link !== 'download'): ?>
            <td width="5" class="koowa_table__download">
                <div class="btn-group">
                    <a class="btn btn-default btn-mini docman_download__button" href="<?php echo $document->download_link; ?>"
                        <?php echo $params->download_in_blank_page ? 'target="_blank"' : ''; ?>
                        >
                        <?php // Text  ?>
                        <?php echo $this->translate('Download'); ?>

                        <?php // Filetype and Filesize  ?>
                        <?php if (($params->show_document_size && $document->size) || ($document->storage_type == 'file' && $params->show_document_extension)): ?>
                            <span class="docman_download__info docman_download__info--inline">(<!--
                                --><?php if ($document->storage_type == 'file' && $params->show_document_extension): ?><!--
                                    --><?php echo $this->escape($document->extension . ($params->show_document_size && $document->size ? ', ':'')) ?><!--
                                --><?php endif ?><!--
                                --><?php if ($params->show_document_size && $document->size): ?><!--
                                    --><?php echo $this->helper('string.humanize_filesize', array('size' => $document->size)) ?><!--
                                --><?php endif ?><!--
                                -->)</span>
                        <?php endif; ?>
                    </a>
                </div>
            </td>
            <?php endif; ?>

            <?php // Edit buttons ?>
            <?php if ($document->canPerform('edit') || $document->canPerform('delete')): ?>
            <td width="5" class="koowa_table__manage">
                <?php // Manage | Import partial template from document view ?>
                <?php echo $this->import('com://site/docman.document.manage.html', array(
                    'document' => $document,
                    'button_size' => 'mini'
                )) ?>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>