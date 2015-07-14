<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class plgSearchBids extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas()
	{
		static $areas = array(
			'auctions' => 'PLG_SEARCH_BIDS_AUCTIONS'
			);
			return $areas;
	}

	/**
	 * Newsfeeds Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();

        $searchText = $text;

        $limit = $this->params->get( 'search_limit', 50 );

        $text = trim( $db->escape($text) );

        if ($text == '') {
            return array();
        }
        $section 	= JText::_( 'Auction Factory' );

        $wheres 	= array();
        switch ($phrase)
        {
            case 'exact':
                $text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
                $wheres2 	= array();
                $wheres2[] 	= 'a.shortdescription LIKE '.$text;
                $wheres2[] 	= 'a.title LIKE '.$text;
                $where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
                break;

            case 'all':
            case 'any':
            default:
                $words 	= explode( ' ', $text );
                $wheres = array();
                foreach ($words as $word)
                {
                    $word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
                    $wheres2 	= array();
                    $wheres2[] 	= 'a.shortdescription LIKE '.$word;
                    $wheres2[] 	= 'a.title LIKE '.$word;
                    $wheres[] 	= implode( ' OR ', $wheres2 );
                }
                $where 	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
                break;
        }

        switch ( $ordering ) {
            case 'alpha':
                $order = 'title ASC';
                break;

            case 'category':
                $order = 'catname ASC, title ASC';
                break;

            case 'oldest':
                $order = 'start_date ASC, title ASC';
                break;
            case 'newest':
                $order = 'start_date DESC, title ASC';
                break;
            case 'popular':
            default:
                $order = 'hits DESC';
        }
        require_once (JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'tools.php');
        $tmp_id = BidsHelperTools::getMenuItemId(array("task"=>"listauctions"));

        $query = "SELECT a.title,"
        . "\n start_date AS created,"
        . "\n shortdescription AS text,"
        . "\n concat('Cat: ',b.title) AS section,"
        . "\n CONCAT( 'index.php?option=com_bids&task=viewbids&Itemid={$tmp_id}&id=', a.id, ':', a.title ) AS href,"
        . "\n '1' AS browsernav"
        . "\n FROM #__bid_auctions a "
        . "\n LEFT JOIN #__categories AS b ON b.id = a.cat"
        . "\n WHERE ( $where )"
        . "\n AND a.published = 1 and a.start_date<=UTC_TIMESTAMP()"
        . "\n ORDER BY $order"
        ;

        $db->setQuery( $query, 0, $limit );
        $rows = $db->loadObjectList();

        $return = array();
        foreach($rows AS $key => $weblink) {
            if(searchHelper::checkNoHTML($weblink, $searchText, array('url', 'text', 'title'))) {
                $return[] = $weblink;
            }
        }

        return $return;
	}
}
