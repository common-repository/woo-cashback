jQuery( document ).ready(function() {
var cur = walletObject.currency;
var wallet = walletObject.walletbalance;
if(isNaN(wallet) || wallet == 0 || wallet == ''){
   wallet = 0; 
}
var wallet = parseFloat(wallet).toFixed(2);
jQuery('.wallet-menu').html('Your Wallet: '+cur+wallet);
});
