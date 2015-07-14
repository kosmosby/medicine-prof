<?php
	// no direct access
	defined( '_JEXEC' ) or die( 'Restricted access' );

	$catshow = $params->get('searchcategory');
	$themeselect = $params->get('theme');
	JHTML::stylesheet($themeselect.".css",'modules/mod_auctionsearch/tmpl/'.$themeselect.'/');

    JLoader::register('BidConfig',JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'options.php');
    JLoader::register('BidsHelper',JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'bids.php');
    BidsHelper::LoadHelperClasses();

    $arr = array();
    $arr['name'] = 'cat';
    $lists['cats'] = BidsHelperHtml::selectCategory($arr);

    $lang = JFactory::getLanguage();
    $lang->load('com_bids');

    JHtml::stylesheet(JUri::root().'/modules/mod_auctionsearch/assets/css/mod_bids_search.css');
?>

<div>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="searchauctionForm" >
		<input type="hidden" name="option" value="com_bids" />
		<input type="hidden" name="task" value="listauctions" />
		<input type="hidden" name="indesc" value="1" />
		<span>
			<span class="mod_auction_search_field mod_auction_search_inner">
                <input type="text" name="keyword" style="height: 20px;" />&nbsp;
                <input name="go" type="submit" class="mod_auction_button_BINq" value="<?php echo JText::_('COM_BIDS_SEARCH'); ?>"/>
            </span>

			<?php
				if ($catshow=="yes") {
                    echo '<div>&nbsp;</div>'.
					    $lists['cats'].
                        '<div>&nbsp;</div>';

				}
			?>
		</span>
	</form>
</div>