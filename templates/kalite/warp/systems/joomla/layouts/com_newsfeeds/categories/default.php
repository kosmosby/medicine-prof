<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

?>

<div id="system">
	
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1 class="title"><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif; ?>

	<?php if ($this->params->get('show_base_description') && ($this->params->get('categories_description') || $this->parent->description)) : ?>
	<div class="description">
		<?php
			if ($this->params->get('categories_description')) {
				echo JHtml::_('content.prepare', $this->params->get('categories_description'), '', 'com_newsfeeds.categories');
			} elseif ($this->parent->description) {
				echo JHtml::_('content.prepare', $this->parent->description, '', 'com_newsfeeds.categories');
			}
		?>
	</div>
	<?php endif; ?>

	<?php echo $this->loadTemplate('items'); ?>

</div>