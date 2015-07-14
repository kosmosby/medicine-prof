<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$Itemid = JRequest::getVar('Itemid');
?>
<div style="overflow: hidden; padding: 10px 0px;">
<div id="medcats_div">
    <div style="padding:5px;">
        <input class="medizd_search_cats" type="button" value="<?php echo JText::_("MEDIZD_SEARCH");?>" onclick="MedCatSearch('<?php echo JUri::base();?>');" />
    </div>
    <div style="padding:5px;">
        <a href="javascript:void(0);" onclick="javascript:MedCatUnselect();"><?php echo JText::_("MEDIZD_UNSELLECT");?></a>
    </div>
    <?php
    $db = JFactory::getDBO();
            $db->setQuery("SELECT * FROM #__comprofiler_plugin_cbmedizd_categories WHERE parent_id = 0 ORDER BY name");
            $cats = $db->loadObjectList();
            
            
            $html = '';
            for($intA = 0; $intA < count($cats); $intA ++){
                $html .= '<div id="'.$cats[$intA]->id.'_sub" class="catsmedizd"><input type="checkbox" id="chkb'.$cats[$intA]->id.'" name="chkbcats[]" value="'.$cats[$intA]->id.'"><label for="chkb'.$cats[$intA]->id.'">'.$cats[$intA]->name.'</label></div>';
                $html .= '<div id="'.$cats[$intA]->id.'_subid" class="subcatsmedizd">';
                $db->setQuery("SELECT * FROM #__comprofiler_plugin_cbmedizd_categories WHERE parent_id = {$cats[$intA]->id} ORDER BY name");
                $inner_cats = $db->loadObjectList();
                for($intB = 0; $intB < count($inner_cats); $intB ++){
                    $html .= '<div><input type="checkbox" name="chkbcats[]" id="chkb'.$inner_cats[$intB]->id.'"  value="'.$inner_cats[$intB]->id.'"><label for="chkb'.$inner_cats[$intB]->id.'">'.$inner_cats[$intB]->name.'</label></div>';
                }
                $html .= '</div>';
            }
            echo $html;
            
    ?>
</div>
<div id="gdidiv">
<table id="jqGrid" class="ui-jqgrid"></table>
    <div id="jqGridPager"></div>

    <script type="text/javascript"> 
        jQuery(document).ready(function () {
            jQuery("#jqGrid").jqGrid({
                //url: 'http://trirand.com/blog/phpjqgrid/examples/jsonp/getjsonp.php?callback=?&qwery=longorders',
                url: '<?php echo JUri::base() . 'index.php?option=com_medicineproducts&task=getItemsJson&tmpl=component&no_html=1&cats=0';?>',
                mtype: "GET",
                datatype: "jsonp",
                colModel: [
                    { name:'id',index:'id',hidden:true, key: true},
                    { label: '<?php echo JText::_("MEDIZD_CODE");?>', index:'id', name: 'code', width: 75, formatter:'showlink', formatoptions:{baseLinkUrl:'<?php echo JUri::base();?>' + 'index.php', addParam:'&option=com_medicineproducts&view=item&Itemid=<?php echo $Itemid;?>'} },
                    { label: '<?php echo JText::_("MEDIZD_NAME");?>', name: 'name', width: 150 },
                    { label: '<?php echo JText::_("MEDIZD_CATEGORY");?>', name: 'catname', width: 150 },
                    //{ label: 'country', name: 'country', width: 120 },
                    { label: '<?php echo JText::_("MEDIZD_DESCRIPTION");?>', name: 'description', width: 150 },
                    //{ label:'proizvoditel', name: 'proizvoditel', width: 130 },
                    //{ label: 'price', name: 'price', width: 75 }
                ],
				viewrecords: true,
                width: 690,
                height: 'auto',
                rowNum: 20,
                pager: "#jqGridPager"
            });
        });
 
   </script>
</div>   
</div>