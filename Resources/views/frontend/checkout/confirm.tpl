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


                <span>Lizenzkosten aktuell</span>
                <table style="text-align:center;overflow-x:auto;">
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

                <div class="panel">
                    <p>Für diese Bestellung möchte ich die Lizenzgebühren (Entsorgungskosten) für das Duale System über Packing24 abrechnen lassen (gilt nur für Deutschland).</p>
                    <form action="https://www.packing24.de/shop/dateifabrikstaging/AddDisposalFee" method="post">
                        <select name="applyLicenseFee" onchange="this.form.submit()">
                            <option disabled="disabled" selected="selected" value="">Bitte wählen</option>
                            <option value="1" {$selected1}>Ja</option>
                            <option value="2" {$selected2}>Nein</option>
                        </select>
                    </form>
                </div>

            </div>
        </div>

    {/if}
{/block}
