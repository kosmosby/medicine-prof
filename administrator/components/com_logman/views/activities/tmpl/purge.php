<?
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 *
 */
defined('_JEXEC') or die; ?>

<?= @helper('behavior.bootstrap', array('type' => false)) ?>
<?= @helper('behavior.jquery') ?>

<style type="text/css">
    html {
        overflow-y: hidden !important;
    }
</style>

<script>
jQuery(function($) {
	var request = function(append_url) {
		url = '?option=com_logman&view=activities';
		if (append_url) {
			url += append_url;
		}
		
		return $.ajax(url, {
    		type: 'post',
    		dataType: 'json',
    		data: {
    			'action': 'purge',
    			'_token': <?= json_encode(version_compare(JVERSION, '1.6', '<') ? JUtility::getToken() : JSession::getFormToken()) ?>
    		},
    		success: function(data, textStatus, jqXHR) {
    			alert(<?= json_encode(@text('Successfully purged')) ?>);
    		    window.parent.location.reload();
    		},
    		error: function(jqXHR, textStatus, errorThrown) {
    			alert(<?= json_encode('An error occurred during request'); ?>);
    		}
    	});
	};
	
	$('#purge-until').click(function(e) {
		e.preventDefault();

		request('&end_date='+document.id('purge_until').get('value'));
	});

	$('#purge-all').click(function(e) {
		e.preventDefault();
		if (confirm(<?=json_encode(@text('This will delete all activities on your site. Are you sure?'))?>)) {
			request();
        }
	});
});
</script>

<div id="activities" style="border: 0; background: white;">
	<form>
			<label for="purge_until"><?=@text('Purge activities before ')?></label>
    		
			<div class="controls">
				<?= @helper('behavior.calendar',
    				array(
    				    'date' => @service('koowa:date')->addDays(-90)->getDate(),
    					'name' => 'purge_until',
    					'format' => '%Y-%m-%d'
    				)); ?>
    			<br />
			</div>
    		<a href="#" class="btn btn-primary" id="purge-until"><?= @text('Purge')?></a>
	        <?= @text('or') ?>
	        <a href="#" id="purge-all"><?= @text('Purge all activities')?></a>
    </form>
</div>
