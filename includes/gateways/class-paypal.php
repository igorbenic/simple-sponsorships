<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 10/12/18
 * Time: 03:44
 */

namespace Simple_Sponsorships\Gateways;


class PayPal extends Payment_Gateway {

	public function __construct() {
		$this->id = 'paypal';
		$this->method_title = __( 'PayPal', 'simple-sponsorships' );

		parent::__construct();
	}
}