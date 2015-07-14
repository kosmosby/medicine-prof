<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldSystemInfo extends JFormField
{
	protected $type = 'SystemInfo';
    protected function getLabel()
    {
        if ($this->label) return $this->label;
        else return JText::_("COM_BIDS_SYSTEM_INFO");

    }

	protected function getInput()
	{
        $writeable		= '<b><font color="green">'. JText::_( 'COM_BIDS_WRITABLE' ) .'</font></b>';
        $unwriteable	= '<b><font color="red">'. JText::_( 'COM_BIDS_UNWRITABLE' ). '</font></b>';

        $template_dir=$unwriteable;
        $image_dir=$unwriteable;
        $config_dir=$unwriteable;

        if (is_writable(AUCTION_TEMPLATE_CACHE))
            $template_dir=$writeable;

        if (is_writable(JPATH_COMPONENT_SITE.DS.'images'))
            $image_dir=$writeable;
        if (is_writable(JPATH_COMPONENT_SITE.DS.'options.php'))
            $config_dir=$writeable;

        $cfg = JFactory::getConfig();
        $error_reporting = $cfg->get('error_reporting');
        $error_reporting_text="";
        if($error_reporting & 	E_ERROR) $error_reporting_text.=" E_ERROR,";
        if($error_reporting & 	E_WARNING) $error_reporting_text.=" E_WARNING,";
        if($error_reporting & 	E_PARSE) $error_reporting_text.=" E_PARSE,";
        if($error_reporting & 	E_NOTICE) $error_reporting_text.=" E_NOTICE,";
        if($error_reporting & 	E_CORE_ERROR ) $error_reporting_text.=" E_CORE_ERROR,";
        if($error_reporting & 	E_CORE_WARNING) $error_reporting_text.=" E_CORE_WARNING,";
        if($error_reporting & 	E_COMPILE_ERROR) $error_reporting_text.=" E_COMPILE_ERROR,";
        if($error_reporting & 	E_COMPILE_WARNING) $error_reporting_text.=" E_COMPILE_WARNING,";
        if($error_reporting & 	E_USER_ERROR) $error_reporting_text.=" E_USER_ERROR,";
        if($error_reporting & 	E_USER_WARNING) $error_reporting_text.=" E_USER_WARNING,";
        if($error_reporting & 	E_USER_NOTICE) $error_reporting_text.=" E_USER_NOTICE,";
        if($error_reporting & 	E_STRICT) $error_reporting_text.=" E_STRICT,";
        if($error_reporting & 	E_RECOVERABLE_ERROR ) $error_reporting_text.=" E_RECOVERABLE_ERROR,";
//        if($error_reporting & 	E_DEPRECATED) $error_reporting_text.=" E_DEPRECATED,";
//        if($error_reporting & 	E_USER_DEPRECATED) $error_reporting_text.=" E_USER_DEPRECATED,";
        if($error_reporting & 	E_ALL) $error_reporting_text.=" E_ALL";

        $local_time=JHTML::date('now','l, d F Y H:i');
        $gmt_time = gmdate('l, d F Y H:i');

        $html="
            <table class='adminlist'>
                <tr>
                    <td colspan='2'><h3>".JText::_( 'COM_BIDS_FILE_PERMISSIONS' )."</h3></td>
                </tr>
                <tr>
                    <td class='item'>
                        ".JText::_( 'COM_BIDS_SMARTY_TEMPLATE_CACHE_DIRECTORY' )."
                    </td>
                    <td>
                        $template_dir
                    </td>
                </tr>
                <tr>
                    <td class='item'>
                        ".JText::_( 'COM_BIDS_IMAGE_UPLOADING_DIRECTORY' )."
                    </td>
                    <td>
                        $image_dir
                    </td>
                </tr>
                <tr>
                    <td class='item'>
                        ".JText::_( 'COM_BIDS_CONFIGURATION_FILE' )."
                    </td>
                    <td>
                        $config_dir
                    </td>
                </tr>
                <tr>
                    <td colspan='2'><h3>".JText::_("COM_BIDS_JOOMLA_SETTINGS")."</h3></td>
                </tr>
                <tr>
                    <td>
                        ".JText::_( 'COM_BIDS_ERROR_REPORTING' )."
                    </td>
                    <td>
                        $error_reporting_text
                    </td>
                </tr>
                <tr>
                    <td>
                        ".JText::_( 'COM_BIDS_SITE_LOCALE_TIME' )."
                    </td>
                    <td>
                        $local_time
                    </td>
                </tr>
                <tr>
                    <td colspan='2'><h3>".JText::_("COM_BIDS_PHP_SETTINGS")."</h3></td>
                </tr>
                <tr>
                    <td>
                        ".JText::_( 'COM_BIDS_DISPLAY_ERRORS' )."
                    </td>
                    <td>
                        ".(ini_get('display_errors')? JText::_( 'COM_BIDS_ON' ) : JText::_( 'COM_BIDS_OFF' ))."
                    </td>
                </tr>
                <tr>
                    <td>
                        ".JText::_( 'COM_BIDS_FILE_UPLOADS' )."
                    </td>
                    <td>
                        ".(ini_get('file_uploads')? JText::_( 'COM_BIDS_ON' ) : JText::_( 'COM_BIDS_OFF' ))."
                    </td>
                </tr>
                <tr>
                    <td>
                        ".JText::_( 'COM_BIDS_MAX_UPLOAD_FILESIZE' )."
                    </td>
                    <td>
                        ".(ini_get('upload_max_filesize'))."
                    </td>
                </tr>
                <tr>
                    <td>
                        ".JText::_( 'COM_BIDS_GMT_TIME' )."
                    </td>
                    <td>
                        $gmt_time
                    </td>
                </tr>
            </table>

        ";
        return $html;
	}
}
