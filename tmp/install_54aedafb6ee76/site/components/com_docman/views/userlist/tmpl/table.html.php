<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2012 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.koowa');?>

<? if ($params->track_downloads): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>

<div class="docman_table_layout docman_userlist_table_layout">

    <? // Page heading ?>
    <? if ($params->get('show_page_heading')): ?>
        <h1 class="docman_page_heading">
            <?= escape($params->get('page_heading')); ?>
        </h1>
    <? endif; ?>

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar" title="false">

    <? // Category ?>
    <? if (($params->show_icon && $category->icon)
        || ($params->show_category_title)
        || ($params->show_image && $category->image)
        || ($category->description_full && $params->show_description)
    ): ?>
        <div class="docman_category">

            <? // Header ?>
            <h3 class="koowa_header">
                <? // Header image ?>
                <? if ($params->show_icon && $category->icon): ?>
                    <span class="koowa_header__item koowa_header__item--image_container">
                        <?= import('com://site/docman.document.icon.html', array('icon' => $category->icon, 'class' => ' koowa_icon--24')) ?>
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

    <? // Tables ?>
    <form action="" method="get" class="-koowa-grid koowa_table_list">

    <? // Category table ?>
    <? if ($params->show_subcategories && count($subcategories)): ?>

        <? // Category header ?>
        <? if ($category->id && $params->show_categories_header): ?>
            <div class="docman_block docman_block--top_margin">
                <h4 class="koowa_header"><?= translate('Categories') ?></h4>
            </div>
        <? endif; ?>

        <? // Table ?>
        <table class="table table-striped koowa_table koowa_table--categories">
            <tbody>
            <? foreach ($subcategories as $subcategory): ?>
                <tr>
                    <td colspan="2">
                    <span class="koowa_header">
                        <? if ($params->show_icon && $subcategory->icon): ?>
                        <span class="koowa_header__item koowa_header__item--image_container">
                            <a class="iconImage" href="<?= helper('route.category', array('entity' => $subcategory)) ?>">
                                <?= import('com://site/docman.document.icon.html', array('icon' => $subcategory->icon)) ?>
                            </a>
                        </span>
                        <? endif ?>

                        <span class="koowa_header__item">
                            <span class="koowa_wrapped_content">
                                <span class="whitespace_preserver">
                                    <a href="<?= helper('route.category', array('entity' => $subcategory)) ?>">
                                        <?= escape($subcategory->title) ?>
                                    </a>
                                </span>
                            </span>
                        </span>
                    </span>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    <? endif; ?>


    <? // Documents table | Import child template from documents view ?>
    <? if (parameters()->total): ?>
        <?= import('com://site/docman.documents.table.html') ?>

    <? // Pagination ?>
        <?= helper('paginator.pagination', array_merge(array(
            'total'      => parameters()->total,
            'show_limit' => (bool) $params->show_document_sort_limit
        ), parameters()->toArray())) ?>

    <? elseif ($category->id): ?>
        <p class="alert alert-info">
            <?= translate('You do not have any documents in this category.'); ?>
        </p>
    <? endif; ?>

    </form>
</div>
