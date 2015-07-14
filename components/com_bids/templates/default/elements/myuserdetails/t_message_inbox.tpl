{foreach from=$lists.messages.received item=mess key=mk }
    <div class="auction_user_messages">

        {'COM_BIDS_FROM'|translate}&nbsp;
        <strong>{$mess->username|default:'COM_BIDS_FROM_GUEST'|translate}</strong>
        ({printdate date=`$mess->modified`})
        {'COM_BIDS_ON'|translate}
        <a href="{$ROOT_HOST}index.php?option=com_bids&task=viewbids&id={$mess->auction_id}">{$mess->title}</a>

    	<span class="auction_msg_text">{$mess->message}</span>
    </div>
{/foreach}
