<div class="everypayform__standalone_wrapper">
    {if $EVERYPAY_MSG}
        <div class="warning alert alert-warning">{$EVERYPAY_MSG}</div>    
    {/if}
    <br /><br />
    <a href="{$link->getPageLink('order.php', true)}?step=3" class="back_link">« {l s='Other payment methods' mod='everypaypayments'}</a>
</div>