<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load', array('wrapper' => false)); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>
<?= helper('behavior.icon_map'); ?>
<?= helper('translator.script', array('strings' => array(
    'Your link should either start with http:// or another protocol',
    'Invalid remote link. This link type is not supported by your server.',
    'Update',
    'Upload'
))); ?>

<ktml:script src="media://com_docman/js/document.js" />

<? // Prepend the upload_folder parameter to the path when it changes ?>
<? if ($menu->params->get('upload_folder')): ?>
<script>
    docmanUploadFolder = <?= json_encode($menu->params->get('upload_folder')); ?>;

    kQuery(function($) {
        $('#storage_path_file').on('change', function(e) {
            this.value = '<?= trim($menu->params->get('upload_folder'), '/') ?>'+'/'+this.value;
        });
    });
</script>
<? endif; ?>


