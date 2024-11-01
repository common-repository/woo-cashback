<?php
/*
Plugin Name: Woo Cashback
Plugin URI: http://www.batika.in
Description: The plugin allows you to cashback to the customers on purchase on your store. 
Version: 1.0.0
Author: Sourav Seth
Author URI: http://www.batika.in
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 *
 * @author  Sourav Seth <sauravseth2010@gmail.com>
 * @package Woo Cashback
*/

// No direct file access
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once('update_wallet.php');
include_once('class-cb.php');

new WC_Cash_Back();
