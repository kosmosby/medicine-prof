<?php
/**
 * @package     Nooku_Components
 * @subpackage  Files
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */
defined('KOOWA') or die( 'Restricted access' ); ?>

<?= helper('com://admin/docman.behavior.icon_map'); ?>
<textarea style="display: none" id="details_container">
<div class="manager">
    <div class="docman_table_container">
        <table class="table table-striped footable"  style="clear: both;">
            <thead>
                <tr>
                    <th width="1">
                        <div class="btn-group">
                            <a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#" style="
                            padding-top: 5px;
                            padding-bottom: 6px;
                            border-bottom: none;
                            padding-left: 10px;
                            border-top: none;
                            border-left: none;
                            border-radius: 0;
                            ">
                                <input type="checkbox" class="-check-all" id="select-check-all" />
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="#" id="select-all"><?= translate('All'); ?></a></li>
                                <li><a href="#" id="select-none"><?= translate('None'); ?></a></li>
                                <li><a href="#" id="select-orphans"><?= translate('Orphans'); ?></a></li>
                            </ul>
                        </div>
                    </th>
                    <th width="1" data-class="expand" data-toggle="true" width="16"></th>
                    <th width="1" data-name="name" class="docman_table__title_field files__sortable">
                        <?= translate('Name'); ?>
                        <span class="files__sortable--indicator koowa_icon--sort koowa_icon--12"></span>
                    </th>
                    <th width="1" data-hide="phone"><?= translate('Size'); ?></th>
                    <th width="1" data-hide="phone,phablet,tablet" data-name="modified_on" class="files__sortable">
                        <?= translate('Last Modified'); ?>
                        <span class="files__sortable--indicator koowa_icon--sort koowa_icon--12"></span>
                    </th>
                    <th width="1" data-hide="phone,phablet" class="file-count" width="1"><?= translate('Document count'); ?></th>
                    <th width="1" data-hide="phone,phablet" width="1" style="text-align: center;"><i class="icon-download"></i></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
</textarea>

<textarea style="display: none" id="details_folder">
	<tr class="files-node files-folder">
		<td width="1">
			<input type="checkbox" class="files-select" value="" />
		</td>
		<td width="1">
            <span class="koowa_icon--folder"><i>[%=name%]</i></span>
		</td>
		<td class="docman_table__title_field" colspan="6">
            <div class="koowa_wrapped_content">
                <div class="whitespace_preserver">
                    <a href="#" class="navigate">[%=name%]</a>
                </div>
		    </div>
	    </td>
	</tr>
</textarea>

<textarea style="display: none" id="details_file">
	<tr class="files-node files-file">
		<td width="1">
			<input type="checkbox" class="files-select" value="" />
		</td>
        [%
        var icon = 'default',
            extension = name.substr(name.lastIndexOf('.')+1).toLowerCase();

        kQuery.each(Docman.icon_map, function(key, value) {
            if (kQuery.inArray(extension, value) !== -1) {
                icon = key;
            }
        });
        %]
		<td width="1">
            <span class="koowa_icon--[%=icon%]"><i>[%=icon%]</i></span>
		</td>
		<td class="docman_table__title_field">
            <div class="koowa_wrapped_content">
                <div class="whitespace_preserver">
                    <a href="#" class="navigate">[%=name%]</a>
                </div>
		    </div>
		</td>
		<td width="1" align="center">
			[%=size.humanize()%]
		</td>
		<td width="1" align="center">
			[%=getModifiedDate(true)%]
		</td>
		<td width="1" class="file-count">
			-
		</td>
        <td width="1" align="right">
            <a class="btn btn-mini" href="[%=download_link%]" target="_blank" download="[%=name%]"><i class="icon-download"></i></a>
        </td>
	</tr>
</textarea>

<textarea style="display: none" id="details_image">
	<tr class="files-node files-image">
		<td width="1">
			<input type="checkbox" class="files-select" value="" />
		</td>
		<td width="1">
            [% if (typeof thumbnail === 'string') { %]
                <img src="[%= client_cache || Files.blank_image %]" alt="[%=name%]" border="0" class="image-thumbnail [%= client_cache ? 'loaded' : '' %]" height="24px" />
            [% } else { %]
                <span class="koowa_icon--image"><i>[%=name%]</i></span>
            [% } %]
		</td>
		<td class="docman_table__title_field">
            <div class="koowa_wrapped_content">
                <div class="whitespace_preserver">
                    <a href="#" class="navigate">[%=name%]</a>
                </div>
		    </div>
		</td>
		<td width="1" align="center">
            [%=size.humanize()%]
            [% if (metadata.image) { %]
            ([%=metadata.image.width%] x [%=metadata.image.height%])
            [% } %]
		</td>
		<td width="1" align="center">
			[%=getModifiedDate(true)%]
		</td>
		<td width="1" class="file-count" width="1">
			-
		</td>
        <td width="1" align="right">
            <a class="btn btn-mini" href="[%=download_link%]" target="_blank" download="[%=name%]"><i class="icon-download"></i></a>
        </td>
	</tr>
</textarea>