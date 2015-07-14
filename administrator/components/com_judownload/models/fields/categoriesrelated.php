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

class JFormFieldCategoriesRelated extends JFormField
{
	protected $type = 'CategoriesRelated';

	protected function getInput()
	{
		$document = JFactory::getDocument();
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('cat_id_related');
		$query->from('#__judownload_categories_relations');
		$query->where('cat_id = ' . (int) $this->form->getValue('id'));
		$query->order("ordering ASC");
		$db->setQuery($query);
		$relcategories = $db->loadColumn();

		$script = '
		jQuery(document).ready(function($){
			$(".category_list").dragsort({ dragSelector: "li", dragEnd: saveOrder, placeHolderTemplate: "<li class=\'placeHolder\'></li>", dragSelectorExclude: "input, textarea, span, a.removeitem"});

            function saveOrder() {
                var data = $("#gallery li").map(function() { return $(this).data("itemid"); }).get();
            };

			$(".category_list").on("click","a.removeitem", function(event){
				event.preventDefault();
				$(this).closest("li").remove();
			});

			$(".browse_cat").bind("dblclick",function(){
				var id = $(this).find("option:selected").val();
				$.ajax({
			  		type: "POST",
			  		url:"index.php?option=com_judownload&task=categories.loadcategories",
			  		data: {id : id, type : "category"}
				}).done(function(data){
					data = $.parseJSON(data);
					$(".browse_cat").html(data.html);
					$(".active_pathway").html(data.path);
				});
			});

			$(".change_categories").click(function(){
				$(".category_selection").slideToggle(400);
			});

			$("#add_related_categories").click(function(event){
    			event.preventDefault();
    			var has_element = [];
				var current_cat = ' . JFactory::getApplication()->input->get('id', 0) . ';
				$.each( $("option:selected",".browse_cat"), function(key, value ) {
					if($(this).data("noselect")){
						return;
					}

					var selectedValue = $(this).val();
					var selectedText = $(this).text();

  					if(current_cat == selectedValue){
						alert("' . JText::_('COM_JUDOWNLOAD_CAN_NOT_ADD_CATEGORY_ITSELF_AS_RELATED_CATEGORY') . '");
						return;
					}
  					if($(".category_list > li[id=\'category-"+selectedValue+"\']").length <= 0){
  						var path = $(".active_pathway").text() + " > " + selectedText;
  						$("<li id=\"category-"+selectedValue+"\"><a class=\"drag-icon\"></a><span>"+path+"</span><input type=\"hidden\" name=\"relcategories[]\" value=\""+selectedValue+"\" /> <a href=\"#\" class=\"removeitem\" ><i class=\"icon-minus\"></i> ' . JText::_('COM_JUDOWNLOAD_REMOVE') . '</a></li>").appendTo(".category_list");
   					}else{
  						has_element.push(selectedText);
  					}
				});

                if(has_element.length == 1){
                    alert("' . JText::_('COM_JUDOWNLOAD_CATEGORY_X_ALREADY_EXISTED') . '".replace("%s", has_element[0]));
				}else if(has_element.length > 1){
				    alert("' . JText::_('COM_JUDOWNLOAD_CATEGORIES_X_ALREADY_EXISTED') . '".replace("%s", has_element.join(", ")));
				}
				return false;
			});
		});';

		$document->addScriptDeclaration($script);

		$html = "";
		$html .= "<div class=\"categories\">";
		$html .= "<ul class=\"category_list nav clearfix\">";
		if ($relcategories)
		{
			foreach ($relcategories AS $key => $relcategory)
			{
				$path = JUDownloadHelper::generateCategoryPath($relcategory);
				$html .= '<li id="category-' . $relcategory . '">';
				$html .= '<a class="drag-icon"></a>';
				$html .= '<span>' . $path . '</span>';
				$html .= '<input type="hidden" name="relcategories[]" value="' . $relcategory . '" />';
				$html .= '<a class="removeitem" href="#"><i class="icon-minus"></i> ' . JText::_('COM_JUDOWNLOAD_REMOVE') . '</a>';
				$html .= "</li>";
			}
		}
		$html .= '</ul>';

		$catId       = $this->form->getValue('id', 0);
		$catParentId = !$catId ? JUDownloadFrontHelperCategory::getRootCategory()->id : JUDownloadHelper::getCategoryByID($catId)->parent_id;

		
		$query = $db->getQuery(true);
		$query->SELECT('title, id, published, parent_id');
		$query->FROM('#__judownload_categories');
		$query->WHERE('parent_id = ' . $catParentId);
		$query->ORDER('lft');
		$db->setQuery($query);
		$categoryList = $db->loadObjectList();
		foreach ($categoryList AS $key => $cat)
		{
			if ($cat->published != 1)
			{
				$cat->title = "[" . $cat->title . "]";
			}
		}

		if ($catParentId != 0)
		{
			$catParent = JUDownloadHelper::getCategoryByID($catParentId);
			array_unshift($categoryList, JHtml::_('select.option', $catParent->parent_id, JText::_('COM_JUDOWNLOAD_BACK_TO_PARENT_CATEGORY'), 'id', 'title'));
		}

		$html .= "<a href='#' class='change_categories' onclick='return false;'><i class=\"icon-folder\"></i> " . JText::_('COM_JUDOWNLOAD_ADD_RELATED_CATEGORIES') . "</a>";
		$html .= '<div class="category_selection" style="display: none;">';
		$html .= '<div class="active_pathway">' . JUDownloadHelper::generateCategoryPath($catParentId, 'li') . '</div>';
		$html .= JHtml::_('select.genericlist', $categoryList, 'browse_cat', 'class="inputbox browse_cat" multiple="multiple" size="8"', 'id', 'title', '', "");
		$html .= '<div class="cat_action clearfix">';
		$html .= "<button id='add_related_categories' class='btn btn-mini btn-primary'>" . JText::_('COM_JUDOWNLOAD_ADD_RELATED_CATEGORIES') . "</button>";
		$html .= "<input type='hidden' name='cat_id_related' id='cat_id_related' value='" . $this->value . "' />";
		$html .= "</div>";
		$html .= "</div>";
		$html .= '</div>';

		return $html;
	}
}

?>