{if !is_null($EVERYPAY_CARDS) && $EVERYPAY_CARDS|count>0}
    <form action="{$EVERYPAY_FORM_ACTION}" id="select_everypay_saved_card" method="POST">
        <p class="select" style="float:left">
            <span>{l s='Select a saved card' mod='everypaypayments'}:</span> 
            <select name="cus_id" id="cust_ids" style="font-family:Monospace">
                {foreach from=$EVERYPAY_CARDS key=k item=v}
                    <option value="{$v['id_customer_token']}">{$v['card_type']} •••• {$v['card_last_four']} ({$v['exp_month']}/{$v['exp_year']})</option>
                {/foreach}
            </select>
        </p>
        <div class="submit-everypay">
            <button input="" type="submit" name="submit_saved_card" class="submit_saved_card button btn btn-success">{l s='Continue' mod='everypaypayments'}</button>
        </div>
    </form>
{/if}