{extends file='parent:frontend/checkout/confirm.tpl'}

{block name='frontend_checkout_confirm_form'}
    {$smarty.block.parent}
    <div class="panel has--border">
        <div class="panel--title primary is--underline" style="margin: 0; padding-left: 1.25rem; padding-right: 1.25rem; background: yellow;">
            Lizenzierung nach VerpackG 01.01.2019
        </div>

        <div class="panel--body is--wide">
            <ul class="list--checkbox list--unstyled">
                <li class="block-group row--tos">
                    {* Lizenzierung *}
                    <span class="block">
                        <div class="">

                            <a href="{url controller='AddDisposalFee' action=$action}">
                                <button class="btn is--primary m-0" {$state}>Entsorgungsgebühr hinzufügen</button>
                            </a>
                            <p>Entsorgungskosten: <b>{$disposalFee}</b> Euro</p>


                                {$names}<br />
                            {foreach $materials as $m}
                                Material: {$m}
                            {/foreach}


                        </div>
                    </span>
                </li>
            </ul>
        </div>
    </div>
{*

    <form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}">
        <button name="sAdd" type="submit" class="btn is--primary" value="VerpackG">{$addOrRemoveBtnText}</button>
    </form>

*}
{/block}