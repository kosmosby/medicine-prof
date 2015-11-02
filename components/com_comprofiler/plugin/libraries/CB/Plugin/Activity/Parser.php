<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\Activity;

use CB\Database\Table\UserTable;
use CBLib\Application\Application;
use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\Registry;

defined('CBLIB') or die();

class Parser
{
	/** @var array  */
	protected $regexp		=	array(	'hashtag'	=>	'/^#(\w+)$/i',
										'profile'	=>	'/^@(\w+)$/i',
										'link'		=>	'#^((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))$#i',
										'email'		=>	'/^[a-z0-9!#$%&\'*+\\\\\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\\\\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i'
									);
	/** @var string  */
	protected $string		=	null;
	/** @var string  */
	protected $parsed		=	null;
	/** @var array  */
	protected $words		=	array();
	/** @var Registry[]  */
	protected $attachments	=	array();

	/**
	 * Constructor
	 *
	 * @param string $string
	 */
	public function __construct( $string )
	{
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user' );

		$this->string	=	$string;
		$this->parsed	=	$string;
		$this->words	=	preg_split( '/\s/i', $string );
	}

	/**
	 * Returns the original unmodified string
	 *
	 * @return string
	 */
	public function original()
	{
		return $this->string;
	}

	/**
	 * Replaces :EMOTE: with emote icons
	 *
	 * @return string
	 */
	public function emotes()
	{
		$this->parsed	=	strtr( $this->parsed, CBActivity::loadEmoteOptions( true ) );

		return $this->parsed;
	}

	/**
	 * Replaces #HASHTAG with stream filter urls
	 *
	 * @return string
	 */
	public function hashtags()
	{
		global $_CB_framework;

		foreach ( $this->words as $word ) {
			if ( preg_match( $this->regexp['hashtag'], $word, $match ) ) {
				$this->parsed	=	str_replace( $word, '<a href="' . $_CB_framework->pluginClassUrl( 'cbactivity', true, array( 'action' => 'activity', 'func' => 'show', 'hashtag' => $match[1] ) ) . '" rel="nofollow">' . htmlspecialchars( $match[0] ) . '</a>', $this->parsed );
			}
		}

		return $this->parsed;
	}

	/**
	 * Replaces @MENTION with profile urls
	 *
	 * @return string
	 */
	public function profiles()
	{
		global $_CB_database, $_CB_framework;

		/** @var UserTable[] $users */
		static $users						=	array();

		foreach ( $this->words as $word ) {
			if ( preg_match( $this->regexp['profile'], $word, $match ) ) {
				$cleanWord					=	Get::clean( $match[1], GetterInterface::STRING );

				if ( ! isset( $users[$cleanWord] ) ) {
					$users[$cleanWord]		=	new UserTable();

					if ( is_numeric( $match[1] ) ) {
						$users[$cleanWord]	=	\CBuser::getUserDataInstance( (int) $match[1] );
					} else {
						$query				=	'SELECT c.*, u.*'
											.	"\n FROM " . $_CB_database->NameQuote( '#__users' ) . " AS u"
											.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
											.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = u.' . $_CB_database->NameQuote( 'id' )
											.	"\n WHERE ( u." . $_CB_database->NameQuote( 'username' ) . ' = ' . $_CB_database->Quote( $cleanWord )													// Match username exactly
											.	' OR u.' . $_CB_database->NameQuote( 'name' ) . ' = ' . $_CB_database->Quote( $cleanWord )																// Match name exactly
											.	' OR u.' . $_CB_database->NameQuote( 'username' ) . ' LIKE ' . $_CB_database->Quote( $_CB_database->getEscaped( $cleanWord, true ) . '%', false )		// Match first half of username (encase of space)
											.	' OR u.' . $_CB_database->NameQuote( 'name' ) . ' LIKE ' . $_CB_database->Quote( $_CB_database->getEscaped( $cleanWord, true ) . '%', false ) . ' )';	// Match first half of name (encase of space)
						$_CB_database->setQuery( $query );
						$_CB_database->loadObject( $users[$cleanWord] );
					}
				}

				$user						=	$users[$cleanWord];

				if ( $user->get( 'id' ) ) {
					$this->parsed			=	str_replace( array( '@' . $user->get( 'id' ), '@' . $user->get( 'name' ), '@' . $user->get( 'username' ), $word ), '<a href="' . $_CB_framework->userProfileUrl( (int) $user->get( 'id' ) ) . '" rel="nofollow">@' . htmlspecialchars( getNameFormat( $user->get( 'name' ), $user->get( 'username' ), Application::Config()->get( 'name_format' ) ) ) . '</a>', $this->parsed );
				}
			}
		}

		return $this->parsed;
	}

	/**
	 * Replaces URLs with clickable html URLs
	 *
	 * @return string
	 */
	public function links()
	{
		foreach ( $this->words as $word ) {
			if ( preg_match( $this->regexp['link'], $word, $match ) ) {
				$this->parsed	=	str_replace( $word, '<a href="' . htmlspecialchars( $match[0] ) . '" rel="nofollow"' . ( ! \JUri::isInternal( $match[0] ) ? ' target="_blank"' : null ) . '>' . htmlspecialchars( $match[0] ) . '</a>', $this->parsed );
			} elseif ( preg_match( $this->regexp['email'], $word, $match ) ) {
				$this->parsed	=	str_replace( $word, '<a href="mailto:' . htmlspecialchars( $match[0] ) . '" rel="nofollow" target="_blank">' . htmlspecialchars( $match[0] ) . '</a>', $this->parsed );
			}
		}

		return $this->parsed;
	}

	/**
	 * Replaces linebreaks with html breaks
	 *
	 * @return string
	 */
	public function linebreaks()
	{
		$this->parsed	=	str_replace( array( "\n", "\r\n" ), '<br />', $this->parsed );

		return $this->parsed;
	}

	/**
	 * Replaces duplicate or bad content
	 *
	 * @return string
	 */
	public function clean()
	{
		// Remove duplicate spaces:
		$this->parsed		=	preg_replace( '/ {2,}/i', ' ', $this->parsed );

		// Remove duplicate tabs:
		$this->parsed		=	preg_replace( '/\t{2,}/i', "\t", $this->parsed );

		// Remove duplicate linebreaks:
		$this->parsed		=	preg_replace( '/(\r\n|\r|\n){2,}/i', '$1', $this->parsed );

		return $this->parsed;
	}

	/**
	 * Parsers urls for their media information
	 *
	 * @return Registry[]
	 */
	public function attachments()
	{
		foreach ( $this->words as $word ) {
			if ( preg_match( $this->regexp['link'], $word, $match ) ) {
				$this->attachment( $match[0] );
			}
		}

		return $this->attachments;
	}

	/**
	 * Parsers a url for media information
	 *
	 * @param string $url
	 * @return null|Registry
	 */
	public function attachment( $url )
	{
		global $_CB_framework;

		$attachment												=	null;

		if ( $url ) {
			$cachePath											=	$_CB_framework->getCfg( 'absolute_path' ) . '/cache/activity_links';
			$cache												=	$cachePath . '/' . md5( $url );

			if ( file_exists( $cache ) ) {
				if ( ( ( $_CB_framework->getUTCNow() - filemtime( $cache ) ) / 3600 ) > 24 ) {
					$request									=	true;
				} else {
					$attachment									=	trim( file_get_contents( $cache ) );
					$request									=	false;
				}
			} else {
				$request										=	true;
			}

			if ( $request ) {
				$client											=	@new \GuzzleHttp\Client();

				try {
					$result										=	$client->get( $url );

					if ( $result->getStatusCode() == 200 ) {
						$attachment['type']						=	'url';
						$attachment['title']					=	array();
						$attachment['description']				=	array();
						$attachment['media']					=	array( 'video' => array(), 'audio' => array(), 'image' => array() );
						$attachment['url']						=	$url;

						$document								=	@new \DOMDocument();

						@$document->loadHTML( (string) $result->getBody() );

						$xpath									=	@new \DOMXPath( $document );

						$paths									=	array(	'title'			=>	array(	'//meta[@name="og:title"]/@content',
																										'//meta[@name="twitter:title"]/@content',
																										'//meta[@name="title"]/@content',
																										'//meta[@property="og:title"]/@content',
																										'//meta[@property="twitter:title"]/@content',
																										'//meta[@property="title"]/@content',
																										'//title'
																									),
																			'description'	=>	array(	'//meta[@name="og:description"]/@content',
																										'//meta[@name="twitter:description"]/@content',
																										'//meta[@name="description"]/@content',
																										'//meta[@property="og:description"]/@content',
																										'//meta[@property="twitter:description"]/@content',
																										'//meta[@property="description"]/@content'
																									),
																			'media'			=>	array(	'video'	=>	array(	'//meta[@name="og:video"]/@content',
																															'//meta[@name="og:video:url"]/@content',
																															'//meta[@name="twitter:player"]/@content',
																															'//meta[@property="og:video"]/@content',
																															'//meta[@property="og:video:url"]/@content',
																															'//meta[@property="twitter:player"]/@content',
																															'//video/@src'
																														),
																										'audio'	=>	array(	'//meta[@name="og:audio"]/@content',
																															'//meta[@name="og:audio:url"]/@content',
																															'//meta[@property="og:audio"]/@content',
																															'//meta[@property="og:audio:url"]/@content',
																															'//audio/@src'
																														),
																										'image'	=>	array(	'//meta[@name="og:image"]/@content',
																															'//meta[@name="og:image:url"]/@content',
																															'//meta[@name="twitter:image"]/@content',
																															'//meta[@name="image"]/@content',
																															'//meta[@property="og:image"]/@content',
																															'//meta[@property="og:image:url"]/@content',
																															'//meta[@property="twitter:image"]/@content',
																															'//meta[@property="image"]/@content',
																															'//img/@src'
																														)
																									)
																		);

						foreach ( $paths as $item => $itemPaths ) {
							foreach ( $itemPaths as $subItem => $itemPath ) {
								if ( $item == 'media' ) {
									foreach ( $itemPath as $subItemPath ) {
										$nodes										=	@$xpath->query( $subItemPath );

										if ( ( $nodes !== false ) && $nodes->length ) {
											foreach ( $nodes as $node ) {
												if ( preg_match( $this->regexp['link'], $node->nodeValue ) ) {
													$itemDomain						=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $node->nodeValue, PHP_URL_HOST ) );
													$itemExt						=	strtolower( pathinfo( $node->nodeValue, PATHINFO_EXTENSION ) );

													if ( in_array( $itemDomain, array( 'youtube', 'youtu' ) ) ) {
														$itemMimeType				=	'video/youtube';
													} else {
														if ( $itemExt == 'm4v' ) {
															$itemMimeType			=	'video/mp4';
														} elseif ( $itemExt == 'mp3' ) {
															$itemMimeType			=	'audio/mp3';
														} elseif ( $itemExt == 'm4a' ) {
															$itemMimeType			=	'audio/mp4';
														} else {
															$itemMimeType			=	cbGetMimeFromExt( $itemExt );

															if ( $itemMimeType == 'application/octet-stream' ) {
																continue;
															}
														}
													}

													$attachment[$item][$subItem][]	=	array( 'url' => $node->nodeValue, 'mimetype' => $itemMimeType, 'extension' => $itemExt );
												}
											}
										}
									}
								} else {
									$nodes											=	@$xpath->query( $itemPath );

									if ( ( $nodes !== false ) && $nodes->length ) {
										foreach ( $nodes as $node ) {
											$value									=	false;

											if ( function_exists( 'mb_detect_encoding' ) && function_exists( 'iconv' ) ) {
												$encoding							=	mb_detect_encoding( $node->nodeValue, mb_detect_order(), true );

												if ( $encoding !== false ) {
													$value							=	iconv( $encoding, 'UTF-8', $node->nodeValue );
												}
											}

											if ( $value === false ) {
												$value								=	utf8_decode( utf8_encode( $node->nodeValue ) );
											}

											$attachment[$item][]					=	$value;
										}
									}
								}
							}
						}

						$urlDomain								=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $url, PHP_URL_HOST ) );
						$urlExt									=	strtolower( pathinfo( $url, PATHINFO_EXTENSION ) );

						if ( in_array( $urlDomain, array( 'youtube', 'youtu' ) ) ) {
							$urlMimeType						=	'video/youtube';
						} else {
							if ( $urlExt == 'm4v' ) {
								$urlMimeType					=	'video/mp4';
							} elseif ( $urlExt == 'mp3' ) {
								$urlMimeType					=	'audio/mp3';
							} elseif ( $urlExt == 'm4a' ) {
								$urlMimeType					=	'audio/mp4';
							} else {
								$urlMimeType					=	cbGetMimeFromExt( $urlExt );
							}
						}

						if ( in_array( $urlDomain, array( 'youtube', 'youtu' ) ) || in_array( $urlExt, array( 'mp4', 'ogv', 'ogg', 'webm', 'm4v' ) ) ) {
							$attachment['media']['video'][]		=	array( 'url' => $url, 'mimetype' => $urlMimeType, 'extension' => $urlExt );
							$attachment['type']					=	'video';
						} elseif ( in_array( $urlExt, array( 'mp3', 'oga', 'ogg', 'weba', 'wav', 'm4a' ) ) ) {
							$attachment['media']['audio'][]		=	array( 'url' => $url, 'mimetype' => $urlMimeType, 'extension' => $urlExt );
							$attachment['type']					=	'audio';
						} elseif ( in_array( $urlExt, array( 'jpg', 'jpeg', 'gif', 'png' ) ) ) {
							$attachment['media']['image'][]		=	array( 'url' => $url, 'mimetype' => $urlMimeType, 'extension' => $urlExt );
							$attachment['type']					=	'image';
						}
					}
				} catch( \Exception $e ) {}

				$attachment										=	json_encode( $attachment );

				if ( ! is_dir( $cachePath ) ) {
					$oldMask									=	@umask( 0 );

					if ( @mkdir( $cachePath, 0755, true ) ) {
						@umask( $oldMask );
						@chmod( $cachePath, 0755 );
					} else {
						@umask( $oldMask );
					}
				}

				file_put_contents( $cache, $attachment );
			}

			if ( $attachment ) {
				$attachment										=	new Registry( $attachment );

				$this->attachments[]							=	$attachment;
			} else {
				$attachment										=	null;
			}
		}

		return $attachment;
	}

	/**
	 * Runs string through all parsers
	 *
	 * @param array $ignore
	 * @return null|string
	 */
	public function parse( $ignore = array() )
	{
		global $_PLUGINS;

		if ( ! $this->string ) {
			return null;
		}

		if ( ! in_array( 'emotes', $ignore ) ) {
			$this->emotes();
		}

		if ( ! in_array( 'hashtags', $ignore ) ) {
			$this->hashtags();
		}

		if ( ! in_array( 'profiles', $ignore ) ) {
			$this->profiles();
		}

		if ( ! in_array( 'links', $ignore ) ) {
			$this->links();
		}

		if ( ! in_array( 'clean', $ignore ) ) {
			$this->clean();
		}

		if ( ! in_array( 'linebreaks', $ignore ) ) {
			$this->linebreaks();
		}

		$_PLUGINS->trigger( 'activity_onParse', array( &$this, $ignore ) );

		return $this->parsed;
	}
}