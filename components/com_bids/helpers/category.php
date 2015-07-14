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


defined('_JEXEC') or die('Restricted access');

class BidsHelperCategory
{
    static function saveJoomlaCategory($name, $parentId, $aliased=false)
    {
        jimport('joomla.application.component.model');
        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_categories' . DS . 'tables');
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_categories' . DS . 'models');
        require_once( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_categories' . DS . 'models' . DS . 'category.php' );

        $modelCategory = JModelLegacy::getInstance('category', 'CategoriesModel');
        $data = array(
            'id' => 0,
            'parent_id' => $parentId,
            'extension' => 'com_bids',
            'title' => $name,
            'alias' => ($aliased ? ($name.'_'.uniqid()) : ''),
            'note' => '',
            'description' => '',
            'published' => '1',
            'access' => '1',
            'metadesc' => '',
            'metakey' => '',
            'created_user_id' => '0',
            'language' => 'en-GB',
            'rules' =>
            array(
                'core.create' =>
                array(),
                'core.delete' =>
                array(),
                'core.edit' =>
                array(),
                'core.edit.state' =>
                array(),
                'core.edit.own' =>
                array(),
            ),
            'params' =>
            array(
                'category_layout' => '',
                'image' => '',
            ),
            'metadata' =>
            array(
                'author' => '',
                'robots' => '',
            ),
        );

        return $modelCategory->save($data);
    }

    static function saveCategories($textcats, $big_parent_id) {

        $db = JFactory::getDbo();

        if ('WIN' == substr(PHP_OS, 0, 3)) {
            $separator = "\r\n";
        } else {
            $separator = "\n";
        }

        $textcats = explode($separator, $textcats);

        $stack = array($big_parent_id);

        $i = 0;
        $last_id = 0;
        foreach ($textcats as $key => $cat) {
            $prevcat = isset($textcats[$i - 1]) ? $textcats[$i - 1] : '';
            $prevcat_spaces = self::getFirstSpaces($prevcat);
            $cat_spaces = self::getFirstSpaces($cat);

            $catname = trim($cat);
            if(!$catname) {
                continue;
            }

            if ($cat_spaces > $prevcat_spaces) {

                //if that moron user puts too many spaces in front of the child category, here we make things right
                if ($cat_spaces - $prevcat_spaces > 1) {
                    $new_cat_spaces = '';
                    for ($j = 0; $j < $prevcat_spaces + 1; $j++) {
                        $new_cat_spaces .= ' ';
                    }
                    $textcats[$key] = $new_cat_spaces . trim($cat);
                }

                array_push($stack, $last_id);

            }

            if ($cat_spaces < $prevcat_spaces) {
                $diff_level = $prevcat_spaces - $cat_spaces;
                for ($j = 0; $j < $diff_level; $j++) {
                    array_pop($stack);
                }
            }

            if( !self::saveJoomlaCategory( $catname, end($stack) ) ) {
                //repeating alias
                if( !self::saveJoomlaCategory( $catname, end($stack), true ) ) {
                    break;
                }
            }

            $db->setQuery("SELECT MAX(id) FROM #__categories");
            $last_id = $db->loadResult();

            $i++;
        }

        return true;
    }

    static function getFirstSpaces($string)
    {
        $count = 0;
        $nr = strlen($string);
        if (!$nr) {
            return $count;
        }
        for ($i = 0; $i <= $nr; $i++) {
            if (' ' == $string[$i]) {
                $count++;
            } else {
                break;
            }
        }
        return $count;
    }
}
