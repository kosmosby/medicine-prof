<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('translator.script', array('strings' => array(
    'Your link should either start with http:// or another protocol',
    'Invalid remote link. This link type is not supported by your server.',
    'Update',
    'Upload'
))); ?>

<? // File selection field for document forms
$local   = translate('Local');
$remote  = translate('Remote');
$current = $document->storage_type === 'remote' ? $remote : $local;
$local_value  = $document->storage_type === 'file' ? $document->storage_path : '';
$remote_value = $document->storage_type === 'remote' ? $document->storage_path : '';
?>

<div class="docman_grid">
    <div class="docman_grid__item one-whole">
        <div class="control-group">
            <label class="control-label"><?= translate('File settings'); ?></label>
            <div class="controls" id="storage-path-container">
                <div class="input-group">
                    <span class="input-group-btn">
                        <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
                            <span data-type="<?= $document->storage_type ? $document->storage_type : 'file'; ?>"
                                  class="current-storage-type">
                            <?= $current; ?></span><span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="#" data-type="file"><?= $local; ?></a>
                                <a href="#" data-type="remote"><?= $remote; ?></a>
                            </li>
                        </ul>
                    </span>

                    <div class="input-group-form-control-field input-file" style="display: none;">
                        <div class="input-group">
                            <?
                            $folder   = dirname($local_value) !== '.' ? dirname($local_value) : '';
                            $file     = ltrim(basename(' '.strtr($local_value, array('/' => '/ '))));
                            $location = 'folder='.$folder.'&file='.$file;
                            ?>
                            <?= helper('modal.select', array(
                                'name'  => 'storage_path_file',
                                'id' => 'storage_path_file',
                                'value' => $local_value,
                                'link'  => route('option=com_docman&view=files&layout=select&tmpl=koowa&'.$location),
                                'link_text' => $document->isNew() ? translate('Upload') : translate('Update'),
                                'callback' => 'docmanSelectFile',
                                'attribs' => array(
                                    'data-rule-storage' => '',
                                    'class' => 'input-group-form-control input-block-level',
                                    'data-type' => 'file'
                                ),
                                'button_attribs' => array(
                                    'data-koowa-modal' => htmlentities(json_encode(array('mainClass' => 'koowa_dialog_modal'))),
                                )
                            ))?>
                        </div>
                    </div>

                    <div class="input-group-form-control-field input-remote" style="display: none;">
                        <div class="input-group">
                            <input data-rule-streamwrapper
                                   data-rule-storage
                                   data-rule-scheme
                                   class="title input-block-level input-group-form-control"
                                   data-type="remote"
                                   id="storage_path_remote"
                                   type="text"
                                   size="25"
                                   maxlength="512"
                                   placeholder="http://"
                                   data-streams="<?= htmlentities(json_encode($document->getSchemes())); ?>"
                                   name="storage_path_remote"
                                   value="<?= escape($remote_value); ?>"
                                />
                        </div>
                        <p class="help-block"><?= translate('Enter the remote URL in the field above') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>