<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 *
 */
defined('_JEXEC') or die; ?>

<?=@helper('behavior.bootstrap', array('type' => false, 'package' => 'logman'))?>
<?=@helper('behavior.jquery')?>

<style type="text/css">
    html {
        overflow-y: hidden !important;
    }
</style>

<script src="media://com_logman/js/logman.js"/>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var Export = new Logman.Export({url: '<?=$export_url?>'});
        Export.bind('exportComplete', function(e, data) {
            if (data.exported) {
                var msg = '<?=@text("EXPORT_DOWNLOAD")?>';
                setTimeout(function() {
                    window.location = "<?=JRoute::_('index.php?option=com_logman&view=activities&format=file&export=1', false)?>";
                }, 3000);
            } else {
                var msg = '<?=@text("EXPORT_EMPTY")?>';
            }
            $('#progress-bar').parent().removeClass('active');
            $('#message-container').fadeOut('slow', function() {
                $(this).html(msg).fadeIn('slow');
            });
        });
        Export.bind('exportUpdate', function(e, data) {
            $('#progress-bar').css('width', data.completed + '%');
        });
        $('#export-btn').one('click', function() {
            $(this).attr('disabled', 'disabled');
            Export.start();
        });
    });
</script>

<form id="logman-export">
    <h4><?=@text('Export to CSV')?></h4>
    <p id="message-container"><?=@text('EXPORT_INIT')?></p>
    <div class="progress">
        <div class="progress progress-striped active">
            <div class="bar" style="width: 0%" id="progress-bar"></div>
        </div>
    </div>
    <div class="form-actions">
        <a href="#" class="btn btn-primary" id="export-btn"><?=@text('Export')?></a>
    </div>
</form>