<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('behavior.koowa'); ?>
<?= helper('bootstrap.load'); ?>


<?= helper('translator.script', array('strings' => array(
    'Create documents'
))); ?>

<ktml:content>

<ktml:module position="submenu">
    <ktml:toolbar type="menubar">
</ktml:module>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="COM_DOCMAN_SUBMENU_FILES" icon="mediamanager icon-images">
</ktml:module>

<script data-inline src="media://com_docman/js/admin/files.default.js" type="text/javascript"></script>

<textarea style="display: none" id="documents_list">
    <div>
        <div class="docman_file_modal">
            <div class="preview extension-[%=metadata.extension%]">
            [%
            var url = Files.app.createRoute({option: 'com_docman', view: 'file', format: 'html', folder: folder, name: name});
            %]
                [% if (typeof image !== 'undefined' && metadata.image) {
                    var width = metadata.image.width,
                    height = metadata.image.height,
                    ratio = 200 / (width > height ? width : height); %]
                    <img src="[%=url%]" style="
                         width: [%=Math.min(ratio*width, width)%]px;
                         height: [%=Math.min(ratio*height, height)%]px
                     " alt="[%=name%]" border="0" />
                [% } else {
                    var icon = 'default',
                        extension = name.substr(name.lastIndexOf('.')+1).toLowerCase();

                    kQuery.each(Docman.icon_map, function(key, value) {
                        if (kQuery.inArray(extension, value) !== -1) {
                            icon = key;
                        }
                    });
                %]
                    <span class="koowa_icon--[%=icon%] koowa_icon--48"><i>Document</i></span>
                [% } %]

                <div class="btn-toolbar">
                    [% if (typeof image !== 'undefined') { %]
                    <a class="btn btn-small" href="[%=url%]" target="_blank">
                        <i class="icon-eye-open"></i> <?= translate('View'); ?>
                    </a>
                    [% } else { %]
                    <a class="btn btn-small" href="[%=url%]" target="_blank" download="[%=name%]">
                        <i class="icon-download"></i> <?= translate('Download'); ?>
                    </a>
                    [% } %]
                </div>
            </div>
            <hr />
            <div class="details">
                <table class="table table-condensed parameters">
                    <tbody>
                        <tr>
                            <td class="detail-label"><?= translate('Name'); ?></td>
                            <td>
                                <div class="koowa_wrapped_content">
                                    <div class="whitespace_preserver">[%=name%]</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="detail-label"><?= translate('Size'); ?></td>
                            <td>[%=size.humanize()%]</td>
                        </tr>
                        <tr>
                            <td class="detail-label"><?= translate('Modified'); ?></td>
                            <td>[%=getModifiedDate(true)%]</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            [% if (documents.length) { %]
            <hr class="last" />
            <h3><?= translate('Attached Documents') ?></h3>
            <table class="table table-condensed table-bordered documents">
                <tbody>
                    [% for (var i = 0; i < documents.length; i++) { var document = documents[i]; %]
                    <tr>
                        <td>
                            <div class="koowa_wrapped_content">
                                <div class="whitespace_preserver">
                                    <a class="document-link" href="#" data-id="[%=document.id%]">[%=document.title%]</a>
                                    <?= translate('in')?> <em>[%=document.category_title%]</em>
                                </div>
                            </div>
                        </td>
                    </tr>
                    [% } %]
                </tbody>
            </table>
            [% } %]
        </div>
    </div>
</textarea>