var everypay_js2   = document.createElement('script');
everypay_js2.type  = 'text/javascript';
everypay_js2.async = true;
everypay_js2.id    = 'everypay-button-script';
everypay_js2.src   = 'https://button.everypay.gr/js/button.js';

document.getElementsByTagName("head")[0].appendChild(everypay_js2);

$(document).ready(function() {
    var $everypayForm = {};
});

function everypayResponseHandler(status, response) {
    var $everypayForm = $('#everypay-payment-form');
    
    if (response.error) {                
        /* displaying the error to the user */
        $everypayForm.find(".form-error").html(response.error.message).show();
        $everypayForm.find(".submit-payment").removeAttr("disabled");
    } else {
        var token = response['token'];
        /* adding the token to the form */
        $everypayForm.append('<input type="hidden" name="token" value="' + token + '"/>');
        /* submitting the form */
        $everypayForm.submit();
    }
}


function bindEverypayForm(){   
    /*
     * The customer mode selector
     * 
     */
    $('.everypay_cardway_selection .checked').removeClass('checked');
    $('.everypay_cardway_selection input[type="radio"]').bind('change',function(){
        
        var selected = $(this).val();
        
        if ($('.everypay_selection_wrapper_'+selected).is(':visible')){
            return;
        }
        
        $('.everypay_cardway_selection > *').attr('disabled', 'disabled');
        
        $('[class^="everypay_selection_wrapper_"]:visible').fadeOut(function(){
            $('.everypay_selection_wrapper_'+selected).fadeIn();
            $('.everypay_cardway_selection > *').removeAttr('disabled');
        });
    });
    
    //$('.everypay_cardway_selection #select_saved_card').trigger('click');
    
    $('.submit_saved_card').bind('click', function(){
       $('.everypay_selection_wrapper_saved_card .loading-everypay').show();
       $('.everypay_cardway_selection').remove();
    });    
    
    $('.everypay_customer_cards_container input[type="submit"]').bind('click', function() {
        if(!confirm('Are you sure?') )
            return false;
    });
    
    var $select_saved = $('#select_saved_card')
    if ($select_saved.length){
        $select_saved.trigger('click')
    }    
}

function bindCardWaySelector(){
    var $submitBtn = $('#payment_bouton .submit');
    var $evButton = $('.button-holder');
    $('#hook_payment_wrapper > .payment_module').bind('click',function(){
        if($(this).hasClass('everypay-buttton-wrapper')){
            $evButton.show();
            $submitBtn.hide();
        } else{
            $submitBtn.show();
            $evButton.hide();
        }
    });
    
    $('#hook_payment_wrapper > .payment_module.everypay-buttton-wrapper').trigger('click');
    $('.everypay-buttton-wrapper [type="radio"]').attr('checked', 'checked');
}

function handleTokenResponse(response) {    
    //var elements2disable = $everypayForm.find("button, input:not([type=checkbox]), select");
    $('#everypay-payment-form').hide(function(){
        $('.loading-everypay').show();
        $('.everypay_cardway_selection').remove();
        
    });

    if (response.error) {
        $everypayForm.find(".form-error").html(response.error.message).show();
        //$(elements2disable).removeAttr('disabled').removeClass('disabled opacty');
    } else {
        $('.opc-overlay').show();
        disableLinks()
        var token = response.token;
        $everypayForm.append($('<input type="hidden" name="everypayToken"/>').val(token));
        $everypayForm.get(0).submit();
        /*
        window.onbeforeunload = function (e) {
            return('Παρακαλούμε περιμένετε να τελειώσει η αποστολή των δεδομένων');
        }*/
    }
}

function disableLinks(){
    $('.payment_module a').unbind('click').bind('click', function(e){
            e.preventDefault();
            return false;
    });
}