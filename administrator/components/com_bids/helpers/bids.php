<?php


defined('_JEXEC') or die;


class BidsHelper
{
    /**
     * Configure the Linkbar.
     *
     * @param    string    The name of the active view.
     *
     * @return    void
     * @since    1.6
     */
    public static function addSubmenu($vName)
    {
        JToolBarHelper::title('test','auction');

        JLoader::register('BidsHelperAdmin',JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'admin.php');
        BidsHelperAdmin::subMenuHelper();
    }
}