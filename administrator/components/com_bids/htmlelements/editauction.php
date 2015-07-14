<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/



defined('_JEXEC') or die();

class JHTMLEditAuction {

    static function editStartDate(&$auction,$isAdmin=false) {

        $cfg = BidsHelperTools::getConfig();

        $dateFormat = BidsHelperDateTime::dateFormatConvert2JHTML(false);
        $fType = CustomFieldsFactory::getFieldType('date');
        $calendarFormat = $fType->dateFormatConversion($dateFormat);
        $auction->start_date = $auction->id ? $auction->start_date : gmdate('Y-m-d H:i:s');

        if(!$cfg->bid_opt_enable_date) {
            $html = $auction->id ? JHTML::date( JFactory::getDate($auction->start_date)->toUnix(), $dateFormat) : JHTML::date('now', $dateFormat);
        } else {
            if($auction->published && !$isAdmin) {
                $html = JHTML::date( JFactory::getDate($auction->start_date)->toUnix(), $dateFormat);
            } else {
                $attribs = array('class' => 'inputbox required validate-start-date');

                $html = JHTML::calendar( $auction->start_date, 'start_date', 'start_date', $calendarFormat, $attribs);
                if ($auction->start_date) {
                    $html = str_replace(' value="' . htmlspecialchars($auction->start_date, ENT_COMPAT, 'UTF-8') . '"',
                            ' value="' . htmlspecialchars(JHtml::date($auction->start_date, $cfg->bid_opt_date_format, false), ENT_COMPAT, 'UTF-8') . '"',
                        $html
                    );
                }

                if ($cfg->bid_opt_enable_hour) {
                    $html .= '&nbsp;<input name="start_hour" size="1" value="' . JHTML::date($auction->start_date, 'H') . '" maxlength="2" class="inputbox required" onblur="bidCheckHours(this)" /> :
                        <input name="start_minutes" size="1" value="' . JHTML::date($auction->start_date, 'i') . '" maxlength="2" class="inputbox required" onblur="bidCheckMinutes(this)" />';
                }

                $html .= '&nbsp;' . JHtml::image(JUri::root() . 'components/com_bids/images/requiredfield.gif', 'Required');
            }
        }

        return $html;
    }

    static function editEndDate(&$auction, $isAdmin=false) {

        $cfg = BidsHelperTools::getConfig();

        $dateFormat = BidsHelperDateTime::dateFormatConvert2JHTML(false);
        $fType = CustomFieldsFactory::getFieldType('date');
        $calendarFormat = $fType->dateFormatConversion($dateFormat);

        $dateFormat = BidsHelperDateTime::dateFormatConvert2JHTML(false);

        if(!$cfg->bid_opt_enable_date) {

            if($auction->id && $auction->published) {
                $html = JHTML::date( JFactory::getDate($auction->end_date)->toUnix() ,$dateFormat);
            } else {
                $tooltipMsg = array();

                if (intval($cfg->bid_opt_default_period) > 0) {
                    $tooltipMsg[] = JText::_('COM_BIDS_AUTO_END_DATE_TOOLTIP_1').' '.$cfg->bid_opt_default_period.' '.JText::_('COM_BIDS_DAYS');
                }
                if ($cfg->bid_opt_allow_proxy && intval($cfg->bid_opt_proxy_default_period) > 0) {
                    $tooltipMsg[] = JText::_('COM_BIDS_AUTO_END_DATE_TOOLTIP_2').' '.$cfg->bid_opt_proxy_default_period.' '.JText::_('COM_BIDS_DAYS');
                }
                if ( ($cfg->bid_opt_global_enable_bin || $cfg->bid_opt_enable_bin_only) && intval($cfg->bid_opt_bin_default_period) > 0 ) {
                    $tooltipMsg[] = JText::_('COM_BIDS_AUTO_END_DATE_TOOLTIP_3').' '.$cfg->bid_opt_bin_default_period.' '.JText::_('COM_BIDS_DAYS');
                }
                if ($cfg->bid_opt_global_enable_reserve_price && intval($cfg->bid_opt_reserve_price_default_period) > 0 ) {
                    $tooltipMsg[] = JText::_('COM_BIDS_AUTO_END_DATE_TOOLTIP_4').' '.$cfg->bid_opt_reserve_price_default_period.' '.JText::_('COM_BIDS_DAYS');
                }

                $html = count($tooltipMsg) ? JHtml::tooltip( implode('<br />',$tooltipMsg) ) : '-';
            }
        } else {
            if( $auction->published && !$isAdmin ) {
                $html = JHTML::date( JFactory::getDate($auction->end_date)->toUnix() ,$dateFormat);
            } else {
                $attribs = array('class' => 'inputbox required validate-start-date');
                $date = $auction->end_date ? $auction->end_date : '';
                $d = $date ? $date : '';

                if (preg_match('/^[0-9][0-9]:[0-9][0-9]$/',$date)) //if it's only end hour
                    $d="";

                $html = JHTML::calendar($d, 'end_date', 'end_date', $calendarFormat, $attribs);
                if ($d)
                    $html=str_replace(' value="'.htmlspecialchars($d, ENT_COMPAT, 'UTF-8').'"', 
                            ' value="'.htmlspecialchars(JHtml::date($d,$cfg->bid_opt_date_format,false), ENT_COMPAT, 'UTF-8').'"',
                            $html
                    );

                if ($cfg->bid_opt_enable_hour) {
                    $endHours = $auction->end_date ? JHTML::date($auction->end_date, 'H') : '00';
                    $endMinutes = $auction->end_date ? JHTML::date($auction->end_date, 'i') : '00';
                    $html .= '&nbsp;<input name="end_hour" size="1" value="' . $endHours . '" maxlength="2" class="inputbox required" onblur="bidCheckHours(this)" /> :
                        <input name="end_minutes" size="1" value="' . $endMinutes . '" maxlength="2" class="inputbox required" onblur="bidCheckMinutes(this)" />';
                }

                $html .= '&nbsp;' . JHtml::image(JUri::root() . 'components/com_bids/images/requiredfield.gif', 'Required');
            }
        }

        return $html;
    }

    static function currentLocalTime() {

        $class = 'auctionCurrentLocalTime';

        $dateString = JHTML::date('now', 'Y,m,d,H,i');

        $langCode = '\'bids\'';

        $js = 'jQueryBids.clock.locale =
            {
                "bids":
                {
                    "weekdays":
                        [
                            "'.JText::_('SUNDAY').'",
                            "'.JText::_('MONDAY').'",
                            "'.JText::_('TUESDAY').'",
                            "'.JText::_('WEDNESDAY').'",
                            "'.JText::_('THURSDAY').'",
                            "'.JText::_('FRIDAY').'",
                            "'.JText::_('SATURDAY').'"
                        ],
                    "months":
                        [
                            "'.JText::_('JANUARY').'",
                            "'.JText::_('FEBRUARY').'",
                            "'.JText::_('MARCH').'",
                            "'.JText::_('APRIL').'",
                            "'.JText::_('MAY').'",
                            "'.JText::_('JUNE').'",
                            "'.JText::_('JULY').'",
                            "'.JText::_('AUGUST').'",
                            "'.JText::_('SEPTEMBER').'",
                            "'.JText::_('OCTOBER').'",
                            "'.JText::_('NOVEMBER').'",
                            "'.JText::_('DECEMBER').'"
                        ]
                }
            };';
        $js .= 'auctionStartCurrentLocalTimer(\'' . $class . '\',' . $dateString . ', '.$langCode.');';

        JFactory::getDocument()->addScriptDeclaration($js);

        $html = '<span class='.$class.'></span>';

        return $html;
    }

    static function selectCategory($auction,$isAdmin=false) {

        $cfg = BidsHelperTools::getConfig();
        //TODO: remove this when putting categories in the framework
        require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'bidscategory.php');
        $cat = JModel::getInstance('bidscategory','bidsModel');

        if($auction->published && !$isAdmin) {
            $path = $cat->getCategoryPathString($auction->cat);

            return implode('\\',$path);
        }

        if ( $cfg->bid_opt_category_page=='catpage' && !$isAdmin ) {
            $html = "<input type='hidden' id='category_selected' name='cat' value='".$auction->cat."' />";
            $catname = implode('\\', $cat->getCategoryPathString($auction->cat) );
            $html .="<span>".$catname."</span>";
        } else {

            $cat->loadCategoryTree();
            $treeCat = $cat->get('categories');

            $spacer = '&nbsp;&nbsp;&nbsp;';
            $opts = array();

            foreach($treeCat as $c) {
                $text = '&nbsp;'.str_repeat($spacer,$c->depth).$c->title.PHP_EOL;
                $opts[] = JHTML::_('select.option',$c->id,$text, 'value', 'text', ($cfg->bid_opt_leaf_posting_only && $c->nrSubcategories) );
            }

            $html = JHTML::_('select.genericlist', $opts, 'cat','class="inputbox" onchange=\'auctionRefreshCustomFields(this);\'', 'value', 'text', $auction->cat);
        }

        return $html;
    }

    static function inputTags($auction) {

        return '<input id="tags" name="tags" class="inputbox" value="'.implode(',',$auction->tagNames).'" type="text"/>'.JHTML::tooltip(JText::_('COM_BIDS_HELP_TAGS'));
    }

    static function formTitle($auction) {

        if(isset($auction->oldid))
        {
            return JText::_('COM_BIDS_REPUBLISH_AUCTION');
        }
        else
        {
            if (isset($auction->id) && $auction->id > 0)
            {
                return JText::_('COM_BIDS_EDIT_AUCTION');
            }
            else
            {
                return JText::_('COM_BIDS_ADD_AUCTION');
            }
        }
    }

    static function inputTitle($auction, $isAdmin = false) {

        if(!$auction->published || $isAdmin) {
            return '<input type="text" name="title" value="'.$auction->title.'" class="inputbox required" id="auctionTitle" />';
        }
        return $auction->title;
    }

    static function selectPublished($auction, $isAdmin = false) {
        if(!$auction->published || $isAdmin) {
            return JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $auction->id ? $auction->published : 1);
        }
        return JText::_('COM_BIDS_YES');
    }

    static function selectAuctionType($auction, $isAdmin = false) {

        if(!$auction->published || $isAdmin) {
            return BidsHelperHtml::selectAuctionType($auction->auction_type);
        }

        switch($auction->auction_type) {
            case AUCTION_TYPE_PUBLIC:
                $text = JText::_('COM_BIDS_PUBLIC_LABEL');
                break;
            case AUCTION_TYPE_PRIVATE:
                $text = JText::_('COM_BIDS_PRIVATE_LABEL');
                break;
            case AUCTION_TYPE_BIN_ONLY:
                $text = JText::_('COM_BIDS_BIN_ONLY_LABEL');
                break;
        }
        return "<span id='bid_auction_type'>" . $text . "</span>";
    }

    static function inputAutomatic($auction, $isAdmin = false) {

        $cfg = BidsHelperTools::getConfig();

        $html = '';
        if( !$auction->published || $isAdmin ) {
            $style = '';
            if($cfg->bid_opt_automatic_auction_select) {
                $checked = $auction->automatic ? 'checked="checked"' : '';
            } else {
                $checked = $cfg->bid_opt_automatic_auction_default ? 'checked="checked"' : '';
                $style = ' style="display: none; visibility: hidden;" ';
                $html .= $cfg->bid_opt_automatic_auction_default ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO') ;
            }
            $html .= '<input class="inputbox" type="checkbox" id="automatic_check" name="automatic" value="1" '.$checked.$style.' />';

            return $html;
        }

        return $auction->automatic ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO');
    }

    static function selectBINType($auction, $isAdmin = false) {

        if( !$auction->published || $isAdmin ) {

            $opts[] = JHTML::_('select.option', '0', JText::_('COM_BIDS_AUCTION_WITHOUT_BIN') );
            $opts[] = JHTML::_('select.option', '1', JText::_('COM_BIDS_AUCTION_WITH_BIN') );

            $disabled = (!$auction->auction_type || $auction->auction_type == AUCTION_TYPE_BIN_ONLY) ? ' disabled="disabled" ' : '';

            $selected = $auction->BIN_price>0 ? 1 : 0;

            return JHTML::_('select.genericlist', $opts, 'bin_OPTION', ' id="bin_OPTION" ' . $disabled . ' class="inputbox" onchange="changeAuctionType();"', 'value', 'text', $selected );
        }

        return $auction->BIN_price ? JText::_('COM_BIDS_AUCTION_WITH_BIN') : JText::_('COM_BIDS_AUCTION_WITHOUT_BIN');
    }

    static function inputBinPrice($auction, $isAdmin = false) {

        if( !$auction->published || $isAdmin ) {

            return '<input class="inputbox validate-bin validate-price priceRight" type="text" id="BIN_price" size="7" name="BIN_price" value="'.number_format($auction->BIN_price,2).'" /><span class="bidsRefreshCurrency">' . $auction->currency . '</span>';
        }

        return BidsHelperAuction::formatPrice($auction->BIN_price).'&nbsp;'.$auction->currency;
    }

    static function selectAutoAcceptBIN($auction, $isAdmin = false) {

        if($auction->published && !$isAdmin) {
            return $auction->params['auto_accept_bin'] ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO');
        }

        if($auction->automatic) {
            return JText::_('COM_BIDS_YES');
        }

        $s = ( isset($auction->params['auto_accept_bin']) ) ? $auction->params['auto_accept_bin'] : 0;

        return JHTML::_('select.booleanlist','auto_accept_bin','',$s).JHtml::tooltip(JText::_('COM_BIDS_PARAM_ACCEPT_BIN_HELP'));
    }

    static function inputQuantity($auction, $isAdmin = false) {

        if( !$auction->published || $isAdmin ) {
            $text = '<input type="text" size="3" class="inputbox validate-numeric priceRight" name="quantity" value="'.($auction->quantity ? $auction->quantity : 1).'" />';
        } else {
            $text = $auction->quantity;
        }

        return $text.JHtml::tooltip(JText::_('COM_BIDS_BIN_QUANTITY_HELP'));
    }

    static function selectEnableSuggestions($auction, $isAdmin = false) {

        $s = isset($auction->params['price_suggest']) ? $auction->params['price_suggest'] : 0;

        $cfg = BidsHelperTools::getConfig();

        if( !$auction->published || $isAdmin ) {
            $text = JHTML::_('select.booleanlist','price_suggest','onclick="changeAuctionType();"',$s);
        } else {
            $text = $s ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO');
        }

        return $text . JHtml::tooltip( JText::sprintf('COM_BIDS_PRICE_SUGGEST_HELP',$cfg->bin_opt_limit_suggestions) );
    }

    static function inputMinNumberSuggestions($auction, $isAdmin = false) {

        $disabled = isset($auction->params['price_suggest']) && $auction->params['price_suggest'] ? '' : 'disabled="disabled"';

        if( !$auction->published || $isAdmin ) {
            $text = '<input type="text" class="inputbox validate-numeric priceRight" id="price_suggest_min" name="price_suggest_min" value="'.(isset($auction->params['price_suggest_min']) ? $auction->params['price_suggest_min'] : 0).'" '.$disabled.'/>';
        } else {
            $text = isset($auction->params['price_suggest_min']) ? $auction->params['price_suggest_min'] : 0;
        }

        return $text . JHtml::tooltip(JText::_('COM_BIDS_PRICE_SUGGEST_MIN_HELP'));
    }

    static function selectCurrency($auction, $isAdmin = false) {

        if( !$auction->published || $isAdmin ) {
            $db = JFactory::getDBO();
            $query = 'SELECT `name` AS value, `name` AS text FROM `#__bid_currency`';
            $db->setQuery($query);
            $opts = $db->loadObjectList();

            return JHTML::_('select.genericlist', $opts, 'currency', 'class="inputbox required" size="1" onchange="bidsRefreshCurrency(this.value)"', 'value', 'text', $auction->currency);
        }

        return $auction->currency;
    }

    static function inputInitialPrice($auction, $isAdmin = false) {

        if( !$auction->published || $isAdmin) {
            return '<input class="inputbox required validate-price priceRight" type="text" size="7" name="initial_price" id="initial_priceID" value="'.number_format($auction->initial_price,2).'" /><span class="bidsRefreshCurrency">'.$auction->currency.'</span>';
        }

        return BidsHelperAuction::formatPrice($auction->initial_price).'&nbsp;'.$auction->currency;
    }

    static function selectShowMaxPrice($auction, $isAdmin = false) {

        $s = isset($auction->params['max_price']) ? $auction->params['max_price'] : 1;

        if( !$auction->published || $isAdmin ) {
            $text = JHTML::_('select.booleanlist','max_price','',$s,'COM_BIDS_SHOW','COM_BIDS_HIDE');
        } else {
            $text = $s ? JText::_('COM_BIDS_SHOW') : JText::_('COM_BIDS_HIDE');
        }

        return $text . JHtml::tooltip(JText::_('COM_BIDS_PARAM_MAX_PRICE_HELP'));
    }

    static function selectShowNumberBids($auction, $isAdmin=false) {

        $s = isset($auction->params['bid_counts']) ? $auction->params['bid_counts'] : 1;

        if( !$auction->published || $isAdmin ) {
            $text = JHTML::_('select.booleanlist','bid_counts','',$s,'COM_BIDS_SHOW','COM_BIDS_HIDE');
        } else {
            $text = $s ? JText::_('COM_BIDS_SHOW') : JText::_('COM_BIDS_SHOW');
        }

        return $text . JHtml::tooltip(JText::_('COM_BIDS_PARAM_COUNTS_HELP'));
    }

    static function inputReservePrice($auction, $isAdmin = false) {

        if( !$auction->published || $isAdmin ) {
            $text = '<input class="inputbox validate-numeric priceRight" type="text" size="7" name="reserve_price" value="'.number_format($auction->reserve_price,2).'" /><span class="bidsRefreshCurrency">' . $auction->currency . '</span>';
        } else {
            $text = BidsHelperAuction::formatPrice($auction->reserve_price).'&nbsp;'.$auction->currency;
        }

        return $text . JHtml::tooltip(JText::_('COM_BIDS_HELP_RESERVE_PRICE'));
    }

    static function selectShowReservePrice($auction, $isAdmin = false) {

        $s = isset($auction->params['show_reserve']) ? $auction->params['show_reserve'] : 0;

        if( !$auction->published || $isAdmin) {
            $text = JHTML::_('select.booleanlist','show_reserve','',$s,'COM_BIDS_SHOW','COM_BIDS_HIDE');
        } else {
            $text = $s ? JText::_('COM_BIDS_SHOW') : JText::_('COM_BIDS_HIDE');
        }

        return $text . JHtml::tooltip( JText::_('COM_BIDS_HELP_SHOW_RESERVE_PRICE') );
    }

    static function inputMinIncrease($auction, $isAdmin = false) {

        $cfg = BidsHelperTools::getConfig();

        if(!$cfg->bid_opt_min_increase_select) {
            return;
        }

        if( !$auction->published || $isAdmin) {
            $mi = $auction->min_increase ? $auction->min_increase : $cfg->bid_opt_min_increase;
            $text = '<input class="inputbox validate-numeric priceRight" type="text" size="7" name="min_increase" value="'.number_format($mi,2).'" /><span class="bidsRefreshCurrency">' . $auction->currency . '</span>';
        } else {
            $text = BidsHelperAuction::formatPrice($auction->min_increase).'&nbsp;'.$auction->currency;
        }

        return $text . JHtml::tooltip( JText::_('COM_BIDS_HELP_MIN_INCREASE') );
    }


    static function inputShortDescription($auction) {

        return '<input class="inputbox required" type="text" size="55" name="shortdescription" value="'.$auction->shortdescription.'" />';
    }

    static function inputDescription($auction) {

        $editor = JFactory::getEditor();

        return $editor->display('description',$auction->description,'80%','250px',65,20);
    }

    static function inputShipmentPrice($auction, $isAdmin = false) {

        $cfg = BidsHelperTools::getConfig();
        $db = JFactory::getDBO();

        if(!$cfg->bid_opt_multiple_shipping) {
            $db->setQuery("SELECT price FROM #__bid_shipment_prices WHERE zone=0 AND auction=".$db->quote($auction->id));
            if($auction->published) {
                return BidsHelperAuction::formatPrice($db->loadResult()).'&nbsp;'.$auction->currency;
            } else {
                return '<input name="shipZones[]" type="hidden" value="0" />' .
                        '<input name="shipPrices[]" type="text" class="inputbox priceRight" size="7"  value="' . number_format($db->loadResult(), 2) . '" /><span class="bidsRefreshCurrency">' . $auction->currency . '</span>';
            }

        }

        $db->setQuery("SELECT * FROM #__bid_shipment_zones ORDER BY `name`");
        $shipZones = $db->loadObjectList();

        $optZones = array();
        $zoneNames = $zoneIds = array();
        foreach($shipZones as $sz) {
            $optZones[] = JHTML::_('select.option',$sz->id,$sz->name);
            $zoneNames[] = $sz->name;
            $zoneIds[] = $sz->id;
        }

        $p = JTable::getInstance('bidshipzone');
        $shipPrices = $p->getPriceList( max($auction->id,@$auction->oldid) );

        $html = '';
        $i = 0;
        if(count($shipPrices)) {
            $html = '<table>';

            if($auction->published) {

                foreach($shipPrices as $sp) {
                    $i++;
                    $html .= '<tr>';
                    $html .= '<td>'.$sp->name.'&nbsp;&nbsp;</td>';
                    $html .= '<td>'.number_format($sp->price,2).'&nbsp;'.$auction->currency.'</td>';
                    $html .= '</tr>';
                }

            } else {

                //already saved shipping zones
                foreach($shipPrices as $sp) {
                    $i++;
                    $html .= '<tr>';
                    $html .= '<td>'.JHTML::_('select.genericlist',$optZones,'shipZones[]','class="inputbox"','value','text',$sp->id).'</td>';
                    $html .= '<td><input type="text" class="inputbox validate-price" name="shipPrices[]" value="'.number_format($sp->price,2).'" /></td>';
                    $html .= '</tr>';
                }
            }

            $html .= '</table>';
        }

        if(!$auction->published || $isAdmin) {

            $html .= '<div id="shippingZonesContainer"></div>';

            $document = JFactory::getDocument();
            $js =
                'var CurrentIndex = '.$i++.';
                var SHIPOptions = Array(\''.implode('\',\'',$zoneNames).'\');
                var SHIPIDS = Array('.implode(',',$zoneIds).');
                var SHIPNO = '.count($shipZones).';';
            $document->addScriptDeclaration($js);

            $html .= JHTML::link('javascript:SHIPAddzone();', JText::_('COM_BIDS_ADD_SHIPPING_ZONE'));
        }

        return $html;
    }

    static function uploadImages($auction) {

        $cfg = BidsHelperTools::getConfig();
        $document = JFactory::getDocument();

        $imageList = '';
        $images = $auction->get('images');
        foreach($images as $img) {
            $imageList .=
                    '<div>'.
                        JHTML::image(AUCTION_PICTURES.'resize_'.$img->picture,$img->picture, 'style="vertical-align:middle"').
                        '&nbsp;<input type="checkbox" name="delete_pictures[]" value="'.$img->id.'" id="bidsDelImg'.$img->id.'" />&nbsp;<label for="bidsDelImg'.$img->id.'" />'.JText::_('COM_BIDS_DELETE').'</label>'.
                    '</div><br />';
        }

        JHTML::script('Stickman.MultiUpload.js','components/'.APP_EXTENSION.'/js/');

        $dirImages = JUri::root().'components/com_bids/images/';
        $startScript = 'window.addEvent(\'domready\', function(){'.
                            'new MultiUpload( $( \'bidpicture\' ), '.($cfg->bid_opt_maxnr_images - $auction->get('imagecount') ).', \'_{id}\', true, true, \''.$dirImages.'\' );'.
                        '});';
        $document->addScriptDeclaration($startScript);

        $class = 'inputbox '. ( $cfg->bid_opt_require_picture ? 'required' : '' );
        $disabled = ( $auction->get('imagecount')>=$cfg->bid_opt_maxnr_images ) ? 'disabled="disabled"' : '';

        $inputUpload = JHTML::tooltip( JText::sprintf('COM_BIDS_UPLOAD_IMAGES_HELP',$cfg->bid_opt_max_picture_size) ).
                '&nbsp;'.'<input type="file" name="picture" id="bidpicture" '.$class.' '.$disabled.' />';

        $html = $imageList.$inputUpload;

        return $html;
    }

    static function textPaymentInfo($auction) {

        return '<textarea class="inputbox" rows="5" cols="45" name="payment_info">'.$auction->payment_info.'</textarea>';
    }

    static function textShipmentInfo($auction) {

        return '<textarea class="inputbox" rows="5" cols="45" name="shipment_info">'.$auction->shipment_info.'</textarea>';
    }
}
