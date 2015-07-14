<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die;
// str_replace helps convert the paths before the template filter transform media:// to full path
$options = str_replace('\/', '/', $config->options->toString());
?>
<?= helper('behavior.modal'); ?>

<ktml:script src="media://com_docman/js/modal.js" />
<script>
kQuery(function(){
    new Docman.Modal.Thumbnail(<?= $options ?>);
});
</script>

<ul class="thumbnail-picker">
    <li class="thumbnail-controls">
        <div class="btn-group">
            <? if($config->allow_automatic): ?>
            <button type="button" class="btn thumbnail-automatic"><?= translate('Generate automatically'); ?></button>
            <? endif ?>
            <a class="koowa-modal mfp-iframe btn"
               data-koowa-modal="<?= htmlentities(json_encode(array('mainClass' => 'koowa_dialog_modal'))); ?>"
               href="<?= route('option=com_docman&view=files&layout=select&tmpl=koowa&container=docman-images&types[]=image&callback=Docman.Modal.request_map.select_image'); ?>"
            >
                <?= translate('Custom'); ?>

                <input name="image" id="image" value="<?= escape($config->value); ?>" type="hidden" disabled="disabled">
            </a>
            <button type="button" class="btn thumbnail-none"><?= translate('None'); ?></button>
        </div>
    </li>
    <li class="thumbnail-info">
        <p class="alert alert-error automatic-unsupported-format">
        <?= translate('Automatically generated thumbnails are only supported on image files.'); ?>
        </p>

        <p class="alert alert-error automatic-unsupported-location">
        <?= translate('Automatically generated thumbnails are only supported on local files.'); ?>
        </p>
    </li>
    <li class="thumbnail-preview">
        <div class="thumbnail">
            <span class="thumbnail-image"></span>
            <button class="btn btn-block thumbnail-change" type="button"><?= translate('Change'); ?></button>
        </div>
    </li>
</ul>