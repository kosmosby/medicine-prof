<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.thumbnail_modal'); ?>

<? if ($params->track_downloads): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>

<? if ($document->isImage()): ?>
    <? if ($document->storage->width): ?>
    <meta itemprop="width" content="<?= $document->storage->width; ?>">
    <? endif; ?>
    <? if ($document->storage->height): ?>
    <meta itemprop="height" content="<?= $document->storage->height; ?>">
    <? endif; ?>
    <meta itemprop="contentUrl" content="<?= $document->image_download_path ?>">

    <a class="koowa_media__item__link" data-path="<?= $document->image_path ?>"
       data-title="<?= escape($document->title); ?>"
       data-id="<?= $document->id; ?>"
       data-width="<?= $document->storage->width; ?>"
       data-height="<?= $document->storage->height; ?>"
       href="<?= $document->image_download_path ?>"
       title="<?= escape($document->title) ?>">
<? else:
$modal = false;
if ($document->storage_type === 'file' && !$params->get('force_download'))
{
    $preview_extensions = object('com://site/docman.controller.behavior.previewable')->getGooglePreviewExtensions();
    $extension = $document->extension;
    if (($params->get('preview_with_gdocs') && in_array($extension, $preview_extensions))
        || $extension === 'pdf') {
        $modal = true;
    }
}
?>
    <a class="docman_track_download <?= $modal ? 'koowa_media__item__link koowa_media__item__link--html' : ''; ?>"
       data-title="<?= escape($document->title); ?>"
       data-id="<?= $document->id; ?>"
       href="<?= ($document->download_link) ?>"
       title="<?= escape($document->title) ?>">
<? endif; ?>

        <? if( $document->image_path ): ?>
            <div class="koowa_media__item__thumbnail">
                <img itemprop="thumbnail" src="<?= $document->image_path ?>" alt="<?= $document->title ?>">
            </div>
        <? else: ?>
            <div class="koowa_media__item__icon">
                <?= import('com://site/docman.document.icon.html', array('icon' => $document->icon, 'class' => ' koowa_icon--48')); ?>
            </div>
        <? endif; ?>

        <? if ($params->show_document_title): ?>
            <div class="koowa_header koowa_media__item__label">
                <div class="koowa_header__item koowa_header__item--title_container">
                    <div class="koowa_wrapped_content">
                        <div class="whitespace_preserver">
                            <div class="overflow_container">
                                <?= escape($document->title) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <? endif; ?>
    </a>