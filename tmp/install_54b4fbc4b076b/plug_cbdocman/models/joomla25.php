<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\Table\Table;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class cbdocmanModel
 * Articles Model for Joomla articles
 */
class cbdocmanModel
{
	/**
	 * Gets Total number of articles
	 *
	 * @param  string       $where
	 * @param  UserTable    $viewer
	 * @param  UserTable    $user
	 * @param  PluginTable  $plugin
	 * @return null|string
	 */
	static public function getDocmanTotal( $where, /** @noinspection PhpUnusedParameterInspection */ $viewer, $user, $plugin )
	{
		global $_CB_database;

		$categories			=	$plugin->params->get( 'article_j_category', null );

		$query				=	'SELECT COUNT(*)'
							.	"\n FROM " . $_CB_database->NameQuote( '#__docman_documents' ) . " AS a"
							.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__docman_categories' ) . " AS b"
							.	' ON b.' . $_CB_database->NameQuote( 'docman_category_id' ) . ' = a.' . $_CB_database->NameQuote( 'docman_category_id' )
							.	"\n WHERE a." . $_CB_database->NameQuote( 'created_by' ) . " = " . (int) $user->get( 'id' )
							.	"\n AND a." . $_CB_database->NameQuote( 'enabled' ) . " = 1"
							.	"\n AND (a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
                                                        .       "\n OR a." . $_CB_database->NameQuote( 'access' ) . " = -1 )".	"\n AND b." . $_CB_database->NameQuote( 'enabled' ) . " = 1"
							.	"\n AND b." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() );

		if ( $categories ) {
			$categories		=	explode( '|*|', $categories );

			cbArrayToInts( $categories );

			$query			.=	"\n AND a." . $_CB_database->NameQuote( 'catid' ) . " NOT IN ( " . implode( ',', $categories ) . " )";
		}

		$query				.=	$where;

		$_CB_database->setQuery( $query );

		return $_CB_database->loadResult();
	}

	/**
	 * Gets articles
	 *
	 * @param  int[]        $paging
	 * @param  string       $where
	 * @param  UserTable    $viewer
	 * @param  UserTable    $user
	 * @param  PluginTable  $plugin
	 * @return Table[]
	 */
	static public function getDocman( $paging, $where, /** @noinspection PhpUnusedParameterInspection */ $viewer, $user, $plugin )
	{
		global $_CB_database;

		$categories			=	$plugin->params->get( 'article_j_category', null );

		$query				=	'SELECT a.*'
							.	', b.' . $_CB_database->NameQuote( 'docman_category_id' ) . ' AS category'
							.	', b.' . $_CB_database->NameQuote( 'title' ) . ' AS category_title'
							.	', b.' . $_CB_database->NameQuote( 'enabled' ) . ' AS category_published'
							.	', b.' . $_CB_database->NameQuote( 'slug' ) . ' AS category_slug'
							.	"\n FROM " . $_CB_database->NameQuote( '#__docman_documents' ) . " AS a"
							.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__docman_categories' ) . " AS b"
							.	' ON b.' . $_CB_database->NameQuote( 'docman_category_id' ) . ' = a.' . $_CB_database->NameQuote( 'docman_category_id' )
							.	"\n WHERE a." . $_CB_database->NameQuote( 'created_by' ) . " = " . (int) $user->get( 'id' )
							.	"\n AND a." . $_CB_database->NameQuote( 'enabled' ) . " = 1"
							.	"\n AND (a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
                                                        .       "\n OR a." . $_CB_database->NameQuote( 'access' ) . " = -1 )"
							.	"\n AND b." . $_CB_database->NameQuote( 'enabled' ) . " = 1"
							.	"\n AND b." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() );

		if ( $categories ) {
			$categories		=	explode( '|*|', $categories );

			cbArrayToInts( $categories );

			$query			.=	"\n AND a." . $_CB_database->NameQuote( 'catid' ) . " NOT IN ( " . implode( ',', $categories ) . " )";
		}

		$query				.=	$where
							.	"\n ORDER BY a." . $_CB_database->NameQuote( 'created_on' ) . " DESC";

		if ( $paging ) {
			$_CB_database->setQuery( $query, $paging[0], $paging[1] );
		} else {
			$_CB_database->setQuery( $query );
		}

                
                
		return $_CB_database->loadObjectList( null, '\CBLib\Database\Table\Table', array( null, '#__docman_documents', 'id' ) );
	}

	/**
	 * Returns the URL for an article
	 *
	 * @param  Table    $row
	 * @param  boolean  $htmlspecialchars
	 * @param  string   $type              'article', 'section' or 'category'
	 * @return string                      URL
	 */
	static public function getUrl( $row, $htmlspecialchars = true, $type = 'article' )
	{
		global $_CB_framework;
                $Itemid = self::getDocmanItemID();
                if(!$Itemid){
                    return '#';
                }
		$url = 'index.php?option=com_docman&view=document&layout=default&alias='.$row->docman_document_id.'-'.$row->slug.'&category_slug='.$row->category_slug.'&Itemid='.$Itemid;

		
		$url		=	JRoute::_( $url, false );
		

		if ( $url ) {
			if ( $htmlspecialchars ) {
				$url	=	htmlspecialchars( $url );
			}
		}

		return $url;
	}
        
        static public function getDocmanItemID(){
            global $_CB_database;
            
            $query = "SELECT m.id"
                    ."\n FROM #__menu as m"
                    ." JOIN #__extensions as e"
                    ." ON m.component_id = e.extension_id AND e.element = 'com_docman'"
                    ." WHERE published = '1' "
                    .	"\n AND m." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
                    ." LIMIT 1";
            $_CB_database->setQuery( $query );
            return $_CB_database->loadResult( );
        }
}
