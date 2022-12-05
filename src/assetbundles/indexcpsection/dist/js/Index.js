/**
 * moldap plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    miniorange
 * @copyright Copyright (c) 2022 miniorange
 * @link      miniorange.com
 * @package   Moldap
 * @since     1.0.0
 */
var popUp = document.getElementById('popUpModal');

$(document).ready(function(){
    setTimeout(function(){
        jQuery("#popUpModal").hide();
    },5000);
});

$("#ldapProtocol").on('change', function(){
    var protocol = jQuery("#ldapProtocol").val();
    if(protocol == "ldaps")
        jQuery("#ldapPort").val("636");
    else
        jQuery("#ldapPort").val("389");
});