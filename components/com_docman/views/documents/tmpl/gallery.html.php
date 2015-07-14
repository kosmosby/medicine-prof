<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load', array('javascript' => true)); ?>
<?= helper('behavior.jquery'); ?>
<?= helper('behavior.modal'); ?>
<?= helper('behavior.photoswipe'); ?>

<? if ($params->track_downloads): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>


<ktml:script src="media://com_docman/js/site/gallery.js" />

<script>
    kQuery(function($) {
        var categoriesGallery = $('.koowa_media_wrapper--categories');
        if ( categoriesGallery ) {
            categoriesGallery.simpleGallery();
        }

        var documentsGallery = $('.koowa_media_wrapper--documents');
        if ( documentsGallery ) {
            documentsGallery.simpleGallery();
        }
    });
</script>

<? // RSS feed ?>
<link href="<?=route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />


<?
// @TODO: we might want to add this to the bootstrap file so it's everywhere
// Advantages of this technique:
// 1. We can make sure there's no flashing content before JS is initialized
//    (by adding html.js-enabled .container {} html.js-enabled .container.js-initialized etc. {} )
//    but keep good functionality when JS is disabled/broken
// 2. We're not using a JS framework but this is super fast, also no extra http request
?>
<script>function addClass(e, c) {if (e.classList) {e.classList.add(c);} else {e.className += ' ' + c;}}var el = document.documentElement;var cl = 'koowa-js-enabled';addClass(el,cl)</script>

<div itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/ImageGallery">

    <? // Page Heading ?>
    <? if ($params->get('show_page_heading')): ?>
        <h1 class="docman_page_heading">
            <?= escape($params->get('page_heading')); ?>
        </h1>
    <? endif; ?>

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar" title="false">


    <? // Category ?>
    <? if (isset($category) &&
        (($params->show_category_title && $category->title)
        || ($params->show_image && $category->image)
        || ($category->description_full && $params->show_description))
    ): ?>
    <div class="docman_category">

        <? // Header ?>
        <? if ($params->show_category_title && $category->title): ?>
        <h3 class="koowa_header">
            <? // Header image ?>
            <? if ($params->show_icon && $category->icon): ?>
            <span class="koowa_header__item koowa_header__item--image_container">
                <?= import('com://site/docman.document.icon.html', array('icon' => $category->icon)) ?>
            </span>
            <? endif ?>

            <? // Header title ?>
            <? if ($params->show_category_title): ?>
            <span class="koowa_header__item">
                <span class="koowa_wrapped_content">
                    <span class="whitespace_preserver">
                        <?= escape($category->title); ?>
                    </span>
                </span>
            </span>
            <? endif; ?>
        </h3>
        <? endif; ?>

        <? // Category image ?>
        <? if ($params->show_image && $category->image): ?>
        <?= helper('behavior.thumbnail_modal'); ?>
        <a class="docman_thumbnail thumbnail" href="<?= $category->image_path ?>">
            <img src="<?= $category->image_path ?>" />
        </a>
        <? endif ?>

        <? // Category description full ?>
        <? if ($category->description_full && $params->show_description): ?>
        <div class="docman_description">
            <?= prepareText($category->description_full); ?>
        </div>
        <? endif; ?>

    </div>
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

    <? if( count($subcategories) || count($documents) ): ?>

        <? // Documents & pagination  ?>
        <form action="" method="get" class="-koowa-grid">
            <div class="koowa_media--gallery">
                <div class="koowa_media_wrapper koowa_media_wrapper--categories">
                    <div class="koowa_media_contents">
                        <?php // this comment below must stay ?>
                        <div class="koowa_media"><!--
                            <? foreach($subcategories as $category): ?>
                         --><div class="koowa_media__item">
                                <div class="koowa_media__item__content">
                                    <div class="koowa_header">
                                        <div class="koowa_header__item koowa_header__item--image_container">
                                            <a class="koowa_header__link" href="<?= route('layout=gallery&slug='.$category->slug) ?>">
                                                <span class="koowa_icon--folder koowa_icon--24"><i>folder</i></span>
                                            </a>
                                        </div>
                                        <div class="koowa_header__item">
                                            <div class="koowa_wrapped_content">
                                                <div class="whitespace_preserver">
                                                    <div class="overflow_container">
                                                        <a class="koowa_header__link" href="<?= route('layout=gallery&slug='.$category->slug) ?>">
                                                            <?= escape($category->title) ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!--
                            <? endforeach ?>
                     --></div>
                    </div>
                </div>
                <div class="koowa_media_wrapper koowa_media_wrapper--documents">
                    <div class="koowa_media_contents">
                        <?php // this comment below must stay ?>
                        <div class="koowa_media"><!--
                            <? foreach ($documents as $document): ?>
                         --><div class="koowa_media__item" itemscope itemtype="http://schema.org/ImageObject">
                                <div class="koowa_media__item__content">
                                    <?= import('com://site/docman.document.gallery.html', array(
                                        'document' => $document,
                                        'params' => $params
                                    )) ?>
                                </div>
                            </div><!--
                            <? endforeach ?>
                     --></div>
                    </div>
                </div>
            </div>

            <? // Pagination ?>
            <? if ($params->show_pagination !== '0' && parameters()->total): ?>
                <?= helper('paginator.pagination', array(
                    'show_limit' => (bool) $params->show_document_sort_limit
                )) ?>
            <? endif; ?>

        </form>

    <? endif; ?>
</div>