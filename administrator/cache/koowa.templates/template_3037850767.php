<?php /**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/nooku/nooku-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' ); ?>

<?php echo $this->import('com:files.files.uploader_scripts.html') ?>

<div class="koowa" style="visibility: hidden">
    <div id="files-upload" style="clear: both" class="uploader-files-empty well">
        <div style="text-align: center;">
            <h3 style=" float: none">
                <?php echo $this->translate('Upload files to {folder}', array(
                    'folder' => '<span id="upload-files-to"></span>'
                )) ?>
            </h3>
        </div>
        <div id="files-upload-controls" class="clearfix">
            <ul class="upload-buttons">
                <li><?php echo $this->translate('Upload from:') ?></li>
                <li><a class="upload-form-toggle target-computer active" href="#computer"><?php echo $this->translate('Computer'); ?></a></li>
                <li><a class="upload-form-toggle target-web" href="#web"><?php echo $this->translate('Web'); ?></a></li>
                <li id="upload-max">
                    <?php echo $this->translate('Each file should be smaller than {size}', array(
                        'size' => '<span id="upload-max-size"></span>'
                    )); ?>
                </li>
            </ul>
        </div>
        <div id="files-uploader-computer" class="upload-form">

            <div style="clear: both"></div>
            <div class="dropzone">
                <h2><?php echo $this->translate('Drag files here') ?></h2>
            </div>
            <h3 class="nodropzone"><?php echo $this->translate('Or select a file to upload:') ?></h3>
            <div id="files-upload-multi"></div>

        </div>
        <div id="files-uploader-web" class="upload-form" style="display: none">
            <form action="" method="post" name="remoteForm" id="remoteForm" >
                <div class="remote-wrap">
                    <input type="text" placeholder="<?php echo $this->translate('Remote Link') ?>" title="<?php echo $this->translate('Remote Link') ?>" id="remote-url" name="file" size="50" />
                    <input type="text" placeholder="<?php echo $this->translate('File name') ?>" id="remote-name" name="name" />
                </div>
                <input type="submit" class="remote-submit btn" disabled value="<?php echo $this->translate('Transfer File'); ?>" />
                <input type="hidden" name="action" value="save" />
            </form>
        </div>
    </div>
</div>
