<?php
/**
 * @package     Nooku_Components
 * @subpackage  Files
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */
defined('KOOWA') or die( 'Restricted access' ); ?>

<?= import('com:files.files.scripts.html');?>
<ktml:script src="media://com_docman/js/footable.js"/>

<script>
Files.sitebase = '<?= $sitebase; ?>';
Files.token = '<?= $token; ?>';

window.addEvent('domready', function() {

    <? if (version_compare(JVERSION, '3.0', 'ge')): ?>
    //Quick j3 sidebar layout fix
    kQuery('#submenu').prependTo('#files-sidebar').addClass('docman-main-nav');
    <? endif ?>

	var sidebarSetHeight = function(){kQuery(window).triggerHandler('sidebar:setHeight')},
        config = <?= json_encode(KObjectConfig::unbox(parameters()->config)); ?>,
		options = {
            cookie: {
                path: '<?= object('request')->getSiteUrl(); ?>'
            },
			state: {
				defaults: {
					limit: <?= (int) parameters()->limit; ?>,
					offset: <?= (int) parameters()->offset; ?>,
					types: <?= json_encode(KObjectConfig::unbox(parameters()->types)); ?>
				}
			},
            root_text: <?= json_encode(translate('Root folder')) ?>,
			types: <?= json_encode(KObjectConfig::unbox(parameters()->types)); ?>,
			container: <?= json_encode($container->toArray()); ?>,
            thumbnails: <?= json_encode($container ? $container->getParameters()->thumbnails : true); ?>,
            events: {afterSelect: sidebarSetHeight, afterSetContainer: sidebarSetHeight,afterSetGrid: sidebarSetHeight}
		};
	options = Object.append(options, config);

	Files.app = new Files.App(options);
    // using onAfterXyz events in the options object overwrites event handlers defined in files.app.js
    Files.app.addEvents(options.events);
});

</script>

<script>
    kQuery(function ($) {
        $('.footable tbody tr td').on('click', 'span.footable-toggle', function(event){
            event.stopPropagation();
        });
        /** Footable fix for Koowa selectables, preventing open/close to toggle select/deselect on table row */
        $('.footable').footable({
            toggleSelector: ' > tbody > tr:not(.footable-row-detail) .footable-toggle',
            breakpoints: {
                phone: 480,
                phablet: 600,
                tablet: 800
            }
        });
    });
</script>

<?= helper('com://admin/docman.behavior.sidebar', array('sidebar' => '#files-sidebar', 'target' => '#files-tree')) ?>


<div id="files-app">
    <?= import('com:files.files.templates_icons.html'); ?>
	<?= import('com://admin/docman.files.wrapper_templates_details.html'); ?>

    <div id="files-sidebar">
        <h3><?= translate('Folders'); ?></h3>
		<div id="files-tree"></div>
	</div>

	<div id="files-canvas" class="docman_admin_list_grid">
	    <div class="path" style="height: 24px;">
            <div id="files-pathway"></div>
			<div class="files-layout-controls btn-group" data-toggle="buttons-radio">
				<button class="btn files-layout-switcher" data-layout="icons" title="<?= translate('Show files as icons'); ?>">
                    <i class="icon-th icon-grid-view-2"></i>
				</button>
				<button class="btn files-layout-switcher" data-layout="details" title="<?= translate('Show files in a list'); ?>">
                    <i class="icon-list"></i>
				</button>
			</div>
            <div class="scopebar-search-container">
                <?= helper('grid.search', array(
                    'submit_on_clear' => false,
                    'placeholder' => @translate('Find by file or folder name&hellip;')
                )) ?>
            </div>
		</div>
		<div class="view">
			<div id="files-grid"></div>
		</div>
    <table class="table">
        <tfoot>
        <tr><td>
            <?= helper('paginator.pagination') ?>
        </td></tr>
        </tfoot>
    </table>

        <?= import('com:files.files.uploader.html');?>
	</div>
	<div style="clear: both"></div>
</div>

<div id="files-new-folder-modal" class="koowa mfp-hide" style="max-width: 600px; position: relative; width: auto; margin: 20px auto;">
    <form class="files-modal well">
        <div style="text-align: center;">
            <h3 style=" float: none">
                <?= translate('Create a new folder in {folder}', array(
                    'folder' => '<span class="upload-files-to"></span>'
                )) ?>
            </h3>
        </div>
        <div class="input-append">
            <input class="span5 focus" type="text" id="files-new-folder-input" placeholder="<?= translate('Enter a folder name') ?>" />
            <button id="files-new-folder-create" class="btn btn-primary" disabled><?= translate('Create'); ?></button>
        </div>
    </form>
</div>

<div id="files-move-modal" class="koowa mfp-hide" style="max-width: 600px; position: relative; width: auto; margin: 20px auto;">
    <form class="files-modal well">
        <div style="text-align: center;">
            <h3 style=" float: none">
                <?= translate('Move to') ?>
            </h3>
        </div>
        <div class="tree-container"></div>
        <button class="btn btn-primary" ><?= translate('Move'); ?></button>
    </form>
</div>

<div id="files-copy-modal" class="koowa mfp-hide" style="max-width: 600px; position: relative; width: auto; margin: 20px auto;">
    <form class="files-modal well">
        <div>
            <h3><?= translate('Copy to') ?></h3>
        </div>
        <div class="tree-container"></div>
        <div class="form-actions" style="padding-left: 0">
            <button class="btn btn-primary" ><?= translate('Copy'); ?></button>
        </div>
    </form>
</div>


