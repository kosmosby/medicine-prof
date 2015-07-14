<?php
//TODO image params/sizes/credits

defined('_JEXEC') or die;
jimport('joomla.html.html');
?>
<div>
    <h1><?php echo JText::_('COM_ISEARCH_CART'); ?></h1>
    <div class="icart_items d_cart d_hidden">
        <?php
        if (sizeof($this->items)) {
            foreach ($this->items as $item) {
                $details = $item->details;
                ?>
                <div class="d_cart_item" style="display:inline-block;width:100%;border:1px solid #c3c3c3;border-radius:5px;padding:5px;margin:5px;">
                    <div style="margin-right: 13px;" class="deposit-item">
                        <a href="/component/isearch/?view=photo&amp;id=<?php echo $item->mid; ?>" >
                            <span class="item-middle">
                                <span style="width: auto; height: auto;" class="item-image">
                                    <img alt="" src="<?php echo $details->medium_thumbnail; ?>">
                                </span>
                            </span>
                        </a>
                    </div>
                    <p>Добавлена в корзину: <?php echo date('H:i d-m-Y', strtotime($item->date_added)); ?></p>
                    <p><?php echo JText::_('COM_ISEARCH_TITLE'); ?>: <?php echo $details->title; ?></p>
                    <p><?php echo JText::_('COM_ISEARCH_VIEWS'); ?>: <?php echo $details->views; ?></p>
                    <p><?php echo JText::_('COM_ISEARCH_DOWNLOADS'); ?>: <?php echo $details->downloads; ?></p>
                    <div class="d_item_right">
                        <div class="d_item_table">
                            <div class="d_item_tr d_item_tr_head"><div><?php echo JText::_('COM_ISEARCH_SIZE'); ?></div>
                                <div><?php echo JText::_('COM_ISEARCH_EXT_SIZE'); ?><span class="d_item_resolution">:</span></div>
                                <div><?php echo JText::_('COM_ISEARCH_FILETYPE'); ?></div>
                                <div><?php echo JText::_('COM_ISEARCH_COST'); ?></div>
                                <div><?php echo JText::_('COM_ISEARCH_DOWNLOAD'); ?></div>

                            </div>
                            <div class="d_item_tr d_xs" data-size="xs" data-license="standard">
                                <div class="first">
                                    <i class="d_sizes d_size_xs d_mar_xs">XS</i>
                                    <span><?php echo JText::_('COM_ISEARCH_SIZE_XS'); ?></span>
                                </div><div>429 x 280, 15.1 cm x 9.9 cm (72 dpi)</div>
                                <div>JPEG</div>
                                <div>1.00</div>
                                <div> <a class="btn d_button button d_green d_s" href="index.php?option=com_isearch&task=buy&id=<?php echo $item->mid; ?>&size=xs" data-size="xs" data-license="standard"><?php echo JText::_('COM_ISEARCH_DOWNLOAD'); ?></a> 
                                    <a data-href="#" class="d_item_purchased d_hidden" target="_blank" data-size="xs" data-license="standard">
                                        <i class="d_loading_icon d_loading_icon_ok d_loading_icon_leaved"></i>
                                    </a> 
                                </div>
                            </div>
                            <div class="d_item_tr d_s" data-size="s" data-license="standard">
                                <div class="first">
                                    <i class="d_sizes d_size_s d_mar_s">S</i>
                                    <span><?php echo JText::_('COM_ISEARCH_SIZE_S'); ?></span></div>
                                <div>875 x 571, 30.9 cm x 20.1 cm (72 dpi)</div>
                                <div>JPEG</div>
                                <div>2.00</div>
                                <div> 
                                    <a class="btn d_button button d_green d_s" href="index.php?option=com_isearch&task=buy&id=<?php echo $item->mid; ?>&size=s" data-size="s" data-license="standard"><?php echo JText::_('COM_ISEARCH_DOWNLOAD'); ?></a> 
                                    <a data-href="#" class="d_item_purchased d_hidden" target="_blank" data-size="s" data-license="standard">
                                        <i class="d_loading_icon d_loading_icon_ok d_loading_icon_leaved"></i>
                                    </a> 
                                </div>
                            </div>
                            <div class="d_item_tr d_m" data-size="m" data-license="standard">
                                        <div class="first">
                                            <i class="d_sizes d_size_m d_mar_m">M</i>
                                            <span><?php echo JText::_('COM_ISEARCH_SIZE_M'); ?></span>
                                        </div>
                                <div>1750 x 1143, 14.8 cm x 9.7 cm (300 dpi)</div>
                                <div>JPEG</div>
                                <div>4.00</div>
                                <div> 
                                    <a class="btn d_button button d_green d_s" href="index.php?option=com_isearch&task=buy&id=<?php echo $item->mid; ?>&size=m" data-size="m" data-license="standard"><?php echo JText::_('COM_ISEARCH_DOWNLOAD'); ?></a> 
                                    <a data-href="#" class="d_item_purchased d_hidden" target="_blank" data-size="m" data-license="standard">
                                        <i class="d_loading_icon d_loading_icon_ok d_loading_icon_leaved"></i>
                                    </a> 
                                </div>
                            </div>
                            <div class="d_item_tr d_l" data-size="l" data-license="standard">
                                <div class="first">
                                    <i class="d_sizes d_size_l d_mar_l">L</i>
                                    <span><?php echo JText::_('COM_ISEARCH_SIZE_L'); ?></span>
                                </div>
                                <div>3500 x 2286, 29.6 cm x 19.4 cm (300 dpi)</div>
                                <div>JPEG</div>
                                <div>8.00</div>
                                <div> 
                                    <a class="btn d_button button d_green d_s" href="index.php?option=com_isearch&task=buy&id=<?php echo $item->mid; ?>&size=l" data-size="l" data-license="standard"><?php echo JText::_('COM_ISEARCH_DOWNLOAD'); ?></a> 
                                    <a data-href="#" class="d_item_purchased d_hidden" target="_blank" data-size="l" data-license="standard">
                                        <i class="d_loading_icon d_loading_icon_ok d_loading_icon_leaved"></i>
                                    </a> 
                                </div>
                            </div>
                            <div class="d_item_tr d_xl" data-size="xl" data-license="standard">
                                <div class="first"><i class="d_sizes d_size_xl d_mar_xl">XL</i>
                                    <span><?php echo JText::_('COM_ISEARCH_SIZE_XL'); ?></span>
                                </div>
                                <div>4200 x 2743, 35.6 cm x 23.2 cm (300 dpi)</div>
                                <div>JPEG</div>
                                <div>10.00</div>
                                <div> 
                                    <a class="btn d_button button d_green d_s" href="index.php?option=com_isearch&task=buy&id=<?php echo $item->mid; ?>&size=xl" data-size="xl" data-license="standard"><?php echo JText::_('COM_ISEARCH_DOWNLOAD'); ?></a>
                                    <a data-href="#" class="d_item_purchased d_hidden" target="_blank" data-size="xl" data-license="standard">
                                        <i class="d_loading_icon d_loading_icon_ok d_loading_icon_leaved"></i>
                                    </a> 
                                </div>
                            </div>
                            <div class="d_item_tr d_el0" data-size="el0" data-license="extended">
                                <div class="first">
                                    <i class="d_sizes d_size_el0 d_mar_el0">EL</i>
                                    <span><?php echo JText::_('COM_ISEARCH_SIZE_EL'); ?></span>
                                </div>
                                <div>4200 x 2743, 35.6 cm x 23.2 cm (300 dpi)</div>
                                <div>JPEG</div>
                                <div>80.00</div>
                                <div> <a class="btn d_button button d_green d_s" href="/cart.html?source=view_item&amp;do=purchase&amp;id=11130679&amp;method=credits&amp;license=extended&amp;size=el0" data-size="el0" data-license="extended"><?php echo JText::_('COM_ISEARCH_DOWNLOAD'); ?></a> 
                                    <a data-href="#" class="d_item_purchased d_hidden" target="_blank" data-size="el0" data-license="extended">
                                        <i class="d_loading_icon d_loading_icon_ok d_loading_icon_leaved"></i>
                                    </a> 
                                </div>
                            </div>
                        </div>
                    </div>


                    <div style="width: 86%;">
                        <input type="button" class="btn button" value="Скачать"/>
                        <input type="button" class="btn" style="float:right;" value="Удалить из корзины" onclick="location.href = 'index.php?option=com_isearch&task=delete_from_cart&id=<?php echo $item->mid; ?>';"/>
                    </div>
                </div>
                <?php
            }
        }
        else
            echo JText::_('COM_ISEARCH_CART_IS_EMPTY');
        ?>
    </div>
</div>