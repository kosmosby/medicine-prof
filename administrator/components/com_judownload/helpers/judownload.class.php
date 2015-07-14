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











if (!class_exists('JUDownload', false))
{
	class JUDownload
	{

		var $error = false;
		var $is_resume = false;
		var $download_speed = 1048576; 
		var $served = false;
		var $user_mime_type = false;
		var $maxMB = 1;
		var $logdir = false;

		public function __construct($file)
		{

			if ($this->served)
			{
				return;
			}
			
			$this->file = $file;
		}

		public function resume()
		{

			if ($this->served)
			{
				return;
			}
			$this->is_resume = true;
		}

		public function speed($kb)
		{

			if (($this->served) or (!isset($kb)) or (!$kb))
			{
				return;
			}
			$this->download_speed = (integer) $kb * 1024;
		}

		public function rename($filename)
		{

			if (($this->served) or (!isset($filename)) or (!strlen($filename)))
			{
				return;
			}
			$this->save_name = $filename;
		}

		
		public function mime($type)
		{

			
			if (($this->served) or (!$type) or (is_array($type)))
			{
				return;
			}
			$this->user_mime_type = $type;
		}

		public function error()
		{

			return $this->error;
		}

		
		public function log($dir, $maxMB = 1)
		{

			$dir = preg_replace('/\/|\\\/i', '/', $dir);
			$dir = preg_replace('/\/$/', '', $dir) . '/';

			if (!is_writable($dir))
			{
				trigger_error("cannot access directory or is not writable $dir!", E_USER_ERROR);
			}
			$this->logdir = $dir;
			if ((integer) $maxMB)
			{
				$this->maxMB = (integer) $maxMB;
			}
		}

		private function log_it()
		{

			
			
			

			$inc = 1;
			$EOL = PHP_EOL;

			
			while (true)
			{
				$logfile = $this->logdir . 'downloads' . $inc . '.log';
				if (!is_file($logfile))
				{
					break;
				}
				else
				{
					if (filesize($logfile) < $this->maxMB * 1024 * 1024)
					{
						break;
					}
				}
				$inc++;
				if ($inc > 100000)
				{
					break;
				} 
			}
			if ($inc > 100000)
			{
				return;
			}

			
			$line = "$this->now\t" . $_SERVER['REMOTE_ADDR'] . "\t$this->file";
			if (!isset($_SERVER['HTTP_RANGE']))
			{
				$line .= "\tFull download.$EOL";
			}
			else
			{
				$line .= "\tRange: " . $_SERVER['HTTP_RANGE'];
			}
			if ($this->error)
			{
				$line .= $EOL . "Download error: $this->error";
			}

			
			if (!$fh = fopen($logfile, 'a'))
			{
				return;
			} 
			fwrite($fh, $line . $EOL);
			fclose($fh);
		}

		
		
		private function file_exist($file)
		{

			if (!$fp = @fopen($file, 'rb'))
			{
				return false;
			}
			@fclose($fp);

			return true;
		}

		private function get_mime_type($file)
		{

			
			if (function_exists('finfo_open'))
			{
				$finfo     = finfo_open(FILEINFO_MIME);
				$mime_type = finfo_file($finfo, $file);
				finfo_close($finfo);
			}
			if ((!isset($mime_type)) &&
				(function_exists('mime_content_type'))
			)
			{
				$mime_type = @mime_content_type($file);
			}
			if (!isset($mime_type))
			{
				
				$mime_types = array('323'     => 'text/h323',
				                    '*'       => 'application/octet-stream',
				                    'acx'     => 'application/internet-property-stream',
				                    'ai'      => 'application/postscript',
				                    'aif'     => 'audio/x-aiff',
				                    'aifc'    => 'audio/x-aiff',
				                    'aiff'    => 'audio/x-aiff',
				                    'asf'     => 'video/x-ms-asf',
				                    'asr'     => 'video/x-ms-asf',
				                    'asx'     => 'video/x-ms-asf',
				                    'au'      => 'audio/basic',
				                    'avi'     => 'video/x-msvideo',
				                    'axs'     => 'application/olescript',
				                    'bas'     => 'text/plain',
				                    'bcpio'   => 'application/x-bcpio',
				                    'bin'     => 'application/octet-stream',
				                    'bmp'     => 'image/bmp',
				                    'c'       => 'text/plain',
				                    'cat'     => 'application/vnd.ms-pkiseccat',
				                    'cdf'     => 'application/x-cdf',
				                    'cdf'     => 'application/x-netcdf',
				                    'cer'     => 'application/x-x509-ca-cert',
				                    'class'   => 'application/octet-stream',
				                    'clp'     => 'application/x-msclip',
				                    'cmx'     => 'image/x-cmx',
				                    'cod'     => 'image/cis-cod',
				                    'cpio'    => 'application/x-cpio',
				                    'crd'     => 'application/x-mscardfile',
				                    'crl'     => 'application/pkix-crl',
				                    'crt'     => 'application/x-x509-ca-cert',
				                    'csh'     => 'application/x-csh',
				                    'css'     => 'text/css',
				                    'dcr'     => 'application/x-director',
				                    'der'     => 'application/x-x509-ca-cert',
				                    'dir'     => 'application/x-director',
				                    'dll'     => 'application/x-msdownload',
				                    'dms'     => 'application/octet-stream',
				                    'doc'     => 'application/msword',
				                    'dot'     => 'application/msword',
				                    'dvi'     => 'application/x-dvi',
				                    'dxr'     => 'application/x-director',
				                    'eps'     => 'application/postscript',
				                    'etx'     => 'text/x-setext',
				                    'evy'     => 'application/envoy',
				                    'exe'     => 'application/octet-stream',
				                    'fif'     => 'application/fractals',
				                    'flr'     => 'x-world/x-vrml',
				                    'gif'     => 'image/gif',
				                    'gtar'    => 'application/x-gtar',
				                    'gz'      => 'application/x-gzip',
				                    'h'       => 'text/plain',
				                    'hdf'     => 'application/x-hdf',
				                    'hlp'     => 'application/winhlp',
				                    'hqx'     => 'application/mac-binhex40',
				                    'hta'     => 'application/hta',
				                    'htc'     => 'text/x-component',
				                    'htm'     => 'text/html',
				                    'html'    => 'text/html',
				                    'htt'     => 'text/webviewhtml',
				                    'ico'     => 'image/x-icon',
				                    'ief'     => 'image/ief',
				                    'iii'     => 'application/x-iphone',
				                    'ins'     => 'application/x-internet-signup',
				                    'isp'     => 'application/x-internet-signup',
				                    'jfif'    => 'image/pipeg',
				                    'jpe'     => 'image/jpeg',
				                    'jpeg'    => 'image/jpeg',
				                    'jpg'     => 'image/jpeg',
				                    'js'      => 'application/x-javascript',
				                    'latex'   => 'application/x-latex',
				                    'lha'     => 'application/octet-stream',
				                    'lsf'     => 'video/x-la-asf',
				                    'lsx'     => 'video/x-la-asf',
				                    'lzh'     => 'application/octet-stream',
				                    'm13'     => 'application/x-msmediaview',
				                    'm14'     => 'application/x-msmediaview',
				                    'm3u'     => 'audio/x-mpegurl',
				                    'man'     => 'application/x-troff-man',
				                    'mdb'     => 'application/x-msaccess',
				                    'me'      => 'application/x-troff-me',
				                    'mht'     => 'message/rfc822',
				                    'mhtml'   => 'message/rfc822',
				                    'mka'     => 'audio/x-matroska',
				                    'mkv'     => 'video/x-matroska',
				                    'mk3d'    => 'video/x-matroska-3d',
				                    'mid'     => 'audio/mid',
				                    'mny'     => 'application/x-msmoney',
				                    'mov'     => 'video/quicktime',
				                    'movie'   => 'video/x-sgi-movie',
				                    'mp2'     => 'video/mpeg',
				                    'mp3'     => 'audio/mpeg',
				                    'mpa'     => 'video/mpeg',
				                    'mpe'     => 'video/mpeg',
				                    'mpeg'    => 'video/mpeg',
				                    'mpg'     => 'video/mpeg',
				                    'mpp'     => 'application/vnd.ms-project',
				                    'mpv2'    => 'video/mpeg',
				                    'ms'      => 'application/x-troff-ms',
				                    'msg'     => 'application/vnd.ms-outlook',
				                    'mvb'     => 'application/x-msmediaview',
				                    'nc'      => 'application/x-netcdf',
				                    'nws'     => 'message/rfc822',
				                    'oda'     => 'application/oda',
				                    'p10'     => 'application/pkcs10',
				                    'p12'     => 'application/x-pkcs12',
				                    'p7b'     => 'application/x-pkcs7-certificates',
				                    'p7c'     => 'application/x-pkcs7-mime',
				                    'p7m'     => 'application/x-pkcs7-mime',
				                    'p7r'     => 'application/x-pkcs7-certreqresp',
				                    'p7s'     => 'application/x-pkcs7-signature',
				                    'pbm'     => 'image/x-portable-bitmap',
				                    'pdf'     => 'application/pdf',
				                    'pfx'     => 'application/x-pkcs12',
				                    'pgm'     => 'image/x-portable-graymap',
				                    'pko'     => 'application/ynd.ms-pkipko',
				                    'pma'     => 'application/x-perfmon',
				                    'pmc'     => 'application/x-perfmon',
				                    'pml'     => 'application/x-perfmon',
				                    'pmr'     => 'application/x-perfmon',
				                    'pmw'     => 'application/x-perfmon',
				                    'pnm'     => 'image/x-portable-anymap',
				                    'pot'     => 'application/vnd.ms-powerpoint',
				                    'ppm'     => 'image/x-portable-pixmap',
				                    'pps'     => 'application/vnd.ms-powerpoint',
				                    'ppt'     => 'application/vnd.ms-powerpoint',
				                    'prf'     => 'application/pics-rules',
				                    'ps'      => 'application/postscript',
				                    'pub'     => 'application/x-mspublisher',
				                    'qt'      => 'video/quicktime',
				                    'ra'      => 'audio/x-pn-realaudio',
				                    'ram'     => 'audio/x-pn-realaudio',
				                    'ras'     => 'image/x-cmu-raster',
				                    'rgb'     => 'image/x-rgb',
				                    'rmi'     => 'audio/mid',
				                    'roff'    => 'application/x-troff',
				                    'rtf'     => 'application/rtf',
				                    'rtx'     => 'text/richtext',
				                    'scd'     => 'application/x-msschedule',
				                    'sct'     => 'text/scriptlet',
				                    'setpay'  => 'application/set-payment-initiation',
				                    'setreg'  => 'application/set-registration-initiation',
				                    'sh'      => 'application/x-sh',
				                    'shar'    => 'application/x-shar',
				                    'sit'     => 'application/x-stuffit',
				                    'snd'     => 'audio/basic',
				                    'spc'     => 'application/x-pkcs7-certificates',
				                    'spl'     => 'application/futuresplash',
				                    'src'     => 'application/x-wais-source',
				                    'sst'     => 'application/vnd.ms-pkicertstore',
				                    'stl'     => 'application/vnd.ms-pkistl',
				                    'stm'     => 'text/html',
				                    'sv4cpio' => 'application/x-sv4cpio',
				                    'sv4crc'  => 'application/x-sv4crc',
				                    'svg'     => 'image/svg+xml',
				                    'swf'     => 'application/x-shockwave-flash',
				                    't'       => 'application/x-troff',
				                    'tar'     => 'application/x-tar',
				                    'tcl'     => 'application/x-tcl',
				                    'tex'     => 'application/x-tex',
				                    'texi'    => 'application/x-texinfo',
				                    'texinfo' => 'application/x-texinfo',
				                    'tgz'     => 'application/x-compressed',
				                    'tif'     => 'image/tiff',
				                    'tiff'    => 'image/tiff',
				                    'tr'      => 'application/x-troff',
				                    'trm'     => 'application/x-msterminal',
				                    'tsv'     => 'text/tab-separated-values',
				                    'txt'     => 'text/plain',
				                    'uls'     => 'text/iuls',
				                    'ustar'   => 'application/x-ustar',
				                    'vcf'     => 'text/x-vcard',
				                    'vrml'    => 'x-world/x-vrml',
				                    'wav'     => 'audio/x-wav',
				                    'wcm'     => 'application/vnd.ms-works',
				                    'wdb'     => 'application/vnd.ms-works',
				                    'wks'     => 'application/vnd.ms-works',
				                    'wmf'     => 'application/x-msmetafile',
				                    'wps'     => 'application/vnd.ms-works',
				                    'wri'     => 'application/x-mswrite',
				                    'wrl'     => 'x-world/x-vrml',
				                    'wrz'     => 'x-world/x-vrml',
				                    'xaf'     => 'x-world/x-vrml',
				                    'xbm'     => 'image/x-xbitmap',
				                    'xla'     => 'application/vnd.ms-excel',
				                    'xlc'     => 'application/vnd.ms-excel',
				                    'xlm'     => 'application/vnd.ms-excel',
				                    'xls'     => 'application/vnd.ms-excel',
				                    'xlt'     => 'application/vnd.ms-excel',
				                    'xlw'     => 'application/vnd.ms-excel',
				                    'xof'     => 'x-world/x-vrml',
				                    'xpm'     => 'image/x-xpixmap',
				                    'xwd'     => 'image/x-xwindowdump',
				                    'z'       => 'application/x-compress',
				                    'zip'     => 'application/zip');

				@preg_match("/\.([^\.]+)$/s", $file, $ext);
				
				if (!isset($ext[1]))
				{
					$ext[1] = '';
				}
				if (array_key_exists(strtolower($ext[1]), $mime_types))
				{
					$mime_type = $mime_types[$ext[1]];
				}
				else
				{
					$mime_type = 'unknown/' . $ext[1];
				}
			}

			return @preg_replace('/\;(.*?)$/', '', $mime_type);
		}

		private function get_file_size($file)
		{

			$size = @filesize($file);
			
			if ($size <= 0)
			{
				
				$file = escapeshellarg($file);
				if (strpos(strtolower(php_uname('s')), 'win') !== false)
				{
					$size = exec("for %v in (" . $file . ") do @echo %~zv");
				}
				else
				{
					$size = trim(`stat -c%s $file`);
				}
			}

			return (float) $size;
		}

		private function read($start, $end)
		{

			

			$interval = 8 * 1024;
			$readed   = 0;
			$error    = false;
			$max      = PHP_INT_MAX;

			

			$requested = (float) $end - (float) $start + 1;

			if ((float) ($start) > (float) $max)
			{

				$offset = (float) $start - (float) $max;

				
				
				if (fseek($this->handler, $max, SEEK_CUR) === -1)
				{
					$error = true;
				}
				else
				{

					$chunk = 1048576; 
					while (true)
					{
						if ($offset <= $chunk)
						{
							$chunk = $offset;
						}
						if (!fread($this->handler, $chunk))
						{
							$error = true;
							break;
						}
						$offset -= $chunk;
						if (!$offset)
						{
							break;
						}
					}

				}

			}
			else
			{

				
				if (fseek($this->handler, $start, SEEK_SET) === -1)
				{
					$error = true;
				}
			}
			ob_clean();

			$begin = microtime(true);

			while (!$error)
			{

				if ($interval >= $requested)
				{
					$interval = (integer) $requested;
				}

				if (!$segment = @fread($this->handler, $interval))
				{
					$error = true;
					break;
				}
				print($segment);

				ob_flush();
				flush();

				
				$requested = $requested - (float) $interval;
				if ((!$requested) or (feof($this->handler)))
				{
					break;
				}

				$readed += $interval;

				
				if ($readed >= $this->download_speed)
				{
					$readed = 0;
					$now    = microtime(true);
					$diff   = (1 - ($now - $begin)) * 1000000;
					if ($diff > 0)
					{
						usleep($diff);
					}
					$begin = microtime(true);
				}
			}

			if ($error)
			{
				$this->error = JText::sprintf("COM_JUDOWNLOAD_CAN_NOT_READ_FILE", $this->file);

				return false;
			}
		}

		private function extend_range($range)
		{

			
			$range = preg_replace('/\/(.*?)$/', '', $range);

			list($start, $end) = explode('-', $range, 2);

			
			if ((strlen($start)) &&
				(strlen($end))
			)
			{
				$start = (float) $start;
				$end   = (float) $end;
			}

			
			if ((strlen($start)) &&
				(!strlen($end))
			)
			{
				if ($start == $this->filesize)
				{
					$start = 0;
				}
				$start = (float) $start;
				$end   = $this->file_size - 1;
			}

			
			if ((!strlen($start)) &&
				(strlen($end))
			)
			{
				$start = $this->file_size - (float) $end;
				$end   = $this->file_size - 1;
			}

			
			if (((!$start) && ($start !== 0)) or
				(!$end) or
				($end < $start)
			)
			{
				
				
				$this->is_resume = false;

				return;
			}

			return array($start, $end);
		}

		public function start()
		{

			if (($this->error) or
				($this->served)
			)
			{
				return false;
			}

			$this->now = @gmdate("D, j M m Y H:i:s ") . 'GMT';

			
			
			if (get_resource_type($this->file) != "stream" && !$this->file_exist($this->file))
			{
				$this->error = JText::sprintf("COM_JUDOWNLOAD_FILE_NOT_FOUND_X", $this->file);
			}

			
			ini_set('magic_quotes_runtime', 0);
			set_time_limit(0);
			session_cache_limiter(false);

			
			
			if (!$mime_type = $this->user_mime_type)
			{
				$mime_type = $this->get_mime_type($this->file);
			}

			
			
			if (get_resource_type($this->file) == "stream")
			{
				$filestat        = fstat($this->file);
				$this->file_size = $filestat['size'];
			}
			else
			{
				$this->file_size = $this->get_file_size($this->file);
			}

			
			
			if (!isset($this->save_name))
			{
				$this->save_name = basename($this->file);
			}

			
			
			$count = 0;
			if ($this->is_resume &&
				isset($_SERVER['HTTP_RANGE'])
			)
			{
				list($size_unit, $ranges) = explode('=', $_SERVER['HTTP_RANGE'], 2);
				if (strpos(strtolower($size_unit), 'bytes') !== false)
				{
					$ranges = explode(',', $ranges);
					$count  = count($ranges);
					
					for ($i = 0; $i < $count; $i++)
					{
						$ranges[$i] = $this->extend_range($ranges[$i]);
					}
				}
			}

			
			if (get_resource_type($this->file) == "stream")
			{
				$this->handler = $this->file;
			}
			
			elseif (!$this->handler = @fopen($this->file, 'rb'))
			{
				$this->error = JText::sprintf("COM_JUDOWNLOAD_CAN_NOT_OPEN_FILE_X_TO_READ", $this->file);
			}

			if (!$this->error)
			{

				
				@flock($this->handler, LOCK_SH);

				if (($this->is_resume) && ($count))
				{
					if (!$last_modified = @filemtime($this->file))
					{
						$this->error = JText::sprintf("COM_JUDOWNLOAD_CAN_NOT_ACCESS_FILE", $this->file);
					}
				}

				
				if ((!$this->error) &&
					((!$count) or (!$this->is_resume))
				)
				{

					header('Content-Description: File Transfer');
					header("Date: $this->now");
					header('Accept-Ranges: bytes');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');
					
					
					header("Content-Disposition: attachment; filename=\"$this->save_name\"");
					header("Content-Type: $mime_type");

					$length = number_format($this->file_size, 0, '', '');

					header("Content-Length: $length");

					$this->read(0, $this->file_size - 1);
				}

				if (($count > 1) &&
					(!$this->error)
				)
				{

					
					$boundary = md5(time());

					header('HTTP/1.1 206 Partial Content');
					header("Date: $this->now");
					if (!strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'firefox') !== false)
					{
						header('Last-Modified: ' . @gmdate("D, j M m Y H:i:s ", $last_modified) . 'GMT');
					}
					header("Content-Type: multipart/byteranges; boundary=$boundary");
					
					
					header("Content-Disposition: attachment; filename=\"$this->save_name\"");

					
					$varlen = strlen($mime_type) + strlen(number_format($start, 0, '', '') . '-' . number_format($end, 0, '', '') . '/' . number_format($this->file_size, 0, '', ''));
					foreach ($ranges AS $range)
					{
						$start = $range[0];
						$end   = $range[1];
						$length += ((float) $end - (float) $start) + $varlen;
					}
					$length = number_format($length + 70, 0, '', '');

					header("Content-Length: $length");

					
					$EOL = "\r\n";
					foreach ($ranges AS $range)
					{
						$start = $range[0];
						$end   = $range[1];
						echo $EOL . '--' . $boundary . $EOL
							. "Content-type: $mime_type" . $EOL
							. 'Content-range: bytes ' . number_format($start, 0, '', '') . '-' . number_format($end, 0, '', '') . '/' . number_format($this->file_size, 0, '', '') . $EOL . $EOL;

						$this->read((float) $start, (float) $end);
					}
					echo $EOL . '--' . $boundary . '--' . $EOL;

				}

				if (($count == 1) &&
					(!$this->error)
				)
				{

					
					$range = $ranges[0];
					$start = $range[0];
					$end   = $range[1];
					header('HTTP/1.1 206 Partial Content');
					header("Date: $this->now");
					if (!strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'firefox') !== false)
					{
						header('Last-Modified: ' . @gmdate("D, j M m Y H:i:s ", $last_modified) . 'GMT');
					}
					header('Content-Range: bytes ' . number_format($start, 0, '', '') . '-' . number_format($end, 0, '', '') . '/' . number_format($this->file_size, 0, '', ''));

					$length = number_format(((float) $end - (float) $start + 1), 0, '', '');
					header("Content-Length: $length");
					header("Content-type: $mime_type");
					
					
					header("Content-Disposition: attachment; filename=\"$this->save_name\"");

					$this->read((float) $start, (float) $end);
				}

				@flock($this->handler, LOCK_UN);
			}

			@fclose($this->handler);

			if (!$this->error)
			{
				$this->served = true;
			}
			else
			{
				if (!headers_sent())
				{
					header("HTTP/1.0 404 Not Found");
				}
			}
			
			if ($this->logdir)
			{
				$this->log_it();
			}

		}
	}
}

?>