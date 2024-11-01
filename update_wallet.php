<?php
/**
 * Woo Cashback Wallet
 *
 * @author 	Sourav Seth
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(isset($_GET['usewallet'])):
$usewallet = sanitize_text_field( $_GET['usewallet'] );

    $userID = sanitize_text_field( $_GET['user'] );
if($usewallet == 1){
    
    update_user_meta($userID,'use_wallet_balance',0);
}if($usewallet == 2){
  
   update_user_meta($userID,'use_wallet_balance',1); 
}
exit;

endif;
if(isset($_GET['discountamount'] )):
$discountamount = sanitize_text_field( $_GET['discountamount'] );
$itemprice = sanitize_text_field( $_GET['itemprice'] );
$userID = sanitize_text_field( $_GET['user'] );
$postID = sanitize_text_field( $_GET['postid'] );
$disType = sanitize_text_field( $_GET['discounttype'] );
    if(isset($discountamount)){
        if($disType=='price'){
           $discountamount = $discountamount; 
        }else{
            $discountamount = $itemprice * $discountamount/100;
        }
    $discountDetailsArray = array('productid'=>$postID,'wallet'=>$discountamount);
    $discontDeatils = serialize($discountDetailsArray);
    update_user_meta( $userID, 'pendding', $discontDeatils );
    exit;
   }
endif;
   ?>
