<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load', array('class' => array('full_height'))); ?>

<ktml:content>

<ktml:script src="media://com_docman/js/admin/files.select.js" />
<script>
window.addEvent('domready', function(){
	kQuery('#insert-document').click(function(e) {
		e.preventDefault();

        <?php if (!empty($callback)): ?>
        window.parent.<?php echo $callback; ?>(Files.app.selected);
        <?php endif; ?>
	});
});
</script>

<div id="document-insert-form" style="text-align: center; display: none;">
	<button class="btn btn-primary" type="button" id="insert-document" disabled><?php echo $this->translate('Insert') ?></button>
</div>