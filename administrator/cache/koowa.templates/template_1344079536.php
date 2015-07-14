<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load', array('wrapper' => false)); ?>
<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.keepalive'); ?>
<?php echo $this->helper('behavior.validator'); ?>
<?php echo $this->helper('behavior.icon_map'); ?>
<?php echo $this->helper('translator.script', array('strings' => array(
    'Your link should either start with http:// or another protocol',
    'Invalid remote link. This link type is not supported by your server.',
    'Update',
    'Upload'
))); ?>

<ktml:script src="media://com_docman/js/document.js" />

<?php // Prepend the upload_folder parameter to the path when it changes ?>
<?php if ($menu->params->get('upload_folder')): ?>
<script>
    docmanUploadFolder = <?php echo json_encode($menu->params->get('upload_folder')); ?>;

    kQuery(function($) {
        $('#storage_path_file').on('change', function(e) {
            this.value = '<?php echo trim($menu->params->get('upload_folder'), '/') ?>'+'/'+this.value;
        });
    });
</script>
<?php endif; ?>


