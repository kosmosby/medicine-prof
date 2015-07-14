<?php defined('_JEXEC') or die('Restricted access'); ?>
<div id="cpanel">
<?php
foreach($this->links as $k=>$link)
{
    JTheFactoryAdminHelper::quickiconButton( $link, 'admin/'.$k.'_integration.png', $this->lables[$k]);
}
?>
</div>
