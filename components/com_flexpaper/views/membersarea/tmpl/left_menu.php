<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');


?>
<div class="grid_6 aktif_sol">
    <ul>
        <li>
            <a href="index.php?option=com_flexpaper&task=list_docs&course_id=<?php echo $this->course_id;?>&Itemid=247" class="sol_ust_baslik"><?php echo $this->title;?></a>
        </li>
        <?php foreach($this->items as $k=>$v) {?>
        <li>
            <a href="javascript:void(0);" class="sol_kategori"><?php echo $k;?></a>
            <ul <?php if(isset($this->activeCourse) && $this->activeCourse->catid == $v[0]->catid) echo 'style="display:inline"';?>>
                <?php for($i=0;$i<count($v);$i++) {?>
                <li>
                    <a <?php if(isset($this->current_id) && $this->current_id && $this->current_id==$v[$i]->id) echo 'id="bolum_aktif"';?> href="index.php?option=com_flexpaper&task=click_doc&view=flexpaper&id=<?php echo $v[$i]->id;?>&course_id=<?php echo $this->course_id;?>&Itemid=248"><?php echo $v[$i]->name;?></a>
                </li>
                <?php }?>
            </ul>
        </li>
        <?php }?>
    </ul>
</div>
