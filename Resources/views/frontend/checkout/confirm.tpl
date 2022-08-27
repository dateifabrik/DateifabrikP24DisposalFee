{extends file='parent:frontend/checkout/confirm.tpl'}

{block name='frontend_checkout_confirm_form'}
    {$smarty.block.parent}

        {* Formular nur für Rechnungsadresse Deutschland ausgeben *}
        {if $countryId == 2}

        <div class="panel has--border">
            <div class="panel--title primary is--underline">
                Lizenzgebühren nach VerpackG
            </div>

            <div class="panel--body is--wide">
                <!--
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
                -->
                
                <p>Für diese Bestellung möchte ich die Lizenzgebühren (Entsorgungskosten) für das Duale System über Packing24 abrechnen lassen (gilt nur für Deutschland).</p>
                <table style="width:100%; text-align:center;overflow-x:auto;">
                    <thead>
                        <tr>
                        {foreach from=$Material key=material item=empty}
                            <th>{$material}</th>
                        {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        {foreach from=$Material key=empty item=kosten}
                            <td>{$kosten|string_format:"%.2f"|replace:".":","} &euro; pro kg</td>
                        {/foreach}
                        </tr>
                    </tbody>                    
                </table>





                <form action="https://www.packing24.de/shop/dateifabrikstaging/AddDisposalFee" method="post">
                    <select name="applyLicenseFee" onchange="this.form.submit()">
                        <option disabled="disabled" selected="selected" value="">Bitte wählen</option>
                        <option value="1" {$selected1}>Ja</option>
                        <option value="2" {$selected2}>Nein</option>
                    </select>
                    <!--
                    <p><button type="submit" class="btn is--primary m-0" {$state}>Entsorgungsgebühr hinzufügen</button></p>
                    -->
                </form>

            </div>
        </div>
    {*

        <form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}">
            <button name="sAdd" type="submit" class="btn is--primary" value="VerpackG">{$addOrRemoveBtnText}</button>
        </form>

    *}

    {/if}
{/block}
