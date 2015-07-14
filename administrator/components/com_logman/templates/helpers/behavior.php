<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanTemplateHelperBehavior extends ComExtmanTemplateHelperBehavior
{
	public function calendar($config = array())
	{
		$html = self::_calendar($config);

		return str_replace('media://system/images', 'media://com_logman/images', $html);
	}

	/**
	 * Loads the calendar behavior and attaches it to a specified element
	 *
	 * @TODO generate patch for 12.3 making this bootstrap friendly
	 *
	 * @return string	The html output
	 */
    private function _calendar($config = array())
	{
		$config = new KConfig($config);
		$config->append(array(
			'date'	  => gmdate("M d Y H:i:s"),
		    'name'    => '',
		    'format'  => '%Y-%m-%d %H:%M:%S',
		    'attribs' => array('size' => 25, 'maxlenght' => 19, 'class' => 'input-small', 'placeholder' => ''),
		    'gmt_offset' => JFactory::getConfig()->get('config.offset') * 3600
 		));

        if($config->date && $config->date != '0000-00-00 00:00:00' && $config->date != '0000-00-00') {
            $config->date = strftime($config->format, strtotime($config->date) /*+ $config->gmt_offset*/);
        }
        else $config->date = '';

	    $html = '';

		// Load the necessary files if they haven't yet been loaded
		if (!isset(self::$_loaded['calendar']))
		{
			$html .= '<script src="media://lib_koowa/js/calendar.js" />';
			$html .= '<script src="media://lib_koowa/js/calendar-setup.js" />';
			$html .= '<style src="media://lib_koowa/css/calendar.css" />';

			$html .= '<script>'.$this->_calendarTranslation().'</script>';

			self::$_loaded['calendar'] = true;
		}

		$html .= "<script>
					window.addEvent('domready', function() {Calendar.setup({
        				inputField     :    '".$config->name."',
        				ifFormat       :    '".$config->format."',
        				button         :    'button-".$config->name."',
        				align          :    'Tl',
        				singleClick    :    true,
        				showsTime	   :    false
    				});});
    			</script>";

		$attribs = KHelperArray::toString($config->attribs);

		$html .= '<div class="input-append">';
   		$html .= '<input type="text" name="'.$config->name.'" id="'.$config->name.'" value="'.$config->date.'" '.$attribs.' />';
   		$html .= '<button type="button" id="button-'.$config->name.'" class="btn" >';
   		$html .= '<i class="icon-calendar"></i>&zwnj;'; //&zwnj; is a zero width non-joiner, helps the button get the right height without adding to the width (like with &nbsp;)
   		$html .= '</button>';
		$html .= '</div>';

		return $html;
    }
}