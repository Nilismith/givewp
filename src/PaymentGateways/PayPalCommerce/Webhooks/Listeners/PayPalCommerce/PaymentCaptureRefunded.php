<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

/**
 * Class PaymentCaptureRefunded
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @sicne 2.8.0
 */
class PaymentCaptureRefunded extends PaymentEventListener {
	/**
	 * @inheritDoc
	 */
	public function processEvent( $event ) {
		$paymentId = $this->getPaymentFromRefund( $event->resource, 'up' );

		$donation = $this->paymentsRepository->getDonationByPayment( $paymentId );

		// If there's no matching donation then it's not tracked by GiveWP
		if ( ! $donation || 'refunded' === $donation->status ) {
			return;
		}

		give_update_payment_status( $donation->ID, 'refunded' );
		give_insert_payment_note( $donation->ID, __( 'Charge refunded in PayPal', 'give' ) );

		/**
		 * Fires when a charge has been refunded via webhook
		 *
		 * @since 2.8.0
		 */
		do_action( 'give_paypal_commerce_webhook_charge_refunded', $event, $donation );
	}

	/**
	 * This uses the links property to get payment id from PayPal
	 *
	 * @since 2.8.0
	 *
	 * @param object $refund
	 *
	 * @return string
	 */
	private function getPaymentFromRefund( $refund ) {
		$link = current(
			array_filter(
				$refund->links,
				static function ( $link ) {
					return $link->rel === 'up';
				}
			)
		);

		$accountDetails = $this->merchantDetails->getDetails();

		$response = wp_remote_request(
			$link->href,
			[
				'method'  => $link->method,
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer $accountDetails->accessToken",
				],
			]
		);

		$response = json_decode( $response['body'], false );

		return $response->id;
	}
}
