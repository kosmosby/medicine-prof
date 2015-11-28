<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load'); ?>
<?php echo $this->helper('behavior.thumbnail_modal'); ?>

<?php if ($params->track_downloads): ?>
    <?php echo $this->helper('behavior.download_tracker'); ?>
<?php endif; ?>

<div class="docman_document_layout">

    <?php // Page Heading ?>
    <?php if ($params->get('show_page_heading')): ?>
    <h1 class="docman_page_heading">
        <?php echo $this->escape($params->get('page_heading')); ?>
    </h1>
    <?php endif; ?>

    <?php // Document | Import partial template from document view ?>
    <?php echo $this->import('com://site/docman.document.document.html', array(
        'document' => $document,
        'params'   => $params,
        'heading'  => '1',
        'buttonstyle' => 'btn-primary',
        'link'     => 1
    )) ?>

</div>