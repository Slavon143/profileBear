<?php if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Svea_Checkout_Helper {

	public static function get_svea_error_message( Exception $e ) {
		$code = $e->getCode();

		switch( $code ) {
			case 400:
				return __( 'The current currency is not supported in the selected country. Please switch country or currency and reload the page.', WC_Svea_Checkout_i18n::TEXT_DOMAIN );
			case 401:
				return __( 'The checkout cannot be displayed due to an error in the connection to Svea. Please contact the shop owner regarding this issue.', WC_Svea_Checkout_i18n::TEXT_DOMAIN );
			default:
				return $e->getMessage();
		}
	}

	public static function get_svea_locale( $locale ) {
		switch( $locale ) {
			case 'sv_SE':
				return 'sv-SE';
			case 'nn_NO':
				return 'nn-NO';
			case 'nb_NO':
				return 'nn-NO';
			case 'fi_FI':
				return 'fi-FI';
			case 'da_DK':
				return 'da-DK';
			default:
				return 'sv-SE';
		}
	}

	/**
	 * Splits full names into first name and last name
	 *
	 * @param $full_name
	 *
	 * @return array
	 */
	public static function split_customer_name( $full_name ) {
		$customer_name = array(
			'first_name'    => '',
			'last_name'     => ''
		);

		// Split name and trim whitespace
		$full_name_split = array_map( 'trim', explode( ' ', trim( $full_name ) ) );

		$full_name_split_count = count( $full_name_split );

		if( $full_name_split_count > 0 ) {
			$customer_name['first_name'] = $full_name_split[0];

			if( $full_name_split_count > 1 ) {
				$customer_name['last_name'] = implode( ' ', array_slice( $full_name_split, 1, $full_name_split_count - 1 ) );
			}
		}

		return $customer_name;
	}
}