<?php
/**
 * Woo Cashback
 *
 * @author 	Sourav Seth
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WC_Cash_Back{
    
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public function __construct()
    {
		//Check if woocommerce plugin is installed.
                add_action( 'admin_notices', array( $this, 'cb_check_required_plugins' ) );
                
                //Add setting link for the admin settings
                add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'wcb_setting_links') );
                
                //Add backend settings
                add_filter( 'woocommerce_get_settings_pages', array( $this, 'wcb_settings_class' ) );
                
                //Add css and js files
                add_action( 'wp_enqueue_scripts',  array( $this, 'wcb_enque_scripts' ) );
                
                 //Add discount field for the admin
                add_action( 'add_meta_boxes', array($this,'product_discount_custom_field' ));
                
                //Saves the cash back value.
                add_action( 'save_post', array($this,'product_discount_form_save' ));
                
                //Send coupon to the registered users
                if ( get_option( 'wcb_enabled' ) == 'yes' ){
                
               
                
               
                //Add cash back button on product details page
                add_action( 'woocommerce_single_product_summary', array($this,'product_discount_add_option_product_detail_page'),35);
                
                //Add use wallet balance checkbox on cart page
                add_action( 'woocommerce_before_cart_totals', array($this,'product_discount_use_wallet_checbox'));
                
                
                //add_action( 'woocommerce_order_details_after_order_table', array($this,'apply_discount_message_thankyou'));
                
                add_action( 'woocommerce_email_before_order_table', array($this,'apply_discount_message_thankyou'));
                
                //After successfull shopping update customer wallet balance. 
                add_action( 'woocommerce_thankyou', array($this,'update_wallet_balance'),20);
                
                //Discount cart balance if customer checked used wallet balance.
                add_action( 'woocommerce_cart_calculate_fees', array($this,'sale_custom_price'));
                
                //Update customer wallet balance.
                add_action( 'woocommerce_thankyou', array($this,'wallet_update'));
                
                //Add shortcode function for display wallet balance.
                add_shortcode( 'wallet', array($this,'user_wallet_display' ));
                
                //Add cashback discount message on cart page.
                add_action( 'woocommerce_after_shop_loop_item', array($this,'discount_off_message_lising_page' ));
                
                //Add Customer Wallet statement on my account page.
                add_action( 'woocommerce_before_my_account', array($this,'wallet_statement' ));
                
                add_action( 'woocommerce_checkout_order_review', array($this,'apply_discount_message_checkout'),20);
                
                }
                
                          
                
    }
    
    
    /**
    *
    * Add necessary js and css files
    *
    */
    public function wcb_enque_scripts(){
        $popupStyle = get_option('wcb_popup_style');
        if(isset($popupStyle)){
            $popupstyle = "popup/".$popupStyle."/colorbox.css";
        }
        wp_enqueue_script( 'jquery' );
        wp_enqueue_style('custom', plugins_url('css/discount.css',__FILE__ ));
        wp_enqueue_style('popup', plugins_url($popupstyle,__FILE__ ));
        wp_enqueue_script('popup', plugins_url('js/jquery.colorbox-min.js',__FILE__ ));
        
    }
    
    
    /**
    *
    * Check if woocommerce is installed and activated and if not
    * activated then deactivate woo cashback.
    *
    */
     public function cb_check_required_plugins() {

        //Check if woocommerce is installed and activated
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) { ?>

            <div id="message" class="error">
                <p>Woo Cash Back requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be activated in order to work. Please install and activate <a href="<?php echo admin_url('/plugin-install.php?tab=search&amp;type=term&amp;s=WooCommerce'); ?>" target="">WooCommerce</a> first.</p>
            </div>

            <?php
            deactivate_plugins( '/woo-cash-back/woo-cash-back.php' );
        }

    }
    
    
    /**
     * Add new link for the settings under plugin links
     *
     * @param array   $links an array of existing links.
     * @return array of links  along with woo cashback settings link.
     *
     */
    public function wcb_setting_links ( $links ) 
    {
     global $woocommerce;
    $settinglinks = '<a href="'.admin_url('admin.php?page=wc-settings&tab=cash_back').'">Settings</a>'; 
     return array_unshift( $settinglinks,$links  );
    }
    
    
    /**
     * Add new admin setting page for woo cashback settings.
     *
     * @param array   $settings an array of existing setting pages.
     * @return array of setting pages along with cashback settings page.
     *
     */
    public function wcb_settings_class( $settings ) {
        $settings[] = include 'class-wc-settings-cash-back.php';
        return $settings;
    }
    
    /**
     * Add new custom field for cash back amount or percentage for admin.
    */
    public function product_discount_custom_field()
    {
            add_meta_box( 'new-add-field', 'Product Discount', array($this, 'product_discount_field_form'), 'product', 'normal', 'high' );
    }
    
    
    /**
     * Add cash back form for admin.
    */
    public function product_discount_field_form( $post )
    {
            $discount_value = get_post_meta( $post->ID, 'product_prise_discount', true );
            $discount_type = get_post_meta( $post->ID, 'product_discount_type', true );
            wp_nonce_field( 'save_quote_meta', 'payment_nonce' );
    ?>
            <p>
                    <label for="discount_price">Cashback Discount</label>
                    <input type="text" name="discount_price" id="discount_price" value="<?php echo $discount_value; ?>" placeholder="Enter discount price" class="short wc_input_price">
            </p>
            <p>
                    <label for="discount_type">Cashback Discount Type</label>
                    <select name="discount_type" id="discount_type" class="short">
                        <option value="price" <?php if($discount_type=='price'){echo 'selected';}?>>Price</option>
                        <option value="percentage"<?php if($discount_type=='percentage'){echo 'selected';}?>>Percentage</option>
                    </select>
                    
            </p>
    <?php
        return $post;
    }
    
    
    /**
	* Saves the custom field values.
	*
	* @return null saves the value of the option. 
	*
	*/
    public function product_discount_form_save( $id )
    {

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

            if( !isset( $_POST['payment_nonce'] ) || !wp_verify_nonce( $_POST['payment_nonce'], 'save_quote_meta' ) ) return;

            if( !current_user_can( 'edit_post' ) ) return;

            if( isset( $_POST['discount_price'] ) )
                    update_post_meta( $id, 'product_prise_discount', esc_attr( strip_tags( sanitize_text_field( $_POST['discount_price'] ) ) ) );
            if( isset( $_POST['discount_type'] ) )
                    update_post_meta( $id, 'product_discount_type', esc_attr( strip_tags( sanitize_text_field( $_POST['discount_type'] ) ) ) );

    }
    
    
    /**
	* Add cash back button on product details page.
	* Calulate discount and upadte
	* If user not logged display alert message after click cashback button
	*/
    public function product_discount_add_option_product_detail_page($pricehtml)
    {
            global $wpdb;
            global $post;
            $currSymbol = '';
            $curr = '';
            $content = '';
            $hiddenvalue = '';
            $checkBox = '';
            $effectiveMsgDivShow = '';
            $product_discount_discount_type = get_post_meta( $post->ID, 'product_discount_type',true);
            $currSymbol = get_woocommerce_currency_symbol();
            $user_id = get_current_user_id();
            $_product = wc_get_product( $post->ID);
            $product_price = $_product->get_price();
            $discount_value = get_post_meta( $post->ID, 'product_prise_discount', true );
            $walletBalance = get_user_meta($user_id,'wallet', true );
            $useWalletBal = get_user_meta($user_id,'use_wallet_balance', true );
                if(empty($useWalletBal)){
                $checkBox = 'checked';
                 }
            if(isset($walletBalance)){
                $walletChecbox = "<div class='wallet-checkbox'><label for='use-wallet'>Use Wallet Balance</label> <input type='checkbox' name='use-wallet' id='use-wallet' value='1' $checkBox></div>";
            }
            if($product_discount_discount_type == 'percentage')
            {
            $disType = '&#37;';
            $percent = $product_price * $discount_value/100;
            $eff_price = $product_price - $percent;
            }else
                {
                $curr = get_woocommerce_currency_symbol();
                $eff_price = $product_price - $discount_value;
                $disType = 'price';
                }
            if ( is_product() )
            {
            $alertms = get_option('wcb_alert_message');
            $bgColor = get_option('wcb_bg');
            $fontColor = get_option('wcb_fc');
            $beforeSelectText = get_option('wcb_before_cash_back_text');
            $afterSelectText = get_option('wcb_after_cash_back_text');
            $termCondition = get_option('wcb_terms_conditions');
            $notLoginUserText = get_option('wcb_alert_message');
            $discount_value = get_post_meta( $post->ID, 'product_prise_discount', true );
            $termConditionLinkDiv = "<a href='javascript:void(0)' class='term-condition-link'><div class='term-condition'>*T&C</div></a>";
              if($user_id > 0)
              {
                  $penddingData =get_user_meta( get_current_user_id(), 'pendding',true);
                    if(isset($penddingData)){
                    $penddingDataUnsrialize = unserialize($penddingData);
                    $selectectedProductId = $penddingDataUnsrialize['productid'];
                        if($selectectedProductId == $post->ID){
                            $SelectText = '&#10004; '.$afterSelectText;
                            $hiddenvalue = 1;
                            $effectiveMsgDivShow = "style='display:block'";
                        }else{
                           $effectiveMsgDivShow = "style='display:none'"; 
                        }
                    }
                    if(isset($SelectText)){
                        $selectText = $SelectText;
                    }
                    else{
                        $selectText = $beforeSelectText;
                    }
              $alertMsg = "<div class='discount_div'>".$selectText."</div>";  
              }else
               {
                $alertMsg = "<div class='discount_div_not_logged'>".$beforeSelectText."</div>";
               }
              if($discount_value)
              {
                $discountDivMsg    = str_replace( '{COUPONCODE}', $discount_value, get_option( 'wcb_discount_message' ));
                $discountDivMessage    = str_replace( '{DISCOUNT}', $curr.$discount_value.$disType, $discountDivMsg);
                $content = "<input type='hidden' name='dis_val' id='dis_val' value=$hiddenvalue><div class='efft-price' $effectiveMsgDivShow>Effective Price ".$currSymbol.$eff_price."</div>"
                    . "<div class='dis-box' style='background-color:$bgColor ;color:$fontColor ;'>".$discountDivMessage."</div>".$alertMsg.$termConditionLinkDiv;
                
             }
            } 
            wp_register_script('update_discount',plugins_url(__FILE__ ));
            $upadateDiscountDataArray = array('disType' => $disType,'disprice' => $discount_value,'itemPrice'=>$product_price,'user'=>$user_id,'postid'=>$post->ID,'afterSelectText'=>$afterSelectText,'beforeSelectText'=>$beforeSelectText,'termCondition'=>$termCondition,'notLoginUserText'=>$notLoginUserText);
            wp_localize_script('update_discount','dicountObject',$upadateDiscountDataArray);
            wp_enqueue_script('update_discount');
            wp_enqueue_script('updatemenu', plugins_url('js/update_discount.js',__FILE__ ));
            echo $content;
    }
    
    
    
       /**
	* Add checkbox use wallet amount on cart page.
	* Calulate discount and upadte
	*/
        public function product_discount_use_wallet_checbox()
        {
            global $wpdb;
            global $post;
            $curr = '';
            $product_discount_discount_type = get_option('wcb_discount_type');
            $currSymbol = get_woocommerce_currency_symbol();
            $user_id = get_current_user_id();
            $_product = wc_get_product( $post->ID);
            $product_price = get_post_meta( $post->ID, 'price', true);
            $discount_value = get_post_meta( $post->ID, 'product_prise_discount', true );
            $walletBalance = get_user_meta($user_id,'wallet',true);
             $useWalletBal = get_user_meta($user_id,'use_wallet_balance',true);
             $checkBox = '';
                if(empty($useWalletBal)){
                $checkBox = 'checked';
                 }
            
            if($product_discount_discount_type == 'percentage')
            {
            $disType = '&#37;';
            $percent = $product_price * $discount_value/100;
            $eff_price = $product_price - $percent;
            }else
                {
                $curr = get_woocommerce_currency_symbol();
                $eff_price = $product_price - $discount_value;
                $disType = 'price';
                }
           
            if ( is_cart())
            {
                $aaa = '';
                $walletChecbox = '';
                $curr = get_woocommerce_currency_symbol();
                if($walletBalance > 0){
                $walletChecbox = "<div class='wallet-checkbox'><label for='use-wallet'>Use Wallet Balance($currSymbol$walletBalance)</label> <input type='checkbox' name='use-wallet' id='use-wallet' value='1' $checkBox></div>";
            }
             $disBalfetch = get_user_meta( $user_id, 'pendding', true );
             $detailunsearilize = unserialize($disBalfetch);
                 if($detailunsearilize['wallet'] > 0)
                 {
               $promoMsg    = str_replace( '{DISCOUNTPRICE}', $curr. $detailunsearilize['wallet'], get_option( 'wcb_applied_coupon_text' ));
                 $aaa = "<div class='use-wallet'><p class='coupon-msg'>".$promoMsg."</p>"
                         ."</div>";
                 
                 }
             echo $aaa.$walletChecbox;   
           }
           wp_register_script('update_discount',__FILE__);
            $upadateDiscountDataArray = array('user'=>$user_id);
            wp_localize_script('update_discount','dicountObject',$upadateDiscountDataArray);
            wp_enqueue_script('update_discount');
            wp_enqueue_script('updatemenu', plugins_url('js/update_discount.js',__FILE__ ));
           
           
    }
    
    
     /**
	* Upadte customer wallet balance.
	*/
    public function update_wallet_balance( $order_id )
    {
    $information_message = get_option( 'wcb_information_message' );
    $order = new WC_Order( $order_id );
    $user_id = get_current_user_id();
        if ( $order->status != 'failed' )
        {
        $discontDeatils = get_user_meta( $user_id, 'pendding', true );
           if(!empty($discontDeatils))
           {
           $disUnse = unserialize($discontDeatils);
           $prevProcessingData = get_user_meta($user_id,'processing',true);
           if(isset($prevProcessingData)){
           $processingOrderIdArray = $prevProcessingData['orderid'];
           }else{
               $processingOrderIdArray = array();
           }
           if($processingOrderIdArray){
               $currentOrderID = array($order_id);
               $margeProductId = array_merge($prevProcessingData['productid'],array($disUnse['productid']));
               $margeWallet = array_merge($prevProcessingData['wallet'],array($disUnse['wallet']));
               $proIdAssign =  array('productid'=>$margeProductId);
               $walletAssign =  array('wallet'=>$margeWallet);
               $disUnse = array_merge($proIdAssign,$walletAssign);
               $orderMarge =array_merge($processingOrderIdArray,$currentOrderID);
           }else{
              
              $disUnse = array('productid'=>array($disUnse['productid']),'wallet'=>array($disUnse['wallet']));
              $orderMarge = array($order_id); 
           }
           $new = array('order' =>'complete','orderid'=>$orderMarge);
           $comArray =array_merge($disUnse,$new);
           update_user_meta( $user_id, 'processing', $comArray );
           delete_user_meta($user_id,'pendding');
           if(isset($information_message)){
            echo "<p class='wcb_info_msg'>".$information_message."</p>";
           }
           }
         }
     }
        
    public function sale_custom_price($cart_object) 
    {
     global $woocommerce;
     $user_id = get_current_user_id();
       if($user_id)
       {
       $usePromoBal = get_user_meta($user_id,'use_wallet_balance',true);
       $penddingData = get_user_meta($user_id,'pendding',true);
       if(isset($penddingData)){
       $penddingDataUnserialize = unserialize($penddingData);
       $pendingProductId = $penddingDataUnserialize['productid'];
       
       }
       if(empty($usePromoBal)){
           $checkBox = 'checked';
       }
       
       foreach($woocommerce->cart->get_cart() as $dd){
           $_product =$dd['data'];
           $_product_id[] = $_product->id;
       }
       if(!empty($_product_id) && !empty($pendingProductId)){
       if(!in_array($pendingProductId,$_product_id)){
            delete_user_meta($user_id,'pendding');
       }
      
       }
       $cartTotal = $woocommerce->cart->subtotal;
       $userWalletDetail = get_user_meta($user_id,'wallet', true);
       }
       if(!empty($userWalletDetail)){
       $discount = $userWalletDetail;
       
       }
       if (!empty($discount)) 
       { 
           if(empty($usePromoBal)){
              
           if($discount >= $cartTotal ){
            $restWalletAmount = $discount - $cartTotal;
            $cartTotal = $cartTotal;
            }else{
               $restWalletAmount = 0;
               $cartTotal = $discount;
               
           }
           
       $cartTotal *= -1;
       $cart_object->add_fee('Wallet Discount', $cartTotal, true, '');
       if(is_checkout()){
            update_user_meta($user_id,'wallet_pendding',$restWalletAmount);
        }
        }else{
          delete_user_meta($user_id,'wallet_pendding');  
        }
       }
    }
    
    
    public function  apply_discount_message_checkout()
    {
       $user_id = get_current_user_id(); 
       $curr = get_woocommerce_currency_symbol();
       $disBalfetch = get_user_meta( $user_id, 'pendding', true );
       $detailunsearilize = unserialize($disBalfetch);
       if(!empty($detailunsearilize['wallet']))
        {
        $promoMsg    = str_replace( '{DISCOUNTPRICE}', $curr. $detailunsearilize['wallet'], get_option( 'wcb_applied_coupon_text' ));
       if ( isset( $user_id ) ) 
            {
           $discontDeatils = get_user_meta( $user_id, 'pendding', true );
           if(!empty($discontDeatils))
           {
           ?>
            <div class="wallet_discount_message">
                <p><?php if(isset($promoMsg)): echo $promoMsg; endif;?></p>
            </div>
            <?php }
            }
        }
        
        
    }
    
    
    public function apply_discount_message_thankyou($order_id){
       $order = new WC_Order( $order_id );
       
        $user_id = get_current_user_id();
        $currSymbol = get_woocommerce_currency_symbol();
        $information_message = get_option( 'wcb_information_message' );
        if ( $order->status != 'failed' ) 
        { 
            if ( isset( $user_id ) ) 
            {
              if(isset($information_message)){
                    echo "<p class='wcb_info_msg'>".$information_message."</p>";
                    }
                
            }
        }
    }
    
   
    
    /**
	* Check transaction is failed or success if success then update wallet amount.
	*/
    public function wallet_update( $order_id )
    {
    $order = new WC_Order( $order_id );
    $user_id = get_current_user_id();
    $currSymbol = get_woocommerce_currency_symbol();
       if ( $order->status != 'failed' ) 
       { 
            if ( isset( $user_id ) ) 
            {
            $usePromoBal = get_user_meta($user_id,'use_wallet_balance', true);
            if(empty($usePromoBal)){
            $onholdWallet = get_user_meta($user_id,'wallet_pendding', true);
            if(isset($onholdWallet)){
            update_user_meta($user_id,'wallet',$onholdWallet);
            delete_user_meta($user_id,'wallet_pendding');
            }
            $userWalletDetail = get_user_meta($user_id,'wallet',true);
            $userWalletBalance = $userWalletDetail;
                //if(!empty($userWalletBalance))
                //{
                    wp_register_script('update_wallet_menu',__FILE__);
                    $walletMenuArray = array('currency'=>$currSymbol,'walletbalance' => $userWalletBalance);
                    wp_localize_script('update_wallet_menu','walletObject',$walletMenuArray);
                    wp_enqueue_script('update_wallet_menu');
                    wp_enqueue_script('updatemenu', plugins_url('js/update_wallet_menu.js',__FILE__ ));
                //}
            }
            }
        }
    }

    public function user_wallet_display()
    {
    global $wpdb;
    global $woocommerce;
    $curr = get_woocommerce_currency_symbol();
    $user_id = get_current_user_id();
    $customerDiscontDeatils = get_user_meta( $user_id, 'processing', true );
    
        if(!empty($customerDiscontDeatils))
        {
        $orderID = $customerDiscontDeatils['orderid'];
       
        $totalOrder = count($orderID);
        $newWalletAmount = $customerDiscontDeatils['wallet'];
        $postdata = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_status = 'wc-completed'", OBJECT );
        
        if(isset($postdata)){
        $totalCompleteOrder = count($postdata);
        
        for($i=0; $i<$totalCompleteOrder; $i++){
          $completeOrderID[] = $postdata[$i]->ID;
        }
       
        if(!empty($orderID) && !empty($completeOrderID)){
        $processingWalletOrderId = array_intersect($completeOrderID,$orderID);
       
        foreach($processingWalletOrderId as $k=>$walletOrderId){
           $processingWalletOrderIdKeys[] =  array_search($walletOrderId,$orderID);
        }
             if(isset($processingWalletOrderIdKeys)){
            foreach($processingWalletOrderIdKeys as $walletKey){
                $walletArray[] = $newWalletAmount[$walletKey];
            }
            $walletArrayCount = count($walletArray);
             }
            
        }
        
            if(!empty($processingWalletOrderId))
            {
            $k = 0;
            foreach($processingWalletOrderId as $completeId){
                   $getPostDataStatus = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE ID = '$completeId'", OBJECT );
                   
                   $walletAmount = $walletArray[$k];
                   if( $getPostDataStatus[0]->post_status == 'wc-completed' ){
                      $Idkey = array_search($completeId,$customerDiscontDeatils['orderid']);
                      $completeOrderDetails = get_user_meta($user_id,'wallet_order_complete_details',true);
                      if(!empty($completeOrderDetails)){
                          $unserializeCompleteData = unserialize($completeOrderDetails);
                          $newCompleteOrder = array('Date'=>array($getPostDataStatus[0]->post_date),'productid'=>array($customerDiscontDeatils['productid'][$Idkey]),'orderid'=>array($customerDiscontDeatils['orderid'][$Idkey]),'wallet'=>array($customerDiscontDeatils['wallet'][$Idkey]));
                          $newOrderDateMarge = array_merge($newCompleteOrder['Date'],$unserializeCompleteData['Date']);
                          $newOrderProductIdMarge = array_merge($newCompleteOrder['productid'],$unserializeCompleteData['productid']);
                          $newOrderOrderIdMarge = array_merge($newCompleteOrder['orderid'],$unserializeCompleteData['orderid']);
                          $newOrderWalletMarge = array_merge($newCompleteOrder['wallet'],$unserializeCompleteData['wallet']);
                          $completeOrderArray = array('Date'=>$newOrderDateMarge,'productid'=>$newOrderProductIdMarge,'orderid'=>$newOrderOrderIdMarge,'wallet'=>$newOrderWalletMarge);
                          $serializeCompleOrder = serialize($completeOrderArray);
                          delete_user_meta($user_id,'wallet_order_complete_details');
                          update_user_meta($user_id,'wallet_order_complete_details',$serializeCompleOrder);
                      }else{
                          $completeOrderArray = array('Date'=>array($getPostDataStatus[0]->post_date),'productid'=>array($customerDiscontDeatils['productid'][$Idkey]),'orderid'=>array($customerDiscontDeatils['orderid'][$Idkey]),'wallet'=>array($customerDiscontDeatils['wallet'][$Idkey])); 
                          $serializeCompleOrder = serialize($completeOrderArray);
                          update_user_meta($user_id,'wallet_order_complete_details',$serializeCompleOrder);
                      }
                      
                      unset($completeId,$customerDiscontDeatils['orderid'][$Idkey]);
                      unset($completeId,$customerDiscontDeatils['wallet'][$Idkey]);
                      $productId = $customerDiscontDeatils['productid'];
                      $restOrder = $customerDiscontDeatils['orderid'];
                      $restWallet = $customerDiscontDeatils['wallet'];
                      $updateRestOrderDataArray = array('productid'=>$productId,'wallet'=>$restWallet,'order'=>'complete','orderid'=>$restOrder);
                      $getWalletAmount = get_user_meta($user_id,'wallet', true); 
                      if(!empty($getWalletAmount))
                        { 
                        $toatalWalletAmount = $getWalletAmount + $walletAmount;
                        update_user_meta( $user_id, 'wallet', $toatalWalletAmount );
                        }else
                            {
                            update_user_meta( $user_id, 'wallet', $walletAmount );
                            $toatalWalletAmount = $walletAmount;
                            }
                    $user = get_post_meta(  $orderID[0], '_customer_user', true );
                    if( $user ) {
                    $data = get_userdata( $user );
                    $email = $data->user_email;
                    }
                    $message = get_option( 'wcb_mail_message' );
                    $subject    = str_replace( '{DISCOUNTPRICE}', $walletAmount, get_option( 'wcb_mail_subject' ) );
                    $search = array('{DISCOUNTPRICE}','{ORDERID}','{WALLETBALANCE}');
                    $replace = array($walletAmount,$orderID[0],$toatalWalletAmount);
                    $body    = str_replace( $search, $replace, get_option( 'wcb_mail_message' ) );
                    add_filter( 'wp_mail_content_type', array( $this, 'wcb_mail_content_type' ) );
                    
                    if ( version_compare( $woocommerce->version, '2.3',  ">=" ) ) {
                      $mailer = WC()->mailer();
                      $mailer->send( $email, $subject, $mailer->wrap_message( $subject, $body ), '', '' );
                    }
                    else
                    wp_mail( $email, $subject, wpautop( $body ) );
                    delete_user_meta( $user_id, 'processing' );
                    update_user_meta($user_id,'processing',$updateRestOrderDataArray);
                    remove_filter( 'wp_mail_content_type', array( $this, 'wcb_mail_content_type' ) );
                    
                            
                   }
                   
                   $k++;
                }
                
               
           }
        }
        }
        if($user_id > 0)
            {
            $userWalletDetail = get_user_meta($user_id,'wallet', true);
            if($userWalletDetail == '' ){
            $userWalletDetail = 0;
            $userWalletBalance = number_format($userWalletDetail,2);
            }else{
               $userWalletBalance = number_format($userWalletDetail,2); 
            }
            
            $walletDetailPath = plugins_url("wallet-details.php?userwallet=$user_id", __FILE__ );
            $walletBalDiss = "<span class='wallet-menu'>"."Your Wallet: ".$curr.$userWalletBalance."</span>";
           
            return $walletBalDiss;
            }
    }
    
    
    
    /**
	* Display off amount or percentage on shop page .
        * and dynamically change color, backgroungcolor, off message allignment
	*/
    public function discount_off_message_lising_page() 
    {
    global $woocommerce, $product, $post;
    $curr = '';
    $disType = '';
    $discountAmount ='';
    $offPriceFontColor = '';
    $offPriceBackgroundColor = '';
    $offPriceAlign = '';
    
            $offPriceAlign = get_option('wcb_discount_align_listing');
            $offPriceBackgroundColor = get_option('wcb_bg');
            $offPriceFontColor = get_option('wcb_fc');
            if($offPriceAlign == "")
             {
             $offPriceAlign = 'default';
             }
             if($offPriceBackgroundColor == "")
             {
             $offPriceBackgroundColor = '#96588a';
             }
             if($offPriceFontColor == "")
             {
             $offPriceFontColor = '#FFF';
             }
            $getDiscountDetail = get_post_meta( $post->ID, 'product_prise_discount',true);
            $getDiscountTypeDetail = get_post_meta( $post->ID, 'product_discount_type', true);
            if(isset($getDiscountDetail)){
            $discountAmount = $getDiscountDetail;
            }
            if(isset($getDiscountTypeDetail)){
            $discountType = $getDiscountTypeDetail;
             if($discountType == 'percentage')
                {
                $disType = '%';
                }
                else
                    {
                    $curr = get_woocommerce_currency_symbol();
                    }
           }
        
            if($discountAmount > 0)
            {
            ?>
            <span class="<?php echo $offPriceAlign; ?>" style="background-color: <?php echo $offPriceBackgroundColor;?>;color: <?php echo $offPriceFontColor;?>;"><?php echo '+'.$curr.$discountAmount.$disType.' OFF'; ?></span>
            <?php
            }
    }
    
    
    /**
	* Display wallet statement on my account page.
	*/
    public function wallet_statement($cc){
        global $wpdb;
        $curr = get_woocommerce_currency_symbol();
        $user_id = get_current_user_id();
        if(isset($user_id)){
            $statementFetch = get_user_meta($user_id,'wallet_order_complete_details', true);
            $statementUnserializes = unserialize($statementFetch);
            $processingFetch = get_user_meta($user_id,'processing', true);
            $countCompleteOrder = count($statementUnserializes['orderid']);
            $countHoldOrder = count($processingFetch['orderid']);
            $countOrder = $countHoldOrder + $countCompleteOrder;
            include_once('wallet_statement.php');
        }
    }
    
    /**
    *
    * Set email content type
    *
    * @return string content type for the email to be sent.
    *
    */
    public function wcb_mail_content_type() {
        return "text/html";
    }
}

?>