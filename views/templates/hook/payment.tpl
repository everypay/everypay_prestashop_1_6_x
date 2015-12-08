<div class="row everypay-payment-row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a style="background: transparent !important" href="#HOOK_PAYMENT" onclick="javascript:void(0);" title="{l s='Pay with your credit card' mod='everypaypayments'}">
                <img height="26px" alt="Pay with your credit card" src="{$module_template_dir}assets/images/icon-visa.gif">
                <img height="26px" alt="Pay with your credit card" src="{$module_template_dir}assets/images/icon-mastercard.gif">
                {l s='Pay safely with your visa or mastercard card' mod='everypaypayments'}</span>
            </a>
        </p>
    </div> 
                
    {assign var="valid" value=$EVERYPAY_CARDS.valid}
    {assign var="expired" value=$EVERYPAY_CARDS.expired}
    {assign var="cards" value=NULL}
    {if $valid|count>0 || $expired|count>0}
        {assign var="cards" value=$valid|@array_merge:$expired}
    {/if}     
    <div class="col-xs-12 everypay-option-select">
        <div>
            {if $EVERYPAY_CUSTOMER_MODE && $cards|count>0 && !$isGuest}
                <div class="everypay_cardway_selection">
                    <label>{l s='You can pay with a' mod='everypaypayments'}:</label>
                    <label for="select_saved_card">
                        <input type="radio" id="select_saved_card" value="saved_card" name="everypay_cardway">{l s='Saved Card' mod='everypaypayments'}</label>
                    <label for="select_new_card"><input type="radio" id="select_new_card" value="new_card" name="everypay_cardway">{l s='New card' mod='everypaypayments'}</label>
                </div>
                <div class="everypay_selection_wrapper_saved_card">
                    <p class="loading-everypay" style="display:none;text-align:center">
                        {l s='Loading please wait' mod='everypaypayments'}...<br /><br />
                        <img height="19px" alt="Loading please wait" src="{$module_template_dir}assets/images/loading-ajax.GIF">
                    </p>
                    {$EVERYPAY_CARD_SELECTION}
                </div>
            {/if}
            <div class="everypay_selection_wrapper_new_card" {if $EVERYPAY_CUSTOMER_MODE && $cards|count>0 && !$isGuest}style="display:none"{/if}>
                <p class="loading-everypay" style="display:none;text-align:center">
                    {l s='Loading please wait' mod='everypaypayments'}...<br /><br />
                    <img height="19px" alt="Loading please wait" src="{$module_template_dir}assets/images/loading-ajax.GIF">
                </p>

                <form action="{$EVERYPAY_FORM_ACTION}" method="POST" id="everypay-payment-form" class="everypay_form">
                    <div class="button-holder" style="float:left;margin-right: 14px;"></div>

                    <div class="submit-everypay">
                        <!--<div class="form-error" style="display:none"></div>-->
                        {if ($EVERYPAY_CUSTOMER_MODE && !$isGuest)}           
                            <p style="text-align: left">
                                
                                <label for="remember_ev_card">
                                    <input id="remember_ev_card" type="checkbox" name="remember_card" />
                                    {l s='Save this card for use in the future?' mod='everypaypayments'}
                                    <br />
                                    <small>{l s='Your card details don\'t get really stored. A unique token gets saved instead.' mod='everypaypayments'}</small>
                                </label>
                            </p>
                        {else}
                            <p style="text-align: left">
                                <small>{l s='Claim: None of your sensitive card data is stored in our e-shop according to PCI-DSS compliance' mod='everypaypayments'}</small>
                            </p>
                        {/if}
                    </div>
                </form>
            </div> <!-- new_selection_wrapper -->
        </div>
    </div>
</div>
<script type="text/javascript">
    //<![CDATA[        
    var EVERYPAY_OPC_BUTTON = {
        amount: {$amountInteger},
        description: "{$description}",
        key: "{$EVERYPAY_CONFIGURATION.pk}",
        locale: "{$locale}",
                callback: 'handleTokenResponse'{if !$EVERYPAY_CONFIGURATION.EVERYPAY_LIVE_MODE},
                sandbox: 1{/if}
    }

    var $everypayForm;
    var MSG_PLEASE_WAIT = '{l s='Submitting' mod='everypaypayments'}';

    $(document).ready(function () {
        $everypayForm = $('#everypay-payment-form');
        bindEverypayForm();
        setTimeout(function () {
            EverypayButton.jsonInit(EVERYPAY_OPC_BUTTON, $everypayForm);
        }, 1500);
    });
    //]]>
</script>


