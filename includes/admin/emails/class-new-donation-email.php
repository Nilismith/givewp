<?php
/**
 * New Donation Email
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.9
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_New_Donation_Email' ) ) :

	/**
	 * Give_New_Donation_Email
	 *
	 * @abstract
	 * @since       1.9
	 */
	class Give_New_Donation_Email extends Give_Email_Notification {
		/* @var Give_Payment $payment */
		public $payment;

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.9
		 */
		public function __construct( $objects = array() ) {
			$this->id          = 'new-donation';
			$this->label       = __( 'New Donation', 'give' );
			$this->description = __( 'Donation Notification will be sent to recipient(s) when new donation received except offline donation.', 'give' );

			$this->has_recipient_field = true;
			$this->notification_status = 'enabled';

			// Initialize empty payment.
			$this->payment = new Give_Payment( 0 );

			parent::__construct();

			add_action( "give_{$this->id}_email_notification", array( $this, 'setup_email_notification' ) );
		}


		/**
		 * Get email subject.
		 *
		 * @since 1.9
		 * @access public
		 * @return string
		 */
		public function get_email_subject() {
			$subject = wp_strip_all_tags( give_get_option( "{$this->id}_email_subject", $this->get_default_email_subject() ) );

			/**
			 * Filters the donation notification subject.
			 *
			 * @since 1.0
			 */
			$subject = apply_filters( 'give_admin_donation_notification_subject', $subject, $this->payment );

			return $subject;
		}


		/**
		 * Get email attachment.
		 *
		 * @since 1.9
		 * @access public
		 * @return string
		 */
		public function get_email_message() {
			$message = give_get_option( "{$this->id}_email_message", $this->get_default_email_message() );
			$message = apply_filters( 'give_donation_notification', $message, $this->payment->ID, $this->payment->payment_meta );

			return $message;
		}


		/**
		 * Get email attachment.
		 *
		 * @since 1.9
		 * @access public
		 * @return array
		 */
		public function get_email_attachments() {
			$attachments = 	array();

			/**
			 * Filters the donation notification email attachments. By default, there is no attachment but plugins can hook in to provide one more multiple.
			 *
			 * @since 1.0
			 */
			$attachments = apply_filters( 'give_admin_donation_notification_attachments', array(), $this->payment->ID, $this->payment->payment_meta );

			return $attachments;
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.9
		 * @access public
		 * @return string
		 */
		public function get_default_email_subject() {
			return esc_attr__( 'New Donation - #{payment_id}', 'give' );
		}


		/**
		 * Get default email message.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @return string
		 */
		public function get_default_email_message() {
			$message = esc_html__( 'Hello', 'give' ) . "\n\n";
			$message .= esc_html__( 'A donation has been made.', 'give' ) . "\n\n";
			$message .= esc_html__( 'Donation:', 'give' ) . "\n\n";
			$message .= esc_html__( 'Donor:', 'give' ) . ' {fullname}' . "\n";
			$message .= esc_html__( 'Amount:', 'give' ) . ' {payment_total}' . "\n";
			$message .= esc_html__( 'Payment Method:', 'give' ) . ' {payment_method}' . "\n\n";
			$message .= esc_html__( 'Thank you', 'give' );


			/**
			 * Filter the new donation email message
			 *
			 * @since 1.9
			 *
			 * @param string $message
			 */
			return apply_filters( 'give_default_new_donation_email', $message );
		}

		/**
		 * Setup email notification.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @param int $payment_id
		 */
		public function setup_email_notification( $payment_id ) {
			$this->payment = new Give_Payment( $payment_id );

			/**
			 * Filters the from name.
			 *
			 * @since 1.0
			 */
			$from_name = apply_filters( 'give_donation_from_name', $this->email->get_from_name(), $this->payment->ID, $this->payment->payment_meta );

			/**
			 * Filters the from email.
			 *
			 * @since 1.0
			 */
			$from_email = apply_filters( 'give_donation_from_address', $this->email->get_from_address(), $this->payment->ID, $this->payment->payment_meta );

			$this->email->__set( 'from_name', $from_name );
			$this->email->__set( 'from_email', $from_email );
			$this->email->__set( 'heading', esc_html__( 'New Donation!', 'give' ) );
			/**
			 * Filters the donation notification email headers.
			 *
			 * @since 1.0
			 */
			$headers = apply_filters( 'give_admin_donation_notification_headers', $this->email->get_headers(), $this->payment->ID, $this->payment->payment_meta );

			$this->email->__set( 'headers', $headers );

			// Send email.
			$this->send_email_notification( array( 'payment_id' => $payment_id ) );
		}
	}

endif; // End class_exists check

return new Give_New_Donation_Email();