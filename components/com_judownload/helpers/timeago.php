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

class TimeAgo
{
	
	private $secondsPerMinute;
	private $secondsPerHour;
	private $secondsPerDay;
	private $secondsPerMonth;
	private $secondsPerYear;
	private $timezone;

	public function __construct($timezone = null)
	{
		$this->secondsPerMinute = 60;
		$this->secondsPerHour   = $this->secondsPerMinute * 60;
		$this->secondsPerDay    = $this->secondsPerHour * 24;
		$this->secondsPerMonth  = $this->secondsPerDay * 30;
		$this->secondsPerYear   = $this->secondsPerMonth * 12;

		
		
		if ($timezone == null)
		{
			$timezone = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
		}

		$this->timezone = $timezone;
	}

	public function inWords($past, $now = "now")
	{
		
		date_default_timezone_set($this->timezone);
		
		$past = strtotime($past);
		
		$now = strtotime($now);

		
		$timeAgo = "";

		
		$timeDifference = $now - $past;
		
		if ($timeDifference <= 29)
		{
			$timeAgo = JText::_("COM_JUDOWNLOAD_LESS_THAN_A_MINUTE");
		}
		
		else
		{
			if ($timeDifference > 29 && $timeDifference <= 89)
			{
				$timeAgo = JText::plural("COM_JUDOWNLOAD_MINUTE", 1);
			}
			
			else
			{
				if ($timeDifference > 89 &&
					$timeDifference <= (($this->secondsPerMinute * 44) + 29)
				)
				{
					$minutes = floor($timeDifference / $this->secondsPerMinute);
					$timeAgo = JText::plural("COM_JUDOWNLOAD_MINUTE", $minutes);
				}
				
				else
				{
					if (
						$timeDifference > (($this->secondsPerMinute * 44) + 29)
						&&
						$timeDifference < (($this->secondsPerMinute * 89) + 29)
					)
					{
						$timeAgo = JText::plural("COM_JUDOWNLOAD_HOUR", 1);
					}
					
					else
					{
						if (
							$timeDifference > (
								($this->secondsPerMinute * 89) +
								29
							)
							&&
							$timeDifference <= (
								($this->secondsPerHour * 23) +
								($this->secondsPerMinute * 59) +
								29
							)
						)
						{
							$hours   = floor($timeDifference / $this->secondsPerHour);
							$timeAgo = JText::plural("COM_JUDOWNLOAD_HOUR", $hours);
						}
						
						else
						{
							if (
								$timeDifference > (
									($this->secondsPerHour * 23) +
									($this->secondsPerMinute * 59) +
									29
								)
								&&
								$timeDifference <= (
									($this->secondsPerHour * 47) +
									($this->secondsPerMinute * 59) +
									29
								)
							)
							{
								$timeAgo = JText::plural("COM_JUDOWNLOAD_DAY", 1);
							}
							
							else
							{
								if (
									$timeDifference > (
										($this->secondsPerHour * 47) +
										($this->secondsPerMinute * 59) +
										29
									)
									&&
									$timeDifference <= (
										($this->secondsPerDay * 29) +
										($this->secondsPerHour * 23) +
										($this->secondsPerMinute * 59) +
										29
									)
								)
								{
									$days    = floor($timeDifference / $this->secondsPerDay);
									$timeAgo = JText::plural("COM_JUDOWNLOAD_DAY", $days);
								}
								
								else
								{
									if (
										$timeDifference > (
											($this->secondsPerDay * 29) +
											($this->secondsPerHour * 23) +
											($this->secondsPerMinute * 59) +
											29
										)
										&&
										$timeDifference <= (
											($this->secondsPerDay * 59) +
											($this->secondsPerHour * 23) +
											($this->secondsPerMinute * 59) +
											29
										)
									)
									{
										$timeAgo = JText::plural("COM_JUDOWNLOAD_MONTH", 1);

									}
									
									else
									{
										if (
											$timeDifference > (
												($this->secondsPerDay * 59) +
												($this->secondsPerHour * 23) +
												($this->secondsPerMinute * 59) +
												29
											)
											&&
											$timeDifference < $this->secondsPerYear
										)
										{
											$months = round($timeDifference / $this->secondsPerMonth);
											
											if ($months == 1)
											{
												$months = 2;
											}
											$timeAgo = JText::plural("COM_JUDOWNLOAD_MONTH", $months);
										}
										
										else
										{
											if (
												$timeDifference >= $this->secondsPerYear
												&&
												$timeDifference < ($this->secondsPerYear * 2)
											)
											{
												$timeAgo = JText::plural("COM_JUDOWNLOAD_YEAR", 1);
											}
											
											else
											{
												$years   = floor($timeDifference / $this->secondsPerYear);
												$timeAgo = JText::plural("COM_JUDOWNLOAD_YEAR", $years);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$timeAgo .= " " . JText::_("COM_JUDOWNLOAD_AGO");

		return $timeAgo;
	}

	public function dateDifference($past, $now = "now")
	{
		
		$seconds = 0;
		$minutes = 0;
		$hours   = 0;
		$days    = 0;
		$months  = 0;
		$years   = 0;

		
		date_default_timezone_set($this->timezone);

		
		$past = strtotime($past);
		
		$now = strtotime($now);

		
		$timeDifference = $now - $past;

		
		if ($timeDifference >= 0)
		{
			switch ($timeDifference)
			{
				
				case ($timeDifference >= $this->secondsPerYear):
					
					$years = floor($timeDifference / $this->secondsPerYear);
					
					$timeDifference = $timeDifference - ($years * $this->secondsPerYear);

				
				case ($timeDifference >= $this->secondsPerMonth && $timeDifference <= ($this->secondsPerYear - 1)):
					
					$months = floor($timeDifference / $this->secondsPerMonth);
					
					$timeDifference = $timeDifference - ($months * $this->secondsPerMonth);

				
				case ($timeDifference >= $this->secondsPerDay && $timeDifference <= ($this->secondsPerYear - 1)):
					
					$days = floor($timeDifference / $this->secondsPerDay);
					
					$timeDifference = $timeDifference - ($days * $this->secondsPerDay);

				
				case ($timeDifference >= $this->secondsPerHour && $timeDifference <= ($this->secondsPerDay - 1)):
					
					$hours = floor($timeDifference / $this->secondsPerHour);
					
					$timeDifference = $timeDifference - ($hours * $this->secondsPerHour);

				
				case ($timeDifference >= $this->secondsPerMinute && $timeDifference <= ($this->secondsPerHour - 1)):
					
					$minutes = floor($timeDifference / $this->secondsPerMinute);
					
					$timeDifference = $timeDifference - ($minutes * $this->secondsPerMinute);

				
				case ($timeDifference <= ($this->secondsPerMinute - 1)):
					
					$seconds = $timeDifference;
			}
		}

		$difference = array(
			"years"   => $years,
			"months"  => $months,
			"days"    => $days,
			"hours"   => $hours,
			"minutes" => $minutes,
			"seconds" => $seconds
		);

		return $difference;
	}
}