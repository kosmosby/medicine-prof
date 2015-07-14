<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

define('PLUPLOAD_MOVE_ERR', 103);
define('PLUPLOAD_INPUT_ERR', 101);
define('PLUPLOAD_OUTPUT_ERR', 102);
define('PLUPLOAD_TMPDIR_ERR', 100);
define('PLUPLOAD_TYPE_ERR', 104);
define('PLUPLOAD_UNKNOWN_ERR', 111);
define('PLUPLOAD_SECURITY_ERR', 105);

class PluploadHandler
{

	public static $conf;

	private static $_error = null;

	private static $_errors = array(
		PLUPLOAD_MOVE_ERR     => "Failed to move uploaded file.",
		PLUPLOAD_INPUT_ERR    => "Failed to open input stream.",
		PLUPLOAD_OUTPUT_ERR   => "Failed to open output stream.",
		PLUPLOAD_TMPDIR_ERR   => "Failed to open temp directory.",
		PLUPLOAD_TYPE_ERR     => "File type not allowed.",
		PLUPLOAD_UNKNOWN_ERR  => "Failed due to unknown error.",
		PLUPLOAD_SECURITY_ERR => "File didn't pass security check."
	);

	
	static function get_error_code()
	{
		if (!self::$_error)
		{
			return null;
		}

		if (!isset(self::$_errors[self::$_error]))
		{
			return PLUPLOAD_UNKNOWN_ERR;
		}

		return self::$_error;
	}

	
	static function get_error_message()
	{
		if ($code = self::get_error_code())
		{
			return self::$_errors[$code];
		}

		return '';
	}

	
	static function handle($conf = array())
	{
		
		@set_time_limit(5 * 60);

		self::$_error = null; 

		$conf = self::$conf = array_merge(array(
			'file_data_name'        => 'file',
			'tmp_dir'               => ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload",
			'target_dir'            => false,
			'cleanup'               => true,
			'max_file_age'          => 5 * 3600,
			'chunk'                 => isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0,
			'chunks'                => isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0,
			'file_name'             => isset($_REQUEST['name']) ? $_REQUEST['name'] : false,
			'allow_extensions'      => false,
			'delay'                 => 0,
			'cb_sanitize_file_name' => array(__CLASS__, 'sanitize_file_name'),
			'cb_check_file'         => false,
		), $conf);

		try
		{
			if (!$conf['file_name'])
			{
				if (!empty($_FILES))
				{
					$conf['file_name'] = $_FILES[$conf['file_data_name']]['name'];
				}
				else
				{
					throw new Exception('', PLUPLOAD_INPUT_ERR);
				}
			}

			
			if ($conf['cleanup'])
			{
				self::cleanup();
			}

			
			if ($conf['delay'])
			{
				usleep($conf['delay']);
			}

			if (is_callable($conf['cb_sanitize_file_name']))
			{
				$file_name = call_user_func($conf['cb_sanitize_file_name'], $conf['file_name']);
			}
			else
			{
				$file_name = $conf['file_name'];
			}

			
			if ($conf['allow_extensions'])
			{
				if (is_string($conf['allow_extensions']))
				{
					$conf['allow_extensions'] = preg_split('{\s*,\s*}', $conf['allow_extensions']);
				}

				if (!in_array(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)), $conf['allow_extensions']))
				{
					throw new Exception('', PLUPLOAD_TYPE_ERR);
				}
			}

			$file_path = rtrim($conf['target_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file_name;
			$tmp_path  = $file_path . ".part";

			
			if ($conf['chunks'])
			{
				self::write_file_to("$file_path.dir.part" . DIRECTORY_SEPARATOR . $conf['chunk']);

				
				if ($conf['chunk'] == $conf['chunks'] - 1)
				{
					self::write_chunks_to_file("$file_path.dir.part", $tmp_path);
				}
			}
			else
			{
				self::write_file_to($tmp_path);
			}

			
			if (!$conf['chunks'] || $conf['chunk'] == $conf['chunks'] - 1)
			{
				if (is_callable($conf['cb_check_file']) && !call_user_func($conf['cb_check_file'], $tmp_path))
				{
					@unlink($tmp_path);
					throw new Exception('', PLUPLOAD_SECURITY_ERR);
				}

				rename($tmp_path, $file_path);

				return array(
					'name' => $file_name,
					'path' => $file_path,
					'size' => filesize($file_path)
				);
			}

			
			return true;

		}
		catch (Exception $ex)
		{
			self::$_error = $ex->getCode();

			return false;
		}
	}

	
	static function write_file_to($file_path, $file_data_name = false)
	{
		if (!$file_data_name)
		{
			$file_data_name = self::$conf['file_data_name'];
		}

		$base_dir = dirname($file_path);
		if (!file_exists($base_dir) && !@mkdir($base_dir, 0777, true))
		{
			throw new Exception('', PLUPLOAD_TMPDIR_ERR);
		}

		if (!empty($_FILES) && isset($_FILES[$file_data_name]))
		{
			if ($_FILES[$file_data_name]["error"] || !is_uploaded_file($_FILES[$file_data_name]["tmp_name"]))
			{
				throw new Exception('', PLUPLOAD_MOVE_ERR);
			}
			move_uploaded_file($_FILES[$file_data_name]["tmp_name"], $file_path);
		}
		else
		{
			
			if (!$in = @fopen("php://input", "rb"))
			{
				throw new Exception('', PLUPLOAD_INPUT_ERR);
			}

			if (!$out = @fopen($file_path, "wb"))
			{
				throw new Exception('', PLUPLOAD_OUTPUT_ERR);
			}

			while ($buff = fread($in, 4096))
			{
				fwrite($out, $buff);
			}

			@fclose($out);
			@fclose($in);
		}
	}

	
	static function write_chunks_to_file($chunk_dir, $file_path)
	{
		if (!$out = @fopen($file_path, "wb"))
		{
			throw new Exception('', PLUPLOAD_OUTPUT_ERR);
		}

		for ($i = 0; $i < self::$conf['chunks']; $i++)
		{
			$chunk_path = $chunk_dir . DIRECTORY_SEPARATOR . $i;
			if (!file_exists($chunk_path))
			{
				throw new Exception('', PLUPLOAD_MOVE_ERR);
			}

			if (!$in = @fopen($chunk_path, "rb"))
			{
				throw new Exception('', PLUPLOAD_INPUT_ERR);
			}

			while ($buff = fread($in, 4096))
			{
				fwrite($out, $buff);
			}
			@fclose($in);

			
			@unlink($chunk_path);
		}
		@fclose($out);

		
		self::rrmdir($chunk_dir);
	}

	static function no_cache_headers()
	{
		
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	static function cors_headers($headers = array(), $origin = '*')
	{
		$allow_origin_present = false;

		if (!empty($headers))
		{
			foreach ($headers AS $header => $value)
			{
				if (strtolower($header) == 'access-control-allow-origin')
				{
					$allow_origin_present = true;
				}
				header("$header: $value");
			}
		}

		if ($origin && !$allow_origin_present)
		{
			header("Access-Control-Allow-Origin: $origin");
		}

		
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
		{
			exit; 
		}
	}

	private static function cleanup()
	{
		
		if (file_exists(self::$conf['target_dir']))
		{
			foreach (glob(self::$conf['target_dir'] . '/*.part') AS $tmpFile)
			{
				if (time() - filemtime($tmpFile) < self::$conf['max_file_age'])
				{
					continue;
				}
				if (is_dir($tmpFile))
				{
					self::rrmdir($tmpFile);
				}
				else
				{
					@unlink($tmpFile);
				}
			}
		}
	}

	
	private static function sanitize_file_name($filename)
	{
		$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
		$filename      = str_replace($special_chars, '', $filename);
		$filename      = preg_replace('/[\s-]+/', '-', $filename);
		$filename      = trim($filename, '.-_');

		return $filename;
	}

	
	private static function rrmdir($dir)
	{
		foreach (glob($dir . '/*') AS $file)
		{
			if (is_dir($file))
			{
				self::rrmdir($file);
			}
			else
			{
				unlink($file);
			}
		}
		rmdir($dir);
	}
}

