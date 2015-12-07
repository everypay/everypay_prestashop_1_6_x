<div class="everypay_container">

    <div class="admin_logo">
        <p>{l s='Accept payments with VISA/Mastercard safely, through EveryPay'}</p>    
    </div>


    <div class="menu_admin">
        <ul>
            <!--
            <li class="text_li_label">Go to:</li>
            <li><a class="button" href="#">Settings</a></li>
            <li><a class="button" href="">Customers</a></li>
            <li><a class="button"href="">Payments</a></li>-->
            <li class="text_li_label">{l s='Are you NEW to EveryPay? You should'}</li>
            <li><a class="button btn-blue" href="http://everypay.gr">{l s='Sign Up'}</a></li>
        </ul>
    </div>

    {$adminMessages.warnings}
    {$adminMessages.errors}
    {$adminMessages.success}

    <div class="ev_settings">
        <form method="post" action="">
            <fieldset>
                <legend><img alt="" src="/modules/{$EVERYPAY_MODULE_NAME}/logo.png" style="width:16px;height:16px;"> {l s='EveryPay account settings'}</legend>

                <div class="mode_switch"><label>{$trans['Choose Mode']}:</label>
                    <input type="radio" id="test_mode" name="mode" value="0" {if !$EVERYPAY_LIVE_MODE}checked{/if}><label for="test_mode">Test</label> 
                    <input type="radio" id="live_mode" name="mode" value="1" {if $EVERYPAY_LIVE_MODE}checked{/if}><label for="live_mode">LIVE</label>
                </div>

                <br />

                <div class="half-fieldset" id="live_mode_keys" {if !$EVERYPAY_LIVE_MODE}style="display:none"{/if}>

                    <h3>{l s='Live Account'} <a href="https://dashboard.everypay.gr" target="_blank"><small>Login at https://dashboard.everypay.gr</small></a></h3>
                    <br />
                    <label for="input_public_key">{$trans['Public Key']}:</label>
                    <div class="margin-form">
                        <input class="monospace" type="text" value="{$EVERYPAY_CONFIGURATION.EVERYPAY_PUBLIC_KEY}" id="input_public_key" name="input_public_key" />
                    </div>

                    <label for="input_secret_key">{$trans['Secret Key']}:</label>
                    <div class="margin-form">
                        <input class="monospace" type="text" value="{$EVERYPAY_CONFIGURATION.EVERYPAY_SECRET_KEY}" id="input_secret_key" name="input_secret_key" />                 
                    </div>

                    <label>&nbsp;</label>
                    <div class="margin-form"><a href="http://everypay.gr/support" target="_blank">{$trans['Where can I find these?']}</a></div>
                </div>


                <div class="half-fieldset" id="test_mode_keys" {if $EVERYPAY_LIVE_MODE}style="display:none"{/if}>                    
                    <h3>{l s='Test Account'} <a href="https://sandbox-dashboard.everypay.gr" target="_blank"><small>Login at https://sandbox-dashboard.everypay.gr</small></a></h3>
                    <br />
                    <label for="input_test_public_key">{$trans['Public Key']}:</label>
                    <div class="margin-form">
                        <input class="monospace" type="text" value="{$EVERYPAY_CONFIGURATION.EVERYPAY_TEST_PUBLIC_KEY}" id="input_test_public_key" name="input_test_public_key" />
                    </div>

                    <label for="input_test_secret_key">{$trans['Secret Key']}:</label>
                    <div class="margin-form">
                        <input class="monospace" type="text" value="{$EVERYPAY_CONFIGURATION.EVERYPAY_TEST_SECRET_KEY}" id="input_test_secret_key" name="input_test_secret_key" />
                    </div>                

                    <label>&nbsp;</label>
                    <div class="margin-form"><a href="http://everypay.gr/support" target="_blank">{$trans['Where can I find these?']}</a></div>
                </div>

                <p class="center"><input type="submit" value="{l s='Save settings'}" name="submitConfiguration" class="button"></p>
            </fieldset>

            <br />

            <fieldset>
                <legend><img alt="" src="..../../../img/admin/edit.gif" style="width:16px;height:16px;"> {l s='Other Settings'}</legend>

                <label for="input_customer_mode">{l s='Enable customers to save cards'}</label>
                <div class="margin-form">
                    <input type="checkbox" value="1" id="input_customer_mode" name="input_customer_mode" {if $EVERYPAY_CUSTOMER_MODE}checked="checked"{/if} autocomplete=off />
                    &nbsp;&nbsp;&nbsp;<a href="https://www.everypay.gr/docs" target="_blank">{$trans['What\'s this?']}</a>
                </div>
                <p class="center"><input type="submit" value="{l s='Save settings'}" name="submitConfiguration" class="button"></p>
            </fieldset>
        </form>
    </div>
</div>
{literal}
    <style type="text/css">
        /* resetters of already css */
        .toolbarBox {}
        /* resetters of already css */
        .everypay_container{}
        .everypay_container .admin_logo{
            background: white url("/modules/everypaypayments/assets/images/back-admin.jpg") no-repeat scroll center bottom / cover ;
            display: inline-block;
            height: auto;
            width: 100%;
        }
        .everypay_container .admin_logo p{
            color: #000;
            font-size: 14px;
            font-style: oblique;
            padding-bottom: 15px;
            padding-left: 91px;
            padding-top: 110px;
        }

        .menu_admin{
            display: inline-block;
            margin: 2%;
            width: 96%;

        }
        .menu_admin ul{}
        .menu_admin ul li{
            float: left;
            margin: 0 3px;
        }
        .menu_admin ul li a{}
        .menu_admin .text_li_label{
            margin-left: 65px;
        }

        .ev_settings input[type="text"],
        .ev_settings textarea,
        .ev_settings select
        {
            margin-bottom: 9px;
            width: 290px;
        }
        .everypay_container .alert{
            margin:10px 0 !important;
            padding:0 15px !important;
        }

        .menu_admin li{
            list-style:none;
        }

        .half-fieldset {
            border-bottom: 1px dashed #C0C0C0;
            float: left;
            margin-bottom: 12px;
            text-align: center;
            width: 100%;
        }
        .half-fieldset h3 {text-align: left; margin-left: 146px;}
        .half-fieldset h3 a {margin-left: 22px; opacity: 0.3; text-decoration: underline}
        .half-fieldset .margin-form{text-align:left;}
        .mode_switch {
            background: none repeat scroll 0 0 #637992;
            color: #FFFFFF;
            display: inline-block;
            padding: 8px;
        }
        .mode_switch label {
            color: #FFFFFF !important;
            float: left;
            font-size: 16px;
            font-weight: normal;
            margin: 0 15px 0 4px;
            text-shadow: none;
            width: auto;
        }
        .mode_switch input {
            float: left;
            margin-top: 1%;
            width: auto;
        }
        .monospace{
            font-family:monospace
        }
    </style>
    <script type="text/javascript">
        //just to reset the radio button
        $(document).ready(function () {
            $('.mode_switch [checked]').trigger('click');

            $('.mode_switch input[type=radio][name=mode]').change(function () {
                if (this.value == '1') {
                    $('#live_mode_keys').show();
                    $('#test_mode_keys').hide();
                }
                else if (this.value == '0') {
                    $('#live_mode_keys').hide();
                    $('#test_mode_keys').show();
                }
            });
        });
    </script>        
{/literal}