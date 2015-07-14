<?php
/**
 * @version $Id: cbpaidErrorHandler.php 1569 2012-12-23 01:35:57Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Specific CBSubs Error handling for logging purposes
 */
class cbpaidErrorHandler {
	/**
	 * @var int  To avoid initializing it more than once by error (shouldn't happen): Use ::install(), keep track of number of installs:
	 */
	protected static $handlerInitialized		=	0;
	/**
	 * @var boolean If handler is active or not: Use ::on() or ::off()
	 */
	protected static $handlerOff				=	true;
	protected static $previousErrorHandler		=	array();
	protected static $alwaysOn					=	false;
	/**
	 * Initialize the PHP error handler with the CBSubs callable handler
	 * Memorizes previous handler to restore it when calling ::uninstall()
	 *
	 * @return void
	 */
	public static function install( ) {
		self::$previousErrorHandler[self::$handlerInitialized++]	=	set_error_handler( array( __CLASS__, '_error_handler_callable' ) );
	}
	/**
	 * Restores the previous PHP error handler memorized when calling ::install()
	 *
	 * @return void
	 */
	public static function uninstall( ) {
		// Safeguard in case it's called too much:
		if ( self::$handlerInitialized && ! self::$alwaysOn ) {
			$previousHandler	=	self::$previousErrorHandler[--self::$handlerInitialized];

			// PHP <5.5 does not accept NULL to restore system handler, but as our error handler returns false when off, it goes to system still: So check for not null (fixes bug #3896):
			if ( $previousHandler !== null ) {
				set_error_handler( $previousHandler );
			}
		}
	}
	/**
	 * Turn error handling inside CBSubs ON
	 *
	 * @return void
	 */
	public static function on( ) {
		self::$handlerOff						=	false;
	}
	/**
	 * Turn error handling inside CBSubs OFF
	 *
	 * @return void
	 */
	public static function off( ) {
		if ( ! self::$alwaysOn ) {
			self::$handlerOff					=	true;
		}
	}
	/**
	 * Keep error handling inside CBSubs always ON until end of execution
	 * This is useful to catch any errors in raw mode, after e.g. an IPN
	 *
	 * @return void
	 */
	public static function keepTurnedOn( ) {
		self::$alwaysOn							=	true;
	}
	/**
	 * Error Handling function of CBSubs to give as argument for set_error_handler
	 * @deprecated : Use cbpaidErrorHandler::init() to set it.
	 *
	 * @param $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 * @return bool
	 */
	public static function _error_handler_callable( $errno, $errstr = '', $errfile = '', $errline = '' ) {
		if ( self::$handlerOff || ( defined( 'E_STRICT' ) && ( $errno == constant( 'E_STRICT' ) ) ) ) {
			return false;
		}

		global $_CB_framework, $_CB_database;

		$cfg['adminEmail']	=	null;

		// if error has been supressed with an @
		if ( error_reporting() == 0 ) {
			return false;
		}

		// check if function has been called by an exception
		if(func_num_args() == 5) {
			// called by trigger_error()
			list($errno, $errstr, $errfile, $errline) = func_get_args();

			$backtrace = debug_backtrace();

		}else {
			// caught exception
			/** @var $exc Exception */
			$exc = func_get_arg(0);
			$errno = $exc->getCode();
			$errstr = $exc->getMessage();
			$errfile = $exc->getFile();
			$errline = $exc->getLine();

			$backtrace = array_reverse( $exc->getTrace() );
		}

		$errorType = array (
			E_ERROR          => 'ERROR',
			E_WARNING        => 'WARNING',
			E_PARSE          => 'PARSING ERROR',
			E_NOTICE         => 'NOTICE',
			E_CORE_ERROR     => 'CORE ERROR',
			E_CORE_WARNING   => 'CORE WARNING',
			E_COMPILE_ERROR  => 'COMPILE ERROR',
			E_COMPILE_WARNING => 'COMPILE WARNING',
			E_USER_ERROR     => 'USER ERROR',
			E_USER_WARNING   => 'USER WARNING',
			E_USER_NOTICE    => 'USER NOTICE' );
		if ( defined( 'E_STRICT' ) ) {						// php 5
			$errorType[E_STRICT]	 =	'STRICT NOTICE';
		}
		if ( defined( 'E_RECOVERABLE_ERROR' ) ) {			// php 5.1.6 + 5.2.x
			$errorType[E_RECOVERABLE_ERROR]	 =	'E_RECOVERABLE_ERROR';
		}
		$errorPriority = array (							//(UNIX-type): 0: Emergency, 1: Alert, 2: Critical, 3: Error, 4: Warning, 5: Notice, 6: Info, 7: Debug
			E_ERROR          => 1,
			E_WARNING        => 4,
			E_PARSE          => 1,
			E_NOTICE         => 5,
			E_CORE_ERROR     => 1,
			E_CORE_WARNING   => 4,
			E_COMPILE_ERROR  => 1,
			E_COMPILE_WARNING => 4,
			E_USER_ERROR     => 1,
			E_USER_WARNING   => 4,
			E_USER_NOTICE    => 5 );
		if ( defined( 'E_STRICT' ) ) {
			$errorPriority[E_STRICT]			=	6;
		}
		if ( defined( 'E_RECOVERABLE_ERROR' ) ) {
			$errorPriority[E_RECOVERABLE_ERROR]	=	6;
		}

		// create error message
		if (array_key_exists($errno, $errorType)) {
			$err = $errorType[$errno];
		} else {
			$err = 'CAUGHT EXCEPTION';
		}

		$errMsg = $err . ': ' . $errstr . ' in ' . $errfile . ' on line ' . $errline;

		// start backtrace:
		$trace		=	'';
		foreach ($backtrace as $v) {
			if (isset($v['class'])) {

				$trace .= 'called in class '.$v['class'].'::'.$v['function'].'(';

				if (isset($v['args'])) {
					$separator = '';

					foreach($v['args'] as $arg ) {
						$trace .= $separator . self::cbpaidGetArgument($arg);
						$separator = ', ';
					}
				}
				$trace .= ')';
				if ( isset( $v['line'] ) ) {
					$trace		.=	' on line ' . $v['line'];
				}
				if ( isset( $v['file'] ) ) {
					$trace		.=	' in file ' . substr( strrchr( $v['file'], '/' ), 1 );
				}

			} elseif (isset($v['function'])) {
				if ( strtolower( $v['function'] ) != strtolower( __FUNCTION__ ) ) {
					$trace .= 'called in function '.$v['function'].'(';
					if (!empty($v['args'])) {

						$separator = '';

						foreach($v['args'] as $arg ) {
							$trace .= $separator . self::cbpaidGetArgument($arg);
							$separator = ', ';
						}
					}
					$trace .= ')';
					if ( isset( $v['line'] ) ) {
						$trace		.=	' on line ' . $v['line'];
					}
					if ( isset( $v['file'] ) ) {
						$trace		.=	' in file ' . substr( strrchr( $v['file'], '/' ), 1 );
					}
				}
			} else {
				$trace	.=	'????';
				$trace	.=	'::::::' . var_export( $v, true );
			}
			$trace	.=	"\n";
		}
		$trace		.=	'$_GET = ' . var_export( $_GET, true ) . "\n";
		$trace		.=	'$_POST = ' . var_export( $_POST, true ) . "\n";

		$errorText	=	$errMsg . "\n"
			.	'Trace:'
			.	$trace . "\n";

		// display error msg, if debug is enabled
		if( $_CB_framework->getCfg( 'debug' ) ) {
			if ( defined( 'E_STRICT' ) && ( $errno != constant( 'E_STRICT' ) ) ) {
				echo '<h2>CBPaid Debug Message</h2>'.nl2br($errMsg).'<br />
    	        Trace:'.nl2br($trace).'<br />';
			}
		}

		// what to do
		switch ($errno) {
			//    	case E_STRICT:	break;		// only if it's defined (php 4 compatibility)
			//        case E_NOTICE:
			//        case E_USER_NOTICE:
			//        	break;

			default:
				if ( array_key_exists( $errno, $errorPriority ) ) {
					$priority	=	$errorPriority[$errno];
				} else {
					$priority	=	7;
				}
				$log			=	new cbpaidHistory( $_CB_database );

				$errorTextForMe	=	( strpos( $errorText, 'cbpaidsubscriptions' ) !== false ) && ( strpos( $errorText, 'parainvite' ) === false );
				if ( $errorTextForMe ) {
					$log->logError( $priority, $errorText, null );
				}

				if ( TRUE || ! $_CB_framework->getCfg( 'debug' ) ){
					// send email to admin
					if ( $errorTextForMe && ! empty( $cfg['adminEmail'] ) ) {
						$params	=&	cbpaidApp::settingsParams();
						$licensee_name	=	$params->get( 'licensee_name' );
						@mail($cfg['adminEmail'], cbpaidApp::version() . ' error on '.$_SERVER['HTTP_HOST'].' customer:'.$licensee_name, 'CBPaid Debug Message: ' . $errorText,
							'From: error_handler');
					}
					if ( $priority == 1 ) {
						// end and display error msg
						exit( self::cbDisplayClientMessage() );
					}
				} else {
					exit('<p>aborting.</p>');
				}
				break;
		}
		return false;
	} // end of errorHandler()
	/**
	 * Error handler: Displays a user fatal error without any translations
	 *
	 * @return null
	 */
	protected static function cbDisplayClientMessage()
	{
		echo 'Fatal Error please contact admin so he can check his history log for errors.';
		return null;
	}
	/**
	 * Error handler: Displays $arg according to his type
	 *
	 * @param  mixed $arg
	 * @return string
	 */
	protected static function cbpaidGetArgument($arg)
	{
		switch (strtolower(gettype($arg))) {

			case 'string':
				return( '"'.str_replace( array("\n"), array(''), $arg ).'"' );

			case 'boolean':
				return ( $arg ? 'true' : 'false' );

			case 'object':
				return 'object('.get_class($arg).')';

			case 'array':
				$ret = 'array(';
				$separator = '';

				foreach ($arg as $k => $v) {
					$ret .= $separator . self::cbpaidGetArgument($k).' => ' . self::cbpaidGetArgument($v);
					$separator = ', ';
				}
				$ret .= ')';

				return $ret;

			case 'resource':
				return 'resource('.get_resource_type($arg).')';

			default:
				return var_export($arg, true);
		}
	}
}