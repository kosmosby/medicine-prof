<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php // Categories ?>
<div class="docman_categories">
    <?php foreach ($categories as $category): ?>

    <?php // Category ?>
    <div class="docman_category docman_category--style">

        <?php // Header ?>
        <h4 class="koowa_header">

            <?php // Header image ?>
            <?php if ($params->show_icon && $category->icon): ?>
                <span class="koowa_header__item koowa_header__item--image_container">
                    <?php // Link ?>
                    <a class="koowa_header__link" href="<?php echo $this->helper('route.category', array('entity' => $category)) ?>">
                        <?php echo $this->import('com://site/docman.document.icon.html', array('icon' => $category->icon, 'class' => ' koowa_icon--24')) ?>
                    </a>
                </span>
            <?php endif ?>

            <?php // Header title ?>
            <span class="koowa_header__item">
                <span class="koowa_wrapped_content">
                    <span class="whitespace_preserver">
                        <a class="koowa_header__link" href="<?php echo $this->helper('route.category', array('entity' => $category)) ?>">
                            <?php echo $this->escape($category->title) ?>
                        </a>
                    </span>
                </span>
            </span>
        </h4>

        <?php if ($params->show_image && $category->image): ?>
            <?php echo $this->helper('behavior.thumbnail_modal'); ?>
            <a class="docman_thumbnail thumbnail" href="<?php echo $category->image_path ?>">
                <img src="<?php echo $category->image_path ?>" />
            </a>
        <?php endif ?>

        <?php // Category description summary ?>
        <?php if ($params->show_description && $category->description_summary): ?>
        <div class="docman_description">
            <?php echo $this->prepareText($category->description_summary); ?>
        </div>
        <?php endif ?>

	</div>
    <?php endforeach; ?>
</div>