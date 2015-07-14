{* search filters: $filters->keyword,$filters->users,$filters->startdate,$filters->enddate,$filters->tags *}
{if $lists.htmlLabelFilters}
<span id="auction_filterbox_shadow">
    <span id="auction_filterbox">
    {$lists.resetFilters}
    {if $task=='showSearchResults'}
        {'COM_BIDS_SEARCH_TEXT'|translate}
    {elseif $task=='tags'}
    &nbsp;
    {else}
        {'COM_BIDS_FILTER'|translate} -
    {/if}
    {foreach from=$lists.htmlLabelFilters key=label item=filter}
        {$label}: {$filter};&nbsp;
    {/foreach}
    </span>
</span>
{/if}
