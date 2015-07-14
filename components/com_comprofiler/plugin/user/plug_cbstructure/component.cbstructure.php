<?php
/**
 * Community Builder (TM)
 * @version $Id: $
 * @package CommunityBuilder
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;
use CBLib\Application\Application;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) {
  die( 'Direct Access to this location is not allowed.' );
}

/**
 * Class CBplug_cbconsultations
 * CB Components-type class for CB consultations
 */
class CBplug_cbstructure extends cbPluginHandler
{
  /**
   * @param  TabTable $tab Current tab
   * @param  UserTable $user Current user
   * @param  int $ui 1 front, 2 admin UI
   * @param  array $postdata Raw unfiltred POST data
   * @return string                HTML
   */
  public function getCBpluginComponent(/** @noinspection PhpUnusedParameterInspection */
    $tab, $user, $ui, $postdata)
  {
    global $_CB_framework;

    outputCbJs(1);
    outputCbTemplate(1);



    ob_start();


    ob_end_clean();



    echo "HI";
  }
}