{extends file="parent:frontend/checkout/ajax_cart.tpl"}

{block name='frontend_checkout_ajax_cart_item_container_inner'}

    {* sets new item order *}
    {if isset($sortedItemsForBasketView)}
        {$sBasket=$sortedItemsForBasketView}
    {/if}
    
    {if $sBasket.content}
        {foreach $sBasket.content as $sBasketItem}
            {block name='frontend_checkout_ajax_cart_row'}
                {include file="frontend/checkout/ajax_cart_item.tpl" basketItem=$sBasketItem}
            {/block}
        {/foreach}
    {else}
        {block name='frontend_checkout_ajax_cart_empty'}
            <div class="cart--item is--empty">
                {block name='frontend_checkout_ajax_cart_empty_inner'}
                    <span class="cart--empty-text">{s name='AjaxCartInfoEmpty'}{/s}</span>
                {/block}
            </div>
        {/block}
    {/if}
{/block}
