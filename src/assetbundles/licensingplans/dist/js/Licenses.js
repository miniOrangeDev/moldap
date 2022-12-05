$("#requestQuote").on('click', function(){
    jQuery("#contactSales").show();
});

$(document).ready(function(){
    jQuery("#contactSales").hide();
});

jQuery('#miniorange_ldap_licensing_contact_us_close').click(function () {
    jQuery('#contactSales').hide();
    setTimeout(function(){
        jQuery("#popUpModal").hide();
    },5000);
});