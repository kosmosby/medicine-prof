<?php /**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/nooku/nooku-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' );

JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);
?>

<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.local_dates'); ?>
<?php echo $this->helper('behavior.keepalive'); ?>
<?php echo $this->helper('behavior.tooltip'); ?>
<?php echo $this->helper('behavior.modal'); ?>
<?php echo $this->helper('bootstrap.load', array('wrapper' => false)); ?>
<?php echo $this->helper('behavior.tree'); ?>

<?php echo $this->helper('translator.script', array('strings' => array(
    'B', 'KB', 'MB', 'GB', 'TB', 'PB',
    'You are deleting {item}. Are you sure?',
    'You are deleting {items}. Are you sure?',
    '{count} files and folders',
    '{count} folders',
    '{count} files',
    'All Files',
    'An error occurred during request',
    'An error occurred with status code: {code}',
    'An error occurred: {error}',
    'Unknown error'
))); ?>

<ktml:script src="media://koowa/com_files/js/history/history.js" />
<?php if (JBrowser::getInstance()->getBrowser() === 'msie'): ?>
<ktml:script src="media://koowa/com_files/js/history/history.html4.js" />
<?php endif; ?>

<ktml:script src="media://koowa/com_files/js/ejs/ejs.js" />

<?php /*
For debugging:
<ktml:script src="media://koowa/com_files/js/spin.min.js" />

<ktml:script src="media://koowa/com_files/js/files.utilities.js" />
<ktml:script src="media://koowa/com_files/js/files.state.js" />
<ktml:script src="media://koowa/com_files/js/files.template.js" />
<ktml:script src="media://koowa/com_files/js/files.grid.js" />
<ktml:script src="media://koowa/com_files/js/files.tree.js" />
<ktml:script src="media://koowa/com_files/js/files.row.js" />
<ktml:script src="media://koowa/com_files/js/files.paginator.js" />
<ktml:script src="media://koowa/com_files/js/files.pathway.js" />

<ktml:script src="media://koowa/com_files/js/files.app.js" />
<ktml:script src="media://koowa/com_files/js/files.uploader.js" />
 */ ?>

<ktml:script src="media://koowa/com_files/js/files.min.js" />

<!--[if lte IE 9]>
<script data-inline src="media://koowa/com_files/js/jquery.placeholder.js" type="text/javascript"></script>
<script data-inline type="text/javascript">
kQuery(function($) {
    $('input, textarea').placeholder();
});
</script>
<![endif]-->

<?php echo $this->helper('icon.icon_map'); ?>
