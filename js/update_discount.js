jQuery( document ).ready(function() {
jQuery( ".discount_div" ).click(function() {
var disVal = jQuery("#dis_val").val();
var disType = dicountObject.disType;
var disprice = dicountObject.disprice;
var itemPrice = dicountObject.itemPrice;
var user = dicountObject.user;
var postid = dicountObject.postid;
var afterSelectText = dicountObject.afterSelectText;
var beforeSelectText = dicountObject.beforeSelectText;
if(disVal == '' || disVal == 0){
jQuery("#dis_val").val(1)
jQuery(".discount_div").html('&#10004; '+ afterSelectText);
jQuery(".efft-price").css('display','block');
jQuery.get("",{'discountamount':disprice,'user':user,'postid':postid,'discounttype':disType,'itemprice':itemPrice},function(data){  
});
}
if(disVal == 1){
jQuery.get("",{'discountamount':'0.00','user':user,'postid':'null','discounttype':disType,'itemprice':itemPrice},function(data){  
});
jQuery("#dis_val").val(0)
jQuery(".discount_div").html(beforeSelectText);
jQuery(".efft-price").css('display','none');
}
});
jQuery( ".term-condition-link" ).click(function() {
 var termCondition = dicountObject.termCondition;
jQuery(".term-condition").colorbox({html:termCondition});
});
jQuery( ".discount_div_not_logged" ).click(function() {
    
 var notLoginUserText = dicountObject.notLoginUserText;
jQuery(".discount_div_not_logged").colorbox({html:notLoginUserText});
});
jQuery("#use-wallet").click( function(){
var user = dicountObject.user;
if( jQuery(this).is(':checked') ){
jQuery.get("",{'usewallet':1,'user':user},function(data){  
location.reload();
}); 
}else{ 
jQuery.get("",{'usewallet':2,'user':user},function(data){ 
location.reload();  
});
}
});
});
