{include file="../configs/config_template.tpl"}
{set_css}
{include file='snippets/t_javascript_language.tpl'}
{if $document_type=='raw'}
    {assign var=target value="target='_blank'"}
{/if}
<div class="auction_header">{'COM_BIDS_USER_RATINGS'|translate} : {$user->username}</div>
<table width="100%" class="auction_myratings">
{if count($ratings)>0}
	 <tr>
		<th class="list_ratings_header">{'COM_BIDS_USERNAME'|translate}</th>
		<th class="list_ratings_header">{'COM_BIDS_BID_TITLE'|translate}</th>
		<th class="list_ratings_header">{'COM_BIDS_BID_RATE'|translate}</th>
	 </tr>
    {section name=ratingsloop loop=$ratings}
        {cycle values='1,2' assign=class}
	 	 <tr class="myrating{$class}">
	 		<td width="15%" >
	 			<a href='{$ROOT_HOST}/index.php?option=com_bids&task=userdetails&id={$ratings[ratingsloop]->voter_id}&Itemid={$Itemid}' {$target}>{$ratings[ratingsloop]->username}</a>
	 		</td>
	 		<td width="*%">
	 			<a href='{$ROOT_HOST}/index.php?option=com_bids&task=viewbids&id={$ratings[ratingsloop]->auction_id}&Itemid={$Itemid}' {$target}>{$ratings[ratingsloop]->title}</a>
	 		</td>
	 		<td width="15%">
	 		    <span class="rating_user" rating="{$ratings[ratingsloop]->rating}">&nbsp;</span>
	 		</td>
	 	</tr>
	 	 <tr class="myrating{$class}">
	 		<td colspan="3" >
	 		      <div class="msg_text">{$ratings[ratingsloop]->review}</div>
	 		</td>
		 </tr>
    {/section}
{else}
      <tr>
      	<td>
      		{if $task=="my_ratings"}
		  		{'COM_BIDS_NO_RATINGS'|translate}
		  	{else}
		  		{'COM_BIDS_NO_RATINGS_USER'|translate}
		  	{/if}
		</td>
      </tr>
{/if}
</table>
