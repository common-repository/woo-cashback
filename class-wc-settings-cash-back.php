<?php
/**
 * Woo Cashback First Order Discount Settings
 *
 * @author 	Sourav Seth
 * @category 	Admin
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (  class_exists( 'WC_Settings_Page' ) ) :

/**
 * WC_Settings_Accounts
 */
class WC_Settings_Cash_Back extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
            $this->id    = 'cash_back';
		$this->label = __( 'Cash Back', 'wc_cash_back' );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 25 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
                add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
                add_action( 'woocommerce_admin_field_wcbeditor', array( $this, 'wcb_display_editor' ) );
		add_action( 'woocommerce_update_option_wcbeditor', array( $this, 'wcb_save_editor_val' ) );
                
        }
        
        /**
	 * Get settings array
	 *
	 * @return array
	 */
        public function get_settings() {

		$coupon = get_posts( array( 'post_type' => 'shop_coupon', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		$coupons = array();
		foreach ( $coupon as $cpn ){
			$coupons[$cpn->post_title] = $cpn->post_title;
		}
		return apply_filters( 'woocommerce_' . $this->id . '_settings', array(

			array(	'title' => __( 'Cash Back Settings', 'cash_back' ), 'type' => 'title','desc' => '', 'id' => 'cash_back_title' ),
                array(
				),
                    
                             array(
					'title' 			=> __( 'Enable', 'wc_cash_back' ),
					'desc' 			=> __( 'Enable woo cash back.', 'wc_cash_back' ),
					'type' 				=> 'checkbox',
					'id'				=> 'wcb_enabled',
					'default' 			=> 'no'											
				),
								
				array(
					'title' 	  => __( 'Discount Background Color', 'wc_cash_back' ),
					'id' 		  => 'wcb_bg',
					'type' 		  => 'color',
					'default'	  => '#96588a',
					'css' => 'width: 125px;',
					'desc_tip'	  =>  true
				),
                                array(
					'title' 	  => __( 'Discount Font Color', 'wc_cash_back' ),
					'id' 		  => 'wcb_fc',
					'type' 		  => 'color',
					'default'	  => '#fff',
					'css' => 'width: 125px;',
					'desc_tip'	  =>  true
				),
                              
                                 array(
					'title' 	=> __( "Discount Align Listing", 'wc_cash_back' ),
					'type' 		=> 'select',
					'id'		=> 'wcb_discount_align_listing',
          'options' => array(
          	'default' => __( 'Default', 'wc_cash_back' ),
          	'top-left' => __( 'Top-Left', 'wc_cash_back' ),
                'top-right' => __( 'Top-right', 'wc_cash_back' ),
                'bottom-left' => __( 'Bottom-Left', 'wc_cash_back' ),
                'bottom-right' => __( 'Bottom-Right', 'wc_cash_back' ),
          	),
					'default' 	=> 'default'
				),
				
                    
                              
                                 array(
					'title' 	=> __( "Popup Style", 'wc_cash_back' ),
					'type' 		=> 'select',
					'id'		=> 'wcb_popup_style',
                            'options' => array(
                            'style1' => __( 'Style1', 'wc_cash_back' ),
                            'style2' => __( 'Style2', 'wc_cash_back' ),
                            'style3' => __( 'Style3', 'wc_cash_back' ),
                            'style4' => __( 'Style4', 'wc_cash_back' ),
                            'style5' => __( 'Style5', 'wc_cash_back' ),
          	),
					'default' 	=> 'style5'
				),
                                array(
					'title' 	  => __( 'Select Before Cash Back Text', 'wc_cash_back' ),
					'id' 		  => 'wcb_before_cash_back_text',
					'type' 		  => 'text',
                                        'css' => 'width: 300px;',
                                        'default'	  => 'Select',
					'desc' 		  => __( 'Enter a cash back button text, if customer not select button display this text.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                                array(
					'title' 	  => __( 'Select After Cash Back Text', 'wc_cash_back' ),
					'id' 		  => 'wcb_after_cash_back_text',
					'type' 		  => 'text',
                                        'css' => 'width: 300px;',
                                        'default'	  => 'Selected',
					'desc' 		  => __( 'Enter a cash back button text, if customer select button display this text.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                                array(
					'title' 	  => __( 'Alert Message', 'wc_cash_back' ),
					'id' 		  => 'wcb_alert_message',
					'type' 		  => 'wcbeditor',
                                        'css' => 'width: 500px;height:100px',
					'default'	  => 'Please login to avail this discount.',
					'desc' 		  => __( 'Enter a alert message, If user not logged then display this message.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                                array(
					'title' 	  => __( 'Discount Message', 'wc_cash_back' ),
					'id' 		  => 'wcb_discount_message',
					'type' 		  => 'wcbeditor',
                                        'css' => 'width: 500px;height:100px',
                                        'default'	  => 'Use Promo code GET{COUPONCODE} and get {DISCOUNT} cashback. COD option will not be available on applying this Promo Code',
					'desc' 		  => __( 'Enter a message to display text in product details page.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                                array(
					'title' 	  => __( 'Applied Coupon Text', 'wc_cash_back' ),
					'id' 		  => 'wcb_applied_coupon_text',
					'type' 		  => 'wcbeditor',
                                        'css' => 'width: 500px;height:100px',
                                        'default'	  => 'Promo Code Applied Extra {DISCOUNTPRICE} Cashback',
					'desc' 		  => __( 'Enter a promocode applied text.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                                array(
					'title' 	  => __( 'Terms & Condition', 'wc_cash_back' ),
					'id' 		  => 'wcb_terms_conditions',
					'type' 		  => 'wcbeditor',
                                        'css' => 'width: 500px;height:200px',
                                        'default'	  => 'Please enter terms and condition',
					'desc' 		  => __( 'Enter a Terms and Condition.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                    
                               array(
					'title' 	  => __( 'Cash Back Information message', 'wc_cash_back' ),
					'id' 		  => 'wcb_information_message',
					'type' 		  => 'wcbeditor',
                                        'css' => 'width: 500px;height:200px',
                                        'default'	  => 'Your cashback on this order will be added to your Wallet after your order is delivered.',
					'desc' 		  => __( 'Enter a Information message.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                    
                                array(
					'title' 	  => __( 'Cash Back Mail Subject', 'wc_cash_back' ),
					'id' 		  => 'wcb_mail_subject',
					'type' 		  => 'wcbeditor',
                                        'css' => 'width: 500px;height:200px',
                                        'default'	  => '{DISCOUNTPRICE} added as cash back in your wallet.',
					'desc' 		  => __( 'Enter a Mail Subject.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                                 array(
					'title' 	  => __( 'Cash Back Mail Message', 'wc_cash_back' ),
					'id' 		  => 'wcb_mail_message',
					'type' 		  => 'wcbeditor',
                                        'css' => 'width: 500px;height:200px',
                                        'default'	  => 'Hi there!<br><br>We have just added {DISCOUNTPRICE} to your Paytm Wallet. This cashback is for Order {ORDERID}<br><br>Your updated Paytm Wallet balance is {WALLETBALANCE} .<br><br><br><br>Best wishes',
					'desc' 		  => __( 'Enter a Mail Message.', 'wc_cash_back' ),
					'desc_tip'	  =>  false
				),
                                array( 'type' => 'sectionend', 'id' => 'simple_wcb_options'),
		)); // End pages settings
	}
        
        /**
	* Output wordpress editor for email body condent.
	*
	* @param array $value array of settings variables.
	* @return null displays the editor. 
	*
	*/
	public function wcb_display_editor( $value ) {
		$option_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] ); ?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<?php echo $value['desc']; ?>
				<?php wp_editor( $option_value, esc_attr( $value['id'] ) ); ?>
			</td>
		</tr>
	<?php
	}

	/**
	* Saves the content fpr wp_editor.
	*
	* @return null saves the value of the option. 
	*
	*/
	public function wcb_save_editor_val( $value ) {
		$email_text = sanitize_text_field( $_POST[$value['id']] );
		update_option( $value['id'], $email_text  );
	}
}
return new WC_Settings_Cash_Back();
endif;