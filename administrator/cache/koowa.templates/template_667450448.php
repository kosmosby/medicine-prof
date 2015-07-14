<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<div class="docman_document" itemscope itemtype="http://schema.org/CreativeWork">

    <?php // Header ?>
    <?php if ($params->show_document_title
          || ($params->show_document_recent && $this->isRecent($document))
          || ($document->canPerform('edit') && $document->isLockable() && $document->isLocked())
          || (!$document->isPublished() || !$document->enabled)
          || ($this->object('user')->getId() == $document->created_by)
          || ($params->show_document_popular && ($document->hits >= $params->hits_for_popular))
    ): ?>
    <h<?php echo $heading; ?> class="koowa_header">

        <?php // Header image ?>
        <?php if ($document->icon && $params->show_document_icon): ?>
        <span class="koowa_header__item koowa_header__item--image_container">
            <?php if ($params->document_title_link && $link == 1): ?>
            <a class="koowa_header__image_link <?php echo $params->document_title_link === 'download' ? 'docman_track_download' : ''; ?>"
               href="<?php echo ($document->title_link) ?>"
               data-title="<?php echo $this->escape($document->title); ?>"
               data-id="<?php echo $document->id; ?>"
               <?php echo $params->download_in_blank_page && $params->document_title_link === 'download' ? 'target="_blank"' : ''; ?>><!--
                -->
                <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $document->icon, 'class' => ' koowa_icon--24')); ?>
            </a>
            <?php else: ?>
                <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $document->icon, 'class' => ' koowa_icon--24')); ?>
            <?php endif; ?>
        </span>
        <?php endif ?>

        <?php // Header title ?>
        <span class="koowa_header__item">
            <span class="koowa_wrapped_content">
                <span class="whitespace_preserver">
                    <?php if ($params->show_document_title): ?>
                        <?php if ($params->document_title_link && $link): ?>
                        <a class="koowa_header__title_link <?php echo $params->document_title_link === 'download' ? 'docman_track_download' : ''; ?>"
                           href="<?php echo ($document->title_link) ?>"
                           data-title="<?php echo $this->escape($document->title); ?>"
                           data-id="<?php echo $document->id; ?>"
                           <?php echo $params->download_in_blank_page && $params->document_title_link === 'download' ? 'target="_blank"' : ''; ?>><!--
                            --><span itemprop="name"><?php echo $this->escape($document->title); ?></span></a>
                        <?php else: ?>
                            <span itemprop="name"><?php echo $this->escape($document->title); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php // Show labels ?>

                    <?php // Label new ?>
                    <?php if ($params->show_document_recent && $this->isRecent($document)): ?>
                        <span class="label label-success"><?php echo $this->translate('New'); ?></span>
                    <?php endif; ?>

                    <?php // Label locked ?>
                    <?php if ($document->canPerform('edit') && $document->isLockable() && $document->isLocked()): ?>
                        <span class="label label-warning"><?php echo $this->helper('grid.lock_message', array('entity' => $document)); ?></span>
                    <?php endif; ?>

                    <?php // Label status ?>
                    <?php if (!$document->isPublished() || !$document->enabled): ?>
                        <?php $status = $document->enabled ? $this->translate($document->status) : $this->translate('Draft'); ?>
                        &nbsp;<span class="label label-<?php echo $document->enabled ? $document->status : 'draft' ?>"><?php echo ucfirst($status); ?></span>
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
                    <?php if (!$params->document_title_link || !$link): ?>
                        <span id="document-<?php echo $this->escape($document->slug) ?>" class="koowa_anchor">
                        <a href="#document-<?php echo $this->escape($document->slug) ?>">#</a>
                    </span>
                    <?php endif; ?>
                </span>
            </span>
        </span>
    </h<?php echo $heading; ?>>
    <?php endif; ?>


    <?php // After title - content plugin event ?>
    <?php echo $this->helper('event.trigger', array(
        'name'       => 'onContentAfterTitle',
        'attributes' => array($event_context, &$document, &$params, 0)
    )); ?>


    <?php // Dates&Owner ?>
    <?php if (($params->show_document_created)
        || ($document->modified_by && $params->show_document_modified)
        || ($params->show_document_created_by)
        || ($params->show_document_category)
        || ($params->show_document_hits && $document->hits)
    ): ?>
    <p class="docman_document_details">

        <?php // Created ?>
        <?php if ($params->show_document_created): ?>
        <span class="created-on-label">
            <time itemprop="datePublished" datetime="<?php echo $document->publish_date ?>">
                <?php echo $this->translate('Published on'); ?> <?php echo $this->helper('date.format', array('date' => $document->publish_date)); ?>
            </time>
        </span>
        <?php endif; ?>

        <?php // Modified ?>
        <?php if ($params->show_document_modified && $document->modified_by): ?>
        <span class="modified-on-label">
            <time itemprop="dateModified" datetime="<?php echo $document->modified_on ?>">
                <?php echo $this->translate('Modified on'); ?> <?php echo $this->helper('date.format', array('date' => $document->modified_on)); ?>
            </time>
        </span>
        <?php endif; ?>

        <?php // Owner ?>
        <?php if ($params->show_document_created_by && $document->created_by):
            $owner = '<span itemprop="author">'.$document->getAuthor()->getName().'</span>'; ?>
            <span class="owner-label">
                <?php echo $this->translate('By {owner}', array('owner' => $owner)); ?>
            </span>
        <?php endif; ?>

        <?php // Category ?>
        <?php if ($params->show_document_category):
            $category = '<span itemprop="genre">'.$document->category_title.'</span>'; ?>
            <span class="category-label">
                <?php echo $this->translate('In {category}', array('category' => $category)); ?>
            </span>
        <?php endif; ?>

        <?php // Downloads ?>
        <?php if ($params->show_document_hits && $document->hits): ?>
            <meta itemprop="interactionCount" content="UserDownloads:<?php echo $document->hits ?>">
            <span class="hits-label">
                <?php echo $this->object('translator')->choose(array('{number} download', '{number} downloads'), $document->hits, array('number' => $document->hits)) ?>
            </span>
        <?php endif ?>
    </p>
    <?php endif; ?>

    <?php // Download area ?>
    
	<?php include(JPATH_SITE . '/components/com_docmanpaypal/buy_now_button_large.php');	
	?>
                <?php if ((!$this->object('user')->isAuthentic() || $document->canPerform('download')) && $show_download == true): ?>
    <div class="docman_download<?php if ($document->description != '') echo " docman_download--right"; ?>">
        <a class="btn btn-large <?php echo $buttonstyle; ?> btn-block docman_download__button docman_track_download"
           href="<?php echo $document->download_link; ?>"
           data-title="<?php echo $this->escape($document->title); ?>"
           data-id="<?php echo $document->id; ?>"
           <?php echo $params->download_in_blank_page ? 'target="_blank"' : ''; ?>
            >

            <?php // Text  ?>
            <?php echo $this->translate('Download'); ?>

            <?php // Filetype and Filesize  ?>
            <?php if (($params->show_document_size && $document->size) || ($document->storage_type == 'file' && $params->show_document_extension)): ?>
                <span class="docman_download__info">(<!--
                --><?php if ($document->storage_type == 'file' && $params->show_document_extension): ?><!--
                    --><?php echo $this->escape($document->extension . ($params->show_document_size && $document->size ? ', ':'')) ?><!--
                --><?php endif ?><!--
                --><?php if ($params->show_document_size && $document->size): ?><!--
                    --><?php echo $this->helper('string.humanize_filesize', array('size' => $document->size)) ?><!--
                --><?php endif ?><!--
                -->)</span>
            <?php endif; ?>
        </a>

        <?php // Filename ?>
        <?php if ($document->storage->name && $params->show_document_filename): ?>
            <p class="docman_download__filename" title="<?php echo $this->escape($document->storage->name); ?>"><?php echo $this->escape($document->storage->name); ?></p>
        <?php endif; ?>
    </div>
    <?php endif ?>


    <?php // Before display - content plugin event ?>
    <?php echo $this->helper('event.trigger', array(
        'name'       => 'onContentBeforeDisplay',
        'attributes' => array($event_context, &$document, &$params, 0)
    )); ?>


    <?php // Document description ?>
    <?php if ($params->show_document_description || $params->show_document_image): ?>
    <div class="docman_description">
        <?php if ($params->show_document_image && $document->image): ?>
            <?php echo $this->helper('behavior.thumbnail_modal'); ?>
            <a class="docman_thumbnail thumbnail" href="<?php echo $document->image_download_path ?>">
                <img itemprop="thumbnailUrl" src="<?php echo $document->image_path ?>" />
            </a>
        <?php endif ?>

        <?php if ($params->show_document_description):
            $field = 'description_'.(isset($description) ? $description : 'full');
        ?>
            <div itemprop="description">
            <?php echo $this->prepareText($document->$field); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif ?>


    <?php // After display - content plugin event ?>
    <?php echo $this->helper('event.trigger', array(
        'name'       => 'onContentAfterDisplay',
        'attributes' => array($event_context, &$document, &$params, 0)
    )); ?>


    <?php // Edit area | Import partial template from document view ?>
    <?php echo $this->import('com://site/docman.document.manage.html', array('document' => $document)) ?>

</div>