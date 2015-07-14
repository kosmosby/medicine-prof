<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2012 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load'); ?>
<?php echo $this->helper('behavior.koowa');?>


<?php // RSS feed ?>
<link href="<?php echo $this->route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />


<?php if ($params->track_downloads): ?>
    <?php echo $this->helper('behavior.download_tracker'); ?>
<?php endif; ?>

<div class="docman_table_layout docman_table_layout--default">

    <?php // Page heading ?>
    <?php if ($params->get('show_page_heading')): ?>
        <h1 class="docman_page_heading">
            <?php echo $this->escape($params->get('page_heading')); ?>
        </h1>
    <?php endif; ?>

    <?php // Toolbar ?>
    <ktml:toolbar type="actionbar" title="false">

    <?php // Category ?>
    <?php if (($params->show_icon && $category->icon)
    || ($params->show_category_title)
    || ($params->show_image && $category->image)
    || ($category->description_full && $params->show_description)
    ): ?>
    <div class="docman_category">

        <?php // Header ?>
        <h3 class="koowa_header">
            <?php // Header image ?>
            <?php if ($params->show_icon && $category->icon): ?>
                <span class="koowa_header__item koowa_header__item--image_container">
                    <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $category->icon, 'class' => ' koowa_icon--24')) ?>
                </span>
            <?php endif ?>

            <?php // Header title ?>
            <?php if ($params->show_category_title): ?>
                <span class="koowa_header__item">
                    <span class="koowa_wrapped_content">
                        <span class="whitespace_preserver">
                            <?php echo $this->escape($category->title); ?>
                        </span>
                    </span>
                </span>
            <?php endif; ?>
        </h3>

        <?php // Category image ?>
        <?php if ($params->show_image && $category->image): ?>
            <?php echo $this->helper('behavior.thumbnail_modal'); ?>
            <a class="docman_thumbnail thumbnail" href="<?php echo $category->image_path ?>">
                <img src="<?php echo $category->image_path ?>" />
            </a>
        <?php endif ?>

        <?php // Category description full ?>
        <?php if ($category->description_full && $params->show_description): ?>
            <div class="docman_description">
                <?php echo $this->prepareText($category->description_full); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php // Tables ?>
    <form action="" method="get" class="-koowa-grid koowa_table_list">

        <?php // Category table ?>
        <?php if ($params->show_subcategories && count($subcategories)): ?>

            <?php // Category header ?>
            <?php if ($category->id && $params->show_categories_header): ?>
            <div class="docman_block docman_block--top_margin">
                <h4 class="koowa_header"><?php echo $this->translate('Categories') ?></h4>
            </div>
            <?php endif; ?>

            <?php // Table ?>
            <table class="table table-striped koowa_table koowa_table--categories">
                <tbody>
                    <?php foreach ($subcategories as $subcategory): ?>
                    <tr>
                        <td colspan="2">
                            <span class="koowa_header">
                                <?php if ($params->show_icon && $subcategory->icon): ?>
                                <span class="koowa_header__item koowa_header__item--image_container">
                                    <a class="iconImage" href="<?php echo $this->helper('route.category', array('entity' => $subcategory)) ?>">
                                        <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $subcategory->icon)) ?>
                                    </a>
                                </span>
                                <?php endif ?>

                                <span class="koowa_header__item">
                                    <span class="koowa_wrapped_content">
                                        <span class="whitespace_preserver">
                                            <a href="<?php echo $this->helper('route.category', array('entity' => $subcategory)) ?>">
                                                <?php echo $this->escape($subcategory->title) ?>
                                            </a>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>


        <?php // Documents table | Import child template from documents view ?>
        <?php if (count($documents)): ?>
            <?php echo $this->import('com://site/docman.documents.table.html') ?>
        <?php endif; ?>

        <?php // Pagination ?>
        <?php if ($this->parameters()->total): ?>
            <?php echo $this->helper('paginator.pagination', array(
                'show_limit' => (bool) $params->show_document_sort_limit
            )) ?>
        <?php endif; ?>

    </form>
</div>
