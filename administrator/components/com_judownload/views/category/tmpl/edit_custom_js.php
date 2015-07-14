<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if(JUDownloadHelper::isJoomla3x()){
	$jsJoomla3x = 1;
}else{
	$jsJoomla3x = 0;
}
?>

<script type="text/javascript">

    jQuery(document).ready(function($){

	var taskSubmit = null;
	var cat_id = <?php echo $this->item->id ? $this->item->id : 0; ?>;
	var old_parent_id = <?php echo $this->item->parent_id; ?>;
	var isJoomla3x = <?php echo $jsJoomla3x; ?>;

	Joomla.submitbutton = function (task) {
		if (task == 'category.cancel') {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('adminForm'));
		} else if(document.formvalidator.isValid(document.id('adminForm'))){
			<?php echo $this->form->getField('description')->save(); ?>
			if(old_parent_id != 0){
				var new_parent_id = jQuery('#jform_parent_id').val();
				if(old_parent_id != new_parent_id){
					checkInheritedDataWhenChangeParentCat(task);
					taskSubmit = task;
				}else{
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}else{
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		}
	};

	function checkInheritedDataWhenChangeParentCat(task){
		var objectPost = {};
		objectPost.id = cat_id;
		objectPost.parent_id = jQuery('#jform_parent_id').val();
		objectPost.selected_fieldgroup = jQuery('#jform_selected_fieldgroup').val();
		objectPost.selected_criteriagroup = jQuery('#jform_selected_criteriagroup').val();
		objectPost.style_id = jQuery('#jform_style_id').val();

		jQuery.ajax({
			type: "POST",
			url : "index.php?option=com_judownload&task=category.checkInheritedDataWhenChangeParentCat",
			data: objectPost
		}).done(function (data) {
			var data = jQuery.parseJSON(data);
			if (!data) return false;

			if (data) {
				if(data.status == 1){
					if(data.fieldGroupChanged == 1){
						jQuery('#warningFieldGroup').show();
						jQuery('#fieldGroupMessage').html(data.fieldGroupMessage);
					}else{
						jQuery('#warningFieldGroup').hide();
					}

					if(data.criteriaGroupChanged == 1){
						jQuery('#warningCriteriaGroup').show();
						jQuery('#criteriaGroupMessage').html(data.criteriaGroupMessage);
					}else{
						jQuery('#warningCriteriaGroup').hide();
					}

					if(data.templateStyleChanged == 1){
						jQuery('#warningTemplateStyle').show();
						jQuery('#templateStyleMessage').html(data.templateStyleMessage);
					}else{
						jQuery('#warningTemplateStyle').hide();
					}
					jQuery('#confirmModal').modal();
				}else{
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		});
	}

		$('#acceptConfirm').on('click',function(e){
			e.preventDefault();
			var task = taskSubmit;
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('adminForm'));
		});

		$('#jform_parent_id').on('change', function (e) {
			e.preventDefault();
			var objectPost = {};
			objectPost.id = cat_id;
			objectPost.parent_id = $('#jform_parent_id').val();

			$.ajax({
				type: "POST",
				url: "index.php?option=com_judownload&task=category.updateInheritField",
				data: objectPost
			}).done(function (data) {
				var data = $.parseJSON(data);
				if (data) {
					$('#jform_selected_fieldgroup').find("option[value='-1']").html(data.message_fieldgroup);
					$('#jform_selected_criteriagroup').find("option[value='-1']").html(data.message_criteriagroup);
					$('#jform_style_id').find("option[value='-1']").html(data.message_style);

					if(isJoomla3x == 1)
					{
						$("#jform_selected_fieldgroup").trigger("liszt:updated");
						$("#jform_selected_criteriagroup").trigger("liszt:updated");
						$("#jform_style_id").trigger("liszt:updated");
					}
				}
			});
		});

		$('#jform_style_id').on('change',function(e){
			e.preventDefault();
			var objectPost = {};
			objectPost.id = cat_id;
			objectPost.parent_id = $('#jform_parent_id').val();
			objectPost.style_id = $('#jform_style_id').val();

			$.ajax({
				type: "POST",
				url : "index.php?option=com_judownload&task=category.checkTemplateChange",
				data: objectPost
			}).done(function (data) {
				var data = $.parseJSON(data);
				if (data) {
					if(data.templateStyleChanged == 1){
						alert(data.templateStyleMessage);
					}
				}
			});
		});

		$('#jform_selected_fieldgroup').on('change',function(e){
			e.preventDefault();
			var objectPost = {};
			objectPost.id = cat_id;
			objectPost.parent_id = $('#jform_parent_id').val();
			objectPost.selected_fieldgroup = $('#jform_selected_fieldgroup').val();

			$.ajax({
				type: "POST",
				url : "index.php?option=com_judownload&task=category.checkFieldGroupChange",
				data: objectPost
			}).done(function (data) {
				var data = $.parseJSON(data);
				if (data) {
					if(data.fieldGroupChanged == 1){
						alert(data.fieldGroupMessage);
					}
				}
			});
		});

		$('#jform_selected_criteriagroup').on('change',function(e){
			e.preventDefault();
			var objectPost = {};
			objectPost.id = cat_id;
			objectPost.parent_id = $('#jform_parent_id').val();
			objectPost.selected_criteriagroup = $('#jform_selected_criteriagroup').val();

			$.ajax({
				type: "POST",
				url : "index.php?option=com_judownload&task=category.checkCriteriaGroupChange",
				data: objectPost
			}).done(function (data) {
				var data = $.parseJSON(data);
				if (data) {
					if(data.criteriaGroupChanged == 1){
						alert(data.criteriaGroupMessage);
					}
				}
			});
		});
	});
</script>