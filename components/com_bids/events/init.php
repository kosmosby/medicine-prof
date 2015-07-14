<?php

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventInit extends JTheFactoryEvents
{
    function onBeforeExecuteTask(&$stopexecution)
    {
        if(!JFolder::exists(AUCTION_PICTURES_PATH)) JFolder::create(AUCTION_PICTURES_PATH);
        if(!JFolder::exists(AUCTION_UPLOAD_FOLDER)) JFolder::create(AUCTION_UPLOAD_FOLDER);
        if(!JFolder::exists(AUCTION_TEMPLATE_CACHE)) JFolder::create(AUCTION_TEMPLATE_CACHE);
    }
}
