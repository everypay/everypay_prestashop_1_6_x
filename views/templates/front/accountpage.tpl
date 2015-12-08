{capture name=path}
    <a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">
        {l s='My account' mod='everypaypayments'}</a>
    <span class="navigation-pipe">{$navigationPipe}</span>{l s='My credit/debit cards' mod='everypaypayments'}
{/capture}

{assign var="valid" value=$EVERYPAY_CARDS.valid}
{assign var="expired" value=$EVERYPAY_CARDS.expired}
{assign var="cards" value=NULL}
{if $valid|count>0 || $expired|count>0}
    {assign var="cards" value=$valid|@array_merge:$expired}
{/if}

<div id="everypay_confirmation_wrapper" class="everypay_customer_cards_container">
    <h1>{l s='My credit/debit cards' mod='everypaypayments'}</h1>
    {if $cards|count > 0 }
        <p> {l s='Here you can review your stored Credit/Debit cards.' mod='everypaypayments'}
            <br /><br />
            {l s='Note that your sensitive card data are not really stored in our store. Instead a unique token is created so that your future transactions use this.' mod='everypaypayments'}
        </p>
        <table class="std" id="order-list">
            <thead>
                <tr>
                    <th>{l s='Card type' mod='everypaypayments'}</th>
                    <th>{l s='Card details' mod='everypaypayments'}</th>
                    <th></th>
                </tr>

            </thead>
            <tbody>
                {foreach from=$valid item=card}
                    <tr>
                        <td>
                            <img src="/modules/everypaypayments/assets/images/icon-{$card['card_type']|strtolower}.gif" />
                        {$card['card_type']}
                        </td>
                        <td>[•••• {$card['card_last_four']}] ({$card['exp_month']}/{$card['exp_year']})</td>
                        <td>
                            <form action="{$form_action}" method="POST">
                                <input type="submit" name="deleteCard" onClick="return confirm('{l s='Are you sure you want to delete this card?' mod='everypaypayments'}')" class="button" value="{l s='Remove' mod='everypaypayments'}">
                                <input type="hidden" name="card" value="{$card['id_customer_token']}" />
                            </form>
                        </td>
                    </tr>
                {/foreach}
                {foreach from=$expired item=card}
                    <tr>
                        <td>
                            <img src="/modules/everypaypayments/assets/images/icon-{$card['card_type']|strtolower}.gif" />
                            {$card['card_type']}
                        </td>
                        <td>[•••• {$card['card_last_four']}] ({$card['exp_month']}/{$card['exp_year']}) - {l s='Expired' mod='everypaypayments'}</td>
                        <td style="color:#444">                            
                            <form action="{$form_action}" method="POST">                                
                                <input type="submit" name="deleteCard" onClick="return confirm('{l s='Are you sure you want to delete this card?' mod='everypaypayments'}')" class="button" value="{l s='Remove' mod='everypaypayments'}">
                                <input type="hidden" name="card" value="{$card['id_customer_token']}" />
                            </form>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        <div class="alert alert-warning">
            <p class="warning">{l s='No stored cards have been found' mod='everypaypayments'}</p>
        </div>
        <p>
            {l s='No credit/debit card has been stored in your account yet. You will be offered to save your card upon your first order that is paid with credit/debit card (check the "save card" option).' mod='everypaypayments'}                
            <br/><br/>
            {l s='Note that your sensitive card data are not really stored in our store. Instead a unique token is created so that your future transactions use this.' mod='everypaypayments'}
        </p>
        
    {/if}

    <ul class="footer_links clearfix">
        <li><a title="{l s='Back to Your Account' mod='everypaypayments'}" href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" class="btn btn-default button button-small"><span><i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='everypaypayments'}</span></a></li>
    </ul>

</div>