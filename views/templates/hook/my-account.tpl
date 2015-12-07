<li class="everypaypayments">
    <a href="{$link->getModuleLink('everypaypayments', 'account')|escape:'htmlall':'UTF-8'}" title="{l s='My credit/debit cards' mod='everypaypayments'}">
        {if !$in_footer}
            <i class="icon-credit-card"></i>
            {*<img src="{$module_template_dir}assets/images/creditcard.gif" 
                 {if isset($mobile_hook)}
                     class="ui-li-icon ui-li-thumb"
                 {else}
                     class="icon"{/if}
                     alt="{l s='My credit/debit cards' mod='everypaypayments'}"/>*}
            {/if}
            <span>{l s='My credit/debit cards' mod='everypaypayments'}</span>
        </a>
    </li>
