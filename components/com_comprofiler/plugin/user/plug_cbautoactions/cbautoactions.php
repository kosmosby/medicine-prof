<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use CBLib\Database\Table\OrderedTable;
use CB\Database\Table\UserTable;
use CBLib\Language\CBTxt;
use CBLib\Registry\Registry;
use CBLib\Registry\ParamsInterface;
use CBLib\Database\Table\TableInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );

class cbautoactionsClass
{

	/**
	 * Loads all the action classes into memory so they can be called as needed
	 */
	static public function getModels()
	{
		global $_PLUGINS;

		static $LOADED						=	0;

		if ( ! $LOADED++ ) {
			$plugin							=	$_PLUGINS->getLoadedPlugin( 'user', 'cbautoactions' );

			if ( $plugin ) {
				$path						=	$_PLUGINS->getPluginPath( $plugin );

				if ( is_dir( $path . '/models' ) ) {
					foreach ( scandir( $path . '/models' ) as $model ) {
						if ( preg_match( '!^([\w-]+)\.php$!', $model, $matches ) ) {
							include_once( $path . '/models/' . $model );
						}
					}
				}
			}
		}
	}

	/**
	 * Prepares action triggers
	 */
	static public function getTriggers()
	{
		global $_CB_database, $_PLUGINS;

		static $LOADED								=	0;

		if ( ! $LOADED++ ) {
			$plugin									=	$_PLUGINS->getLoadedPlugin( 'user', 'cbautoactions' );

			if ( $plugin ) {
				$query								=	'SELECT *'
													.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_autoactions' )
													.	"\n WHERE " . $_CB_database->NameQuote( 'trigger' ) . " != ''"
													.	"\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1"
													.	"\n ORDER BY " . $_CB_database->NameQuote( 'system' ) . " ASC, " . $_CB_database->NameQuote( 'ordering' ) . " ASC";
				$_CB_database->setQuery( $query );
				$rows								=	$_CB_database->loadObjectList( null, 'cbautoactionsActionTable', array( $_CB_database ) );

				/** @var $rows cbautoactionsActionTable[] */
				if ( $rows ) foreach ( $rows as $row ) {
					$triggers						=	explode( '|*|', $row->get( 'trigger' ) );

					if ( $triggers ) foreach ( $triggers as $trigger ) {
						$trigger					=	trim( htmlspecialchars( $trigger ) );

						if ( $trigger && ( ! in_array( $trigger, array( 'internalGeneral', 'internalUsers' ) ) ) ) {
							$references				=	$row->getParams()->get( 'references' );

							if ( $references ) {
								$references			=	explode( '|*|', $references );

								cbArrayToInts( $references );
							}

							if ( $references ) {
								// Prepare a list of variables to send to the anonymous function:
								$vars				=	array(	1	=>	'$var1 = null',
																2	=>	'$var2 = null',
																3	=>	'$var3 = null',
																4	=>	'$var4 = null',
																5	=>	'$var5 = null',
																6	=>	'$var6 = null',
																7	=>	'$var7 = null',
																8	=>	'$var8 = null',
																9	=>	'$var9 = null',
																10	=>	'$var10 = null'
															);

								// Change variables to references as needed:
								foreach ( $vars as $i => $var ) {
									if ( in_array( $i, $references ) ) {
										$vars[$i]	=	'&' . $var;
									}
								}

								$function			=	'global $_PLUGINS;'
													.	'$args	=	array( ' . (int) $row->id . ', \'' . $trigger . '\', &$var1, &$var2, &$var3, &$var4, &$var5, &$var6, &$var7, &$var8, &$var9, &$var10 );'
													.	'return $_PLUGINS->call( ' . (int) $plugin->id . ', \'getTrigger\', \'cbautoactionsPlugin\', $args );';

								$function			=	create_function( implode( ', ', $vars ), $function );
							} else {
								$function			=	function( $var1 = null, $var2 = null, $var3 = null, $var4 = null, $var5 = null, $var6 = null, $var7 = null, $var8 = null, $var9 = null, $var10 = null ) use ( $_PLUGINS, $plugin, $row, $trigger ) {
															$args	=	array( $row, $trigger, &$var1, &$var2, &$var3, &$var4, &$var5, &$var6, &$var7, &$var8, &$var9, &$var10 );

															return $_PLUGINS->call( $plugin->id, 'getTrigger', 'cbautoactionsPlugin', $args );
														};
							}

							$_PLUGINS->registerFunction( $trigger, $function );
						}
					}
				}
			}
		}
	}

	/**
	 * Manually triggers an action
	 *
	 * @param cbautoactionsActionTable $trigger
	 * @param UserTable                $user
	 * @param null|string              $rawPassword
	 * @param array                    $vars
	 * @param array                    $extras
	 * @return mixed
	 */
	static public function triggerAction( $trigger, $user, $rawPassword = null, $vars = array(), $extras = array() )
	{
		global $_PLUGINS;

		$plugin		=	$_PLUGINS->getLoadedPlugin( 'user', 'cbautoactions' );

		if ( $plugin ) {
			$args	=	array( $trigger, $user, $rawPassword, $vars, $extras );

			return $_PLUGINS->call( $plugin->id, 'getAction', 'cbautoactionsPlugin', $args );
		}

		return null;
	}

	/**
	 * Parses a string for PHP functions
	 *
	 * @param string $input
	 * @param array  $vars
	 * @return string
	 */
	static public function formatCondition( $input, $vars = array() )
	{
																// \[cb:parse(?: +function="([^"/\[\] ]+)")( +(?: ?[a-zA-Z-_]+="(?:[^"]|\\")+")+)?(?:(?:\s*/])|(?:]((?:[^\[]|\[(?!/?cb:parse[^\]]*])|(?R))+)?\[/cb:parse]))
		$regex												=	'%\[cb:parse(?: +function="([^"/\[\] ]+)")( +(?: ?[a-zA-Z-_]+="(?:[^"]|\\\\")+")+)?(?:(?:\s*/])|(?:]((?:[^\[]|\[(?!/?cb:parse[^\]]*])|(?R))+)?\[/cb:parse]))%i';

		if ( preg_match_all( $regex, $input, $results, PREG_SET_ORDER ) ) {
			foreach( $results as $matches ) {
				$function									=	( isset( $matches[1] ) ? $matches[1] : null );

				if ( $function ) {
					$value									=	( isset( $matches[3] ) ? self::formatCondition( $matches[3], $vars ) : null );
					$options								=	new Registry();

					if ( isset( $matches[2] ) ) {
						if ( preg_match_all( '/(?:([a-zA-Z-_]+)="((?:[^"]|\\\\\\\\")+)")+/i', $matches[2], $optionResults, PREG_SET_ORDER ) ) {
							foreach( $optionResults as $option ) {
								$k							=	( isset( $option[1] ) ? $option[1] : null );
								$v							=	( isset( $option[2] ) ? $option[2] : null );

								if ( $k ) {
									$options->set( $k, $v );
								}
							}
						}
					}

					switch ( $function ) {
						case 'clean':
							switch( $options->get( 'method' ) ) {
								case 'cmd':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::COMMAND ), $input );
									break;
								case 'numeric':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::NUMERIC ), $input );
									break;
								case 'unit':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::UINT ), $input );
									break;
								case 'int':
								case 'integer':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::INT ), $input );
									break;
								case 'bool':
								case 'boolean':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::BOOLEAN ), $input );
									break;
								case 'str':
								case 'string':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::STRING ), $input );
									break;
								case 'html':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::HTML ), $input );
									break;
								case 'float':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::FLOAT ), $input );
									break;
								case 'base64':
									$input					=	str_replace( $matches[0], Get::clean( $value, GetterInterface::BASE64 ), $input );
									break;
								case 'tags':
									$input					=	str_replace( $matches[0], strip_tags( $value ), $input );
									break;
							}
							break;
						case 'convert':
							switch( $options->get( 'method' ) ) {
								case 'uppercase':
									$input					=	str_replace( $matches[0], strtoupper( $value ), $input );
									break;
								case 'uppercasewords':
									$input					=	str_replace( $matches[0], ucwords( $value ), $input );
									break;
								case 'uppercasefirst':
									$input					=	str_replace( $matches[0], ucfirst( $value ), $input );
									break;
								case 'lowercase':
									$input					=	str_replace( $matches[0], strtolower( $value ), $input );
									break;
								case 'lowercasefirst':
									$input					=	str_replace( $matches[0], lcfirst( $value ), $input );
									break;
							}
							break;
						case 'math':
							$input							=	str_replace( $matches[0], self::formatMath( $value ), $input );
							break;
						case 'time':
							$input							=	str_replace( $matches[0], ( $options->has( 'time' ) ? strtotime( $options->get( 'time', null, GetterInterface::STRING ), ( is_numeric( $value ) ? (int) $value : strtotime( $value ) ) ) : strtotime( $value ) ), $input );
							break;
						case 'date':
							$offset							=	$options->get( 'offset' );
							$input							=	str_replace( $matches[0], cbFormatDate( ( is_numeric( $value ) ? (int) $value : strtotime( $value ) ), ( $offset ? true : false ), true, $options->get( 'date-format' ), $options->get( 'time-format' ), ( $offset != 'true' ? $offset : null ) ), $input );
							break;
						case 'length':
							$input							=	str_replace( $matches[0], strlen( $value ), $input );
							break;
						case 'replace':
							$input							=	str_replace( $matches[0], ( $options->has( 'count' ) ? str_replace( $options->get( 'search' ), $options->get( 'replace' ), $value, $options->get( 'count', 0, GetterInterface::INT ) ) : str_replace( $options->get( 'search' ), $options->get( 'replace' ), $value ) ), $input );
							break;
						case 'position':
							switch( $options->get( 'occurrence' ) ) {
								case 'last':
									$input					=	str_replace( $matches[0], strrpos( $value, $options->get( 'search' ) ), $input );
									break;
								case 'first':
								default:
									$input					=	str_replace( $matches[0], strpos( $value, $options->get( 'search' ) ), $input );
									break;
							}
							break;
						case 'occurrence':
							$input							=	str_replace( $matches[0], strstr( $value, $options->get( 'search' ) ), $input );
							break;
						case 'repeat':
							$input							=	str_replace( $matches[0], str_repeat( $value, $options->get( 'count', 0, GetterInterface::INT ) ), $input );
							break;
						case 'extract':
							$input							=	str_replace( $matches[0], ( $options->has( 'length' ) ? substr( $value, $options->get( 'start', 0, GetterInterface::INT ), $options->get( 'length', 0, GetterInterface::INT ) ) : substr( $value, $options->get( 'start', 0, GetterInterface::INT ) ) ), $input );
							break;
						case 'trim':
							switch( $options->get( 'direction' ) ) {
								case 'left':
									$input					=	str_replace( $matches[0], ( $options->has( 'characters' ) ? ltrim( $value, $options->get( 'characters', null, GetterInterface::STRING ) ) : ltrim( $value ) ), $input );
									break;
								case 'right':
									$input					=	str_replace( $matches[0], ( $options->has( 'characters' ) ? rtrim( $value, $options->get( 'characters', null, GetterInterface::STRING ) ) : rtrim( $value ) ), $input );
									break;
								default:
									$input					=	str_replace( $matches[0], ( $options->has( 'characters' ) ? trim( $value, $options->get( 'characters', null, GetterInterface::STRING ) ) : trim( $value ) ), $input );
									break;
							}
							break;
						case 'encode':
							switch( $options->get( 'method' ) ) {
								case 'cslashes':
									$input					=	str_replace( $matches[0], addcslashes( $value, $options->get( 'characters', null, GetterInterface::STRING ) ), $input );
									break;
								case 'slashes':
									$input					=	str_replace( $matches[0], addslashes( $value ), $input );
									break;
								case 'entity':
									$input					=	str_replace( $matches[0], htmlentities( $value ), $input );
									break;
								case 'html':
									$input					=	str_replace( $matches[0], htmlspecialchars( $value ), $input );
									break;
								case 'url':
									$input					=	str_replace( $matches[0], urlencode( $value ), $input );
									break;
								case 'base64':
									$input					=	str_replace( $matches[0], base64_encode( $value ), $input );
									break;
								case 'md5':
									$input					=	str_replace( $matches[0], md5( $value ), $input );
									break;
								case 'sha1':
									$input					=	str_replace( $matches[0], sha1( $value ), $input );
									break;
								case 'password':
									$user					=	new UserTable();

									$input					=	str_replace( $matches[0], $user->hashAndSaltPassword( $value ), $input );
									break;
							}
							break;
						case 'decode':
							switch( $options->get( 'method' ) ) {
								case 'cslashes':
									$input					=	str_replace( $matches[0], stripcslashes( $value ), $input );
									break;
								case 'slashes':
									$input					=	str_replace( $matches[0], stripslashes( $value ), $input );
									break;
								case 'entity':
									$input					=	str_replace( $matches[0], html_entity_decode( $value ), $input );
									break;
								case 'html':
									$input					=	str_replace( $matches[0], htmlspecialchars_decode( $value ), $input );
									break;
								case 'url':
									$input					=	str_replace( $matches[0], urldecode( $value ), $input );
									break;
								case 'base64':
									$input					=	str_replace( $matches[0], base64_encode( $value ), $input );
									break;
							}
							break;
						default:
							if ( ! $function ) {
								continue;
							}

							$class							=	$options->get( 'class', null, GetterInterface::STRING );
							$subFunction					=	null;
							$static							=	false;
							$result							=	null;

							if ( strpos( $function, '::' ) !== false ) {
								list( $class, $function )	=	explode( '::', $function, 2 );

								$static						=	true;
							} elseif ( strpos( $class, '::' ) !== false ) {
								$subFunction				=	$function;

								list( $class, $function )	=	explode( '::', $class, 2 );

								$static						=	true;
							}

							if ( $class ) {
								$object						=	null;

								$options->unsetEntry( 'class' );

								if ( isset( $vars[$class] ) && is_object( $vars[$class] ) ) {
									$object					=	$vars[$class];
									$class					=	get_class( $object );
								}

								if ( $static ) {
									if ( $subFunction ) {
										if ( is_callable( array( $class, $function ) ) ) {
											$object			=	call_user_func_array( array( $class, $function ), array() );

											if ( method_exists( $object, $subFunction ) ) {
												$result		=	call_user_func_array( array( $object, $subFunction ), $options->asArray() );
											}
										}
									} else {
										if ( is_callable( array( $class, $function ) ) ) {
											$result			=	call_user_func_array( array( $class, $function ), $options->asArray() );
										}
									}
								} else {
									if ( $object || class_exists( $class ) ) {
										if ( ! $object ) {
											$object			=	new $class();

											if ( $value && method_exists( $object, 'load' ) ) {
												$object->load( $value );
											}
										}

										if ( method_exists( $object, $function ) ) {
											$result			=	call_user_func_array( array( $object, $function ), $options->asArray() );
										}
									}
								}
							} else {
								if ( function_exists( $function ) ) {
									$result					=	call_user_func_array( $function, $options->asArray() );
								}
							}

							if ( ( ! is_array( $result ) ) && ( ! is_object( $result ) ) ) {
								$input						=	str_replace( $matches[0], $result, $input );
							}
							break;
					}

					// If no replacement is done above then the string still exists; lets just replace the substitution with the found value:
					$input									=	str_replace( $matches[0], $value, $input );
				}
			}

			$input											=	self::formatCondition( $input, $vars );
		}

		return $input;
	}

	/**
	 * Parses a string for math expressions
	 *
	 * @param string $value
	 * @return string
	 */
	static public function formatMath( $value )
	{
		if ( preg_match( '/(?:\(\s*)([^(]+?)(?:\s*\))/i', $value, $expression ) ) {
			// Sub-Expression
			$value					=	str_replace( $expression[0], self::formatMath( $expression[1] ), $value );

			return self::formatMath( $value );
		} elseif ( preg_match( '/([+-]?\d*\.?\d+)\s*\*\s*([+-]?\d*\.?\d+)/i', $value, $expression ) ) {
			// Multiply
			$left					=	( isset( $expression[1] ) ? trim( $expression[1] ) : null );
			$right					=	( isset( $expression[2] ) ? trim( $expression[2] ) : null );
			$value					=	str_replace( $expression[0], ( $left * $right ), $value );

			return self::formatMath( $value );
		} elseif ( preg_match( '%([+-]?\d*\.?\d+)\s*/\s*([+-]?\d*\.?\d+)%i', $value, $expression ) ) {
			// Divide:
			$left					=	( isset( $expression[1] ) ? trim( $expression[1] ) : null );
			$right					=	( isset( $expression[2] ) ? trim( $expression[2] ) : null );
			$value					=	str_replace( $expression[0], ( $left / $right ), $value );

			return self::formatMath( $value );
		} elseif ( preg_match( '/([+-]?\d*\.?\d+)\s*([+%-])\s*([+-]?\d*\.?\d+)/i', $value, $expression ) ) {
			// Add, Subtract, Modulus:
			$left					=	( isset( $expression[1] ) ? trim( $expression[1] ) : null );
			$operator				=	( isset( $expression[2] ) ? trim( $expression[2] ) : null );
			$right					=	( isset( $expression[3] ) ? trim( $expression[3] ) : null );

			if ( $operator ) {
				switch( $operator ) {
					case '+':
						$value		=	str_replace( $expression[0], ( $left + $right ), $value );
						break;
					case '-':
						$value		=	str_replace( $expression[0], ( $left - $right ), $value );
						break;
					case '%':
						$value		=	str_replace( $expression[0], ( $left % $right ), $value );
						break;
				}
			}

			return self::formatMath( $value );
		}

		return $value;
	}

	/**
	 * Compares two values to see if they're a match based off the supplied operator
	 *
	 * @param string $field
	 * @param string $operator
	 * @param string $value
	 * @param array  $vars
	 * @return bool|int|string
	 */
	static public function getFieldMatch( $field, $operator, $value, $vars = array() )
	{
		if ( $operator === '' ) {
			return true;
		}

		$field			=	cbautoactionsClass::formatCondition( trim( $field ), $vars );
		$value			=	cbautoactionsClass::formatCondition( trim( $value ), $vars );

		switch ( (int) $operator ) {
			case 1:
				$match	=	( $field != $value );
				break;
			case 2:
				$match	=	( $field > $value );
				break;
			case 3:
				$match	=	( $field < $value );
				break;
			case 4:
				$match	=	( $field >= $value );
				break;
			case 5:
				$match	=	( $field <= $value );
				break;
			case 6:
				$match	=	( ! $field );
				break;
			case 7:
				$match	=	( $field );
				break;
			case 8:
				$match	=	( stristr( $field, $value ) );
				break;
			case 9:
				$match	=	( ! stristr( $field, $value ) );
				break;
			case 10:
				$match	=	( preg_match( $value, $field ) );
				break;
			case 11:
				$match	=	( ! preg_match( $value, $field ) );
				break;
			case 0:
			default:
				$match	=	( $field == $value );
				break;
		}

		return $match;
	}

	/**
	 * Returns string name of an operator from int
	 *
	 * @param int|string $operator
	 * @return string
	 */
	static public function getOperatorTitle( $operator )
	{
		switch ( (int) $operator ) {
			case 1:
				$title	=	CBTxt::T( 'Not Equal To' );
				break;
			case 2:
				$title	=	CBTxt::T( 'Greater Than' );
				break;
			case 3:
				$title	=	CBTxt::T( 'Less Than' );
				break;
			case 4:
				$title	=	CBTxt::T( 'Greater Than or Equal To' );
				break;
			case 5:
				$title	=	CBTxt::T( 'Less Than or Equal To' );
				break;
			case 6:
				$title	=	CBTxt::T( 'Empty' );
				break;
			case 7:
				$title	=	CBTxt::T( 'Not Empty' );
				break;
			case 8:
				$title	=	CBTxt::T( 'Does Contain' );
				break;
			case 9:
				$title	=	CBTxt::T( 'Does Not Contain' );
				break;
			case 10:
				$title	=	CBTxt::T( 'Is REGEX' );
				break;
			case 11:
				$title	=	CBTxt::T( 'Is Not REGEX' );
				break;
			case 0:
				$title	=	CBTxt::T( 'Equal To' );
				break;
			default:
				$title	=	CBTxt::T( 'Unknown' );
				break;
		}

		return $title;
	}

	/**
	 * Encodes a string to URL safe
	 *
	 * @param string $str
	 * @return string
	 */
	static public function escapeURL( $str )
	{
		return urlencode( trim( $str ) );
	}

	/**
	 * Encodes a string to XML safe
	 *
	 * @param string $str
	 * @return string
	 */
	static public function escapeXML( $str )
	{
		return htmlspecialchars( trim( $str ), ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * Encodes a string to SQL safe
	 *
	 * @param string $str
	 * @return string
	 */
	static public function escapeSQL( $str )
	{
		global $_CB_database;

		return $_CB_database->getEscaped( $str );
	}
}

cbautoactionsClass::getModels();

class cbautoactionsActionTable extends OrderedTable
{
	/** @var int */
	var $id				=	null;
	/** @var int */
	var $system			=	null;
	/** @var string */
	var $title			=	null;
	/** @var string */
	var $description	=	null;
	/** @var string */
	var $type			=	null;
	/** @var string */
	var $trigger		=	null;
	/** @var int */
	var $object			=	null;
	/** @var int */
	var $variable		=	null;
	/** @var string */
	var $access			=	null;
	/** @var ParamsInterface */
	var $conditions		=	null;
	/** @var int */
	var $published		=	null;
	/** @var int */
	var $ordering		=	null;
	/** @var ParamsInterface */
	var $params			=	null;

	/** @var string */
	var $_password		=	null;
	/** @var CBuser */
	var $_cbuser		=	null;
	/** @var array */
	var $_extras		=	null;
	/** @var array */
	var $_vars			=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl			=	'#__comprofiler_plugin_autoactions';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key		=	'id';

	/**
	 * Ordering keys and for each their ordering groups.
	 * E.g.; array( 'ordering' => array( 'tab' ), 'ordering_registration' => array() )
	 * @var array
	 */
	protected $_orderings	=	array( 'ordering' => array() );

	/**
	 * Generic check for whether dependencies exist for this object in the db schema
	 * Should be overridden if checks need to be done before delete()
	 *
	 * @param  int  $oid  key index (only int supported here)
	 * @return boolean
	 */
	public function canDelete( /** @noinspection PhpUnusedParameterInspection */ $oid = null )
	{
		if ( $this->get( 'system' ) ) {
			$this->setError( CBTxt::T( 'System actions can not be deleted' ) );

			return false;
		}

		return true;
	}

	/**
	 * Copies this record (no checks)
	 * canCopy should be called first to check if a copy is possible.
	 *
	 * @param  null|TableInterface|self  $object  The object being copied otherwise create new object and add $this
	 * @return self|boolean                       OBJECT: The new object copied successfully, FALSE: Failed to copy
	 */
	public function copy( $object = null )
	{
		if ( $object === null ) {
			$object		=	clone $this;
		}

		if ( $object->get( 'system' ) ) {
			$object->set( 'system', 0 );
		}

		return parent::copy( $object );
	}

	/**
	 * Executes an action method (e.g. execute, validate, installed)
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed|null
	 */
	public function call( $method, $args = array() )
	{
		global $_PLUGINS;

		static $cache		=	array();

		$id					=	$this->get( 'type' );

		if ( ! isset( $cache[$id] ) ) {
			$class			=	'cbautoactionsAction' . trim( preg_replace( '/[^-a-zA-Z0-9_]/', '', $id ) );

			if ( ! class_exists( $class ) ) {
				$class		=	null;
			}

			$cache[$id]		=	$class;
		}

		$class				=	$cache[$id];

		if ( $class ) {
			array_unshift( $args, $this );

			$plugin			=	$_PLUGINS->getLoadedPlugin( 'user', 'cbautoactions' );

			if ( $plugin ) {
				return $_PLUGINS->call( $plugin->id, $method, $class, $args );
			}
		}

		return null;
	}

	/**
	 * Parses the conditions into a params object
	 *
	 * @return ParamsInterface
	 */
	public function &getConditions()
	{
		static $cache	=	array();

		$id				=	$this->get( 'id' );

		if ( ! isset( $cache[$id] ) ) {
			$cache[$id]	=	new Registry( $this->get( 'conditions' ) );
		}

		return $cache[$id];
	}

	/**
	 * Parses the params into a params object
	 *
	 * @return ParamsInterface
	 */
	public function &getParams()
	{
		static $cache	=	array();

		$id				=	$this->get( 'id' );

		if ( ! isset( $cache[$id] ) ) {
			$cache[$id]	=	new Registry( $this->get( 'params' ) );
		}

		return $cache[$id];
	}

	/**
	 * Parses a string using trigger specific substitution information
	 *
	 * @param $string
	 * @param bool $htmlspecialchars
	 * @param array|bool $translate
	 * @return string
	 */
	public function getSubstituteString( $string, $htmlspecialchars = true, $translate = true )
	{
		if ( $this->get( '_password' ) ) {
			$string		=	str_ireplace( '[password]', $this->get( '_password' ), $string );
		}

		if ( $this->get( '_cbuser' ) ) {
			$string		=	$this->_cbuser->replaceUserVars( $string, $htmlspecialchars, false, $this->get( '_extras' ), $translate );
		}

		if ( $this->getParams()->get( 'format', false, GetterInterface::BOOLEAN ) ) {
			$string		=	cbautoactionsClass::formatCondition( $string, $this->get( '_vars' ) );
		}

		return $string;
	}

	/**
	 * Returns the internal action URL for firing an action
	 *
	 * @return string
	 */
	public function getActionURL()
	{
		global $_CB_framework;

		return '<a href="' . $_CB_framework->pluginClassUrl( 'cbautoactions', true, array( 'action' => 'action', 'actions' => $this->get( 'id' ) ), 'html', 0, true ) . '" target="_blank">' . CBTxt::T( 'Click to Execute' ) . '</a>';
	}
}

cbautoactionsClass::getTriggers();

class cbautoactionsPlugin extends cbPluginHandler
{

	/**
	 * Prepares the action to be executed from trigger
	 *
	 * @param cbautoactionsActionTable|int $trigger
	 * @param string                       $event
	 * @param mixed                        $var1
	 * @param mixed                        $var2
	 * @param mixed                        $var3
	 * @param mixed                        $var4
	 * @param mixed                        $var5
	 * @param mixed                        $var6
	 * @param mixed                        $var7
	 * @param mixed                        $var8
	 * @param mixed                        $var9
	 * @param mixed                        $var10
	 * @return mixed
	 */
	public function getTrigger( $trigger, $event, &$var1 = null, &$var2 = null, &$var3 = null, &$var4 = null, &$var5 = null, &$var6 = null, &$var7 = null, &$var8 = null, &$var9 = null, &$var10 = null )
	{
		if ( is_integer( $trigger ) ) {
			$triggerId				=	$trigger;

			$trigger				=	new cbautoactionsActionTable();

			$trigger->load( $triggerId );
		}

		if ( $trigger->get( 'id' ) ) {
			$vars					=	array(	'trigger'	=>	$event,
												'var1'		=>	&$var1,
												'var2'		=>	&$var2,
												'var3'		=>	&$var3,
												'var4'		=>	&$var4,
												'var5'		=>	&$var5,
												'var6'		=>	&$var6,
												'var7'		=>	&$var7,
												'var8'		=>	&$var8,
												'var9'		=>	&$var9,
												'var10'		=>	&$var10
											);

			if ( $trigger->get( 'object' ) == 3 ) {
				$user				=	CBuser::getUserDataInstance( (int) $trigger->get( 'variable' ) );
			} elseif ( $trigger->get( 'object' ) == 2 ) {
				$user				=	CBuser::getMyUserDataInstance();
			} elseif ( $trigger->get( 'object' ) == 1 ) {
				$user				=	$this->prepareUser( ${ 'var' . (int) $trigger->get( 'variable' ) } );

				if ( $user->get( 'id' ) && $trigger->getParams()->get( 'reload', false, GetterInterface::BOOLEAN ) ) {
					$user->load( (int) $user->get( 'id' ) );
				}
			} else {
				$user				=	$this->getUser( $vars );

				if ( $user->get( 'id' ) && $trigger->getParams()->get( 'reload', false, GetterInterface::BOOLEAN ) ) {
					$user->load( (int) $user->get( 'id' ) );
				}
			}

			if ( $user->get( 'id' ) ) {
				$rawPassword		=	$this->input( 'post/passwd', null, GetterInterface::STRING );

				if ( ! $rawPassword ) {
					$rawPassword	=	$this->input( 'post/password', null, GetterInterface::STRING );
				}
			} else {
				$rawPassword		=	null;
			}

			return $this->getAction( $trigger, $user, $rawPassword, $vars );
		}

		return null;
	}

	/**
	 * Trys to parse the variables for a user object
	 *
	 * @param array $vars
	 * @return UserTable
	 */
	private function getUser( $vars )
	{
		$user				=	null;

		// Lets first try to find a user object:
		foreach ( $vars as $var ) {
			if ( is_object( $var ) && ( $var instanceof UserTable ) ) {
				$user		=	$var;
				break;
			}
		}

		// We failed to find a user object so lets try to parse for one:
		if ( ! $user ) {
			foreach ( $vars as $var ) {
				$var		=	$this->prepareUser( $var, false );

				if ( is_object( $var ) && ( $var instanceof UserTable ) ) {
					$user	=	$var;
					break;
				}
			}
		}

		if ( ! $user ) {
			$user			=	CBuser::getUserDataInstance( null );
		}

		return $user;
	}

	/**
	 * Trys to load a user object from a variable
	 *
	 * @param object|int $userVar
	 * @param boolean $fallback
	 * @return UserTable
	 */
	private function prepareUser( $userVar, $fallback = true )
	{
		if ( is_object( $userVar ) ) {
			if ( $userVar instanceof UserTable ) {
				$user		=	$userVar;
			} elseif ( isset( $userVar->user_id ) ) {
				$userId		=	(int) $userVar->user_id;
			} elseif ( isset( $userVar->user ) ) {
				$userId		=	(int) $userVar->user;
			} elseif ( isset( $userVar->id ) ) {
				$userId		=	(int) $userVar->id;
			}
		} elseif ( is_integer( $userVar ) ) {
			$userId			=	$userVar;
		}

		if ( isset( $userId ) && is_integer( $userId ) ) {
			$user			=	CBuser::getUserDataInstance( (int) $userId );

			if ( ( ! $user->get( 'id' ) ) && ( ! $fallback ) ) {
				$user		=	null;
			}
		}

		if ( ! isset( $user ) ) {
			if ( $fallback ) {
				$user		=	CBuser::getUserDataInstance( null );
			} else {
				$user		=	null;
			}
		}

		return $user;
	}

	/**
	 * Parses substitution extras array from available variables
	 *
	 * @param array $vars
	 * @return array
	 */
	private function getExtras( $vars = array() )
	{
		$extras							=	array();

		foreach ( $vars as $key => $var ) {
			if ( is_object( $var ) || is_array( $var ) ) {
				/** @var array|object $var */
				if ( is_object( $var ) ) {
					$paramsArray		=	get_object_vars( $var );
				} else {
					$paramsArray		=	$var;
				}

				$this->prepareExtras( $key, $paramsArray, $extras );
			} else {
				$extras[$key]			=	$var;
			}
		}

		$get							=	$this->getInput()->getNamespaceRegistry( 'get' );

		if ( $get ) {
			$this->prepareExtras( 'get', $get->asArray(), $extras );
		}

		$post							=	$this->getInput()->getNamespaceRegistry( 'post' );

		if ( $post ) {
			$this->prepareExtras( 'post', $post->asArray(), $extras );
		}

		$files							=	$this->getInput()->getNamespaceRegistry( 'files' );

		if ( $files ) {
			$this->prepareExtras( 'files', $files->asArray(), $extras );
		}

		$cookie							=	$this->getInput()->getNamespaceRegistry( 'cookie' );

		if ( $cookie ) {
			$this->prepareExtras( 'cookie', $cookie->asArray(), $extras );
		}

		$server							=	$this->getInput()->getNamespaceRegistry( 'server' );

		if ( $server ) {
			$this->prepareExtras( 'server', $server->asArray(), $extras );
		}

		$env							=	$this->getInput()->getNamespaceRegistry( 'env' );

		if ( $env ) {
			$this->prepareExtras( 'env', $env->asArray(), $extras );
		}

		return $extras;
	}

	/**
	 * Converts array or object into pathed extras substitutions
	 *
	 * @param string       $prefix
	 * @param array|object $items
	 * @param array        $extras
	 */
	private function prepareExtras( $prefix, $items, &$extras )
	{
		foreach ( $items as $k => $v ) {
			if ( is_array( $v ) ) {
				$multi					=	false;

				foreach ( $v as $kv => $cv ) {
					if ( is_numeric( $kv ) ) {
						$kv				=	(int) $kv;
					}

					if ( is_object( $cv ) || is_array( $cv ) || ( $kv && ( ! is_int( $kv ) ) ) ) {
						$multi			=	true;
					}
				}

				if ( ! $multi ) {
					$v					=	implode( '|*|', $v );
				}
			}

			$k							=	'_' . ltrim( str_replace( ' ', '_', trim( strtolower( $k ) ) ), '_' );

			if ( ( ! is_object( $v ) ) && ( ! is_array( $v ) ) ) {
				$extras[$prefix . $k]	=	$v;
			} elseif ( $v ) {
				if ( is_object( $v ) ) {
					$subItems			=	get_object_vars( $v );
				} else {
					$subItems			=	$v;
				}

				$this->prepareExtras( $prefix . $k, $subItems, $extras );
			}
		}
	}

	/**
	 * Executes the action
	 *
	 * @param cbautoactionsActionTable $trigger
	 * @param UserTable                $user
	 * @param string                   $rawPassword
	 * @param array                    $vars
	 * @param array                    $extras
	 * @return mixed
	 */
	public function getAction( $trigger, $user, $rawPassword = null, $vars = array(), $extras = array() )
	{
		$cbUser				=	new CBuser();
		$cbUser->_cbuser	=	$user;

		$extras				=	array_merge( $extras, $this->getExtras( $vars ) );

		$vars['self']		=	$trigger;
		$vars['user']		=	$user;

		$trigger->set( '_cbuser', $cbUser );
		$trigger->set( '_password', ( $rawPassword ? $rawPassword : $user->get( 'password' ) ) );
		$trigger->set( '_extras', $extras );
		$trigger->set( '_vars', $vars );

		if ( $user->get( 'id' ) ) {
			$gids			=	Application::User( (int) $user->get( 'id' ) )->getAuthorisedGroups( false );

			array_unshift( $gids, -3 );

			if ( Application::User( (int) $user->get( 'id' ) )->isGlobalModerator() ) {
				array_unshift( $gids, -5 );
			} else {
				array_unshift( $gids, -4 );
			}
		} else {
			$gids			=	$user->get( 'gids', array() );

			array_unshift( $gids, -2 );
		}

		array_unshift( $gids, -1 );

		$trigger->set( '_gids', $gids );

		$access				=	explode( '|*|', $trigger->get( 'access' ) );

		if ( ! array_intersect( $access, $gids ) ) {
			if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
				var_dump( CBTxt::T( 'AUTO_ACTION_ACCESS_FAILED', ':: Action [action] :: Access check for [user_id] failed: looking for [access] in [groups]', array( '[action]' => (int) $trigger->get( 'id' ), '[user_id]' => (int) $user->get( 'id' ), '[access]' => implode( ', ', $access ), '[groups]' => implode( ', ', $gids ) ) ) );
			}

			return null;
		}

		foreach ( $trigger->getConditions() as $i => $conditional ) {
			/** @var ParamsInterface $conditional */
			$condTranslate	=	$conditional->get( 'translate', 0, GetterInterface::BOOLEAN );
			$condField		=	$trigger->getSubstituteString( $conditional->get( 'field', null, GetterInterface::HTML ), true, $condTranslate );
			$condOperator	=	$conditional->get( 'operator', '0', GetterInterface::STRING );
			$condValue		=	$trigger->getSubstituteString( $conditional->get( 'value', null, GetterInterface::HTML ), true, $condTranslate );

			if ( ! cbautoactionsClass::getFieldMatch( $condField, $condOperator, $condValue, $vars ) ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_CONDITIONAL_FAILED', ':: Action [action] :: Conditional [cond] failed for [user_id]: [field] [operator] [value]', array( '[action]' => (int) $trigger->get( 'id' ), '[cond]' => ( $i + 1 ), '[user_id]' => (int) $user->get( 'id' ), '[field]' => cbautoactionsClass::formatCondition( $condField ), '[operator]' => cbautoactionsClass::getOperatorTitle( $condOperator ), '[value]' => cbautoactionsClass::formatCondition( $condValue ) ) ) );
				}

				return null;
			}
		}

		$excludeGlobal		=	explode( ',', $this->params->get( 'exclude', null, GetterInterface::STRING ) );
		$excludeTrigger		=	explode( ',', $trigger->getParams()->get( 'exclude', null, GetterInterface::STRING ) );
		$exclude			=	array_filter( array_merge( $excludeGlobal, $excludeTrigger ) );

		if ( $exclude ) {
			cbArrayToInts( $exclude );

			$exclude		=	array_unique( $exclude );

			if ( in_array( (int) $user->get( 'id' ), $exclude ) ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_USER_EXCLUDED', ':: Action [action] :: User [user_id] excluded', array( '[action]' => (int) $trigger->get( 'id' ), '[user_id]' => (int) $user->get( 'id' ) ) ) );
				}

				return null;
			}
		}

		return $trigger->call( 'execute', array( $user ) );
	}

	/**
	 * Returns the internal general URL for firing internal general actions
	 *
	 * @return string
	 */
	public function loadInternalGeneralURL()
	{
		global $_CB_framework;

		return '<a href="' . $_CB_framework->pluginClassUrl( 'cbautoactions', true, array( 'action' => 'general', 'token' => md5( $_CB_framework->getCfg( 'secret' ) ) ), 'raw', 0, true ) . '" target="_blank">' . CBTxt::T( 'Click to Process' ) . '</a>';
	}

	/**
	 * Returns the internal users URL for firing internal users actions
	 *
	 * @return string
	 */
	public function loadInternalUsersURL()
	{
		global $_CB_framework;

		return '<a href="' . $_CB_framework->pluginClassUrl( 'cbautoactions', true, array( 'action' => 'users', 'token' => md5( $_CB_framework->getCfg( 'secret' ) ) ), 'raw', 0, true ) . '" target="_blank">' . CBTxt::T( 'Click to Process' ) . '</a>';
	}
}