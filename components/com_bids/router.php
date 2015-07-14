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

require_once( JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'tools.php' );
require_once( JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'route.php' );

function bidsBuildRoute(&$query) {

    $segments = array();
    if(empty($query['task'])){
        return $segments;
    }

    $task = strtolower($query['task']);
    unset($query['task']);

    switch($task) {
        case 'viewbids':
            if(!empty($query['id'])) {

                $segments[] = $task;

                list($id,$title) = explode(':',$query['id']);
                $segments[] = $id;
                $segments[] = JFilterOutput::stringURLUnicodeSlug($title);

                unset($query['id']);
            }
            break;
        case 'listcats':
        case 'tree':

            $segments[] = $task;

            if(isset($query['cat']) && $query['cat']){

                $path = bidsSEFCatPath($query['cat']);
                $segments[] = implode('/',$path).'/'.$query['cat'];

                unset($query['cat']);
            }
            break;
        case 'search';

            $segments[] = $task;

            break;
        case 'tags':

            $segments[] = $task;
            if(!empty($query['tagid'])){
                list($tagid,$slug) = explode(':',$query['tagid']);
                $segments[] = $tagid;
                $segments[] = JFilterOutput::stringURLUnicodeSlug($slug);
                unset($query['tagid']);
            }

            break;
        case 'listauctions':

            $segments[] = $task;

            if(!empty($query['cat'])) {

                $path = bidsSEFCatPath($query['cat']);
                $segments[] = implode('/',$path).'/'.$query['cat'];

                unset($query['cat']);
            }

            if(!empty($query['users'])) {

                $username = bidsSefUsername($query['users']);
                if ($username) {
                    $segments[]='user/'.$query['users'].'/'.JFilterOutput::stringURLUnicodeSlug($username);
                    unset($query['users']);
                }
            }

            break;
        case 'userdetails':

            $segments[] = $task;

            if(!empty($query['id'])) {

                $username = bidsSefUsername($query['id']);
                if ($username) {
                    $segments[] = $query['id'];
                    $segments[] = JFilterOutput::stringURLUnicodeSlug($username);
                }
                unset($query['id']);
            }

            break;

        default:
            if(false==strpos($task,'.')) {
                $segments[] = $task;
            } else {
                $query['task'] = $task;
            }

            break;
    }

    return $segments;
}

function bidsParseRoute($segments) {

    $vars = array();

    switch($segments[0]){
        case 'viewbids':

            $vars['task'] = 'viewbids';
            $v=explode('/',$segments[1]);
            $vars['id']=$v[0];

            break;
        case 'search':

            $vars['task'] = 'search';

            break;
        case 'tags':

            $vars['task'] = 'tags';
            $vars['tagid'] = $segments[1].':'.$segments[2];

            break;
        case 'listauctions':

            $vars['task'] = 'listauctions';

            $categories = $segments;
            unset($categories[0]);

            if( isset($segments[1]) && $segments[1]=="user" ) {
                $vars['users']=$segments[2];
            } else {
                $vars['cat']=end($categories);

            }

            break;
        case 'userdetails':

            $vars['task']='userdetails';

            $userId = isset($segments[1]) ? $segments[1] : 0;
            if($userId) {
                $vars['id']=$userId;
            }

            break;

        case 'listcats':
        case 'tree':

            $vars['task'] = $segments[0];
            if(count($segments)>1) {
                $vars['cat'] = intval(end($segments));
            }

            break;

        default:

            $vars['task'] = $segments[0];

            break;
    }

    $needles = array();
    if(!empty($vars['task'])) {
        $needles["task"]=$vars['task'];
        if($router_itemID = BidsHelperRoute::getMenuItemId($needles)){
            $vars["Itemid"] = $router_itemID;
        }
    }

    return $vars;
}

function bidsSEFCatPath($catid) {

    static $paths = array();

    if(!isset($paths[$catid])) {

        $database = JFactory::getDbo();

        $q = $database->getQuery(true);
        $q->select('p.*')
            ->from('#__categories c')
            ->leftJoin('#__categories p ON c.lft BETWEEN p.lft AND p.rgt')
            ->where('p.extension=\'com_bids\' AND p.published=1 AND c.id='.$database->quote($catid))
            ->order('p.lft ASC');
        $database->setQuery($q);
        $pathRows = $database->loadObjectList();

        $path = array();
        foreach($pathRows as $r) {

            if($r->id==1) {
                //category system root
                continue;
            }

            $path[] = JFilterOutput::stringURLUnicodeSlug($r->title);
        }

        $paths[$catid] = $path;
    }

    return $paths[$catid];
}

function bidsSefUsername($userid) {

    static $usernames = array();

    if(!isset($usernames[$userid])) {

        $database = JFactory::getDbo();

        $q = "SELECT username FROM #__users WHERE id=" . $database->quote($userid);
        $database->setQuery($q);
        $rec = $database->loadObject();

        $usernames[$userid] = empty($rec->username) ? 0 : $rec->username;
    }

    return $usernames[$userid];
}