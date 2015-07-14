{section name=ratingsloop loop=$ratings}
    <div class="auction_user_rating">
        <span class="myrating_title">
            <a href='{$ROOT_HOST}index.php?option=com_bids&task=viewbids&id={$ratings[ratingsloop]->auction_id}'>{$ratings[ratingsloop]->title}</a>
        </span>

        <span class="myrating_user">
            <a href='{$ROOT_HOST}index.php?option=com_bids&task=userdetails&id={$ratings[ratingsloop]->voter_id}'>
                {$ratings[ratingsloop]->username}
            </a>
            <em>({printdate date=`$ratings[ratingsloop]->modified`})</em>
        </span>
        <span class="myrating_stars">
            &nbsp;<span class="rating_user" rating="{$ratings[ratingsloop]->rating}" title="{$ratings[ratingsloop]->rating}/10"></span>
        </span>
        {if $ratings[ratingsloop]->review}
            <div class="myrating_message msg_text">{$ratings[ratingsloop]->review}</div>
        {/if}
    </div>
    <div class="div_spacer"></div>
    {$lists.linkUserRatings}
{sectionelse}
    <div>
         {'COM_BIDS_NO_RATINGS'|translate}
    </div>
{/section}
