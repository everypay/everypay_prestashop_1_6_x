{assign var="cards" value=$EVERYPAY_CARDS.valid}
{assign var="expired" value=$EVERYPAY_CARDS.expired}
{assign var="cards" value=NULL}
{if $valid|count>0 || $expired|count>0}
    {assign var="cards" value=$valid|@array_merge:$expired}
{/if}

{if $cards|count>0}
    <form action="{$EVERYPAY_FORM_ACTION}" id="select_everypay_saved_card" method="POST">
        <p class="select" style="float:left">
            <span>{l s='Select a saved card' mod='everypaypayments'}:</span> 
            <select name="cus_id" id="cust_ids" style="font-family:Monospace">
                {foreach from=$cards key=k item=card}
                    <option value="{$card['id_customer_token']}">{$card['card_type']} •••• {$card['card_last_four']} ({$card['exp_month']}/{$card['exp_year']})</option>
                {/foreach}
            </select>
        </p>
        <div class="submit-everypay">
            <button input="" type="submit" name="submit_saved_card" class="submit_saved_card button btn btn-success">{l s='Continue' mod='everypaypayments'}</button>
        </div>
    </form>
{/if}

{if $cards|count == 0 && $expired|count > 0}
    {l s='All of your stored cards have expired! Please use a valid one' mod='everypaypayments'}
{/if}