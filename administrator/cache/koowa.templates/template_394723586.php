<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php if ($params->track_downloads): ?>
    <?php echo $this->helper('behavior.download_tracker'); ?>
<?php endif; ?>

<?php foreach ($documents as $document): ?>
    <?php // Document | Import child template from document view ?>
    <?php echo $this->import('com://site/docman.document.document.html', array(
        'document' => $document,
        'params' => $params,
        'heading' => '4',
        'buttonstyle' => 'btn-default',
        'link' => 1,
        'description' => 'summary'
    )) ?>
<?php endforeach ?>