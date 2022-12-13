{extends file='parent:frontend/checkout/cart.tpl'}

{* Product table content *}
{block name='frontend_checkout_cart_panel'}
    <div class="panel has--border">
        <div class="panel--body is--rounded">

            {* Product table header *}
            {block name='frontend_checkout_cart_cart_head'}
                {include file="frontend/checkout/cart_header.tpl"}
            {/block}

    {foreach $sBasket.content as $content}
        {if $content.additional_details.supplierID == 20}
            {$content.ordernumber} {$content.articlename}<br />
        {/if}
    {/foreach}            

            {* Basket items *}
            {foreach $sBasket.content as $sBasketItem}
                {block name='frontend_checkout_cart_item'}
                    {include file='frontend/checkout/cart_item.tpl' isLast=$sBasketItem@last}
                {/block}
            {/foreach}

            {* Product table footer *}
            {block name='frontend_checkout_cart_cart_footer'}
                {include file="frontend/checkout/cart_footer.tpl"}
            {/block}
        </div>
    </div>
{/block}

