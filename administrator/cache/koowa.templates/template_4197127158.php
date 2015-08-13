<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load'); ?>
<?php echo $this->helper('behavior.koowa');?>
<?php echo $this->helper('behavior.modal');?>


<?php // RSS feed ?>
<link href="<?php echo $this->route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />


<div class="docman_list_layout docman_list_layout--default">

    <?php // Page Heading ?>
    <?php if ($params->get('show_page_heading')): ?>
        <h1 class="docman_page_heading">
            <?php echo $this->escape($params->get('page_heading')); ?>
        </h1>
    <?php endif; ?>

    <?php // Toolbar ?>
    <ktml:toolbar type="actionbar" title="false">

    <?php // Category ?>
    <?php if (($params->show_category_title && $category->title)
          || ($params->show_image && $category->image)
          || ($category->description_full && $params->show_description)
    ): ?>
    <div class="docman_category">

        <?php // Header ?>
        <?php if ($params->show_category_title && $category->title): ?>
        <h3 class="koowa_header">
            <?php // Header image ?>
            <?php if ($params->show_icon && $category->icon): ?>
                <span class="koowa_header__item koowa_header__item--image_container">
                    <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $category->icon)) ?>
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
        <?php endif; ?>

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


    <?php // Sub categories ?>
    <?php if ($params->show_subcategories && count($subcategories)): ?>
        <?php if ($category->id && $params->show_categories_header): ?>
            <div class="docman_block docman_block--top_margin">
                <?php // Header ?>
                <h3 class="koowa_header koowa_header--bottom_margin">
                    <?php echo $this->translate('Categories') ?>
                </h3>
            </div>
        <?php endif; ?>

        <?php // Categories list ?>
        <?php echo $this->import('com://site/docman.list.categories.html', array(
            'categories' => $subcategories,
            'params' => $params,
            'config' => $config
        ))?>
    <?php endif; ?>


    <?php // Documents header & sorting ?>
    <?php if (count($documents)): ?>
        <div class="docman_block">
            <?php if ($params->show_documents_header): ?>
            <h3 class="koowa_header">
                <?php echo $this->translate('Documents')?>
            </h3>
            <?php endif; ?>
            <?php if ($params->show_document_sort_limit): ?>
                <div class="docman_sorting btn-group form-search">
                    <label for="sort-documents" class="control-label"><?php echo $this->translate('Order by') ?></label>
                    <?php echo $this->helper('paginator.sort_documents', array(
                        'sort'      => 'document_sort',
                        'direction' => 'document_direction',
                        'attribs'   => array('class' => 'input-medium', 'id' => 'sort-documents')
                    )); ?>
                </div>
            <?php endif; ?>
        </div>


        <?php // Documents & pagination  ?>
        <form action="" method="get" class="-koowa-grid">

            <?php // Document list | Import child template from documents view ?>
            <?php echo $this->import('com://site/docman.documents.list.html',array(
                'documents' => $documents,
                'params' => $params
            ))?>

            <?php // Pagination  ?>
            <?php if ($this->parameters()->total) : ?>
                <?php echo $this->helper('paginator.pagination', array(
                    'show_limit' => (bool) $params->show_document_sort_limit
                )) ?>
            <?php endif; ?>

        </form>
    <?php endif; ?>
</div>
