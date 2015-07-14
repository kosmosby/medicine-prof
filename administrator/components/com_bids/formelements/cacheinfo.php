<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldCacheInfo extends JFormField
{
	protected $type = 'CacheInfo';
    protected function getLabel()
    {
        if ($this->label) return $this->label;
        else return JText::_("COM_BIDS_CACHE_INFO");

    }

	protected function getInput()
	{
        $writeable		= '<b><font color="green">'. JText::_( 'COM_BIDS_WRITABLE' ) .'</font></b>';
        $unwriteable	= '<b><font color="red">'. JText::_( 'COM_BIDS_UNWRITABLE' ). '</font></b>';

        $template_dir=$unwriteable;

        if (is_writable(AUCTION_TEMPLATE_CACHE))
            $template_dir=$writeable;

        jimport('joomla.filesystem.folder');
        $cache_files = JFolder::files(AUCTION_TEMPLATE_CACHE);
        $cache_size = 0;
        $nr_files = 0;
        $last_cached = $first_cached = JText::_("COM_BIDS_NOTHING_CACHED");
        if(is_array($cache_files)){
            $last_cached = 0;
            $first_cached = 0;
            foreach ($cache_files as $file){
                $cache_size += filesize(AUCTION_TEMPLATE_CACHE.DS.$file);
                $last_cached = ($last_cached)? max($last_cached,date("d M Y H:i:s", filemtime(AUCTION_TEMPLATE_CACHE.DS.$file))):date("d M Y H:i:s", filemtime(AUCTION_TEMPLATE_CACHE.DS.$file));
                $first_cached = ($first_cached)? max($first_cached,date("d M Y H:i:s", filemtime(AUCTION_TEMPLATE_CACHE.DS.$file))):date("d M Y H:i:s", filemtime(AUCTION_TEMPLATE_CACHE.DS.$file));
            }
            $nr_files = count($cache_files);
        }
        $cache_size = sprintf ("%.2f kB", $cache_size/1024);
        
    	$dir = AUCTION_TEMPLATE_CACHE;
		if(!Jfolder::exists($dir)){
		$cache_text = JText::_("COM_BIDS_CREATE_CACHE_FILE");
		}else{
		$cache_text = JText::_("COM_BIDS_PURGE_CACHE_FILE");
		}
	
        $html="
            <table class='adminlist'>
                <tr>
                    <td class='paramlist_key'>".JText::_("COM_BIDS_SMARTY_TEMPLATE_CACHE_DIRECTORY")."</td>
                    <td>$template_dir</td>
                </tr>
                <tr>
                    <td class='paramlist_key'>".JText::_("COM_BIDS_NR_FILES_CACHED")."</td>
                    <td>$nr_files</td>
                </tr>
                <tr>
                    <td class='paramlist_key'>".JText::_("COM_BIDS_CACHE_SIZE")."</td>
                    <td>$cache_size</td>
                </tr>
                <tr>
                    <td class='paramlist_key'>".JText::_("COM_BIDS_LATEST_CACHED_FILE_ON")."</td>
                    <td>$last_cached</td>
                </tr>
                <tr>
                    <td class='paramlist_key'>".JText::_("COM_BIDS_OLDEST_CACHED_FILE_ON")."</td>
                    <td>$first_cached</td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <a href='index.php?option=".APP_EXTENSION."&task=purgecache'>".$cache_text."</a>
                    </td>
                </tr>
            </table>
          ";
	    return $html;
    }
}
