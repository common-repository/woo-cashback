<?php
/**
 * Woo Cashback
 *
 * @author 	Sourav Seth
 * @category 	My Account Page
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<section class="section my-account-orders">
	
	<h2>Wallet Statements</h2>

	<table class="table shop_table my_account_orders">

		<thead>
			<tr>
				<th class="order-number"><span class="nobr">Order</span></th>
				<th class="order-date"><span class="nobr">Date</span></th>
				<th class="order-status"><span class="nobr">Status</span></th>
				<th class="order-total"><span class="nobr">Total</span></th>
				<th class="order-actions">Product</th>
			</tr>
		</thead>

		<tbody>
                    <?php
                    
                    $k=0;
                     for($i=0; $i<$countOrder; $i++ ){
                         if(empty($statementUnserializes['orderid'][$i])){$orderId = $processingFetch['orderid'][$k];}else{$orderId = $statementUnserializes['orderid'][$i];}
                         $order = wc_get_order($orderId);
                         $customer_view_order_url = esc_url( $order->get_view_order_url() );
                     ?>
                    <tr class="order">
                                        
					<td class="order-number">
						<a href="<?php echo $customer_view_order_url;?>">
                                                        <?php if(empty($statementUnserializes['orderid'][$i])){echo $processingFetch['orderid'][$k];}else{echo $statementUnserializes['orderid'][$i];}?>						
                                                </a>
					</td>
					<td class="order-date">
						<time><?php echo get_the_date('F j, Y',$orderId);?></time>
					</td>
					<td class="order-status" style="text-align:left; white-space:nowrap;">
						<?php if(empty($statementUnserializes['orderid'][$i])){echo 'On Hold';}else{echo 'Completed';}?>					
                                        </td>
					<td class="order-total">
                                            <span class="amount">
                                                <?php if(empty($statementUnserializes['wallet'][$i])){echo $curr.$processingFetch['wallet'][$k];}else{echo $curr.$statementUnserializes['wallet'][$i];}?>
                                            </span>					
                                        </td>
					<td class="order-actions">
                                            <?php if(empty($statementUnserializes['productid'][$i])){$productId = $processingFetch['productid'][$k];}else{$productId = $statementUnserializes['productid'][$i];}?>
                                                <a href="<?php echo get_permalink($productId);?>" class="button view">View</a>					
                                        </td>
                    </tr>
                     <?php 
                        if(empty($statementUnserializes['orderid'][$i])){
                        
                         $k = $k+1;
                        
                        }
                     
                     }
                     ?>
                </tbody>

	</table><!-- /.wallet_statement_table -->

</section>
