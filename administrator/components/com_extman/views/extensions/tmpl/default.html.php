<?
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die; ?>

<?= helper('bootstrap.load'); ?>
<?= helper('behavior.koowa');?>

<script>
kQuery(function($) {
    $('#installer-form').on('koowa:beforeDelete', function(event) {
        var message = <?= json_encode(translate('Uninstalling this extension will remove all its data and settings. Do you want to proceed?')); ?>;

        if (!confirm(message)) {
            event.preventDefault();
        }
    });
});
</script>

<style>
.toolbar-list a.disabled {
  color: gray;
  font-weight: normal;
}
.toolbar-list .disabled span {
  background-position: bottom;
}
</style>

<ktml:module position="submenu">
    <ktml:toolbar type="menubar">
</ktml:module>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="EXTman">
</ktml:module>

<div class="-installer-grid">
<form action="" method="get" class="-koowa-grid" id="installer-form">
    <table class="table table-striped table-hover">
    	<thead>
    		<tr>
    			<th class="title" width="20px"></th>
    			<th class="title" nowrap="nowrap">
    			    <?= translate('Currently Installed') ?>
    			</th>
    			<th class="title" width="10%" align="center">
    			    <?= translate('Version') ?>
    			</th>
    		</tr>
    	</thead>
    	<tbody>
    	<? foreach($extensions as $extension): ?>
    		<tr>
    			<td align="center">
                    <?= helper('grid.radio', array('entity'=> $extension)); ?>
    			</td>
    			<td>
    				<?= translateComponentName($extension->name); ?>
    			</td>
    			<td align="center">
    			    <?= $extension->version ?>
    			</td>
    		</tr>
    	<? endforeach ?>
    	</tbody>
    </table>
    </form>
</div>