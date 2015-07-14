<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die;
// str_replace helps convert the paths before the template filter transform media:// to full path
$options = str_replace('\/', '/', $config->options->toString());
?>
<?php echo $this->helper('behavior.modal'); ?>

<ktml:script src="media://com_docman/js/modal.js" />
<script>
kQuery(function(){
    new Docman.Modal.Thumbnail(<?php echo $options ?>);
});
</script>

<ul class="thumbnail-picker">
    <li class="thumbnail-controls">
        <div class="btn-group">
            <?php if($config->allow_automatic): ?>
            <button type="button" class="btn thumbnail-automatic"><?php echo $this->translate('Generate automatically'); ?></button>
            <?php endif ?>
            <a class="koowa-modal mfp-iframe btn"
               data-koowa-modal="<?php echo htmlentities(json_encode(array('mainClass' => 'koowa_dialog_modal'))); ?>"
               href="<?php echo $this->route('option=com_docman&view=files&layout=select&tmpl=koowa&container=docman-images&types[]=image&callback=Docman.Modal.request_map.select_image'); ?>"
            >
                <?php echo $this->translate('Custom'); ?>

                <input name="image" id="image" value="<?php echo $this->escape($config->value); ?>" type="hidden" disabled="disabled">
            </a>
            <button type="button" class="btn thumbnail-none"><?php echo $this->translate('None'); ?></button>
        </div>
    </li>
    <li class="thumbnail-info">
        <p class="alert alert-error automatic-unsupported-format">
        <?php echo $this->translate('Automatically generated thumbnails are only supported on image files.'); ?>
        </p>

        <p class="alert alert-error automatic-unsupported-location">
        <?php echo $this->translate('Automatically generated thumbnails are only supported on local files.'); ?>
        </p>
    </li>
    <li class="thumbnail-preview">
        <div class="thumbnail">
            <span class="thumbnail-image"></span>
            <button class="btn btn-block thumbnail-change" type="button"><?php echo $this->translate('Change'); ?></button>
        </div>
    </li>
</ul>