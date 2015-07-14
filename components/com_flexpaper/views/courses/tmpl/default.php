<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if($this->view_menu) {
    $this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'quizes'.DS.'tmpl' );
    echo $this->loadTemplate('topmenu');
}

?>

<div id="osemsc-list">
    <?php 
        $x=0;

        $prev_cat_id = 0;

        for($i=0;$i<count($this->items);$i++) {?>

            <?php if(isset($this->items[$i]->the_first_havent_bought) && $this->items[$i]->the_first_havent_bought) {?>

                <br />
                <div class="clear" style="clear: both;">&nbsp;</div>

            <?php $x=0; }

            //categories
            $cat_id = $this->items[$i]->category_id; 

            if($cat_id != $prev_cat_id) {?>
                <div style="clear: both; font-weight: bold;">
                <?php echo $this->items[$i]->category; $x=0;?>
                </div>
            <?php }
            $prev_cat_id = $cat_id;    
           ?> 

            <div class="egitim_icerik msc-first <?php if(!$this->items[$i]->bought) echo 'egitim_pasif';?>" <?php if(!$this->items[$i]->bought) echo 'style="opacity: 0.6;"';?>>
                <p class="egitim_resim">

                    <?php if(!$this->items[$i]->bought) {?>
                    <a href="<?php echo $this->items[$i]->content_link;?>">
                    <?php } else {?>
                    <a  href="index.php?option=com_flexpaper&task=list_docs&course_id=<?php echo $this->items[$i]->id;?>&Itemid=<?php echo $this->items[$i]->membersarea_itemid;?>">
                    <?php }?>


                        <?php if(isset($this->items[$i]->image) && $this->items[$i]->image) {?>
                            <img height="97" border="0" src="<?php echo JURI::base();?><?php echo $this->items[$i]->image;?>">
                        <?php } else {?>
                            <img border="0" src="components/com_osemsc/assets/msc_logo/no-image.jpg">
                        <?php }?>
                    </a>
                    <span>

                        <?php if(!$this->items[$i]->bought) {?>
                        <a href="<?php echo $this->items[$i]->content_link;?>">
                        <?php } else {?>
                        <a  href="index.php?option=com_flexpaper&task=list_docs&course_id=<?php echo $this->items[$i]->id;?>&Itemid=<?php echo $this->items[$i]->membersarea_itemid;?>">
                        <?php }?>

                        <?php echo $this->items[$i]->title;?>
                        </a>
                    </span>
                </p>

                <div ><?php //echo $this->items[$i]->description;//echo substr($this->items[$i]->description,0,strpos($this->items[$i]->description,'{readmore}'));?></div>


                <?php if(!$this->items[$i]->bought) {?>
                    <a href="<?php echo $this->items[$i]->content_link;?>" class="detayli_bilgi"></a>
                <?php } else {?>
                    <a class="egitimer_giris" href="index.php?option=com_flexpaper&task=list_docs&course_id=<?php echo $this->items[$i]->id;?>&Itemid=<?php echo $this->items[$i]->membersarea_itemid;?>">Подробно</a>
                <?php }?>

                <?php
                 if(isset($this->items[$i]->params) && count($this->items[$i]->params) && !$this->items[$i]->bought) {
                    foreach($this->items[$i]->params as $k=>$v) {?>
                        <?php if(isset($this->items[$i]->params->$k->price) && $this->items[$i]->params->$k->price) {?>
                            <a href="javascript:void(-1)" class="fiyat_buton msc-button-select-a" id="msc-button-select-a-<?php echo $this->items[$i]->id;?>">
                                 <?php echo $this->items[$i]->params->$k->price;?>
                             РУБ
                            </a>
                            <select style="display: none;" size="1" class="msc_options" name="msc_option" id="<?php echo $k;?>">
                                <option selected="selected" has_trial="0" standard_recurrence="1 Month" standard_price="TL <?php echo $this->items[$i]->params->$k->price;?>" trial_recurrence="0" trial_price="0" title="<?php echo $this->items[$i]->params->$k->optionname;?>" id="<?php echo $k;?>" value="<?php echo $k;?>"><?php echo $this->items[$i]->params->$k->optionname;?></option>
                            </select>
                        <?php }?>

                        <?php }
                    }?>


            </div>

       <?php
            if($x==3) { ?>
                <div class="clear" style="clear: both;">&nbsp;</div>
            <?php $x=0; } else { ?>
    <?php $x++; } }

    if(!count($this->items)) {
        echo JText::_('COM_FLEXPAPER_HAVENT_BOUGHT_COURSES');
    }

    ?>
</div>