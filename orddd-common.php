<?php

/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Common functions used in admin as well as on frontend.
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Common-Functions
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Common functions used in the plugin
 *
 * @class orddd_common
 */
class orddd_common {

	/**
	 * Default Constructor
	 *
	 * @since 8.1
	 */
	public function __construct() {
		add_filter( 'ordd_pro_tracker_data', array( &$this, 'orddd_ts_add_plugin_tracking_data' ), 10, 1 );
		add_filter( 'ts_tracker_opt_out_data', array( &$this, 'orddd_get_data_for_opt_out' ), 10, 1 );
	}

	/**
	 * Tracking data to send when No, thanks. button is clicked.
	 *
	 * @hook ts_tracker_opt_out_data
	 *
	 * @param array $params Parameters to pass for tracking data.
	 *
	 * @return array Data to track when opted out.
	 * @since 6.8
	 */
	public function orddd_get_data_for_opt_out( $params ) {
		$plugin_data['ts_meta_data_table_name'] = 'ts_tracking_meta_data';
		$plugin_data['ts_plugin_name']          = 'Order Delivery Date Pro for WooCommerce';

		$params['plugin_data'] = $plugin_data;

		return $params;
	}

	/**
	 * Get Order Delivery Date license key
	 *
	 * @return array License data
	 * @since 6.8
	 */
	private static function ts_get_plugin_license_key() {
		return array(
			'license_key'   => get_option( 'edd_sample_license_key_odd_woo' ),
			'active_status' => get_option( 'edd_sample_license_status_odd_woo' ),
		);
	}

	/**
	 * Get Order Delivery Date plugin version
	 *
	 * @return array Order Delivery Date plugin version
	 * @since 6.8
	 */
	private static function ts_get_plugin_version() {
		global $orddd_version;
		return $orddd_version;
	}

	/**
	 * Get all plugin options starting with orddd_ prefix.
	 *
	 * @globals resource WordPress object
	 *
	 * @return array Plugin Settings
	 * @since 6.8
	 */
	private static function ts_get_all_plugin_options_values() {
		global $wpdb;
		$orddd_custom_count = 0;
		$results            = self::orddd_get_shipping_settings();

		if ( isset( $results[0] ) ) {
			$orddd_custom_count = count( $results );
		}

		$delivery_settings_based_on = array();
		$time_slider_in_custom      = 'off';
		$text_block_enabled         = 'off';
		
		$fixed_weekday_charges      = 'off';
		$percentage_weekday_charges = 'off';

		$percentage_specific_day_charges = 'off';
		$fixed_specific_day_charges      = 'off';

		$percentage_sameday_charges = 'off';
		$fixed_sameday_charges      = 'on';

		$percentage_nextday_charges = 'off';
		$fixed_nextday_charges      = 'off';

		$fixed_time_slot_charges      = 'off';
		$percentage_time_slot_charges = 'off';

		$orddd_lockout_date_after_orders = 'off';

		$has_custom_method_based_on_shipping                                   = 'off';
		$has_custom_method_based_on_product_categories                         = 'off';
		$has_custom_method_based_on_product_categories_and_shipping_method     = 'off';
		$has_custom_method_based_on_products                                   = 'off';
		$has_custom_method_based_on_products_and_shipping_method               = 'off';
		$has_custom_method_based_on_pickup_locations                           = 'off';
		$has_custom_method_based_on_pickup_locations_and_shipping_method       = 'off';
		$weekday_based_schedule_enable                                         = 'off';
		

		$shipping_methods_disabled_by_dates     = 'off';
		$shipping_methods_disabled_by_weekdays  = 'off';
		$methods_disabled                       = ORDDD_Functions::orddd_get_disabled_shipping();	
		
		if ( 'on' === get_option( 'orddd_delivery_checkout_options' ) ) {
			$weekday_based_schedule_enable = 'on';
		}

		if ( 'text_block' === get_option( 'orddd_enable_day_wise_settings' ) ) {
			$text_block_enabled = 'on';
		}
		if ( count( $methods_disabled ) > 1 ) {
			foreach ( $methods_disabled as $method_disabled ) {
				foreach ( $method_disabled['delivery_date'] as $delivery_date ) {
					if ( strpos( $delivery_date, 'weekday' ) ) {
						$shipping_methods_disabled_by_weekdays = 'on';
					}
					if ( strpos( $delivery_date, '-' ) ) {
						$shipping_methods_disabled_by_dates = 'on';
					}				
				}
			}
		}		
		
		$holidays = 'off';

		$business_opening_time = ! empty( get_option( 'orddd_business_opening_time' ) ) ? 'on' : 'off';
		$business_closing_time = ! empty( get_option( 'orddd_business_closing_time' ) ) ? 'on' : 'off';
		$time_slots_blocked    = ! empty( get_option( 'orddd_disable_time_slot_log' ) ) ? 'on' : 'off' ;	

		$tax_calculation_for_delivery_charges    =  'on' === get_option( 'orddd_enable_tax_calculation_for_delivery_charges' ) ? 'on' : 'off';
		$pickup_location_mandatory               =  'on' === get_option( 'orddd_pickup_location_mandatory' ) ? 'on' : 'off';
		$import_event_from_calendar              = ! empty( get_option( 'orddd_ics_feed_urls' ) ) ? 'on' : 'off';
		$reminder_email_before_days              = get_option( 'orddd_reminder_email_before_days' ) > 0 ? 'on' : 'off';
		$time_slider_enabled                     = 'on' ===  get_option( 'orddd_enable_delivery_time' ) ? 'on' : 'off';
		
		$global_lockout_for_time_slots =  get_option( 'orddd_global_lockout_time_slots' ) > 0 ? 'on' : 'off';

		$orddd_asap_enabled_in_custom = 'off';
		$ordd_lockout_enabled         = get_option( 'orddd_lockout_date_after_orders' );
		if ( $ordd_lockout_enabled > 0 ) {
			$orddd_lockout_date_after_orders = 'on';
		}
		
		$weekdays = ORDDD_Functions::orddd_global_weekdays_settings();
		if ( 'on' === get_option( 'orddd_enable_weekdays' ) ) {
			foreach ( $weekdays as $weekday ) {
				if ( $weekday['additional_charges'] > 0 ) {
					if ( 'percentage' === $weekday['charges_type'] ) {
						$percentage_weekday_charges = 'on';			
					} else {
						$fixed_weekday_charges = 'on';
					}
				}
			}
		}
		$specific_days = ORDDD_Functions::orddd_get_global_specific_dates();

		if ( 'on' === get_option( 'orddd_enable_specific_delivery_dates' ) ) {
			foreach ( $specific_days as $specific_day ) {
				if ( $specific_day['additional_charges'] > 0 ) {
					if ( 'percentage' === $specific_day['charges_type'] ) {
						$percentage_specific_day_charges = 'on';			
					} else {
						$fixed_specific_day_charges = 'on';
					}
				}
			}
		}
		
		$time_slots = ORDDD_Functions::orddd_get_timeslots();
		
		foreach ( $time_slots as $time_slot ) {
			if ( $time_slot->additional_charges > 0 ) {
				if ( 'percentage' === $time_slot->charges_type ) {
					$percentage_time_slot_charges = 'on';					
				} else {
					$fixed_time_slot_charges = 'on';
				}
			}		
		}
		
		$same_day_cutoff_enabled = ORDDD_Functions::orddd_same_day_cutoff_enabled();
		$same_day_fees           = get_option( 'orddd_same_day_additional_charges' );		
		if ( 'on' === $same_day_cutoff_enabled && $same_day_fees > 0 ) {
			$same_day_charges_type   = get_option( 'orddd_same_day_charges_type');
			if ( 'percentage' === $same_day_charges_type ) {
				$percentage_sameday_charges = 'on';			
			} else {
				$fixed_sameday_charges = 'on';	
			}
		}

		$next_day_cutoff_enabled = ORDDD_Functions::orddd_next_day_cutoff_enabled();
		$next_day_fees           = get_option( 'orddd_next_day_additional_charges' );
		if ( 'on' === $next_day_cutoff_enabled && $next_day_fees > 0 ) {
			$next_day_charges_type   = get_option( 'orddd_next_day_charges_type');
			if ( 'percentage' === $next_day_charges_type ) {
				$percentage_nextday_charges = 'on';
			} else {
				$fixed_nextday_charges = 'on';
			}
		}
		
		$current_holidays = $recurring_holidays = false;

		$global_holidays = ORDDD_Functions::orddd_get_global_holidays();
		if ( count( $global_holidays ) > 0 ) {
			foreach ( $global_holidays as $holiday ) {
				if ( 'current' === $holiday['holiday_type'] ) {
					$current_holidays = true;
				}
				if ( 'recurring' === $holiday['holiday_type'] ) {
					$recurring_holidays = true;
				}
			}
		}

		foreach ( $results as $result ) {
			if ( empty( trim( $result->option_value, '[' ) ) ) {
				continue;
			}
			$method            = unserialize( $result->option_value, ['allowed_classes' => false] );			
			$based_on          = $method['delivery_settings_based_on'];
			if ( empty( $based_on ) ) {
				continue;
			}
			$shipping_methods                = count( $method['shipping_methods'] );
			$pickup_categories               = count( $method['categories_for_pickup_locations'] );		
			$shipping_methods_for_categories = count( $method['shipping_methods_for_categories'] );
			$shipping_methods_for_products   = count( $method['shipping_methods_for_products'] );

			$delivery_settings_based_on[] = array(
				'method'                          => $based_on[0],
				'shipping_methods'                => $shipping_methods,
				'pickup_categories'               => $pickup_categories,
				'shipping_methods_for_categories' => $shipping_methods_for_categories,
				'shipping_methods_for_products'   => $shipping_methods_for_products,
			);
			if ( 'shipping_methods' === $based_on[0] && $shipping_methods > 0 ) {
				$has_custom_method_based_on_shipping = 'on';
			}
			if ( 'product_categories' === $based_on[0] ) {
				if ( $shipping_methods_for_categories > 0 ) {
					$has_custom_method_based_on_product_categories_and_shipping_method = 'on';
				} else {
					$has_custom_method_based_on_product_categories = 'on';
				}
			}
			if ( 'products' === $based_on[0] ) {
				if ( $shipping_methods_for_products > 0 ) {
					$has_custom_method_based_on_products_and_shipping_method = 'on';
				} else {
					$has_custom_method_based_on_products = 'on';
				}
			}
			if ( 'orddd_locations' === $based_on[0] ) {
				if ( $pickup_categories > 0 ) {
					$has_custom_method_based_on_pickup_locations_and_shipping_method = 'on';
				} else {
					$has_custom_method_based_on_pickup_locations = 'on';
				}
			}
			if ( isset( $method['weekday_based_schedule_enable'] ) && 'on' === $method['weekday_based_schedule_enable'] ) {
				$weekday_based_schedule_enable = 'on';
			}	
			if ( isset( $method['time_settings']['enable'] ) && 'on' === $method['time_settings']['enable'] ) {
				$time_slider_enabled = 'on';
			}		
			if ( isset( $method['orddd_delivery_checkout_options'] ) && 'text_block' === $method['orddd_delivery_checkout_options'] ) {
				$text_block_enabled = 'on';
			}
			if ( isset( $method['timeslot_asap_option'] ) && 'checked' === $method['timeslot_asap_option'] ) {
				$orddd_asap_enabled_in_custom      = 'on';
			}
			if ( isset( $method['date_lockout'] ) && $method['date_lockout'] > 0 ) {
				$orddd_lockout_date_after_orders = 'on';
			}

			if ( 'on' === $method['delivery_type']['weekdays'] ) {
				foreach ( $method['weekdays'] as $weekday ) {
					if ( $weekday['additional_charges'] > 0 ) {
						if ( 'percentage' === $weekday['charges_type'] ) {
							$percentage_weekday_charges = 'on';							
						} else {
							$fixed_weekday_charges = 'on';
						}
					}
				}
			}
			if ( 'on' === $method['delivery_type']['specific_dates'] && strpos( $method['specific_dates'], 'fixed' ) ) {
				$fixed_specific_day_charges = 'on';
			}
			if ( 'on' === $method['delivery_type']['specific_dates'] && strpos( $method['specific_dates'], 'percentage' ) ) {
				$percentage_specific_day_charges = 'on';
			}
			
			if ( ! empty( $method['same_day']['after_hours'] ) && $method['same_day']['additional_charges'] > 0 ) {
				if ( 'percentage' === $method['same_day']['charges_type'] ) {
					$percentage_sameday_charges = 'on';	
				} else {
					$fixed_sameday_charges = 'on';
				}
			}

			if ( ! empty( $method['next_day']['after_hours'] ) && $method['next_day']['additional_charges'] > 0 ) {
				if ( 'percentage' === $method['next_day']['charges_type'] ) {
					$percentage_nextday_charges = 'on';						
				} else {
					$fixed_nextday_charges = 'on';
				}
			}

			$custom_holidays = ORDDD_Functions::orddd_get_custom_holidays( $method );
			if ( count( $custom_holidays ) > 0 ) {
				if ( strpos( $method['holidays'], 'on' ) ) {
					$recurring_holidays = true;
				} else {
					$current_holidays =  true;
				}			
			}			
		}
		if ( $recurring_holidays && $current_holidays ) {
			$holidays = 'recurring_and_current';
		} elseif ( $recurring_holidays ) {
			$holidays = 'recurring';
		} elseif ( $current_holidays ) {
			$holidays ='current';
		}
		
		return array(
			'enable_delivery'                       => get_option( 'orddd_enable_delivery_date' ),
			'delivery_options'                      => get_option( 'orddd_delivery_checkout_options' ),
			'weekday_wise_settings'                 => $weekday_based_schedule_enable,
			'date_mandatory'                        => get_option( 'orddd_date_field_mandatory' ),
			'shipping_days'                         => get_option( 'orddd_enable_shipping_days' ),
			'specific_delivery_dates'               => get_option( 'orddd_enable_specific_delivery_dates' ),
			'delivery_time'                         => get_option( 'orddd_enable_delivery_time' ),
			'same_day_delivery'                     => get_option( 'orddd_enable_same_day_delivery' ),
			'next_day_delivery'                     => get_option( 'orddd_enable_next_day_delivery' ),
			'time_slot'                             => get_option( 'orddd_enable_time_slot' ),
			'time_slot_mandatory'                   => get_option( 'orddd_time_slot_mandatory' ),
			'orddd_asap_enabled'                    => get_option( 'orddd_time_slot_asap' ),
			'orddd_asap_charges'                    => get_option( 'orddd_asap_charges' ),
			'orddd_asap_charges_type'               => get_option( 'orddd_asap_charges_type' ),
			'orddd_asap_charges_label'              => get_option( 'orddd_asap_charges_label' ),
			'populate_first_time_slot'              => get_option( 'orddd_auto_populate_first_available_time_slot' ),
			'populate_first_delivery_date'          => get_option( 'orddd_enable_autofill_of_delivery_date' ),
			'no_fields_for'                         => array(
				'virtual_product'  => get_option( 'orddd_no_fields_for_virtual_product' ),
				'featured_product' => get_option( 'orddd_no_fields_for_featured_product' ),
			),
			'edit_date_for_customers'               => get_option( 'orddd_allow_customers_to_edit_date' ),
			'shipping_multiple_address'             => get_option( 'orddd_shipping_multiple_address_compatibility' ),
			'amazon_payments_advanced_gateway'      => get_option( 'orddd_amazon_payments_advanced_gateway_compatibility' ),
			'woocommerce_subscriptions'             => get_option( 'orddd_woocommerce_subscriptions_compatibility' ),
			'woocommerce_subscriptions_auto_update' => get_option( 'orddd_woocommerce_subscriptions_auto_update' ),
			'custom_delivery'                       => ORDDD_Functions::orddd_shipping_based_delivery_enabled(),
			'custom_delivery_count'                 => $orddd_custom_count,
			'calendar_sync'                         => get_option( 'orddd_calendar_sync_integration_mode' ),
			'minimum_delivery_time'                 => get_option( 'orddd_minimumOrderDays' ),
			'min_amount_for_waiving_del_charges'    => get_option( 'orddd_max_order_total_for_charges' ),
			'delivery_charges_based_on_days_enable' => get_option( 'orddd_delivery_charges_based_on_days_enable' ),
			'enable_business_days'                  => get_option( 'orddd_enable_shipping_days' ),
			'lockout_date_on_product_quantity'      => get_option( 'orddd_lockout_date_quantity_based' ),
			'global_max_order_for_custom_settings'  => get_option( 'orddd_global_lockout_custom' ),
			'display_delivery_info_in_mobile_app'   => get_option( 'orddd_add_delivery_in_order_notes' ),
			'enable_pickup_locations'               => get_option( 'orddd_enable_pickup_locations' ),
			'calendar_theme'                        => get_option( 'orddd_calendar_theme' ),
			'time_slots_in_list_view'               => get_option( 'orddd_time_slots_in_list_view' ),
			'delivery_dates_in_dropdown'            => get_option( 'orddd_delivery_dates_in_dropdown' ),
			'calendar_display_mode'                 => get_option( 'orddd_calendar_display_mode' ),
			'delivery_date_on_cart_page'            => get_option( 'orddd_delivery_date_on_cart_page' ),
			'calendar_sync_integration_mode'        => get_option( 'orddd_calendar_sync_integration_mode' ),
			'add_to_calendar_order_received_page'   => get_option( 'orddd_add_to_calendar_order_received_page' ),
			'add_to_calendar_customer_email'        => get_option( 'orddd_add_to_calendar_customer_email' ),
			'add_to_calendar_my_account_page'       => get_option( 'orddd_add_to_calendar_my_account_page' ),
			'custom_orders_csv_export_plugin'       => get_option( 'orddd_show_fields_in_csv_export_check' ),
			'pdf_invoice_and_packing_slips_plugin'  => get_option( 'orddd_show_fields_in_pdf_invoice_and_packing_slips' ),
			'invoice_and_delivery_note_plugin'      => get_option( 'orddd_show_fields_in_invoice_and_delivery_note' ),
			'woocommerce_print_orders_plugin'       => get_option( 'orddd_show_fields_in_cloud_print_orders' ),
			'shipping_multiple_address_plugin'      => get_option( 'orddd_shipping_multiple_address_compatibility' ),
			'orddd_disable_shipping_methods'        => count( ORDDD_Functions::orddd_get_disabled_shipping()),
			'field_placement_on_checkout'           => get_option( 'orddd_delivery_date_fields_on_checkout_page' ),			
			'time_slider_enabled'                   => $time_slider_enabled,			
			'text_block_enabled'                    => $text_block_enabled,
			'asap_enabled_in_custom'                => $orddd_asap_enabled_in_custom,
			'fixed_time_slot_charges'               => $fixed_time_slot_charges,
			'percentage_time_slot_charges'          => $percentage_time_slot_charges,
			'percentage_weekday_charges'            => $percentage_weekday_charges,
			'fixed_weekday_charges'                 => $fixed_weekday_charges,
			'fixed_specific_day_charges'            => $fixed_specific_day_charges,
			'percentage_specific_day_charges'       => $percentage_specific_day_charges,
			'fixed_sameday_charges'                 => $fixed_sameday_charges,
			'percentage_sameday_charges'            => $percentage_sameday_charges,
			'fixed_nextday_charges'                 => $fixed_nextday_charges,
			'percentage_nextday_charges'            => $percentage_nextday_charges,
			'holidays'                              => $holidays,
			'business_opening_time'                 => $business_opening_time,
			'business_closing_time'                 => $business_closing_time,
			'time_slots_blocked'                    => $time_slots_blocked,
			'global_lockout_for_time_slots'         => $global_lockout_for_time_slots,
			'tax_calculation_for_delivery_charges'  => $tax_calculation_for_delivery_charges,
			'shipping_methods_disabled_by_dates'    => $shipping_methods_disabled_by_dates,
			'shipping_methods_disabled_by_weekdays' => $shipping_methods_disabled_by_weekdays,
			'pickup_location_mandatory'             => $pickup_location_mandatory,
			'import_event_from_calendar'            => $import_event_from_calendar,
			'reminder_email_before_days'            => $reminder_email_before_days,
			'has_custom_method_based_on_shipping'                               => $has_custom_method_based_on_shipping,
			'has_custom_method_based_on_product_categories'                     => $has_custom_method_based_on_product_categories,
			'has_custom_method_based_on_product_categories_and_shipping_method' => $has_custom_method_based_on_product_categories_and_shipping_method,
			'has_custom_method_based_on_products'                               => $has_custom_method_based_on_products,
			'has_custom_method_based_on_products_and_shipping_method'           => $has_custom_method_based_on_products_and_shipping_method,
			'has_custom_method_based_on_pickup_locations'                       => $has_custom_method_based_on_pickup_locations,
			'has_custom_method_based_on_pickup_locations_and_shipping_method'   => $has_custom_method_based_on_pickup_locations_and_shipping_method,
		
		 );

	}

	/**
	 * Get order counts based on order status.
	 *
	 * @globals resource WordPress object
	 *
	 * @return int Number of Deliveries
	 * @since 6.8
	 */
	private static function ts_get_order_counts() {
		global $wpdb;
		$order_count = 0;
		$orddd_query = 'SELECT count(ID) AS delivery_orders_count FROM `' . $wpdb->prefix . "posts` WHERE post_type = 'shop_order' AND post_status NOT IN ('wc-cancelled', 'wc-refunded', 'trash', 'wc-failed' ) AND ID IN ( SELECT post_id FROM `" . $wpdb->prefix . 'postmeta` WHERE meta_key IN ( %s, %s ) )';

		$results = $wpdb->get_results( $wpdb->prepare( $orddd_query, '_orddd_timestamp', get_option( 'orddd_delivery_date_field_label' ) ) ); //phpcs:ignore
		if ( isset( $results[0] ) ) {
			$order_count = $results[0]->delivery_orders_count;
		}

		return $order_count;
	}

	/**
	 * Plugin's data to be tracked when Allow option is choosed.
	 *
	 * @hook ts_tracker_data
	 *
	 * @param array $data Contains the data to be tracked.
	 *
	 * @return array Plugin's data to track.
	 * @since 6.8
	 */
    public static function orddd_ts_add_plugin_tracking_data( $data ) {
		$plugin_data['ts_meta_data_table_name'] = 'ts_tracking_meta_data';
		$plugin_data['ts_plugin_name']          = 'Order Delivery Date Pro for WooCommerce';

		// Store count info.
		$plugin_data['deliveries_count'] = self::ts_get_order_counts();

		// Get all plugin options info.
		$plugin_data['deliveries_settings']  = self::ts_get_all_plugin_options_values();		
		$plugin_data['orddd_plugin_version'] = self::ts_get_plugin_version();
		$plugin_data['license_key_info']     = self::ts_get_plugin_license_key();
		$plugin_data['orddd_allow_tracking'] = get_option( 'ordd_pro_allow_tracking' );
		$data['plugin_data']                 = $plugin_data;
		return $data;
	}

	/**
	 * Return the date with the selected langauge in Appearance tab
	 *
	 * @globals array $orddd_languages Languages array
	 * @globals array $orddd_languages_locale Languages locale array
	 *
	 * @param string $delivery_date_formatted Selected date
	 * @param string $delivery_date_timestamp Unix timestamp of the selected date
	 * @return string Selected delivery date in the translated language
	 * @since 2.6.3
	 */
	public static function delivery_date_language( $delivery_date_formatted, $delivery_date_timestamp, $is_time_visible = true, $order = null ) {
		global $orddd_languages, $orddd_languages_locale, $orddd_date_formats, $wp_locale;
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$initial_locale = get_locale();

		$date_language = get_option( 'orddd_language_selected' );
		if ( defined( 'ICL_LANGUAGE_CODE' ) && '' !== constant( 'ICL_LANGUAGE_CODE' ) ) {
			$wpml_current_language = constant( 'ICL_LANGUAGE_CODE' );
			if ( ! empty( $wpml_current_language ) ) {
				$date_language = $wpml_current_language;
				if ( isset($_GET['number_of_dates'] ) ) {//phpcs:ignore
					$path = $_SERVER[ 'HTTP_REFERER' ]; //phpcs:ignore
					$parts = parse_url( $path );
					$slug = str_replace( '/', '', $parts[ 'path' ] );
					$post = get_page_by_path( $slug );
					$post_id = false;
					$post_language ='';
					if ( $post && function_exists( 'pll_get_post_language' ) ) {
						$post_language = pll_get_post_language( $post->ID );					
					}
					if ( '' !== $post_language ) {
						$date_language = $post_language;
					}
				}
			}
		}

		if ( '' !== $delivery_date_timestamp ) {

			if ( 'en-GB' !== $date_language ) {
				$locale_format   = isset( $orddd_languages[ $date_language ] ) ? $orddd_languages[ $date_language ] : $orddd_languages['en-GB'];
				$lang            = $orddd_languages_locale[ $locale_format ][2];
				$loaded_language = $lang;
				
				// Load the language selected in the Appearance settings.
				if ( ! in_array( $lang, get_available_languages(), true ) ) {
					$loaded_language = wp_download_language_pack( $lang );
				}

				load_default_textdomain( $loaded_language );
				$GLOBALS['wp_locale'] = new WP_Locale();

				$date_format = get_option( 'orddd_delivery_date_format' );

				$time_settings = gmdate( 'H:i', $delivery_date_timestamp );

				if ( is_a( $order, 'WC_Order' ) ) {
					$check_past_two_weeks_order = self::is_order_from_past_2_weeks( $order );
				} else {
					$check_past_two_weeks_order = false;
				}

				$delivery_date_formatted = $check_past_two_weeks_order ? wp_date( $orddd_date_formats[ $date_format ], $delivery_date_timestamp ) : date_i18n( $orddd_date_formats[ $date_format ], $delivery_date_timestamp );

				if ( $time_settings != '00:00' && $time_settings != '00:01' && $is_time_visible ) {
					$time_format_to_show     = self::orddd_get_time_format();
					$delivery_date_formatted = date_i18n( $orddd_date_formats[ $date_format ] . ' ' . $time_format_to_show, $delivery_date_timestamp );
				}

				// Load the default language again after setting the delivery date to required language.
				load_default_textdomain( $initial_locale );
				$GLOBALS['wp_locale'] = new WP_Locale();
			}
		}
		return $delivery_date_formatted;
	}

	/**
	 * Returns delivery date for an order
	 *
	 * @globals array $orddd_date_formats Date formats array
	 *
	 * @param int $order_id Order ID
	 * @return string Delivery date for an order
	 * @since 2.6.3
	 */
	public static function orddd_get_order_delivery_date( $order_id, $vendor_id = 0 ) {

		global $orddd_date_formats;
		$order_timestamp_key = '_orddd_timestamp';
		if ( $vendor_id ) {
			$order_timestamp_key = '_orddd_timestamp_' . $vendor_id;
		}
		$order = wc_get_order( $order_id );

		$delivery_date_timestamp = $order->get_meta( $order_timestamp_key, true );
		$delivery_date_formatted = '';

		$date_field_label = orddd_custom_delivery_functions::orddd_fetch_delivery_date_field_label( $order_id, $vendor_id );
		$date_field_value = $order->get_meta( $date_field_label, true );

		$check_past_two_weeks_order = self::is_order_from_past_2_weeks( $order );

		if ( $delivery_date_timestamp != '' ) {
			$date_format              = get_option( 'orddd_delivery_date_format' );
			$delivery_date_timestamp += 0; // this will convert to long type.
			$delivery_date_formatted  = $check_past_two_weeks_order ? wp_date( $orddd_date_formats[ $date_format ], $delivery_date_timestamp ) : date_i18n( $orddd_date_formats[ $date_format ], $delivery_date_timestamp );
			$time_settings            = date( 'H:i', $delivery_date_timestamp );
			$time_slider_enabled      = orddd_common::orddd_check_order_time_slider_enabled( $order_id );
			if ( $time_slider_enabled && $time_settings != '00:00' && $time_settings != '00:01' ) {
				$time_format_to_show     = self::orddd_get_time_format();
				$delivery_date_formatted = $check_past_two_weeks_order ? wp_date( $orddd_date_formats[ $date_format ] . ' ' . $time_format_to_show, $delivery_date_timestamp ) : date_i18n( $orddd_date_formats[ $date_format ] . ' ' . $time_format_to_show, $delivery_date_timestamp );
			}
			$delivery_date_formatted = self::delivery_date_language( $delivery_date_formatted, $delivery_date_timestamp, $time_slider_enabled, $order );
		} elseif ( ! empty( $date_field_value ) ) { //phpcs:ignore
			$delivery_date_formatted = $date_field_value;
		}

		return $delivery_date_formatted;
	}

	/**
	 * Returns time slot for an order
	 *
	 * @param int  $order_id Order ID
	 * @param bool $is_subscription_parent_order True if it is a renewal order, else false.
	 * @return string Time slot for an order
	 * @since 2.6.3
	 */
	public static function orddd_get_order_timeslot( $order_id, $is_subscription_parent_order = false, $vendor_id = 0 ) {

		$order_time_slot     = '';
		$time_format_to_show = self::orddd_get_time_format();

		$order = wc_get_order( $order_id );
		$data  = $order->get_meta();
		$items = $order->get_items();

		global $typenow;

		$field_label = orddd_custom_delivery_functions::orddd_fetch_time_slot_field_label( $order_id, $vendor_id );
		if ( true == $is_subscription_parent_order ) {
			$field_label = '_' . $field_label;
		}

		$order_time_slot_key = '_orddd_time_slot';
		if ( $vendor_id > 0 ) {
			$order_time_slot_key = '_orddd_time_slot_' . $vendor_id;
		}
		if ( empty( $data ) ) {
			$data = array();
			$data[$order_time_slot_key][0] = $order->get_meta( $order_time_slot_key, true );
			$data[$field_label][0]  = $order->get_meta( $field_label, true );
		}

		if ( isset( $data[ $order_time_slot_key ] ) && '' != $data[ $order_time_slot_key ] ) {
			$order_time_slot = $data[ $order_time_slot_key ][0];
		} elseif ( isset( $data[ $field_label ] ) && array_key_exists( $field_label, $data ) ) {
			if ( isset( $data[ $field_label ][0] ) && $data[ $field_label ][0] != 'select' && $data[ $field_label ][0] != '' ) {
				if ( false != strpos( $data[ $field_label ][0], 'Possible' ) ) {
					$order_time_slot = $data[ $field_label ][0];
				} else {
					$order_time_slot = $data[ $field_label ][0];
				}
			}
		}

		if ( $order_time_slot != ''  && $order_time_slot !== 'asap' && $order_time_slot != __( 'As Soon As Possible.', 'order-delivery-date' ) && '0' !== $order_time_slot ) {
			$time_slot_arr = explode( ' - ', $order_time_slot );
			$from_time     = date_i18n( $time_format_to_show, strtotime( $time_slot_arr[0] ) );

			if ( isset( $time_slot_arr[1] ) ) {
				$to_time         = date_i18n( $time_format_to_show, strtotime( $time_slot_arr[1] ) );
				$order_time_slot = $from_time . ' - ' . $to_time;
			} else {
				$order_time_slot = $from_time;
			}
		} else if ( $order_time_slot != '' && ( $order_time_slot === 'asap' || $order_time_slot ===  __( 'As Soon As Possible.', 'order-delivery-date' ) ) ) {
			$order_time_slot = __( 'As Soon As Possible.', 'order-delivery-date' );
			$order_time_slot = apply_filters( 'orddd_modify_as_soon_as_text', $order_time_slot );
		}

		return $order_time_slot;
	}

	/**
	 * Returns Estimated Text Block for an order
	 *
	 * @param int $order_id Order ID
	 * @return string Text block for an order
	 * @since 8.7
	 */
	public static function orddd_get_order_estimated_text_block( $order_id ) {
		global $orddd_date_formats;
		$orddd_estimated_shipping_timestamp = '';
		$orddd_text_block                   = '';

		$order = wc_get_order( $order_id );
		$estimated_shipping_timestamp  = $order->get_meta('orddd_estimated_shipping_timestamp', true );
		
		if ( '' != $estimated_shipping_timestamp ) {
			$shipping_date = gmdate( $orddd_date_formats[ get_option( 'orddd_delivery_date_format' ) ], $estimated_shipping_timestamp );

			$orddd_min_between_days = get_option( 'orddd_min_between_days' );
			$orddd_max_between_days = get_option( 'orddd_max_between_days' );

			$results = self::orddd_get_shipping_settings();

			if ( is_array( $results ) && count( $results ) > 0 ) {
				$location          = self::orddd_get_order_location( $order_id );
				$shipping_method   = self::orddd_get_order_shipping_method( $order_id );
				$product_category  = self::orddd_get_cart_product_categories( $order_id );
				$shipping_class    = self::orddd_get_cart_shipping_classes( $order_id );
				$shipping_settings = array();
				foreach ( $results as $key => $value ) {
					$shipping_methods  = array();
					$shipping_settings = get_option( $value->option_name );
					if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'orddd_locations' ) {
						if ( isset( $shipping_settings['orddd_locations'] ) ) {
							$shipping_methods = $shipping_settings['orddd_locations'];
						}
						$shipping_method = $location;
					} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'shipping_methods' ) {
						if ( isset( $shipping_settings['shipping_methods'] ) ) {
							$shipping_methods = $shipping_settings['shipping_methods'];

							if ( false !== strpos( $shipping_methods[0], 'fedex' ) ) {
								$shipping_method = $shipping_methods[0];
							}
						}
					} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'product_categories' ) {
						if ( isset( $shipping_settings['product_categories'] ) ) {
							$shipping_methods = $shipping_settings['product_categories'];
						}
						if ( ! is_array( $product_category ) ) {
							$shipping_method = explode( ',', $product_category );
						} else {
							$shipping_method = $product_category;
						}
					}

					if ( in_array( $shipping_method, $shipping_methods ) || in_array( $shipping_class, $shipping_methods ) || ( is_array( $shipping_method ) && count( array_intersect( $shipping_method, $shipping_methods ) ) ) ) {

						$orddd_min_between_days = $shipping_settings['orddd_min_between_days'];
						$orddd_max_between_days = $shipping_settings['orddd_max_between_days'];
					}
				}
			}

			$estimated_date_text = str_replace(
				array(
					'%shipping_date%',
					'%delivery_range_start_days%',
					'%delivery_range_end_days%',
				),
				array(
					$shipping_date,
					$orddd_min_between_days,
					$orddd_max_between_days,
				),
				get_option( 'orddd_estimated_date_text' )
			);

			$orddd_text_block = __( $estimated_date_text, 'order-delivery-date' );//phpcs:ignore
		}
		return $orddd_text_block;
	}

	/**
	 * Returns between days from a start date till end date
	 *
	 * @param string $FromDate Start date of the range
	 * @param string $ToDate End date of the range
	 * @return array Dates between the start and the end date
	 * @since 2.8.4
	 */
	public static function orddd_get_betweendays( $FromDate, $ToDate ) {
		$Days[]             = $FromDate;
		$FromDate_timestamp = strtotime( $FromDate );
		$ToDate_timestamp   = strtotime( $ToDate );
		if ( $FromDate_timestamp != $ToDate_timestamp ) {
			while ( $FromDate_timestamp < $ToDate_timestamp ) {
				$FromDate           = gmdate( 'd-n-Y', strtotime( '+1 day', strtotime( $FromDate ) ) );
				$FromDate_timestamp = $FromDate_timestamp + 86400;
				$Days[]             = $FromDate;
			}
		}
		return $Days;
	}

	/**
	 * Returns option key to update the Custom Delivery Settings in options table
	 *
	 * @param int $row_id Custom settings row id.
	 * @return int Option key
	 * @since 3.0
	 */
	public static function get_shipping_setting_option_key( $row_id ) {
		if ( $row_id != '' ) {
			$option_key = $row_id;
		} else {
			$option_key = get_option( 'orddd_shipping_based_settings_option_key' );
			if ( $option_key == '' || $option_key == null ) {
				$option_key = 1;
			} else {
				$option_key = $option_key + 1;
			}
		}
		return $option_key;
	}

	/**
	 * Returns yes if the time settings is enabled for the Custom Delivery Setting
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @return string
	 * @since 3.0
	 */

	public static function orddd_time_settings_enable_for_custom_delivery() {

		$time_settings_for_shipping_method = wp_cache_get( 'time_settings_for_shipping_method' );

		if ( false === $time_settings_for_shipping_method ) {
			global $wpdb;

			$results                           = self::orddd_get_shipping_settings();
			$shipping_settings                 = array();
			$time_settings_for_shipping_method = 'no';
			if ( is_array( $results ) && count( $results ) > 0 ) {
				foreach ( $results as $key => $value ) {
					$shipping_settings = get_option( $value->option_name );
	
					if ( isset( $shipping_settings['time_settings'] ) && isset( $shipping_settings['time_settings']['enable'] ) && 'on' === $shipping_settings['time_settings']['enable'] ) {
						$time_settings = $shipping_settings['time_settings'];
						$from_hours = isset( $time_settings['from_hours'] ) && '' !== $time_settings['from_hours'] ? gmdate( 'H', strtotime( $time_settings['from_hours'] . ':' . $time_settings['from_mins'] ) ) : '';
						$to_hours   = isset( $time_settings['to_hours'] ) && '' !== $time_settings['to_hours'] ? gmdate( 'H', strtotime( $time_settings['to_hours'] . ':' . $time_settings['to_mins'] ) ) : '';
						
						if ( $from_hours != '' && $to_hours != '' ) {
							$time_settings_for_shipping_method = 'yes';
							break;
						}
					}
				}
				wp_cache_set( 'time_settings_for_shipping_method', $time_settings_for_shipping_method );
			}
		}
		return $time_settings_for_shipping_method;
	}

	/**
	 * Returns yes if the time slots is enabled for the Custom Delivery Setting
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @return string
	 * @since 8.2
	 */
	public static function orddd_time_slots_enable_for_custom_delivery( $vendor_id = 0 ) {
		global $wpdb;
		$results                        = self::orddd_get_shipping_settings( 0, $vendor_id );
		$shipping_settings              = array();
		$time_slots_for_shipping_method = 'no';
		if ( is_array( $results ) && count( $results ) > 0 ) {
			foreach ( $results as $key => $value ) {
				if ( $vendor_id > 0 ) {
					$shipping_settings = unserialize( $value->option_value, ['allowed_classes' => false] );
				} else {
					$shipping_settings = get_option( $value->option_name );
				}
				if ( ( is_array( $shipping_settings ) && isset( $shipping_settings['time_slots'] ) && $shipping_settings['time_slots'] != '' ) || ( is_object( $shipping_settings ) && isset( $shipping_settings->time_slots ) && $shipping_settings->time_slots != '' ) ) {
					$time_slots_for_shipping_method = 'yes';
					break;
				}
			}
		}
		return $time_slots_for_shipping_method;
	}

	/**
	 * Returns timestamp for the selected delivery date
	 *
	 * @param string $delivery_date Selected delivery date
	 * @param string $date_format Selected date format
	 * @param array  $time_setting Settings for the time range
	 * @return string
	 * @since 1.0
	 */

	public static function orddd_get_timestamp( $delivery_date, $date_format, $time_setting ) {
		if ( $delivery_date != '' ) {
			if ( 1 == gmdate( 'I' ) ) {
				$date_arr = explode( '-', $delivery_date );
				$m        = $date_arr[1];
				$d        = $date_arr[0];
				$y        = $date_arr[2];
				$hour     = 0;
				$min      = 0;
				if ( isset( $time_setting['enable'] ) && $time_setting['enable'] == 'on' ) {
					$time_setting_selected = $time_setting['time_selected'];
					$timing_arr            = explode( ' ', $time_setting_selected );
					$time_setting_selected = gmdate( 'H:i', strtotime( $time_setting_selected ) );
					$timing_array          = explode( ':', $time_setting_selected );
					$hour                  = $timing_array[0];
					$min                   = $timing_array[1];
				}
				$timestamp = gmmktime( $hour, $min, 1, $m, $d, $y );
			} else {
				if ( isset( $time_setting['enable'] ) && 'on' == $time_setting['enable'] ) {
					$delivery_date .= ' ' . $time_setting['time_selected'];
				}
				$timestamp = strtotime( $delivery_date );
			}
		} else {
			$timestamp = '';
		}
		return $timestamp;
	}

	/**
	 * Load hidden fields on the checkout page
	 *
	 * @globals resource $wpdb WordPress object
	 * @globals resource $post WordPress post object
	 * @globals resource $woocommerce WooCommerce object
	 * @globals array $orddd_date_formats Date formats array
	 * @globals array $orddd_languages Languages array
	 * @globals array $orddd_weekdays Weekdays array
	 * @globals array $orddd_shipping_days Shipping weekdays array
	 *
	 * @return string
	 * @since 1.0
	 */
	public static function load_hidden_fields( $called_from = '', $order_id = '' ) {
		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time      = current_time( 'timestamp', $gmt );
		$current_date_time = strtotime( gmdate( 'Y-m-d', $current_time ) );
		$additional_data   = array();
		$var               = '';

		$vendors = self::orddd_get_vendors_from_products_in_cart();
		$vendors = apply_filters( 'orddd_get_vendors_from_multisite', $vendors );
		foreach ( $vendors as $vendor_id ) {
			$var .= '<input type="hidden" name="h_deliverydate_' . $vendor_id . '" id="h_deliverydate_' . $vendor_id . '" value="">';
			$var .= '<input type="hidden" name="orddd_unique_custom_settings_' . $vendor_id . '" id="orddd_unique_custom_settings_' . $vendor_id . '" value="" >';
			$var .= '<input type="hidden" name="time_setting_enable_for_shipping_method_' . $vendor_id . '" id="time_setting_enable_for_shipping_method_' . $vendor_id . '" value="" >';
		}

		$var .= '<input type="hidden" name="orddd_time_settings_selected_' . $vendor_id . '" id="orddd_time_settings_selected_' . $vendor_id . '" value="" >';
		$var .= '<input type="hidden" name="time_setting_enable_for_shipping_method" id="time_setting_enable_for_shipping_method" value="" >';
		$var .= '<input type="hidden" name="orddd_zone_id" id="orddd_zone_id" value="" >';

		return apply_filters( 'orddd_hidden_variables', $var, $additional_data );
	}

	/**
	 * Send the data to JS through localize_script
	 *
	 * @param string $order_id
	 * @return array
	 */
	public static function orddd_localize_data_script( $order_id = '' ) {
		global $wpdb, $orddd_date_formats, $post, $woocommerce, $orddd_languages, $orddd_weekdays, $orddd_shipping_days, $post, $cart_products_row;

		wp_cache_flush();
		$gmt = false;

		$vendor_settings     = array();
		$additional_data_all = array();

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$vendors = self::orddd_get_vendors_from_products_in_cart();
		$is_vendor_screen = apply_filters( 'orddd_integration_vendors_is_vendor', false ) && isset( $_GET['order_id'] );//phpcs:ignore

		if ( ! in_array( 0, $vendors ) ) {
			$vendors = array_merge( array(0), $vendors );
		}

		foreach ( $vendors as $vendor_id ) {

			$is_vendor = 0 !== $vendor_id;

			$orddd_settings = array();

			$current_time           = current_time( 'timestamp', $gmt );
			$current_date_time      = strtotime( gmdate( 'Y-m-d', $current_time ) );
			$additional_data        = array();
			$orddd_settings         = array();
			$min_date               = '';
			$load_delivery_date_var = array();
			$min_hour               = 0;
			$min_minute             = 0;
			$minimum_delivery_time  = '';
			$var                    = '';
			$is_inline              = 'inline_calendar' === get_option( 'orddd_calendar_display_mode' ) ? true : false;
			$is_onload 				= 'onload_calendar' === get_option( 'orddd_calendar_display_mode' ) ? true: false;
			if ( $is_onload ) {
				$is_inline = true;
			}
			$field_name             = $is_inline && ( 'no' === get_option( 'orddd_delivery_dates_in_dropdown' ) || '' === get_option( 'orddd_delivery_dates_in_dropdown' ) ) ? 'orddd_datepicker_' . $vendor_id : 'e_deliverydate_' . $vendor_id;

			if ( is_admin() ) {
				$field_name = 'e_deliverydate_' . $vendor_id;
				$is_inline  = false;
				$is_onload = false;
			}

			if ( ! empty( $cart_products_row ) ) {
				$cpr_string = '';
				$cpr_count  = 0;
				foreach( $cart_products_row as $cpr ) {
					if ( 0 === $cpr_count ) {
						$cpr_string .= 'e_deliverydate_' . $cpr . ', ';
					} else {
						$cpr_string .= '#e_deliverydate_' . $cpr . ', ';
					}
					$cpr_count++;
				}
				$cpr_string = substr( trim( $cpr_string ), 0, -1 );
				$field_name = $cpr_string;
			}

			$orddd_settings['h_deliverydate']               = '';
			$orddd_settings['orddd_unique_custom_settings'] = '';
			$orddd_settings['custom_settings']              = '';
			$orddd_settings['orddd_rest_url']               = get_rest_url();
			$orddd_settings['enable_delivery_date']         = self::orddd_get_option( 'orddd_enable_delivery_date', '', $vendor_id );
			$orddd_settings['priority']         			= self::orddd_get_option( 'orddd_priority_for_delivery_schedule', 0, $vendor_id );
			$orddd_settings['priority_enabled'] 			= orddd_custom_delivery_functions::orddd_is_priority_enabled();
			$default_schedule_priority						= self::orddd_get_option( 'orddd_default_schedule_priority' );
			$orddd_settings['default_schedule_priority']	= $default_schedule_priority;
			if ( $is_vendor_screen ) {
				$orddd_settings['enable_delivery_date']         = self::orddd_get_option( 'orddd_enable_delivery_date', '', self::orddd_get_vendor_id() );			
			}
			$orddd_settings['update_order_review'] = '';

			$orddd_shopping_cart_hook = self::orddd_get_shopping_cart_hook();
			if ( 'woocommerce_review_order_before_submit' === $orddd_shopping_cart_hook ) {
				$orddd_settings['update_order_review'] = true;
			}

			$current_date            = gmdate( 'j-n-Y', $current_time );
			$current_hour            = gmdate( 'H', $current_time );
			$current_minute          = gmdate( 'i', $current_time );
			$current_weekday         = gmdate( 'w', $current_time );
			$next_date               = gmdate( 'j-n-Y', strtotime( $current_date . '+1 day' ) );
			$same_day_cutoff_enabled = ORDDD_Functions::orddd_same_day_cutoff_enabled( $vendor_id );
			$next_day_cutoff_enabled = ORDDD_Functions::orddd_next_day_cutoff_enabled( $vendor_id );

			// fetch holidays.
			if ( is_admin() ) { // all days are to be enabled for the admin.
				$holidays_str = '';
			} else { // load the holidays for front end.
				// todo: We can use the holiday cache approch at other places also.
				$holidays_str = wp_cache_get( 'orddd_general_delivery_date_holidays_' . $vendor_id );
				if ( false == $holidays_str ) {
					$holidays_arr = array();
					$holidays     = self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id );

					if ( '' !== $holidays && '{}' !== $holidays && '[]' !== $holidays && 'null' !== $holidays ) {
						$holidays_arr = json_decode( self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id ) );
					}

					$holidays_str = '';
					foreach ( $holidays_arr as $k => $v ) {
						// Replace single quote in the holiday name with the html entities
						// @todo: Need to fix the double quotes issue in the holiday name.
						// An error comes in console when the holiday name contains double quotes in it.
						$name                  = str_replace( "'", '&apos;', $v->n );
						$name                  = str_replace( '"', '&quot;', $name );
						$name                  = str_replace( '/', ' ', $name );
						$name                  = str_replace( '-', ' ', $name );
						$name                  = str_replace( ':', '&#58;', $name );
						$name                  = trim( $name );
						$date_arr              = explode( '-', $v->d );
						$holiday_date          = $date_arr[1] . '-' . $date_arr[0] . '-' . $date_arr[2];
						$holiday_timestamp     = strtotime( $holiday_date );
						$current_day_timestamp = strtotime( $current_date );

						if ( isset( $v->r_type ) && $v->r_type == 'on' ) {
							$holiday_date_arr = explode( '-', $v->d );
							$recurring_date   = $holiday_date_arr[0] . '-' . $holiday_date_arr[1];
							$holidays_str    .= '"' . $name . ':' . $recurring_date . '",';
						} else {
							if ( $holiday_timestamp < $current_day_timestamp ) {
								continue;
							}

							$holidays_str .= '"' . $name . ':' . $v->d . '",';
						}
					}

					$holidays_str = apply_filters( 'ordd_add_to_holidays_str', $holidays_str );

					if ( '' !== $holidays_str ) {
						$holidays_str = substr( $holidays_str, 0, strlen( $holidays_str ) - 1 );
					}

					wp_cache_set( 'orddd_general_delivery_date_holidays_' . $vendor_id, $holidays_str );
				}
			}

			$orddd_settings['orddd_delivery_date_holidays'] = $holidays_str;

			// Lockout.
			$lockout_days_str = '';
			if ( ! is_admin() ) {
				// todo: We can use the lockout date cache approch at other places also.
				$lockout_days_str = wp_cache_get( 'orddd_general_lockout_days_str_' . $vendor_id );
				if ( false == $lockout_days_str ) {
					$lockout_date_after_order = self::orddd_get_option( 'orddd_lockout_date_after_orders', '', $vendor_id );
					$booked_days              = ORDDD_Lockout_Days::orddd_get_booked_dates( $vendor_id );

					foreach ( $booked_days as $booked_day ) {
						$lockout_days_str .= '"' . $booked_day . '",';
					}

					if ( 'on' === self::orddd_get_option( 'orddd_enable_time_slot', '', $vendor_id ) ) {
						$booked_timeslot_days = ORDDD_Lockout_Days::orddd_get_booked_timeslot_days( $vendor_id );
						$blocked_days         = ORDDD_Lockout_Days::orddd_get_blocked_timeslot_days( $vendor_id );

						foreach ( $booked_timeslot_days as $booked_day ) {
							$lockout_days_str .= '"' . $booked_day . '",';
						}

						foreach ( $blocked_days as $booked_day ) {
							$lockout_days_str .= '"' . $booked_day . '",';
						}
					}

					if ( '' !== $lockout_days_str ) {
						$lockout_days_str = substr( $lockout_days_str, 0, strlen( $lockout_days_str ) - 1 );
					}
					wp_cache_set( 'orddd_general_lockout_days_str_' . $vendor_id, $lockout_days_str );
				}
			}
			if ( ! $lockout_days_str ) {
				$lockout_days_str = '';
			}
			$orddd_settings['orddd_lockout_days'] = $lockout_days_str;

			// Min Date.
			$minimum_delivery_time = self::orddd_get_option( 'orddd_minimumOrderDays', '', $vendor_id );
			if ( '' == $minimum_delivery_time ) {
				$minimum_delivery_time = 0;
			}

			$delivery_time_seconds = $minimum_delivery_time * 60 * 60;

			$advance_booking_hrs = self::orddd_get_option( 'orddd_minimumOrderDays', '', $vendor_id );
			if ( 'on' === self::orddd_get_option( 'orddd_enable_day_wise_settings', '', $vendor_id ) ) {
				$current_weekday  = 'orddd_weekday_' . gmdate( 'w', $current_time );
				$advance_settings = self::orddd_get_option( 'orddd_advance_settings', array(), $vendor_id );

				if ( '' === $advance_settings || '{}' === $advance_settings || '[]' === $advance_settings ) {
					$advance_settings = array();
				}

				foreach ( $advance_settings as $ak => $av ) {
					if ( $current_weekday === $av['orddd_weekdays'] ) {
						if ( '' != $av['orddd_minimumOrderDays'] ) {
							$minimum_delivery_time = $av['orddd_minimumOrderDays'];
							$delivery_time_seconds = $av['orddd_minimumOrderDays'] * 60 * 60;
							$advance_booking_hrs   = $av['orddd_minimumOrderDays'];
						}
					}
				}
			}

			$date_to_check      = gmdate( 'n-j-Y', $current_time );
			$delivery_dates_arr = array();

			if ( 'on' === self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) ) {
				$delivery_dates = self::orddd_get_option( 'orddd_delivery_dates', '', $vendor_id );
				if ( '' !== $delivery_dates && '{}' !== $delivery_dates && '[]' !== $delivery_dates && 'null' !== $delivery_dates ) {
					$delivery_dates_arr = json_decode( self::orddd_get_option( 'orddd_delivery_dates', '', $vendor_id ) );
				}
			}

			$dates_to_check = array();
			foreach ( $delivery_dates_arr as $k => $v ) {
				$dates_to_check[] = $v->date;
			}

			$current_weekday = gmdate( 'w', $current_time );

			if ( 'checked' !== self::orddd_get_option( 'orddd_weekday_' . $current_weekday, '', $vendor_id ) && ( ( 'on' === self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) && is_array( $dates_to_check ) && count( $dates_to_check ) > 0 && ! in_array( $date_to_check, $dates_to_check ) ) || ( 'on' !== self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) ) ) ) {
				$current_time = strtotime( $current_date );
			}

			$min_date_array = self::get_min_date(
				$delivery_time_seconds,
				array(
					'enabled'    => self::orddd_get_option( 'orddd_enable_delivery_time', '', $vendor_id ),
					'from_hours' => self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ),
					'to_hours'   => self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id ),
					'from_mins'  => self::orddd_get_option( 'orddd_delivery_from_mins', '', $vendor_id ),
					'to_mins'    => self::orddd_get_option( 'orddd_delivery_to_mins', '', $vendor_id ),
				),
				$holidays_str,
				$lockout_days_str,
				array(),
				array(),
				$vendor_id
			);

			// check mindate is today.. if yes, then check if all time slots are past, if yes, then set mindate to tomorrow.
			if ( 'on' === self::orddd_get_option( 'orddd_enable_time_slot', '', $vendor_id ) ) {
				$last_slot_hrs          = 0;
				$last_slot_min          = 0;
				$current_date           = gmdate( 'j-n-Y', $current_time );
				$existing_timeslots_arr = ORDDD_Functions::orddd_get_timeslots( $vendor_id );

				foreach ( $existing_timeslots_arr as $k => $v ) {
					$hours = $v->fh;
					$mins  = $v->fm;

					if ( 'array' === gettype( json_decode( $v->dd ) ) && count( json_decode( $v->dd ) ) > 0 ) {
						$dd                    = json_decode( $v->dd );
						$check_min_date        = gmdate( 'n-j-Y', strtotime( $min_date_array['min_date'] ) );
						$min_time_on_last_slot = apply_filters( 'orddd_min_delivery_on_last_slot', false );

						if ( $min_time_on_last_slot ) {
							$hours = $v->th;
							$mins  = $v->tm;
						}

						if ( is_array( $dd ) && count( $dd ) > 0 ) {
							if ( in_array( $check_min_date, $dd ) ) {
								$current_slot_hrs  = $hours;
								$current_slot_mins = $mins;
								if ( $current_slot_hrs > $last_slot_hrs || ( $current_slot_hrs === $last_slot_hrs && $current_slot_mins > $last_slot_min ) ) {
									$last_slot_hrs = $current_slot_hrs;
									$last_slot_min = $current_slot_mins;
								}
							} else {
								$min_weekday = gmdate( 'w', strtotime( $min_date_array['min_date'] ) );
								$min_weekday = 'orddd_weekday_' . $min_weekday;

								if ( in_array( $min_weekday, $dd ) ) {
									$current_slot_hrs  = $hours;
									$current_slot_mins = $mins;

									if ( $current_slot_hrs >= $last_slot_hrs || ( $current_slot_hrs == $last_slot_hrs && $current_slot_mins > $last_slot_min ) ) {
										$last_slot_hrs = $current_slot_hrs;
										$last_slot_min = $current_slot_mins;
									}
								} elseif ( in_array( 'all', $dd ) ) {
									$current_slot_hrs  = $hours;
									$current_slot_mins = $mins;

									if ( $current_slot_hrs > $last_slot_hrs || ( $current_slot_hrs == $last_slot_hrs && $current_slot_mins > $last_slot_min ) ) {
										$last_slot_hrs = $current_slot_hrs;
										$last_slot_min = $current_slot_mins;
									}
								}
							}
						}
					} else {
						$current_slot_hrs  = $hours;
						$current_slot_mins = $mins;

						if ( $current_slot_hrs > $last_slot_hrs || ( $current_slot_hrs == $last_slot_hrs && $current_slot_mins > $last_slot_min ) ) {
							$last_slot_hrs = $current_slot_hrs;
							$last_slot_min = $current_slot_mins;
						}
					}
				}

				if ( $last_slot_hrs != 0 ) {
					$last_slot       = $last_slot_hrs . ':' . trim( $last_slot_min );
					$min_hour_in_sec = orddd_calculate_cutoff_time_slots( $delivery_time_seconds, $current_time, $min_date_array ); // If some of the weekdays are disabled then then the difference between current time & min date will be greater than the actual MDT.
					$booking_date2   = $min_date_array['min_date'] . ' ' . $last_slot;

					$booking_date2 = gmdate( 'Y-m-d G:i', strtotime( $booking_date2 ) );
					$date2         = new DateTime( $booking_date2 );

					$booking_date1 = gmdate( 'Y-m-d G:i', $current_time );
					$date1         = new DateTime( $booking_date1 );

					if ( version_compare( phpversion(), '5.3.0', '>' ) ) {
						$calculated_difference = $date2->diff( $date1 );
					} else {
						$calculated_difference = self::dateTimeDiff( $date2, $date1 );
					}

					if ( $calculated_difference->days > 0 ) {
						$days_in_hour             = $calculated_difference->h + ( $calculated_difference->days * 24 );
						$calculated_difference->h = $days_in_hour;
					}

					if ( $calculated_difference->i > 0 ) {
						$min_in_hour                      = $calculated_difference->h + ( $calculated_difference->i / 60 );
						$calculated_minimum_delivery_time = $min_in_hour * 60 * 60;
					} else {
						$calculated_minimum_delivery_time = $calculated_difference->h * 60 * 60;
					}

					if ( $calculated_difference->invert == 0 || $calculated_minimum_delivery_time < $min_hour_in_sec ) {
						$disabled_day = apply_filters( 'orddd_disable_day_when_all_time_slots_expired', true );
						if ( $disabled_day ){
							$min_date_array['min_date'] = gmdate( 'j-n-Y', strtotime( $min_date_array['min_date'] . '+1 day' ) );
						}
					}
				}
			}

			$orddd_delivery_from_hours = self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id );
			$orddd_delivery_from_mins  = self::orddd_get_option( 'orddd_delivery_from_mins', '', $vendor_id );

			if ( '' !== $orddd_delivery_from_hours ) {

				// We set the current hour in the time slider to nearest round time of 5 minutes.
				$next_five           = ceil( $current_time / 300 ) * 300;
				$next_current_hour   = gmdate( 'H', $next_five );
				$next_current_minute = gmdate( 'i', $next_five );

				if ( $current_date === $min_date_array['min_date'] && 'on' === $next_day_cutoff_enabled && 'on' !== $same_day_cutoff_enabled ) {
					$orddd_settings['orddd_min_hour']   = $orddd_delivery_from_hours;
					$orddd_settings['orddd_min_minute'] = $orddd_delivery_from_hours;
				} elseif ( ( $orddd_delivery_from_hours < $current_hour || ( $orddd_delivery_from_hours === $current_hour && $current_minute > 0 ) ) && $current_date === $min_date_array['min_date'] && ( $current_hour > $min_date_array['min_hour'] || ( $current_hour === $min_date_array['min_hour'] && $current_minute > $min_date_array['min_minute'] ) ) ) {
					$orddd_settings['orddd_min_hour']   = $next_current_hour;
					$orddd_settings['orddd_min_minute'] = $next_current_minute;
				} elseif ( $orddd_delivery_from_hours < $min_date_array['min_hour'] || ( $orddd_delivery_from_hours === $min_date_array['min_hour'] && $min_date_array['min_minute'] > 0 ) ) {
					// We set the current hour in the time slider to nearest round time of 5 minutes.
					$min_date_str    = $min_date_array['min_date'] . ' ' . $min_date_array['min_hour'] . ':' . $min_date_array['min_minute'];
					$next_five       = ceil( strtotime( $min_date_str ) / 300 ) * 300;
					$next_min_hour   = gmdate( 'H', $next_five );
					$next_min_minute = gmdate( 'i', $next_five );

					$orddd_settings['orddd_min_hour']   = $next_min_hour;
					$orddd_settings['orddd_min_minute'] = $next_min_minute;
				} elseif ( $orddd_delivery_from_hours > $current_hour ) {
					$orddd_settings['orddd_min_hour']   = $orddd_delivery_from_hours;
					$orddd_settings['orddd_min_minute'] = $orddd_delivery_from_mins;
				} elseif ( '0' === $min_date_array['min_hour'] && '0' === $min_date_array['min_minute'] && $current_date !== $min_date_array['min_date'] ) {
					$orddd_settings['orddd_min_hour']   = $orddd_delivery_from_hours;
					$orddd_settings['orddd_min_minute'] = $orddd_delivery_from_mins;
				} elseif ( $min_date_array['min_hour'] === $orddd_delivery_from_hours ) {
					$orddd_settings['orddd_min_hour']   = $orddd_delivery_from_hours;
					$orddd_settings['orddd_min_minute'] = $orddd_delivery_from_mins;
				} else {
					$orddd_settings['orddd_min_hour']   = $next_current_hour;
					$orddd_settings['orddd_min_minute'] = $next_current_minute;
				}

				$orddd_settings['orddd_min_hour_set'] = $orddd_delivery_from_hours;
				$orddd_settings['orddd_min_mins_set'] = $orddd_delivery_from_mins;
			}

			$orddd_settings['orddd_max_hour_set']        = self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id );
			$orddd_settings['orddd_max_mins_set']        = self::orddd_get_option( 'orddd_delivery_to_mins', '', $vendor_id );
			$orddd_settings['orddd_delivery_from_hours'] = $orddd_delivery_from_hours;
			$orddd_settings['orddd_delivery_from_mins']  = $orddd_delivery_from_mins;

			$orddd_settings['orddd_current_day']    = $current_date;
			$orddd_settings['orddd_current_hour']   = $current_hour;
			$orddd_settings['orddd_current_minute'] = $current_minute;
			$orddd_settings['orddd_next_day']       = $next_date;

			if ( has_filter( 'orddd_first_available_delivery_date' ) ) {
				$min_order_date = apply_filters( 'orddd_first_available_delivery_date', $min_date_array['min_date'] );
			} else {
				$min_order_date = $min_date_array['min_date'];
			}

			$dates_from_current_week = apply_filters( 'orddd_get_dates_from_current_week', false );
			$number_of_months        = apply_filters( 'orddd_number_of_months', get_option( 'orddd_number_of_months', 1 ) );

			$additional_data['min_date_array']                       = $min_date_array;
			$orddd_settings['dates_from_current_week']               = $dates_from_current_week;
			$orddd_settings['orddd_minimumOrderDays']                = $min_order_date;
			$orddd_settings['orddd_min_date_set']                    = '';
			$orddd_settings['orddd_current_date_set']                = $min_date_array['current_date_to_check'];
			$orddd_settings['orddd_number_of_dates']                 = self::orddd_get_option( 'orddd_number_of_dates', '', $vendor_id );
			$orddd_settings['orddd_number_of_months']                = $number_of_months;
			$orddd_settings['orddd_timeslot_field_mandatory']        = self::orddd_get_option( 'orddd_time_slot_mandatory', '', $vendor_id );
			$orddd_settings['orddd_shipping_method_hidden_vars_arr'] = '';
			$orddd_settings['orddd_current_time']                    = $current_time;
			$orddd_settings['orddd_same_day_delivery']               = $same_day_cutoff_enabled;
			$orddd_settings['orddd_next_day_delivery']               = $next_day_cutoff_enabled;
			$orddd_settings['orddd_time_settings_selected']          = '';

			if ( 'on' === $same_day_cutoff_enabled ) {
				$minimum_delivery_time = orddd_get_minimum_delivery_time( $vendor_id );
				$same_day_cut_off      = orddd_get_cutoff_timestamp( 'same_day', $vendor_id );
				$current_timestamp     = current_time( 'timestamp', $gmt );
				$cut_off_with_min_time = $current_timestamp + $minimum_delivery_time;

				$enable_day_until_cutoff = apply_filters( 'orddd_enable_day_until_cutoff', false );
				if ( $enable_day_until_cutoff && ( 0 !== $minimum_delivery_time || '' !== $minimum_delivery_time ) ) {
					$cut_off_with_min_time = $current_timestamp;
				}

				if ( $cut_off_with_min_time < $same_day_cut_off ) {
					$orddd_settings['is_sameday_cutoff_reached'] = 'no';
				} else {
					$orddd_settings['is_sameday_cutoff_reached'] = 'yes';
				}
			}

			if ( 'on' === $next_day_cutoff_enabled ) {
				$minimum_delivery_time = orddd_get_minimum_delivery_time( $vendor_id );
				$next_day_cut_off      = orddd_get_cutoff_timestamp( 'next_day', $vendor_id );
				$cut_off_with_min_time = $current_time + $minimum_delivery_time;

				if ( 'on' === self::orddd_get_option( 'orddd_enable_day_wise_settings', '', $vendor_id ) ) {
					$current_weekday  = 'orddd_weekday_' . gmdate( 'w', $current_time );
					$advance_settings = false !== self::orddd_get_option( 'orddd_advance_settings', '', $vendor_id ) ? self::orddd_get_option( 'orddd_advance_settings', '', $vendor_id ) : array();

					if ( '' !== $advance_settings && '{}' !== $advance_settings && '[]' !== $advance_settings ) {
						foreach ( $advance_settings as $ak => $av ) {
							if ( $current_weekday == $av['orddd_weekdays'] ) {
								if ( '' != $av['orddd_disable_next_day_delivery_after_hours'] ) {
									$cut_off_time   = explode( ':', $av['orddd_disable_next_day_delivery_after_hours'] );
									$cut_off_hour   = $cut_off_time[0];
									$cut_off_minute = $cut_off_time[1];
								}

								$orddd_settings['orddd_before_cutoff_weekday'] = $av['orddd_before_cutoff_weekday'];
								$orddd_settings['orddd_after_cutoff_weekday']  = $av['orddd_after_cutoff_weekday'];
							}
						}
					}
				}

				if ( $current_time >= $next_day_cut_off ) {
					$orddd_settings['is_nextday_cutoff_reached'] = 'yes';
				}
			}

			$admin_url     = get_admin_url();
			$admin_url_arr = explode( '://', $admin_url );
			$home_url      = get_home_url();
			$home_url_arr  = explode( '://', $home_url );

			if ( is_admin() ) {
				$ajax_url = $admin_url;
			} else {
				if ( $admin_url_arr[0] !== $home_url_arr[0] ) {
					$admin_url_arr[0] = $home_url_arr[0];
					$ajax_url         = implode( '://', $admin_url_arr );
				} else {
					$ajax_url = $admin_url;
				}
			}

			$orddd_settings['orddd_admin_url']                  = $ajax_url;
			$orddd_settings['wc_ajax_url']                      = WC_AJAX::get_endpoint( '%%endpoint%%' );
			$orddd_settings['orddd_is_admin']                   = is_admin();
			$orddd_settings['orddd_is_inline']                  = $is_inline;
			$orddd_settings['orddd_is_onload']                  = $is_onload;
			$orddd_settings['orddd_is_cart']                    = is_cart() && wc_post_content_has_shortcode( 'woocommerce_cart' );
			$orddd_settings['is_block_cart']                    = has_block( 'woocommerce/cart' );
			$orddd_settings['is_block_checkout']                = has_block( 'woocommerce/checkout' );
			$orddd_settings['orddd_is_account_page']            = is_account_page();
			$orddd_settings['orddd_delivery_date_on_cart_page'] = get_option( 'orddd_delivery_date_on_cart_page' );
			$orddd_settings['orddd_ship_to_destination']        = get_option( 'woocommerce_ship_to_destination' );
			$orddd_settings['is_local_pickup_enabled']          = self::is_local_pickup_enabled();

			$orddd_date_field_label = self::orddd_get_option( 'orddd_delivery_date_field_label', '', $vendor_id );
			if ( '' === $orddd_date_field_label ) {
				$orddd_date_field_label = ORDDD_DELIVERY_DATE_FIELD_LABEL;
			}

			$orddd_settings['orddd_field_label'] = __( $orddd_date_field_label, 'order-delivery-date' ); // phpcs:ignore

			$orddd_timeslot_field_label = self::orddd_get_option( 'orddd_delivery_timeslot_field_label', '', $vendor_id );
			if ( '' === $orddd_timeslot_field_label ) {
				$orddd_timeslot_field_label = ORDDD_DELIVERY_TIMESLOT_FIELD_LABEL;
			}

			$orddd_settings['orddd_timeslot_field_label']                    = __( $orddd_timeslot_field_label, 'order-delivery-date' ); // phpcs:ignore
			$orddd_settings['orddd_field_name']                              = $field_name;
			$orddd_settings['orddd_auto_populate_first_available_time_slot'] = get_option( 'orddd_auto_populate_first_available_time_slot' );

			$auto_populate_pickup_location                               = get_option( 'orddd_auto_populate_first_pickup_location' );
			$orddd_settings['orddd_auto_populate_first_pickup_location'] = $auto_populate_pickup_location;
			$orddd_settings['orddd_location_sort_order']                 = orddd_common::orddd_get_option( 'orddd_location_sort_order', 'default', $vendor_id );
			$orddd_settings['orddd_enable_pickup_locations']                 = orddd_common::orddd_get_option( 'orddd_enable_pickup_locations', '', $vendor_id );
			$orddd_settings['orddd_delivery_time_format']                = get_option( 'orddd_delivery_time_format' );
			$orddd_settings['orddd_enable_autofill_of_delivery_date']    = get_option( 'orddd_enable_autofill_of_delivery_date' );
			$orddd_settings['orddd_enable_shipping_based_delivery']      = ORDDD_Functions::orddd_shipping_based_delivery_enabled( $vendor_id, 1 );
			$orddd_settings['orddd_enable_shipping_days']                = get_option( 'orddd_enable_shipping_days' );
			$orddd_settings['orddd_date_field_mandatory']                = self::orddd_get_option( 'orddd_date_field_mandatory', '', $vendor_id );
			$orddd_settings['orddd_enable_availability_display']         = get_option( 'orddd_enable_availability_display' );
			$orddd_settings['orddd_show_partially_booked_dates']         = get_option( 'orddd_show_partially_booked_dates' );
			$orddd_settings['field_placement_on_checkout']               = get_option( 'orddd_delivery_date_fields_on_checkout_page' );

			$allshippingdays = array();
			foreach ( $orddd_shipping_days as $n => $day_name ) {
				$allshippingdays[ $n ] = self::orddd_get_option( $n, '', $vendor_id );
			}

			$allshippingdayskeys = array_keys( $allshippingdays );
			$shipping_checked    = 'No';
			foreach ( $allshippingdayskeys as $key ) {
				if ( $allshippingdays[ $key ] == 'checked' ) {
					$shipping_checked = 'Yes';
				}
			}

			if ( $shipping_checked == 'Yes' ) {
				foreach ( $allshippingdays as $key  => $s_value ) {
					$orddd_settings[ $key ] = $allshippingdays[ $key ];

				}
			} elseif ( $shipping_checked == 'No' ) {
				foreach ( $allshippingdays as $key  => $s_value ) {
					$orddd_settings[ $key ] = 'checked';
				}
			}

			$zone_id           = '';
			$orddd_shipping_id = '';
			$zone_details      = self::orddd_get_zone_id();

			if ( is_admin() && isset( $post->ID ) && '' !== $post->ID && ( 'shop_order' === $post->post_type || 'shop_subscription' === $post->post_type ) ) {
				$order_id          = $post->ID;
				$zone_details      = explode( '-', self::orddd_get_zone_id_for_order( $order_id ) );
				$zone_id           = isset( $zone_details[0] ) ? $zone_details[0] : '';
				$orddd_shipping_id = isset( $zone_details[1] ) ? $zone_details[1] : '';
			} elseif ( isset( WC()->cart ) && WC()->cart->get_cart_contents_count() > 0 ) {
				$shipping_packages = WC()->cart->get_shipping_packages();
				$shipping_zone     = wc_get_shipping_zone( reset( $shipping_packages ) );
				$zone_id           = $shipping_zone->get_id();
				if ( 'yes' === get_option( 'woocommerce_shipping_cost_requires_address' ) ) {
					$zone_id = '';
				}
			}

			$orddd_settings['orddd_zone_id']     = $zone_id;
			$orddd_settings['orddd_shipping_id'] = $orddd_shipping_id;
			$orddd_settings['orddd_zones']       = $zone_details;

			$categories       = self::orddd_get_cart_product_categories( $order_id );
			$category_to_send = implode( ',', $categories );

			$products         = self::orddd_get_cart_products( $order_id );
			$products_to_send = implode( ',', $products );

			$shipping_classes       = self::orddd_get_cart_shipping_classes( $order_id );
			$shipping_class_to_send = implode( ',', $shipping_classes );
			$roles                  = self::orddd_get_current_user_role();
			$users                  = array();
			if ( is_array( $roles ) && count( $roles ) > 0 ) {
				foreach ( $roles as $key => $value ) {
					$users[] = $value;
				}
			}
			$users_to_send = implode( ',', $users );

			$orddd_settings['orddd_category_settings_to_load']       = $category_to_send;
			$orddd_settings['orddd_shipping_class_settings_to_load'] = $shipping_class_to_send;
			$orddd_settings['orddd_product_settings_to_load']        = $products_to_send;
			$orddd_settings['orddd_user_role_settings_to_load']      = $users_to_send;

			// Hidden fields for common delivery days, dates, lockout days and also holidays
			// when settings for product categories as well as shipping classes are added and multiple products
			// are added to the cart.
			$get_common_delivery_days = self::orddd_common_delivery_days_for_product_category( $orddd_shipping_id );

			$get_common_delivery_days_json    = '';
			$delivery_dates_str               = '';
			$orddd_is_days_common             = '';
			$orddd_categories_settings_common = '';
			$holidays_str                     = '';
			$locked_days_str                  = '';

			if ( isset( $get_common_delivery_days['orddd_categories_settings_common'] ) ) {
				$orddd_categories_settings_common = $get_common_delivery_days['orddd_categories_settings_common'];
			}

			$orddd_settings['orddd_categories_settings_common'] = $orddd_categories_settings_common;

			if ( 'yes' === $orddd_categories_settings_common && 'on' == $default_schedule_priority ) {
				$get_common_delivery_days = array(
					'orddd_common_weekdays' => '',
					'orddd_common_specific_dates' => '',
					'orddd_common_holidays' => '',
					'orddd_common_locked_days' => '',
				);
			}

			if ( is_array( $get_common_delivery_days['orddd_common_weekdays'] ) && count( $get_common_delivery_days['orddd_common_weekdays'] ) > 0 ) {
				$get_common_delivery_days_json = json_encode( $get_common_delivery_days['orddd_common_weekdays'] );
			}

			$orddd_settings['orddd_common_delivery_days_for_product_category'] = $get_common_delivery_days_json;

			if ( is_array( $get_common_delivery_days['orddd_common_specific_dates'] ) && count( $get_common_delivery_days['orddd_common_specific_dates'] ) > 0 ) {
				foreach ( $get_common_delivery_days['orddd_common_specific_dates'] as $key => $value ) {
					$delivery_dates_str .= '"' . $value . '",';
				}
				$delivery_dates_str = substr( $delivery_dates_str, 0, strlen( $delivery_dates_str ) - 1 );
			}

			$orddd_settings['orddd_common_delivery_dates_for_product_category'] = $delivery_dates_str;

			if ( is_array( $get_common_delivery_days['orddd_common_holidays'] ) && count( $get_common_delivery_days['orddd_common_holidays'] ) > 0 ) {
				foreach ( $get_common_delivery_days['orddd_common_holidays'] as $key => $value ) {
					$holidays_str .= '"' . $value . '",';
				}
				$holidays_str = substr( $holidays_str, 0, strlen( $holidays_str ) - 1 );
			}

			$orddd_settings['orddd_common_holidays_for_product_category'] = $holidays_str;

			if ( is_array( $get_common_delivery_days['orddd_common_locked_days'] ) && count( $get_common_delivery_days['orddd_common_locked_days'] ) > 0 ) {
				foreach ( $get_common_delivery_days['orddd_common_locked_days'] as $key => $value ) {
					$locked_days_str .= '"' . $value . '",';
				}
				$locked_days_str = substr( $locked_days_str, 0, strlen( $holidays_str ) - 1 );
			}

			$orddd_settings['orddd_common_locked_days'] = $locked_days_str;

			if ( isset( $get_common_delivery_days['orddd_is_days_common'] ) ) {
				$orddd_is_days_common = $get_common_delivery_days['orddd_is_days_common'];
			}

			$orddd_settings['orddd_is_days_common'] = $orddd_is_days_common;

			

			// For switching between subscriptions.
			if ( class_exists( 'WC_Subscriptions' ) && class_exists( 'WC_Subscriptions_Switcher' ) && isset( WC()->cart->recurring_carts ) && false !== WC_Subscriptions_Switcher::cart_contains_switches( 'any' ) ) {

				foreach ( WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart ) {
					$packages = $recurring_cart->get_shipping_packages();

					foreach ( $packages as $i => $base_package ) {
						$orddd_settings['recurring_cart_key'] = $recurring_cart_key;
					}
				}
			}

			// Compatibility with WooCommerce Subscriptions plugin.
			if ( class_exists( 'WC_Subscriptions' ) && get_option( 'orddd_enable_woo_subscriptions_compatibility' ) == 'on' && get_option( 'orddd_woocommerce_subscriptions_compatibility' ) == 'on' ) {
				if ( class_exists( 'ws_addon_for_orddd' ) ) {
					$subscrition_var = ws_addon_for_orddd::orddd_check_the_subscription_period();
				}
				if ( isset( $subscrition_var['orddd_if_renewal_subscription'] ) ) {
					$orddd_settings['orddd_if_renewal_subscription'] = 'yes';
				}
				if ( isset( $subscrition_var['orddd_number_of_dates_for_subscription'] ) ) {
					$orddd_settings['orddd_number_of_dates_for_subscription'] = $subscrition_var['orddd_number_of_dates_for_subscription'];
				}
				if ( isset( $subscrition_var['orddd_start_date_for_subscription'] ) ) {
					$delivery_time_seconds = $minimum_delivery_time;
					$current_hour          = gmdate( 'H:i', $current_time );
					$min_timestamp         = strtotime( $subscrition_var['orddd_start_date_for_subscription'] . ' ' . $current_hour );
					if ( 0 !== $minimum_delivery_time ) {
						$min_date_array = self::get_min_date(
							$delivery_time_seconds,
							array(
								'enabled'    => self::orddd_get_option( 'orddd_enable_delivery_time', '', $vendor_id ),
								'from_hours' => self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ),
								'to_hours'   => self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id ),
							),
							$holidays_str,
							$locked_days_str,
							array(),
							array(),
							$vendor_id
						);
					}
					$orddd_settings['orddd_start_date_for_subscription'] = $min_date_array['min_date'];

				}
				$orddd_settings['orddd_subscriptions_settings'] = get_option( 'orddd_enable_woo_subscriptions_compatibility' );

			}

			$alldays = array();
			
			foreach ( $orddd_weekdays as $n => $day_name ) {
				// all weekdays to be enabled for the admin.
				$alldays[ $n ] = ( is_admin() || $is_vendor_screen ) ? 'checked' : self::orddd_get_option( $n, '', $vendor_id );
			}

			$alldayskeys = array_keys( $alldays );
			$checked     = 'No';
			foreach ( $alldayskeys as $key ) {
				if ( $alldays[ $key ] == 'checked' ) {
					$checked = 'Yes';
				}
			}
			if ( $checked == 'Yes' ) {
				foreach ( $alldayskeys as $key ) {
					$orddd_settings[ $key ]         = $alldays[ $key ];
					$load_delivery_date_var[ $key ] = $alldays[ $key ];
				}
			} elseif ( 'No' === $checked && 'on' !== self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) ) {
				foreach ( $alldayskeys as $key ) {
					$orddd_settings[ $key ]         = 'checked';
					$load_delivery_date_var[ $key ] = 'checked';
				}
			}

			if ( 'No' === $checked ) {
				$orddd_settings['orddd_is_all_weekdays_disabled'] = 'yes';

			} else {
				$orddd_settings['orddd_is_all_weekdays_disabled'] = 'no';
			}

			$orddd_settings['orddd_load_delivery_date_var']  = json_encode( $load_delivery_date_var );
			$orddd_settings['orddd_specific_delivery_dates'] = self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id );
			$orddd_settings['orddd_recurring_days']          = self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id );

			$disable_for_delivery_days = 'no';
			if ( has_filter( 'orddd_to_calculate_minimum_hours_for_disable_days' ) ) {
				$disable_for_delivery_days = apply_filters( 'orddd_to_calculate_minimum_hours_for_disable_days', $disable_for_delivery_days );
			}

			$orddd_settings['orddd_disable_for_delivery_days'] = $disable_for_delivery_days;

			$orddd_disable_for_holidays = 'no';
			if ( has_filter( 'orddd_to_calculate_minimum_hours_for_holidays' ) ) {
				$orddd_disable_for_holidays = apply_filters( 'orddd_to_calculate_minimum_hours_for_holidays', $orddd_disable_for_holidays );
			}

			$orddd_settings['orddd_disable_for_holidays'] = $orddd_disable_for_holidays;

			// fetch specific delivery dates.
			$delivery_dates_str = '';
			if ( 'on' === self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) ) {
				$delivery_dates_arr = array();
				$delivery_dates     = self::orddd_get_option( 'orddd_delivery_dates', '', $vendor_id );
				if ( '' !== $delivery_dates && '{}' !== $delivery_dates && '[]' !== $delivery_dates && 'null' !== $delivery_dates ) {
					$delivery_dates_arr = json_decode( $delivery_dates );
				}

				// Sorting specific dates in ascending order.
				usort( $delivery_dates_arr, array( 'orddd_common', 'bkap_sort_specific_dates_array' ) );

				foreach ( $delivery_dates_arr as $key => $value ) {

					foreach ( $value as $k => $v ) {
						$temp_arr[ $k ] = $v;
					}

					$temp_date = strtotime( date_format( DateTime::createFromFormat( 'm-d-Y', $temp_arr['date'] ), 'Y-m-d' ) );
					if ( $temp_date >= $current_date_time ) { // Only considering the specific dates which are future dated.
						$delivery_dates_str .= '"' . $temp_arr['date'] . '",';
					}
				}
				$length =  strlen( $delivery_dates_str ) > 1 ?  ( strlen( $delivery_dates_str ) -1 ) : 0;
				$delivery_dates_str = substr( $delivery_dates_str, 0, $length );
			}

			$orddd_settings['orddd_delivery_dates']     = $delivery_dates_str;
			$orddd_settings['orddd_enable_time_slot']   = self::orddd_get_option( 'orddd_enable_time_slot', '', $vendor_id );
			$orddd_settings['orddd_enable_time_slider'] = self::orddd_get_option( 'orddd_enable_delivery_time', '', $vendor_id );

			if ( is_admin() ) {
				$orddd_settings['orddd_enable_time_slot'] = orddd_admin_delivery_class::orddd_is_time_slot_enabled();
			}
			if ( $is_vendor_screen ) {
				include_once ORDDD_PLUGIN_DIR . '/includes/admin/orddd-admin-delivery.php';
				$orddd_settings['orddd_enable_time_slot'] = orddd_admin_delivery_class::orddd_is_time_slot_enabled( self::orddd_get_vendor_id() );
			}

			$disable_minimum_delivery_time_slider = '';
			if ( has_filter( 'orddd_disable_minimum_delivery_time_slider' ) ) {
				$disable_minimum_delivery_time_slider = apply_filters( 'orddd_disable_minimum_delivery_time_slider', '' );
			}
			$orddd_settings['orddd_time_slider_min_time']                 = '';
			$orddd_settings['orddd_disable_minimum_delivery_time_slider'] = $disable_minimum_delivery_time_slider;

			$disabled_days         = self::get_disabled_days( $vendor_id );
			$disabled_days_str     = '';
			$disabled_weekdays_str = '';

			if ( '' !== $disabled_days ) {
				$disabled_days_arr     = explode( '&', $disabled_days );
				$disabled_days_str     = $disabled_days_arr[0];
				$disabled_weekdays_str = $disabled_days_arr[1];
			}

			if ( is_admin() ) { // next day and same day cut off settings should not be applied for the admin.
				$disabled_days_str     = '';
				$disabled_weekdays_str = '';
			}

			$orddd_settings['orddd_disabled_days_str']     = $disabled_days_str;
			$orddd_settings['orddd_disabled_weekdays_str'] = $disabled_weekdays_str;

			$options                                      = self::get_datepicker_options( $holidays_str, $lockout_days_str, $vendor_id );
			$orddd_settings['orddd_option_str']           = $options;
			$orddd_settings['orddd_delivery_date_format'] = get_option( 'orddd_delivery_date_format' );

			$show = 'datepicker';
			if ( 'on' === self::orddd_get_option( 'orddd_enable_delivery_time', '', $vendor_id ) ) {
				$show = 'timepicker';
				if ( 'on' === self::orddd_get_option( 'orddd_enable_delivery_date', '', $vendor_id ) ) {
					$show = 'datetimepicker';
				}
			}

			$orddd_settings['orddd_show_datepicker'] = $show;

			$first_day_of_week = '';
			if ( '' !== get_option( 'start_of_week' ) ) {
				$first_day_of_week = get_option( 'start_of_week' );
			}

			$orddd_settings['orddd_start_of_week'] = $first_day_of_week;

			$field_note_text = __( get_option( 'orddd_delivery_date_field_note' ), 'order-delivery-date' );//phpcs:ignore
			$field_note_text = stripslashes( wp_filter_post_kses( str_replace( array( "\r\n", "\r", "\n" ), '<br/>', $field_note_text ) ) );

			$orddd_settings['orddd_field_note_text'] = $field_note_text;
			if ( strpos( $field_note_text, '"' ) !== false ) {
				$orddd_settings['orddd_field_note_text'] = $field_note_text;
			}

			//$hidden_vars_str                                   = esc_attr( self::orddd_get_shipping_based_settings( $vendor_id ) );
			$hidden_vars_str                                   = self::orddd_get_shipping_based_settings( $vendor_id ) ;
			$orddd_settings['orddd_hidden_vars_str']           = $hidden_vars_str;
			$orddd_settings['orddd_default_hidden_vars_str']   = $hidden_vars_str;
			$orddd_settings['orddd_delivery_checkout_options'] = self::orddd_get_option( 'orddd_delivery_checkout_options', '', $vendor_id );
			if ( $is_vendor_screen ) {
				$orddd_settings['orddd_delivery_checkout_options'] = self::orddd_get_option( 'orddd_delivery_checkout_options', '', self::orddd_get_vendor_id() );			
			}
			$orddd_settings['orddd_minimum_delivery_time']     = self::orddd_get_option( 'orddd_minimumOrderDays', '', $vendor_id );

			$orddd_settings['orddd_minimum_pickup_time']     = self::orddd_get_option( 'orddd_minimum_pickup_time', '', $vendor_id );

			if ( 'text_block' === self::orddd_get_option( 'orddd_delivery_checkout_options', '', $vendor_id ) ) {
				$orddd_settings['orddd_global_minimum_delivery_time'] = self::orddd_get_option( 'orddd_minimumOrderDays', '', $vendor_id );
				$orddd_settings['orddd_min_between_days']             = self::orddd_get_option( 'orddd_min_between_days', '', $vendor_id );
				$orddd_settings['orddd_max_between_days']             = self::orddd_get_option( 'orddd_max_between_days', '', $vendor_id );
				$orddd_settings['orddd_is_shipping_text_block']       = get_option( 'orddd_is_shipping_text_block', '', $vendor_id );
			}

			$disable_delivery_fields = 'no';
			if ( has_filter( 'orddd_disable_delivery_fields' ) ) {
				$disable_delivery_fields = apply_filters( 'orddd_disable_delivery_fields', $disable_delivery_fields );
			}

			$orddd_settings['orddd_disable_delivery_fields'] = $disable_delivery_fields;

			$partially_booked_dates_str = str_replace( "'", "", orddd_widget::get_partially_booked_dates( '', array(), $vendor_id ) );
			$available_deliveries_arr   = explode( '&', $partially_booked_dates_str );

			$orddd_settings['orddd_partially_booked_dates'] = htmlspecialchars( $available_deliveries_arr[0], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );

			if ( isset( $available_deliveries_arr[1] ) ) {
				$orddd_settings['orddd_available_deliveries'] = htmlspecialchars( $available_deliveries_arr[1], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
			}

			$add_tooltip_for_weekday_str = '';
			if ( has_filter( 'orddd_tooltip_for_weekday' ) ) {
				$add_tooltip_for_weekday = apply_filters( 'orddd_tooltip_for_weekday', '' );
				if ( is_array( $add_tooltip_for_weekday ) && count( $add_tooltip_for_weekday ) > 0 ) {
					foreach ( $add_tooltip_for_weekday as $key => $value ) {
						$add_tooltip_for_weekday_str .= $key . '=>' . $value . ';';
					}
				}
			}

			$orddd_settings['add_tooltip_for_weekday'] = $add_tooltip_for_weekday_str;

			/** Return the date in d-m-Y format which will by default autofil this date in the delivery date
			 * field on the checkout page. The past dates will still be available in the calendar.
			 * Example date, passed from the hook should be 29-6-2019. Here 29 is date, 6 is month and 2019 is year.
			 *
			 * Code to be added in the functions.php file.
			 *
			 * add_filter( 'orddd_first_autofil_delivery_date', 'orddd_first_autofil_delivery_date_callback' );
			 * function orddd_first_autofil_delivery_date_callback() {
			 *      return '29-6-2019';
			 * }
			 */
			$first_autofil_date = '';
			if ( has_filter( 'orddd_first_autofil_delivery_date' ) ) {
				$first_autofil_date = apply_filters( 'orddd_first_autofil_delivery_date', '' );
			}

			if ( 0 === $vendor_id ) {

				// Add default/general parameters needed in initialize-datepicker.js.
				// TODO: Remove the settings below which have been put in general_settings from code above when we are sure they are not needed.
				$orddd_settings['general_params']['field_placement_on_checkout']           = get_option( 'orddd_delivery_date_fields_on_checkout_page' );
				$orddd_settings['general_params']['orddd_is_account_page']                 = is_account_page();
				$orddd_settings['general_params']['orddd_is_admin']                        = is_admin();
				$orddd_settings['general_params']['update_order_review']                   = $orddd_settings['update_order_review'];
				$orddd_settings['general_params']['orddd_shipping_class_settings_to_load'] = $orddd_settings['orddd_shipping_class_settings_to_load'];
				$orddd_settings['general_params']['orddd_category_settings_to_load']       = $orddd_settings['orddd_category_settings_to_load'];
				$orddd_settings['general_params']['orddd_shipping_class_settings_to_load'] = $orddd_settings['orddd_shipping_class_settings_to_load'];
				$orddd_settings['general_params']['orddd_product_settings_to_load']        = $orddd_settings['orddd_product_settings_to_load'];
				$orddd_settings['general_params']['orddd_user_role_settings_to_load']      = $orddd_settings['orddd_user_role_settings_to_load'];
				$orddd_settings['general_params']['orddd_enable_shipping_based_delivery']  = $orddd_settings['orddd_enable_shipping_based_delivery'];
				$orddd_settings['general_params']['orddd_admin_url']  = $orddd_settings['orddd_admin_url'];
				$orddd_settings['general_params']['wc_ajax_url']  = $orddd_settings['wc_ajax_url'];
				$orddd_settings['general_params']['orddd_is_admin']  = $orddd_settings['orddd_is_admin'];
				$orddd_settings['general_params']['orddd_is_inline']  = $orddd_settings['orddd_is_inline'];
				$orddd_settings['general_params']['orddd_is_cart']  = $orddd_settings['orddd_is_cart'];
				$orddd_settings['general_params']['orddd_is_account_page']  = $orddd_settings['orddd_is_account_page'];
				$orddd_settings['general_params']['orddd_delivery_date_on_cart_page']  = $orddd_settings['orddd_delivery_date_on_cart_page'];
				$orddd_settings['general_params']['orddd_current_hour']  = $orddd_settings['orddd_current_hour'];
				$orddd_settings['general_params']['orddd_current_minute']  = $orddd_settings['orddd_current_minute'];
				$orddd_settings['general_params']['orddd_delivery_checkout_options']  = $orddd_settings['orddd_delivery_checkout_options'];
				$orddd_settings['general_params']['orddd_delivery_checkout_options']  = $orddd_settings['orddd_delivery_checkout_options'];
				$orddd_settings['general_params']['orddd_disable_delivery_fields']  = $orddd_settings['orddd_disable_delivery_fields'];
				$orddd_settings['general_params']['orddd_zone_id']  = $orddd_settings['orddd_zone_id'];
				$orddd_settings['general_params']['orddd_shipping_id']  = $orddd_settings['orddd_shipping_id'];
				$orddd_settings['general_params']['orddd_zones']  = $orddd_settings['orddd_zones'];
				$orddd_settings['general_params']['orddd_date_field_mandatory']  = $orddd_settings['orddd_date_field_mandatory'];
				$orddd_settings['general_params']['orddd_timeslot_field_mandatory']  = $orddd_settings['orddd_timeslot_field_mandatory'];
				$orddd_settings['general_params']['woocommerce_advanced_flat_rate'] = false;
				if ( ( is_plugin_active( 'woo-extra-flat-rate/advanced-flat-rate-shipping-for-woocommerce.php' ) || is_plugin_active( 'advanced-flat-rate-shipping-for-woocommerce/advanced-flat-rate-shipping-for-woocommerce.php' ) ) ) {
					// Advanced Flat Rate Shipping Method WooCommerce by Multidots.
					$orddd_settings['general_params']['woocommerce_advanced_flat_rate'] = true;
				}
			}

			$orddd_settings['orddd_first_autofil_delivery_date'] = $first_autofil_date;
			$orddd_settings['ordd_ajax_product_search']          = 'no';
			if ( is_admin() ) {
				$orddd_settings['product_search_url'] = home_url() . '/wp-admin/admin-ajax.php?action=orddd_json_search_products&';
				$ordd_products                        = ORDDD_Functions::orddd_get_products_with_orddd_delivery_enabled();
				if ( empty( $ordd_products ) ) {
					$orddd_settings['ordd_ajax_product_search'] = 'yes';
				}
			}

			$disable_shipping_array = false !== get_option( 'orddd_disable_shipping_methods' ) && '' !== get_option( 'orddd_disable_shipping_methods' ) ? json_decode( get_option( 'orddd_disable_shipping_methods' ), true ) : array();
			$disable_shipping = ! empty( $disable_shipping_array ) ? 'yes' : 'no';
			$orddd_settings['disable_shipping'] = $disable_shipping;

			$selected_shipping = isset( WC()->session ) && WC()->session->get( 'chosen_shipping_methods' ) ? WC()->session->get( 'chosen_shipping_methods' ) : array();

			$orddd_settings['orddd_selected_shipping_method'] = isset( $selected_shipping[0] ) && false !== $selected_shipping[0] ? $selected_shipping[0] : '';

			// Order type.
			$enable_order_type            = self::orddd_get_option( 'orddd_enable_order_type', '' );
			$hide_shipping                = self::orddd_get_option( 'orddd_hide_shipping_methods', '' );
			$hide_shipping_address_fields = self::orddd_get_option( 'orddd_hide_shipping_address_fields', '' );

			$orddd_settings['enable_order_type']        = $enable_order_type;
			$orddd_settings['order_type_hide_shipping'] = 'on' === $enable_order_type && 'on' === $hide_shipping ? 'yes' : 'no';
			$orddd_settings['order_type_hide_shipping_fields'] = 'on' === $enable_order_type && 'on' === $hide_shipping_address_fields ? 'yes' : 'no';
			$orddd_settings['orddd_order_type_minimum_amount'] = ORDDD_Order_Type::is_minimum_order_amount();

			$orddd_settings['wc_needs_shipping'] = ( WC()->cart && WC()->cart->needs_shipping() ) ? true : false;
			$orddd_settings['hide_select_text']  = apply_filters( 'orddd_hide_select_timeslot_text', false );
			$vendor_settings[ $vendor_id ]       = $orddd_settings;
			$additional_data_all[ $vendor_id ]   = $additional_data;
		}

		return apply_filters( 'orddd_hidden_variables_array', $vendor_settings, $additional_data_all );
	}

	/**
	 * This function is to sort the specific dates array by dates in acending order
	 *
	 * @return string Return true/false based on the sorting result.
	 * @since 8.x
	 */
	public static function bkap_sort_specific_dates_array( $a1, $b1 ) {

		$format = 'm-d-Y';

		$a = strtotime( date_format( DateTime::createFromFormat( $format, $a1->date ), 'Y-m-d H:i:s' ) );
		$b = strtotime( date_format( DateTime::createFromFormat( $format, $b1->date ), 'Y-m-d H:i:s' ) );

		if ( $a == $b ) {
			return 0;
		} elseif ( $a > $b ) {
			return 1;
		} else {
			return -1;
		}
	}

	/**
	 * Get the zone id for the address on the checkout page
	 *
	 * @globals resource $wpdb WordPress object
	 * @globals resource $post WordPress post object
	 * @globals resource $woocommerce WooCommerce object
	 *
	 * @param array $post_array Post variables
	 * @param bool  $is_ajax True if called from ajax, else false.
	 * @return string Return zone id if not called from ajax
	 * @since 6.7
	 */
	public static function orddd_get_zone_id_for_order( $order_id ) {
		$post_array = array();
		$order      = wc_get_order( $order_id );
		$zone_str   = '';

		if ( ! $order_id ) {
			return false;
		}
		$order = wc_get_order( $order_id );

		$post_array['billing_country']  = $order->get_billing_country();
		$post_array['billing_state']    = $order->get_billing_state();
		$post_array['billing_postcode'] = $order->get_billing_postcode();		
		
		$post_array['ID']               = $order_id;

		$is_subscription = false;
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$subscription = wcs_get_subscription( $order_id );
			if ( is_object( $subscription ) && wcs_is_subscription( $subscription ) ) {
				$is_subscription = true;
			} elseif ( wcs_order_contains_renewal( $order ) ) {
				$subscriptions = wcs_get_subscriptions_for_renewal_order( $order );
				$subscription  = array_pop( $subscriptions );

				$order_id = $subscription->get_parent_id();
			}
		}
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '2.6.0', '>=' ) ) {
			global $wpdb, $woocommerce, $post;
			$shipping_zone_id = '';
			if ( is_array( $post_array ) && count( $post_array ) > 0 ) {
				$country  = '';
				$state    = '';
				$postcode = '';
				if ( isset( $post_array['billing_country'] ) ) {
					$country = strtoupper( wc_clean( $post_array['billing_country'] ) );
				}

				if ( isset( $post_array['billing_state'] ) ) {
					$state = strtoupper( wc_clean( $post_array['billing_state'] ) );
				}

				$continent = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $country ) ) );

				if ( isset( $post_array['billing_postcode'] ) ) {
					$postcode = wc_normalize_postcode( wc_clean( $post_array['billing_postcode'] ) );
				}
			}

			if ( '' == $country ) {
				$default_country = get_option( 'woocommerce_default_country' );
				if ( strpos( $default_country, ':' ) !== false ) {
					$default_country_arr = explode( ':', $default_country );
					$country             = $default_country_arr[0];
				} else {
					$country = $default_country;
				}
			}

			$cache_key        = WC_Cache_Helper::get_cache_prefix( 'shipping_zones' ) . 'wc_shipping_zone_' . md5( sprintf( '%s+%s+%s', $country, $state, $postcode ) );
			$matching_zone_id = wp_cache_get( $cache_key, 'shipping_zones' );

			if ( false === $matching_zone_id ) {
				$matching_zone_id = self::orddd_get_matching_zone_id( $postcode, $country, $state, $continent );
			}

			if ( class_exists( 'WC_Shipping_Zones' ) ) {
				$shipping_class_id     = '';
				$zone_shipping_methods = "SELECT `order_item_id` FROM {$wpdb->prefix}woocommerce_order_items WHERE `order_item_type` = 'shipping' AND order_id = $order_id";

				$results = $wpdb->get_results( $zone_shipping_methods ); //phpcs:ignore

				if ( is_array( $results ) && count( $results ) > 0 && isset( $results[0] ) ) {
					$method_id   = wc_get_order_item_meta( $results[0]->order_item_id, 'method_id' );
					$instance_id = wc_get_order_item_meta( $results[0]->order_item_id, 'instance_id' );
				}

				if ( isset( $results[0] ) ) {
					$shipping_zone_id = $method_id . ':' . $instance_id;

					if ( $is_subscription ) {
						$shipping_zone_id = $method_id;
						$zone_str         = $matching_zone_id . '-' . $shipping_zone_id;
					} else {
						if ( 'table_rate' == $method_id ) {
							$items = $order->get_items();
							foreach ( $items as $key => $value ) {
								$product_id = $value['product_id'];
								$terms      = get_the_terms( $product_id, 'product_shipping_class' );
								if ( '' != $terms ) {
									foreach ( $terms as $term => $val ) {
										$shipping_class_id = $val->term_id;
									}
								}
							}

							$table_rate_shipping_classes = $wpdb->get_results( "SELECT rate_id, rate_class FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = '{$instance_id}'" ); //phpcs:ignore

							foreach ( $table_rate_shipping_classes as $tkey => $tvalue ) {
								if ( $shipping_class_id == $tvalue->rate_class ) {
										$shipping_zone_id = $method_id . ':' . $instance_id . ':' . $tvalue->rate_id;
								}
							}
						} elseif ( 'wf_fedex_woocommerce_shipping' == $method_id ) {

							$query         = 'SELECT a.meta_key, a.meta_value, b.order_item_name FROM `' . $wpdb->prefix . 'woocommerce_order_itemmeta` AS a, `' . $wpdb->prefix . "woocommerce_order_items` AS b WHERE a.order_item_id = b.order_item_id AND b.order_item_type = 'shipping' AND b.order_id = " . $order_id;
							$fedex_results = $wpdb->get_results( $query );//phpcs:ignore

							foreach ( $fedex_results as $k => $v ) {
								$shipping_method = $v->order_item_name;
							}

							$fedex_settings = get_option( 'woocommerce_wf_fedex_woocommerce_shipping_settings' );
							$fedex_services = $fedex_settings['services'];
							if ( is_array( $fedex_services ) && count( $fedex_services ) > 0 ) {
								foreach ( $fedex_services as $fedex_services_key => $fedex_services_value ) {
									$fedex_services_enabled = $fedex_services_value['enabled'];
									if ( '1' == $fedex_services_enabled || $fedex_services_enabled ) {
										$fedex_services_name = $fedex_services_value['name'];
										if ( '' == $fedex_services_name ) {
											$fedex_services_name = self::orddd_get_fedex_service_name( $fedex_services_key );
										}
										if ( $shipping_method == $fedex_services_name ) {
											$shipping_zone_id = $method_id . ':' . $fedex_services_key;
											break;
										}
									}
								}
							}
						} else {
							$shipping_zone_id = $method_id . ':' . $instance_id;
							// WooCommerce Tree Table Rate Shipping compatibility.
							if ( is_plugin_active( 'wc-tree-table-rate-shipping/wc-tree-table-rate-shipping.php' ) ) {
								$matching_zone_id = $shipping_zone_id;
							}
						}

						$zone_str = $matching_zone_id . '-' . $shipping_zone_id;

					}
				}
			}

			return $zone_str;
		}
	}

	/**
	 * Get the matching zone ID for the postcode.
	 *
	 * @param string $postcode Billing/Shipping postcode.
	 * @param string $country Billing/Shipping Country.
	 * @param string $state Billing/Shipping State.
	 * @param string $continent Billing/Shipping continent.
	 * @return void
	 */
	public static function orddd_get_matching_zone_id( $postcode, $country, $state, $continent ) {
		global $wpdb;
		// phpcs:disable
		// Work out criteria for our zone search
		$criteria   = array();
		$criteria[] = $wpdb->prepare( "( ( location_type = 'country' AND location_code = %s )", $country );
		$criteria[] = $wpdb->prepare( "OR ( location_type = 'state' AND location_code = %s )", $country . ':' . $state );
		$criteria[] = $wpdb->prepare( "OR ( location_type = 'continent' AND location_code = %s )", $continent );
		$criteria[] = 'OR ( location_type IS NULL ) )';

		// Postcode range and wildcard matching
		$postcode_locations = $wpdb->get_results( "SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode';" );

		if ( $postcode_locations ) {
			$zone_ids_with_postcode_rules = array_map( 'absint', wp_list_pluck( $postcode_locations, 'zone_id' ) );
			$matches                      = wc_postcode_location_matcher( $postcode, $postcode_locations, 'zone_id', 'location_code', $country );
			$do_not_match                 = array_unique( array_diff( $zone_ids_with_postcode_rules, array_keys( $matches ) ) );

			if ( ! empty( $do_not_match ) ) {
				$criteria[] = 'AND zones.zone_id NOT IN (' . implode( ',', $do_not_match ) . ')';
			}
		}

		// Get matching zones
		$matching_zone_id = $wpdb->get_var( "SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones LEFT OUTER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND location_type != 'postcode' WHERE " . implode( ' ', $criteria ) . ' ORDER BY zone_order ASC LIMIT 1' ); //phpcs:ignore

		if ( false === $matching_zone_id || is_null( $matching_zone_id ) ) {
			$postcode_locations = $wpdb->get_results( "SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode';" );
			$zones              = array();
			if ( $postcode_locations ) {
				$zone_ids_with_postcode_rules = array_map( 'absint', wp_list_pluck( $postcode_locations, 'zone_id' ) );
				$matches                      = wc_postcode_location_matcher( $postcode, $postcode_locations, 'zone_id', 'location_code', $country );
				$zones                        = array_keys( $matches );
			}

			if ( count( $zones ) > 0 ) {
				$matching_zone_id = $wpdb->get_var(
					"SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones LEFT OUTER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id WHERE zones.zone_id IN (" . implode( ',', $zones )  . ') ORDER BY zone_order ASC LIMIT 1 
				'
				);
			}
		}

		return $matching_zone_id;
	}

	/**
	 * Get the zone ids & first shipping methods for all zones.
	 *
	 * @return array
	 */
	public static function orddd_get_zone_id() {
		$cache_option_value_name = 'orddd_get_zone_id';
		$cache_option_date_name  = 'orddd_get_zone_id_cache_date';

		$cached_timestamp = get_option( $cache_option_date_name, 0 );
		// new day, new cache.
		if ( 0 === $cached_timestamp || gmdate( 'j', time() ) !== gmdate( 'j', $cached_timestamp ) || ( time() - $cached_timestamp ) > 60 * 60 * 24 ) {
			$cached_value = false;
		} else {
			$cached_value = get_option( $cache_option_value_name );
		}
		if ( false !== $cached_value ) {
			return $cached_value;
		}
		$zones             = WC_Shipping_Zones::get_zones();
		$rest_of_the_world = WC_Shipping_Zones::get_zone_by( 'zone_id', 0 );
		array_unshift( $zones, $rest_of_the_world->get_data() );

		$first_methods = array();

		foreach ( $zones as $key => $zone ) {
			$zone_id = $zone['id'];
			if ( 0 == $zone_id ) {
				$shipping_methods = $rest_of_the_world->get_shipping_methods();
			} else {
				$shipping_methods = $zone['shipping_methods'];
			}

			foreach ( $shipping_methods as $id => $method ) {
				if ( 'no' === $method->enabled ) {
					continue;
				}
				$method_id   = $method->id;
				$instance_id = $method->instance_id;

				if ( 'free_shipping' == $method_id && '' !== $method->requires && 'coupon' !== $method->requires ) {
					$min_amount = $method->min_amount;
					if ( isset( WC()->cart ) ) {
						$cart_total = WC()->cart->subtotal;

						if ( $cart_total < $min_amount ) {
							continue;
						}
					}
				}

				// Add all the FEDEX service keys on checkout.
				if ( 'fedex' == $method->id ) {
					$fedex_instance = $instance_id;
					$fedex_settings = $method->instance_settings;
					$fedex_id       = '';
					$fedex_services = $fedex_settings['services'];

					if ( is_array( $fedex_services ) && count( $fedex_services ) > 0 ) {
						foreach ( $fedex_services as $fedex_services_key => $fedex_services_value ) {
							$fedex_services_enabled = $fedex_services_value['enabled'];
							if ( '1' == $fedex_services_enabled ) {
								$fedex_services_name = $fedex_services_value['name'];
								if ( '' == $fedex_services_name ) {
									$fedex_services_name = self::orddd_get_fedex_service_name( $fedex_services_key );
								}
								if ( is_plugin_active( 'woocommerce-shipping-fedex/woocommerce-shipping-fedex.php' ) ) {
									$fedex_id = 'fedex' . ':' . $fedex_instance . ':' . $fedex_services_key;
								} else {
									$fedex_id = $zone_id . ':' . 'fedex' . ':' . $fedex_services_key;
								}
								$first_methods[ $fedex_services_key ] = $zone_id . '-' . $fedex_id;
							}
						}
					}
					break;
				} elseif ( 'flexible_shipping' === $method->id ) {
					$flexible_methods = get_option( $method->shipping_methods_option );

					foreach ( $flexible_methods as $flexible_methods_key => $flexible_methods_value ) {
						if ( 'yes' === $flexible_methods_value['method_enabled'] ) {
							$first_methods[] = $zone_id . '-' . $flexible_methods_value['id_for_shipping'];
						}
					}
				} elseif( 'ups' == $method->id ) {
					
					$ups_instance = $instance_id;
					$ups_settings = $method->instance_settings;
					$ups_services = $ups_settings['services'];
					foreach ( $ups_services as $ups_services_key => $ups_services_value ) {
						$ups_services_enabled = $ups_services_value['enabled'];
						if ( '1' == $ups_services_enabled ) {
							$ups_id                   = 'ups' . ':' . $ups_instance . ':' . $ups_services_key;
							$first_methods[] = $zone_id . '-' . $ups_id;
						}
					}
					break;
				}elseif ( 'wbs' == $method->id && ( is_plugin_active( 'weight-based-shipping-for-woocommerce/plugin.php' ) || is_plugin_active( 'wc-weight-based-shipping/plugin.php' ) ) ) {
					// Custom Delivery Settings for Weight based Shippng plugin by weightbasedshipping.com
					$wbs_settings = get_option( 'wbs_' . $instance_id . '_config' );
					if ( isset( $wbs_settings['enabled'] ) && $wbs_settings['enabled'] == 1 ) {
						foreach ( $wbs_settings['rules'] as $rk => $rv ) {
							if ( isset( $rv['meta']['enabled'] ) && $rv['meta']['enabled'] == 1 ) {
								$package_title            = $rv['meta']['title'];
								$package_id               = ORDDD_Functions::orddd_get_shipping_package_id( $package_title );
								$title                    = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ' . $package_title;
								$id                       = $method->id . ':' . $instance_id . ':' . $package_id;
								$first_methods[] = $zone_id . '-' . $id;
							}
						}
					}
					break;
				}
				
				$first_methods = self::orddd_get_shipping_id( $zone_id, $method_id, $instance_id, $first_methods );
			
				// break;
			}
		}

		if ( ( is_plugin_active( 'woo-extra-flat-rate/advanced-flat-rate-shipping-for-woocommerce.php' ) || is_plugin_active( 'advanced-flat-rate-shipping-for-woocommerce/advanced-flat-rate-shipping-for-woocommerce.php' ) ) ) {
			// Advanced Flat Rate Shipping Method WooCommerce by Multidots.
			$methods = get_posts(
				array(
					'posts_per_page' => '-1',
					'post_type'      => 'wc_afrsm',
					'post_status'    => array( 'publish' ),
					'order'          => 'ASC',
				)
			);

			foreach ( $methods as $method ) {
				$zone_id                   = 'advanced_flat_rate_shipping:' . $method->ID;
				$first_methods[ $zone_id ] = $zone_id . '-' . $zone_id;
			}
		}
		update_option( $cache_option_value_name, $first_methods );
		update_option( $cache_option_date_name, time() );

		return $first_methods;
	}

	/**
	 * Get the method id & instance id for a zone.
	 *
	 * @param int $zone_id Zone ID.
	 * @param int $method_id Shipping method ID.
	 * @param int $instance_id Instance ID for shipping method.
	 * @return string
	 */
	public static function orddd_get_shipping_id( $zone_id, $method_id, $instance_id, $first_methods = false ) {
		global $woocommerce, $wpdb;
		$shipping_zone_id = $method_id . ':' . $instance_id;
		$matching_zone_id = $zone_id;
		$current_methods  = false === $first_methods ? array(): $first_methods;
		$match_found      = false;

		if( 'table_rate' == $method_id ) {
			$shipping_class_id = '';
			if ( isset( $woocommerce->cart ) && $woocommerce->cart->cart_contents_count != 0 ) {
				foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
					if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
						$product_id = $values['data']->get_id();
					} else {
						$product_id = $values['data']->id;
					}

					$product = wc_get_product( $product_id );
					if ( is_a( $product, 'WC_Product_Variation' ) ) {
						$product_id = $product->get_parent_id();
					}
					
					$terms = get_the_terms( $product_id, 'product_shipping_class' );
					if ( '' != $terms ) {
						foreach ( $terms as $term => $val ) {
							$shipping_class_id = $val->term_id;
						}
					}
				}
			}
			$table_rate_shipping_classes = $wpdb->get_results( "SELECT rate_id, rate_class FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = '{$instance_id}'" ); //phpcs:ignore

			foreach ( $table_rate_shipping_classes as $tkey => $tvalue ) {
				if ( $shipping_class_id == $tvalue->rate_class ) {
					$shipping_zone_id = $method_id . ':' . $instance_id . ':' . $tvalue->rate_id;
					$current_methods[] = $matching_zone_id . '-' . $shipping_zone_id;
					$match_found = true;
				}
			}
		}

		if ( isset( WC()->session ) && is_plugin_active( 'woocommerce-advanced-shipping/woocommerce-advanced-shipping.php' ) ) {
			$advance_shipping_method = WC()->session->get( 'chosen_shipping_methods' );
			if ( null !== $advance_shipping_method ) {
				$matching_zone_id = $advance_shipping_method[0];
				$shipping_zone_id = $advance_shipping_method[0];
				$current_methods[] = $matching_zone_id . '-' . $shipping_zone_id;
				$match_found = true;
			}
		}

		// WooCommerce Tree Table Rate Shipping compatibility.
		if ( is_plugin_active( 'wc-tree-table-rate-shipping/wc-tree-table-rate-shipping.php' ) ) {
			$matching_zone_id = $shipping_zone_id;
			$current_methods[] = $matching_zone_id . '-' . $shipping_zone_id;
			$match_found = true;
		}
		if ( false !== $first_methods ) {			
			if ( ! $match_found ) {
				$current_methods[] = $matching_zone_id . '-' . $shipping_zone_id;
			}
			return $current_methods;
		}
		return $matching_zone_id . '-' . $shipping_zone_id;
	}

	/**
	 * Options for JQuery Datepicker
	 *
	 * @param string $holidays_str Added holidays
	 * @param string $lockout_str Booked days for delivery
	 * @return array Options to enable on datepicker
	 * @since 1.0
	 */
	public static function get_datepicker_options( $holidays_str, $lockout_str, $vendor_id = 0 ) {
		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );

		$options                 = array();
		$min_date                = '';
		$min_hour                = 0;
		$min_minute              = 0;
		$same_day_cutoff_enabled = ORDDD_Functions::orddd_same_day_cutoff_enabled( $vendor_id );
		$next_day_cutoff_enabled = ORDDD_Functions::orddd_next_day_cutoff_enabled( $vendor_id );

		if ( 'on' === self::orddd_get_option( 'orddd_enable_delivery_date', '', $vendor_id ) && ( 'on' === $same_day_cutoff_enabled || 'on' === $next_day_cutoff_enabled ) ) {
			$options[] = 'beforeShow:maxdt';
		} elseif ( 'on' !== $next_day_cutoff_enabled && 'on' !== $same_day_cutoff_enabled && 'on' === self::orddd_get_option( 'orddd_enable_delivery_date', '', $vendor_id ) ) {
			$options[] = 'beforeShow:avd';
		}

		$options[] = "dateFormat:'" . self::orddd_get_option( 'orddd_delivery_date_format', '', $vendor_id ) . "'";

		$time_settings_enable = self::orddd_get_option( 'orddd_enable_delivery_time', '', $vendor_id );

		if ( 'on' === $time_settings_enable ) {
			$current_date          = gmdate( 'j-n-Y', $current_time );
			$current_hour          = gmdate( 'H', $current_time );
			$current_minute        = gmdate( 'i', $current_time );
			$minimum_delivery_time = self::orddd_get_option( 'orddd_minimumOrderDays', '', $vendor_id );

			if ( '' === $minimum_delivery_time ) {
				$minimum_delivery_time = 0;
			}

			$delivery_time_seconds = $minimum_delivery_time * 60 * 60;
			$min_date_array        = self::get_min_date(
				$delivery_time_seconds,
				array(
					'enabled'    => $time_settings_enable,
					'from_hours' => self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ),
					'from_mins'  => self::orddd_get_option( 'orddd_delivery_from_mins', '', $vendor_id ),
					'to_hours'   => self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id ),
					'to_mins'    => self::orddd_get_option( 'orddd_delivery_to_mins', '', $vendor_id ),
				),
				$holidays_str,
				$lockout_str,
				array(),
				array(),
				$vendor_id
			);

			if ( ! is_account_page() && ! is_admin() ) {
				if ( '' !== self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ) ) {
					if ( $current_date == $min_date_array['min_date'] && 'on' === $next_day_cutoff_enabled && 'on' !== $same_day_cutoff_enabled ) {
						$options[] = 'hourMin:' . self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id );
						$options[] = 'minuteMin:' . self::orddd_get_option( 'orddd_delivery_from_mins', '', $vendor_id );
					} elseif ( ( self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ) < $current_hour || ( self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ) == $current_hour && $current_minute > 0 ) ) && $current_date == $min_date_array['min_date'] && ( $current_hour > $min_date_array['min_hour'] || ( $current_hour == $min_date_array['min_hour'] && $current_minute > $min_date_array['min_minute'] ) ) && $current_hour <= self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id ) ) {
						$options[] = 'hourMin:' . $current_hour;
						$options[] = 'minuteMin:' . $current_minute;
					} elseif ( self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ) < $min_date_array['min_hour'] || ( self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id ) == $min_date_array['min_hour'] && $min_date_array['min_minute'] > 0 ) && $min_date_array['min_hour'] <= self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id ) ) {
						$options[] = 'hourMin:' . $min_date_array['min_hour'];
						$options[] = 'minuteMin:' . $min_date_array['min_minute'];
					} else {
						$options[] = 'hourMin:' . self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id );
						$options[] = 'minuteMin:' . self::orddd_get_option( 'orddd_delivery_from_mins', '', $vendor_id );
					}
				}
			}

			if ( self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id ) != '' ) {
				$options[] = 'hourMax:' . self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id );
			}

			$step_min  = apply_filters( 'orddd_time_slider_minute_step', 5 );
			$options[] = 'stepMinute:' . $step_min;

			if ( '1' === get_option( 'orddd_delivery_time_format' ) ) {
				$options[] = "timeFormat:'hh:mm TT'";
			} else {
				$options[] = "timeFormat:'HH:mm'";
			}
		}

		$options_str = implode( '&', $options );
		return $options_str;
	}

	/**
	 * Get days to disable on the calendar when the same day or next day is enabled.
	 *
	 * @globals array $orddd_shipping_days Shipping weekdays array
	 * @globals array $orddd_weekdays Weekdays array
	 *
	 * @return string String of the days to disable in the calendar.
	 * @since 1.0
	 */
	public static function get_disabled_days( $vendor_id = 0 ) {
		global $orddd_shipping_days, $orddd_weekdays;

		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );

		$disable_weekdays     = array();
		$disabled_days        = array();
		$disabled_days_str    = '';
		$disable_weekdays_str = '';
		$var                  = '';

		$holidays_arr     = array();
		$holiday_date_arr = array();

		$holidays = self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id );

		if ( '' !== $holidays && '{}' !== $holidays && '[]' !== $holidays && 'null' !== $holidays ) {
			$holidays_arr = json_decode( self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id ) );
		}

		foreach ( $holidays_arr as $k => $v ) {
			$holiday_date_arr[] = $v->d;
		}

		$minimum_delivery_time   = orddd_get_minimum_delivery_time( $vendor_id );
		$same_day_cutoff_enabled = ORDDD_Functions::orddd_same_day_cutoff_enabled( $vendor_id );
		$next_day_cutoff_enabled = ORDDD_Functions::orddd_next_day_cutoff_enabled( $vendor_id );

		if ( 'on' === $same_day_cutoff_enabled ) {
			$same_day_cut_off      = orddd_get_cutoff_timestamp( 'same_day', $vendor_id );
			$cut_off_with_min_time = $current_time + $minimum_delivery_time;

			$enable_day_until_cutoff = apply_filters( 'orddd_enable_day_until_cutoff', false );
			if ( $enable_day_until_cutoff && ( 0 !== $minimum_delivery_time || '' !== $minimum_delivery_time ) ) {
				$cut_off_with_min_time = $current_time;
			}

			if ( 'on' === self::orddd_get_option( 'orddd_enable_shipping_days','', $vendor_id ) ) {
				$days_disabled          = 'No';
				$orddd_weekdays_enabled = array();
				foreach ( $orddd_shipping_days as $s_key => $s_value ) {
					$day_check = self::orddd_get_option( $s_key, '', $vendor_id );
					if ( 'checked' === $day_check ) {
						$orddd_weekdays_enabled[ $s_key ] = $day_check;
					}
				}

				$current_day = gmdate( 'w', $same_day_cut_off );
			}

			if ( $cut_off_with_min_time >= $same_day_cut_off && ! in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ), $holiday_date_arr ) ) {
				$disabled_days[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time );
			}
		}

		if ( 'on' === $next_day_cutoff_enabled ) {
			$next_day_cut_off      = orddd_get_cutoff_timestamp( 'next_day', $vendor_id );
			$cut_off_with_min_time = $current_time + $minimum_delivery_time;

			if ( 'on' === self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
				$days_disabled          = 'No';
				$orddd_weekdays_enabled = array();
				foreach ( $orddd_shipping_days as $s_key => $s_value ) {
					$day_check = self::orddd_get_option( $s_key, '', $vendor_id );
					if ( $day_check == 'checked' ) {
						$orddd_weekdays_enabled[ $s_key ] = $day_check;
					}
				}
			} else {
				$days_disabled          = 'No';
				$orddd_weekdays_enabled = array();
				foreach ( $orddd_weekdays as $s_key => $s_value ) {
					$day_check = self::orddd_get_option( $s_key, '', $vendor_id );
					if ( $day_check == 'checked' ) {
						$orddd_weekdays_enabled[ $s_key ] = $day_check;
					}
				}
			}
			
			$specific_dates     = ORDDD_Functions::orddd_get_global_specific_dates( $vendor_id );
			$all_specific_dates = array();
			foreach ( $specific_dates  as $key => $date ) {
				$all_specific_dates[] = $date['date'];
			}

			$next_day           = gmdate( 'w', $next_day_cut_off + 86400 );
			$next_day_timestamp = $next_day_cut_off + 86400;
			$next_date          = gmdate( 'Y-m-d', $next_day_timestamp );
			$day_after          = gmdate( 'Y-m-d', strtotime( $next_date . ' +1 day' ) );

			if ( $current_time < $next_day_cut_off && $cut_off_with_min_time < strtotime( $day_after ) ) {
				$current_weekday                 = gmdate( 'w', $current_time );
				$disabled_days_for_shipping_days = 'No';
				$j = $current_weekday;
				for (  $i = 0; $i <= 6; $i ++ ) {
					$is_day_disabled = 'no';
					if ( 'on' === get_option( 'orddd_enable_shipping_days' ) ) {
						if ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ! isset( $orddd_weekdays_enabled[ 'orddd_shipping_day_' . $j ] ) ) {
							$is_day_disabled = 'yes';
						}
					} else {
						if ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ! isset( $orddd_weekdays_enabled[ 'orddd_weekday_' . $j ] )  && ( ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 && !in_array( gmdate( 'Y-m-d', $current_time ), $all_specific_dates ) ) || is_array( $all_specific_dates ) && count( $all_specific_dates ) == 0 )  ) {
							$is_day_disabled = 'yes';
						}
					}

					if ( $is_day_disabled == 'yes' ) {
						$disabled_days_for_shipping_days = 'Yes';
						$current_time                    = strtotime( '+1 day', $current_time );
						$j                               = gmdate( 'w', $current_time );
					} else {
						if ( in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ), $holiday_date_arr ) ) {
							$consider_holiday_for_nextday_cutoff = apply_filters( 'orddd_consider_holiday_for_nextday_cutoff', false );
							if ( ! $consider_holiday_for_nextday_cutoff ) {
								$current_time = strtotime( '+1 day', $current_time );
								$j            = gmdate( 'w', $current_time );
							}
						} else {
							break;
						}
					}
				}

				// Disabled the current date in the calendar only when same day is disabled for delivery
				// and next day is enabled.
				if ( 'on' !== $same_day_cutoff_enabled && 'yes' === $is_day_disabled ) {
					$disable_weekdays[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, current_time( 'timestamp', $gmt ) );
					$disable_weekdays[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time );
				}
				$same_day_after_hours = get_option( 'orddd_disable_same_day_delivery_after_hours' );
				$same_day_after_mins  = get_option( 'orddd_disable_same_day_delivery_after_minutes' );
				if ( ( isset( $same_day_after_hours ) && ( $same_day_after_hours == 0 || '' === $same_day_after_hours ) && isset( $same_day_after_mins ) && ( $same_day_after_mins == 0 || '' === $same_day_after_mins ) ) ) {
						$disable_weekdays[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time );
				}
			} else {
				if ( 'on' !== $same_day_cutoff_enabled ) {
					$disable_weekdays[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time );
				}
				$j               = $next_day;
				$current_weekday = gmdate( 'w', $current_time );

				for ( $i = 0; $i <= 6; $i ++ ) {
					$is_day_disabled = 'no';
					if ( 'on' === self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
						if ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ! isset( $orddd_weekdays_enabled[ 'orddd_shipping_day_' . $j ] ) ) {
							$is_day_disabled = 'yes';
						}
					} else {
						if ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ( ! isset( $orddd_weekdays_enabled[ 'orddd_weekday_' . $j ] ) || ! isset( $orddd_weekdays_enabled[ 'orddd_weekday_' . $current_weekday ] ) ) && ( ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 && !in_array( gmdate( 'Y-m-d', $current_time ), $all_specific_dates ) ) ) ) {
							$is_day_disabled = 'yes';
						}
					}

					if ( $is_day_disabled == 'yes' ) {
						$days_disabled      = 'Yes';
						$disable_weekdays[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $next_day_timestamp );
						$current_time       = strtotime( '+1 day', $current_time );
						$next_day_timestamp = strtotime( '+1 day', $next_day_timestamp );
						$j                  = date( 'w', $next_day_timestamp );
						$current_weekday    = gmdate( 'w', $current_time );

					} else {
						if ( $current_time <= $next_day_timestamp ) {
							if ( in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $next_day_timestamp ), $holiday_date_arr ) ) {
								$days_disabled      = 'Yes';
								$disable_weekdays[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time );

								$consider_holiday_for_nextday_cutoff = apply_filters( 'orddd_consider_holiday_for_nextday_cutoff', false );
								if ( ! $consider_holiday_for_nextday_cutoff ) {
									$next_day_timestamp = strtotime( '+1 day', $next_day_timestamp );
								}
								$current_time = strtotime( '+1 day', $current_time );

								$j = gmdate( 'w', $current_time );
							} else {
								$current_time = strtotime( '+1 day', $current_time );
								$j            = gmdate( 'w', $current_time );
							}
						} else {
							break;
						}
					}
				}
				
				$disabled_days[] = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $next_day_timestamp );
			}
		}

		if ( is_array( $disabled_days ) && count( $disabled_days ) > 0 ) {
			$disabled_days_str = '"' . implode( '","', $disabled_days ) . '"';
		}

		if ( is_array( $disable_weekdays ) && count( $disable_weekdays ) > 0 ) {
			$disable_weekdays_str = '"' . implode( '","', $disable_weekdays ) . '"';
		}

		if ( $disable_weekdays_str == '' && $disabled_days_str == '' ) {
			return '';
		} else {
			return $disabled_days_str . '&' . $disable_weekdays_str;
		}
	}
	/**
	 * Free up delivery date & time when the order is moved to trash
	 *
	 * @globals string $typenow Current page
	 * @hook wp_trash_post
	 *
	 * @param int $order_id Order ID
	 * @since 3.2
	 */
	public static function orddd_cancel_delivery_for_trashed( $order_id ) {
		global $typenow;
		if ( 'shop_order' != $typenow && ( ! isset( $_POST['orddd_post_type'] ) || ( isset( $_POST['orddd_post_type'] ) && ( $_POST['orddd_post_type'] != 'shop_order' ) ) ) && ! ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] ) ) {
			return;
		} else {
			if ( ORDDD_Functions::is_hpos_enabled() ) {
				$post_obj    = wc_get_order( $order_id );
				$post_status = $post_obj->get_status();
				$post_type   = $post_obj->get_type();
				
			} else {
				$post_obj    = get_post( $order_id );
				$post_status = $post_obj->post_status;
				$post_type   = $post_obj->post_type;
			}
			
			// array of all the  order statuses for which the deliveries do not need to be freed up
			$status = array( 'wc-pending', 'wc-cancelled', 'wc-refunded', 'wc-failed' );

			if ( 'shop_order' === $post_type && ( ! in_array( $post_status, $status ) ) ) {
				
				orddd_lockout_functions::orddd_maybe_increase_delivery_lockout( $order_id );
				
			}			
		}
	}

	/**
	 * Block the delivery date & time again when the order is restored from the trash
	 *
	 * @hook untrash_post
	 * @param int    $post_id Order ID.
	 * @param string $previous_status Order Previous Status.
	 * @since 7.5
	 */
	public static function orddd_untrash_order( $order_id, $previous_status ) {
		$status = array( 'wc-pending', 'wc-cancelled', 'wc-refunded', 'wc-failed' );
		$order  = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		$type = $order->get_type();

		if ( 'shop_order' === $type && ( ! in_array( $previous_status, $status ) ) ) {
			// untrash the delivery dates as well.
			orddd_lockout_functions::orddd_maybe_reduce_delivery_lockout( $order_id );
		}
	}

	/**
	 * Cancel Delivery for the order when the order status is cancelled/refunded/failed.
	 *
	 * @hook woocommerce_order_status_cancelled
	 * @hook woocommerce_order_status_refunded
	 * @hook woocommerce_order_status_failed
	 * @globals resource $wpdb WordPress Object
	 * @globals string $typenow Current page
	 * @param int $order_id Order ID
	 * @since 3.2
	 */
	public static function orddd_cancel_delivery( $order_id ) {
		global $wpdb, $typenow;
		$shipping_method = '';

		$delivery_date_timestamp = '';

		$order     = wc_get_order( $order_id );
		$post_meta = $order->get_meta( '_orddd_timestamp' );
		if ( isset( $post_meta ) && $post_meta != '' && $post_meta != null ) {
			$delivery_date_timestamp = $post_meta;
		}

		$time_format_to_show = self::orddd_get_time_format();

		$time_field_label   = orddd_custom_delivery_functions::orddd_fetch_time_slot_field_label( $order_id );
		$timeslt            = '';
		$timeslot_post_meta = $order->get_meta( $time_field_label );
		$post_timeslot      = $order->get_meta( '_orddd_time_slot' );
		if ( isset( $timeslot_post_meta ) && $timeslot_post_meta != '' && $timeslot_post_meta != null ) {
			$timeslt = self::orddd_change_time_slot_format( $timeslot_post_meta, $time_format_to_show );
		} elseif ( $post_timeslot && $post_timeslot != '' && $post_timeslot != null ) {
			$timeslt = self::orddd_change_time_slot_format( $post_timeslot, $time_format_to_show );
		}

		$total_quantities = 1;
		if ( get_option( 'orddd_lockout_date_quantity_based' ) == 'on' ) {
			$items            = $order->get_items();
			$total_quantities = 0;
			foreach ( $items as $key => $value ) {
				$total_quantities += $value['qty'];
			}
		}

		$shipping_based_lockout     = $shipping_based_timeslot_lockout = 'No';
		$shipping_settings_to_check = array();		

		$shipping_settings_to_check = array();
		$vendor_id = 0;
		$is_vendor = apply_filters( 'orddd_integration_vendors_is_vendor', false );
		
		$type       = '';
		$categories = array();

		$custom_delivery_schedule_id = $order->get_meta( '_orddd_delivery_schedule_id', true );

		if ( $is_vendor ) {
			$vendor_id = get_current_user_id();
		}
		
		$results = self::orddd_get_shipping_settings( 1, $vendor_id );

		if ( isset( $custom_delivery_schedule_id ) && 0 != $custom_delivery_schedule_id ) {
			$option_name                                = 'orddd_shipping_based_settings_' . $custom_delivery_schedule_id;
			 $option_value = orddd_common::orddd_get_option( $option_name, '', $vendor_id );
			if ( '' !== $option_value ) {
				$shipping_settings_to_check[ $option_name ] = $option_value;
			}
			$total_settings_applied = $order->get_meta( '_orddd_total_settings_applied', true );
			if ( isset( $total_settings_applied ) && '' != $total_settings_applied ) {
				$count                      = 0;
				$shipping_settings_to_check = array();

				while ( $count < $total_settings_applied ) {
					$custom_delivery_schedule_id                = $order->get_meta( '_orddd_delivery_schedule_id_' . $count, true );
					$option_name                                = 'orddd_shipping_based_settings_' . $custom_delivery_schedule_id;
					$shipping_settings_to_check[ $option_name ] = orddd_common::orddd_get_option( $option_name, '', $vendor_id );
					$count++;
				}
			}
		}

		if ( is_array( $shipping_settings_to_check ) && count( $shipping_settings_to_check ) > 0 ) {
			foreach ( $shipping_settings_to_check as $setting_key => $setting_value ) {
				if ( $delivery_date_timestamp != '' ) {
					if ( isset( $setting_value['delivery_settings_based_on'][0] ) && 'product_categories' === $setting_value['delivery_settings_based_on'][0] ) {
						$categories = isset( $setting_value['product_categories'] ) ? $setting_value['product_categories'] : array();
						$type       = 'product_cat';

					} elseif ( isset( $setting_value['delivery_settings_based_on'][0] ) && 'shipping_methods' === $setting_value['delivery_settings_based_on'][0] ) {
						$categories = array();
						$classes    = isset( $setting_value['shipping_methods'] ) ? $setting_value['shipping_methods'] : array();
						foreach ( $classes as $class ) {
							$shipping_class_term = get_term_by( 'slug', $class, 'product_shipping_class' );
							if ( $shipping_class_term ) {
								array_push( $categories, $class );
							}
						}
						$type = 'product_shipping_class';
					}

					if ( get_option( 'orddd_lockout_date_quantity_based' ) == 'on' ) {
						$total_quantities = self::orddd_get_total_quantities_for_categories( $categories, $type, '', $order_id );
						if ( empty( $categories ) && 0 === $total_quantities ) {
							$total_quantities = 1;
						}
					}

					$delivery_date = gmdate( ORDDD_LOCKOUT_DATE_FORMAT, $delivery_date_timestamp );
					if ( isset( $setting_value['orddd_lockout_date'] ) ) {
						$lockout_date_array = $setting_value['orddd_lockout_date'];
						if ( $lockout_date_array == '' || $lockout_date_array == '{}' || $lockout_date_array == '[]' || $lockout_date_array == 'null' ) {
							$lockout_date_arr = array();
						} else {
							$lockout_date_arr = (array) json_decode( $lockout_date_array );
						}
					} else {
						$lockout_date_arr = array();
					}
					foreach ( $lockout_date_arr as $k => $v ) {
						$orders = $v->o;
						if ( $delivery_date == $v->d ) {
							if ( $v->o == $total_quantities ) {
								unset( $lockout_date_arr[ $k ] );
							} else {
								$orders                 = $v->o - $total_quantities;
								$lockout_date_arr[ $k ] = array(
									'o' => $orders,
									'd' => $v->d,
								);
							}
						}
					}
					$setting_value['orddd_lockout_date'] = json_encode( $lockout_date_arr );
					orddd_common::orddd_update_option( $setting_key, $setting_value, true );
				}
				$shipping_based_lockout = 'Yes';

				if ( isset( $setting_value['time_slots'] ) ) {
					$time_slots = explode( '},', $setting_value['time_slots'] );
					foreach ( $time_slots as $tk => $tv ) {
						$timeslot_values = self::get_timeslot_values( $tv );
						if ( ( $timeslot_values['lockout'] != '' && $timeslot_values['lockout'] != '0' ) || '' !== $setting_value['date_lockout'] ) {
							$select_time_slot = explode( '-', $timeslt );
							$select_from_time = gmdate( 'H:i', strtotime( $select_time_slot[0] ) );
							if ( isset( $select_time_slot[1] ) && $select_time_slot[1] != '' ) {
								$select_to_time       = gmdate( 'H:i', strtotime( $select_time_slot[1] ) );
								$select_time_slot_str = $select_from_time . ' - ' . $select_to_time;
							} else {
								$select_time_slot_str = $select_from_time;
							}

							if ( $timeslot_values['time_slot'] == $select_time_slot_str ) {
								if ( $delivery_date_timestamp != '' ) {
									$lockout_date = gmdate( 'j-n-Y', $delivery_date_timestamp );
								} else {
									$lockout_date = '';
								}

								if ( isset( $setting_value['orddd_lockout_time_slot'] ) ) {
									$lockout_time = $setting_value['orddd_lockout_time_slot'];
									if ( $lockout_time == '' || $lockout_time == '{}' || $lockout_time == '[]' || $lockout_time == 'null' ) {
										$lockout_time_arr = array();
									} else {
										$lockout_time_arr = (array) json_decode( $lockout_time );
									}
								} else {
									$lockout_time_arr = array();
								}

								foreach ( $lockout_time_arr as $time_k => $time_v ) {
									$orders = $time_v->o;
									if ( $timeslt == $time_v->t && $lockout_date == $time_v->d ) {
										if ( $time_v->o == $total_quantities ) {
											unset( $lockout_time_arr[ $time_k ] );
										} else {
											$orders                      = $time_v->o - $total_quantities;
											$lockout_time_arr[ $time_k ] = array(
												'o' => $orders,
												't' => $time_v->t,
												'd' => $time_v->d,
											);
										}
									}
								}

								$setting_value['orddd_lockout_time_slot'] = json_encode( $lockout_time_arr );
								orddd_common::orddd_update_option( $setting_key, $setting_value, true );
								$shipping_based_timeslot_lockout = 'Yes';
								break;
							}
						}
					}
				}
			}
		}

		if ( $shipping_based_lockout == 'No' ) {
			$delivery_date = '';
			if ( $delivery_date_timestamp != '' ) {
				$delivery_date = gmdate( ORDDD_LOCKOUT_DATE_FORMAT, $delivery_date_timestamp );
			}

			$lockout_days = orddd_common::orddd_get_option( 'orddd_lockout_days', '', $vendor_id );
			if ( $lockout_days == '' || $lockout_days == '{}' || $lockout_days == '[]' || $lockout_days == 'null' ) {
				$lockout_days_arr = array();
			} else {
				$lockout_days_arr = (array) json_decode( $lockout_days );
			}

			foreach ( $lockout_days_arr as $k => $v ) {
				$orders = $v->o;
				if ( $delivery_date == $v->d ) {
					if ( $v->o == $total_quantities ) {
						unset( $lockout_days_arr[ $k ] );
					} else {
						$orders                 = $v->o - $total_quantities;
						$lockout_days_arr[ $k ] = array(
							'o' => $orders,
							'd' => $v->d,
						);
					}
				}
			}

			$delivery_dates = $order->get_meta( '_orddd_shipping_delivery_dates' );
			if ( is_array( $delivery_dates ) && count( $delivery_dates ) > 0 ) {
				foreach ( $delivery_dates as $d_key => $d_value ) {
					foreach ( $d_value as $d_k => $d_v ) {
						if ( preg_match( '/e_deliverydate/', $d_k ) ) {
							$address_key = explode( '_', $d_k );
							if ( isset( $d_value[ 'h_deliverydate_' . $address_key[2] . '_' . $address_key[3] . '_' . $address_key [4] ] ) ) {
								$h_deliverydate = $d_value[ 'h_deliverydate_' . $address_key[2] . '_' . $address_key[3] . '_' . $address_key [4] ];
								$delivery_date  = gmdate( ORDDD_LOCKOUT_DATE_FORMAT, strtotime( $h_deliverydate ) );
								foreach ( $lockout_days_arr as $k => $v ) {
									$orders = $v->o;
									if ( $delivery_date == $v->d ) {
										if ( $v->o == $total_quantities ) {
											unset( $lockout_days_arr[ $k ] );
										} else {
											$orders = $v->o - $total_quantities;
											$v->o   = $orders;
											$v->d   = $v->d;
										}
									}
								}
							}
						}
					}
				}
			}
			$lockout_days_jarr = json_encode( $lockout_days_arr );
			orddd_common::orddd_update_option( 'orddd_lockout_days', $lockout_days_jarr, true );
		}

		if ( $shipping_based_timeslot_lockout == 'No' ) {
			if ( $delivery_date_timestamp != '' ) {
				$lockout_date = gmdate( 'j-n-Y', $delivery_date_timestamp );
			} else {
				$lockout_date = '';
			}

			$lockout_time = orddd_common::orddd_get_option( 'orddd_lockout_time_slot', '', $vendor_id );
			if ( $lockout_time == '' || $lockout_time == '{}' || $lockout_time == '[]' || $lockout_time == 'null' ) {
				$lockout_time_arr = array();
			} else {
				$lockout_time_arr = (array) json_decode( $lockout_time );
			}
			foreach ( $lockout_time_arr as $k => $v ) {
				$orders = $v->o;
				if ( $timeslt == $v->t && $lockout_date == $v->d ) {
					if ( $v->o == $total_quantities ) {
						unset( $lockout_time_arr[ $k ] );
					} else {
						$orders                 = $v->o - $total_quantities;
						$lockout_time_arr[ $k ] = array(
							'o' => $orders,
							't' => $v->t,
							'd' => $v->d,
						);
					}
				}
			}

			$delivery_dates = $order->get_meta( '_orddd_shipping_delivery_dates' );
			if ( is_array( $delivery_dates ) && count( $delivery_dates ) > 0 ) {
				foreach ( $delivery_dates as $d_key => $d_value ) {
					foreach ( $d_value as $d_k => $d_v ) {
						if ( preg_match( '/e_deliverydate/', $d_k ) ) {
							$address_key = explode( '_', $d_k );
							if ( isset( $d_value[ 'h_deliverydate_' . $address_key[2] . '_' . $address_key[3] . '_' . $address_key [4] ] ) ) {
								$h_deliverydate = $d_value[ 'h_deliverydate_' . $address_key[2] . '_' . $address_key[3] . '_' . $address_key [4] ];
								$lockout_date   = gmdate( 'j-n-Y', strtotime( $h_deliverydate ) );
								if ( isset( $d_value[ 'time_slot_' . $address_key[2] . '_' . $address_key[3] . '_' . $address_key [4] ] ) ) {
									$timeslot  = explode( ' - ', $d_value[ 'time_slot_' . $address_key[2] . '_' . $address_key[3] . '_' . $address_key [4] ] );
									$from_time = gmdate( 'H:i', strtotime( $timeslot[0] ) );
									if ( isset( $timeslot[1] ) && $timeslot[1] != '' ) {
										$to_time = gmdate( 'H:i', strtotime( $timeslot[1] ) );
										$timeslt = $from_time . ' - ' . $to_time;
									} else {
										$timeslt = $from_time;
									}
									foreach ( $lockout_time_arr as $k => $v ) {
										$orders = $v->o;
										if ( $timeslt == $v->t && $lockout_date == $v->d ) {
											if ( $v->o == $total_quantities ) {
												unset( $lockout_time_arr[ $k ] );
											} else {
												$orders = $v->o - $total_quantities;
												$v->o   = $orders;
												$v->t   = $v->t;
												$v->d   = $v->d;
											}
										}
									}
								}
							}
						}
					}
				}
			}
			$lockout_time_jarr = json_encode( $lockout_time_arr );
			orddd_common::orddd_update_option( 'orddd_lockout_time_slot', $lockout_time_jarr, true );
		}

		// Delete the Event from the Google Calendar
		if ( 'directly' === get_option( 'orddd_calendar_sync_integration_mode' ) || 'oauth_sync' === get_option( 'orddd_calendar_sync_integration_mode' ) ) {
			$gcal = new OrdddGcal();
			$gcal->delete_event( $order_id );
		}
	}

	/**
	 * Check if delivery is enabled for the product category
	 *
	 * @param int $product_id Product ID
	 * @return string 'on' if the delivery is enabled for the product category, else off.
	 * @since 2.8.6
	 */
	public static function orddd_admin_product_has_delivery( $product_id ) {
		$terms                    = get_the_terms( $product_id, 'product_cat' );
		$product_category_enabled = 'off';
		$is_enabled               = 'no';
		if ( $terms == '' ) {
			if ( has_filter( 'orddd_remove_delivery_date_if_product_category_no' ) ) {
				$is_enabled = apply_filters( 'orddd_remove_delivery_date_if_product_category_no', $is_enabled );
			}
			if ( $is_enabled == 'yes' ) {
				$product_category_enabled = 'on';
			} else {
				return 'on';
			}
		} else {
			foreach ( $terms as $term ) {
				$categoryid    = $term->term_id;
				$delivery_date = get_term_meta( $categoryid, 'orddd_delivery_date_for_product_category', true );
				if ( has_filter( 'orddd_remove_delivery_date_if_product_category_no' ) ) {
					$is_enabled = apply_filters( 'orddd_remove_delivery_date_if_product_category_no', $is_enabled );
				}
				if ( $is_enabled == 'yes' ) {
					if ( $delivery_date === 'on' ) {
						$product_category_enabled = 'on';
					} else {
						$product_category_enabled = 'off';
						break;
					}
				} else {
					if ( $delivery_date === 'on' ) {
						return 'on';
					} else {
						return 'off';
					}
				}
			}
		}
		return $product_category_enabled;
	}

	/**
	 * Return the custom delivery settings added in the admin
	 *
	 * @return string Custom delivery settings
	 * @since 3.0
	 */
	public static function orddd_get_shipping_based_settings( $vendor_id = 0 ) {

		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );

		$results         = self::orddd_get_shipping_settings( 1, $vendor_id );
		$hidden_vars_str = '';

		if ( is_array( $results ) && count( $results ) > 0 ) {
			$i                         = 0;
			$shipping_method_js_str    = array();
			$product_categories_js_str = array();
			$shipping_settings         = array();
			$shipping_methods_exists   = 'no';
			$j                         = 1;

			foreach ( $results as $key => $value ) {
				// 9.19.0 TEST: Created below variable so we can pass the custom delivery schedule id to JS variable & then save it back in `_orddd_delivery_schedule_id` post meta.
				$custom_setting_id_arr = explode( 'orddd_shipping_based_settings_', $value->option_name );
				$custom_setting_id     = $custom_setting_id_arr[1];

				$shipping_settings = self::orddd_get_option( $value->option_name, '', $vendor_id );

				if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'shipping_methods' ) {
					if ( isset( $shipping_settings['shipping_methods'] ) ) {
						$shipping_methods_exists = 'yes';
					}
				}
				$users = array();
				if ( isset( $shipping_settings['user_role'] ) && is_array( $shipping_settings['user_role'] ) && count( $shipping_settings['user_role'] ) > 0 ) {
					foreach ( $shipping_settings['user_role'] as $key => $value ) {
						$users[] = $value;
					}
				}
				$users_role_str = implode( ',', $users );

				$shipping_method_str             = self::orddd_get_shipping_method_str( $shipping_settings );
				$shipping_methods_for_categories = self::orddd_get_shipping_methods_for_categories( $shipping_settings );
				$shipping_methods_for_products   = self::orddd_get_shipping_methods_for_products( $shipping_settings );
				$enable_delivery_date            = self::orddd_get_shipping_enable_delivery_date( $shipping_settings );
				$delivery_checkout_option        = self::orddd_get_shipping_delivery_checkout_option( $shipping_settings );
				$date_field_mandatory            = self::orddd_get_shipping_date_field_mandatory( $shipping_settings );
				$time_slots_enable               = self::orddd_is_shipping_timeslot_enable( $shipping_settings );
				$timeslot_field_mandatory        = self::orddd_get_shipping_time_field_mandatory( $shipping_settings );
				$new_array                       = self::orddd_get_shipping_hidden_variables( $shipping_settings, $timeslot_field_mandatory, $vendor_id );
				$var_time                        = self::orddd_get_shipping_time_settings_variable( $shipping_settings, $new_array['orddd_delivery_date_holidays'], $new_array['orddd_lockout_days'], $vendor_id );
				$orddd_min_between_days          = self::orddd_get_shipping_orddd_min_between_days( $shipping_settings );
				$orddd_max_between_days          = self::orddd_get_shipping_orddd_max_between_days( $shipping_settings );
				$orddd_minimum_delivery_time     = self::orddd_get_shipping_minimum_delivery_time( $shipping_settings );
				$orddd_minimum_pickup_time       = self::orddd_get_shipping_minimum_pickup_time( $shipping_settings );
				$orddd_date_field_label          = self::orddd_get_shipping_date_field_label( $shipping_settings, $vendor_id );
				$orddd_time_field_label          = self::orddd_get_shipping_time_field_label( $shipping_settings, $vendor_id );
				$orddd_date_field_note           = self::orddd_get_shipping_date_field_note( $shipping_settings, $vendor_id );

				// 9.19.0 TEST: The 'unique_settings_key' variable is used in initialize-datepicker-functions.js file as 'current_unique_setting_key' JS variable & as 'orddd_unique_custom_settings' hidden variable, to determine if the settings of datepicker need to be updated or not. Earlier this was set to $j counter, like:
				// 'custom_settings_' . $j
				// Instead, we are now setting it to:
				// 'custom_settings_' . $custom_setting_id
				// This `orddd_unique_custom_settings` hidden variaable is then used to store data in '_orddd_delivery_schedule_id' post meta.
				if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'shipping_methods' === $shipping_settings['delivery_settings_based_on'][0] ) {
					$shipping_method_js_str[ $i ]['unique_settings_key'] = 'custom_settings_' . $custom_setting_id;
					$shipping_method_js_str[ $i ]['shipping_methods']    = $shipping_method_str;
				} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'products' === $shipping_settings['delivery_settings_based_on'][0] ) {
					$shipping_method_js_str[ $i ]['unique_settings_key']           = 'custom_settings_' . $custom_setting_id;
					$shipping_method_js_str[ $i ]['products']                      = $shipping_method_str;
					$shipping_method_js_str[ $i ]['shipping_methods_for_products'] = $shipping_methods_for_products;
				} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'product_categories' === $shipping_settings['delivery_settings_based_on'][0] ) {
					$shipping_method_js_str[ $i ]['unique_settings_key']             = 'custom_settings_' . $custom_setting_id;
					$shipping_method_js_str[ $i ]['product_categories']              = $shipping_method_str;
					$shipping_method_js_str[ $i ]['shipping_methods_for_categories'] = $shipping_methods_for_categories;
				} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'order_type' === $shipping_settings['delivery_settings_based_on'][0] ) {
					$shipping_method_js_str[ $i ]['unique_settings_key'] = 'custom_settings_' . $custom_setting_id;
					$shipping_method_js_str[ $i ]['order_type']    = $shipping_method_str;
				} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'user_role' === $shipping_settings['delivery_settings_based_on'][0] ) {
					$shipping_method_js_str[ $i ]['unique_settings_key'] = 'custom_settings_' . $custom_setting_id;
					$shipping_method_js_str[ $i ]['user_role']           = $users_role_str;
				}

				$shipping_method_js_str[ $i ]['enable_delivery_date']            = $enable_delivery_date;
				$shipping_method_js_str[ $i ]['priority']            			 = isset( $shipping_settings['orddd_priority_for_delivery_schedule'] ) ? $shipping_settings['orddd_priority_for_delivery_schedule'] : 0;
				$shipping_method_js_str[ $i ]['date_field_mandatory']            = $date_field_mandatory;
				$shipping_method_js_str[ $i ]['time_slots']                      = $time_slots_enable;
				$shipping_method_js_str[ $i ]['timeslot_field_mandatory']        = $timeslot_field_mandatory;
				$shipping_method_js_str[ $i ]['hidden_vars']                     = json_encode( $new_array );
				$shipping_method_js_str[ $i ]['time_settings']                   = $var_time;
				$shipping_method_js_str[ $i ]['orddd_delivery_checkout_options'] = $delivery_checkout_option;
				$shipping_method_js_str[ $i ]['orddd_min_between_days']          = $orddd_min_between_days;
				$shipping_method_js_str[ $i ]['orddd_max_between_days']          = $orddd_max_between_days;
				$shipping_method_js_str[ $i ]['orddd_minimum_delivery_time']     = $orddd_minimum_delivery_time;
				$shipping_method_js_str[ $i ]['orddd_minimum_pickup_time']       = $orddd_minimum_pickup_time;
				$shipping_method_js_str[ $i ]['orddd_date_field_label']          = __( $orddd_date_field_label, 'order-delivery-date' );
				$shipping_method_js_str[ $i ]['orddd_time_field_label']          = __( $orddd_time_field_label, 'order-delivery-date' );
				$shipping_method_js_str[ $i ]['orddd_field_note_text']           = __( $orddd_date_field_note, 'order-delivery-date' );

				if ( has_filter( 'orddd_custom_delivery_settings' ) ) {
					$shipping_method_js_str[ $i ] = apply_filters( 'orddd_custom_delivery_settings',  $shipping_method_js_str[ $i ], $shipping_settings, $custom_setting_id );
				}

				$i++;
				$j++;
			}

			$hidden_vars_str = json_encode( $shipping_method_js_str );
		}

		return $hidden_vars_str;
	}

	/**
	 * Return the selected settings by option value of the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Custom delivery settings
	 * @since 3.0
	 */
	public static function orddd_get_shipping_method_str( $shipping_settings ) {
		$shipping_method_str = '';
		if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'shipping_methods' ) {
			if ( isset( $shipping_settings['shipping_methods'] ) ) {
				$shipping_methods = $shipping_settings['shipping_methods'];
				foreach ( $shipping_methods as $key => $value ) {
					$shipping_method_str .= $value . ',';
				}
				$shipping_method_str = substr( $shipping_method_str, 0, -1 );
			}
		} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'products' ) {
			if ( isset( $shipping_settings['products'] ) ) {
				$products = $shipping_settings['products'];
				foreach ( $products as $key => $value ) {
					$shipping_method_str .= $value . ',';
				}
				$shipping_method_str = substr( $shipping_method_str, 0, -1 );
			}
		} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'product_categories' ) {
			if ( isset( $shipping_settings['product_categories'] ) ) {
				$product_categories = $shipping_settings['product_categories'];
				foreach ( $product_categories as $key => $value ) {
					$shipping_method_str .= $value . ',';
				}
				$shipping_method_str = substr( $shipping_method_str, 0, -1 );
			}
		} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'order_type' ) {
			if ( isset( $shipping_settings['order_type'] ) ) {
				$order_type = $shipping_settings['order_type'];
				foreach ( $order_type as $key => $value ) {
					$shipping_method_str .= $value . ',';
				}
				$shipping_method_str = substr( $shipping_method_str, 0, -1 );
			}
		}
		return $shipping_method_str;
	}

	/**
	 * Return the selected shipping method/shipping class/product category for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Selected shipping method/shipping class/product category for the Custom delivery settings
	 * @since 3.0
	 */

	public static function orddd_get_shipping_methods_for_categories( $shipping_settings ) {
		$shipping_methods_for_categories     = array();
		$shipping_methods_for_categories_str = '';
		if ( isset( $shipping_settings['shipping_methods_for_categories'] ) ) {
			$shipping_methods_for_categories = $shipping_settings['shipping_methods_for_categories'];
			foreach ( $shipping_methods_for_categories as $key => $value ) {
				$shipping_methods_for_categories_str .= $value . ',';
			}

			if ( '' !== $shipping_methods_for_categories_str ) {
				$shipping_methods_for_categories_str = substr( $shipping_methods_for_categories_str, 0, -1 );
			}
		}
		return $shipping_methods_for_categories_str;
	}

	/**
	 * Return the selected shipping method/shipping class/product category/products for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Selected shipping method/shipping class/product category/products for the Custom delivery settings
	 * @since 3.0
	 */
	public static function orddd_get_shipping_methods_for_products( $shipping_settings ) {
		$shipping_methods_for_products     = array();
		$shipping_methods_for_products_str = '';
		if ( isset( $shipping_settings['shipping_methods_for_products'] ) ) {
			$shipping_methods_for_products = $shipping_settings['shipping_methods_for_products'];
			foreach ( $shipping_methods_for_products as $key => $value ) {
				$shipping_methods_for_products_str .= $value . ',';
			}

			if ( '' !== $shipping_methods_for_products_str ) {
				$shipping_methods_for_products_str = substr( $shipping_methods_for_products_str, 0, -1 );
			}
		}
		return $shipping_methods_for_products_str;
	}

	/**
	 * Return the enable delivery date checkbox value for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Enable delivery date checkbox value
	 * @since 3.0
	 */
	public static function orddd_get_shipping_enable_delivery_date( $shipping_settings ) {
		$enable_delivery_date = '';
		if ( isset( $shipping_settings['enable_shipping_based_delivery'] ) ) {
			$enable_delivery_date = $shipping_settings['enable_shipping_based_delivery'];
		}
		if ( is_admin() ) {
			$enable_delivery_date = 'on';
		}
		return $enable_delivery_date;
	}

	/**
	 * Return the delivery checkout option radio button value for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Delivery checkout option value
	 * @since 3.0
	 */
	public static function orddd_get_shipping_delivery_checkout_option( $shipping_settings ) {
		$delivery_checkout_option = '';
		if ( isset( $shipping_settings['orddd_delivery_checkout_options'] ) ) {
			$delivery_checkout_option = $shipping_settings['orddd_delivery_checkout_options'];
		}
		return $delivery_checkout_option;
	}

	/**
	 * Return the min range for text block value for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Minimum between days range value
	 * @since 3.0
	 */
	public static function orddd_get_shipping_orddd_min_between_days( $shipping_settings ) {
		$orddd_min_between_days = '';
		if ( isset( $shipping_settings['orddd_min_between_days'] ) ) {
			$orddd_min_between_days = $shipping_settings['orddd_min_between_days'];
		}
		return $orddd_min_between_days;
	}

	/**
	 * Return the max range for text block value for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Maximum between days range value
	 * @since 3.0
	 */
	public static function orddd_get_shipping_orddd_max_between_days( $shipping_settings ) {
		$orddd_max_between_days = '';
		if ( isset( $shipping_settings['orddd_min_between_days'] ) ) {
			$orddd_max_between_days = $shipping_settings['orddd_max_between_days'];
		}
		return $orddd_max_between_days;
	}

	/**
	 * Return the minimum delivery time in hours for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Minimum Delivery Time for the settings
	 * @since 3.0
	 */

	public static function orddd_get_shipping_minimum_delivery_time( $shipping_settings ) {
		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time                  = current_time( 'timestamp', $gmt );
		$current_weekday               = 'orddd_weekday_' . gmdate( 'w', $current_time );
		$orddd_minimum_delivery_time   = isset( $shipping_settings['minimum_delivery_time'] ) ? $shipping_settings['minimum_delivery_time'] : 0;
		$weekday_based_schedule_enable = isset( $shipping_settings['weekday_based_schedule_enable'] ) ? $shipping_settings['weekday_based_schedule_enable'] : '';
		$weekday_based_schedule        = isset( $shipping_settings['weekday_based_schedule'] ) ? $shipping_settings['weekday_based_schedule'] : array();
		if ( 'on' === $weekday_based_schedule_enable && is_array( $weekday_based_schedule ) && count( $weekday_based_schedule ) > 0 ) {
			foreach ( $weekday_based_schedule as $key => $value ) {
				if ( $value['weekday'] == $current_weekday ) {
					$orddd_minimum_delivery_time = $value['minimum_time'];
					break;
				}
			}
		}
		
		return $orddd_minimum_delivery_time;
	}

	/**
	 * Return the minimum pickup time in hours for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Minimum Delivery Time for the settings
	 * @since 9.17.3
	 */

	public static function orddd_get_shipping_minimum_pickup_time( $shipping_settings ) {
		$minimum_pickup_time = '';
		if ( isset( $shipping_settings['minimum_pickup_time'] ) ) {
			$minimum_pickup_time = $shipping_settings['minimum_pickup_time'];
		}
		return $minimum_pickup_time;
	}

	/**
	 * Return the delivery date field label for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Delivery Date field label for the settings
	 * @since 3.0
	 */
	public static function orddd_get_shipping_date_field_label( $shipping_settings, $vendor_id = 0 ) {
		$shipping_date_field_label = '';
		if ( isset( $shipping_settings['orddd_shipping_based_delivery_date_field_label'] ) && '' != $shipping_settings['orddd_shipping_based_delivery_date_field_label'] ) {
			$shipping_date_field_label = $shipping_settings['orddd_shipping_based_delivery_date_field_label'];
		} else {
			$shipping_date_field_label = self::orddd_get_option( 'orddd_delivery_date_field_label', '', $vendor_id );
		}

		if ( '' === $shipping_date_field_label ) {
			$shipping_date_field_label = ORDDD_DELIVERY_DATE_FIELD_LABEL;
		}

		return $shipping_date_field_label;
	}

	/**
	 * Return the delivery date field note for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Delivery Date field note for the settings
	 * @since 3.0
	 */
	public static function orddd_get_shipping_date_field_note( $shipping_settings, $vendor_id = 0 ) {
		$shipping_date_field_note = '';
		if ( isset( $shipping_settings['orddd_shipping_based_delivery_date_field_note_text'] ) && '' != $shipping_settings['orddd_shipping_based_delivery_date_field_note_text'] ) {
			$shipping_date_field_note = $shipping_settings['orddd_shipping_based_delivery_date_field_note_text'];
		} else {
			$shipping_date_field_note = self::orddd_get_option( 'orddd_delivery_date_field_note', '', $vendor_id );
		}

		return $shipping_date_field_note;
	}

	/**
	 * Return the delivery time field label for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 * @return string Delivery time field label for the settings
	 * @since 3.0
	 */
	public static function orddd_get_shipping_time_field_label( $shipping_settings, $vendor_id = 0 ) {
		$shipping_time_field_label = '';
		if ( isset( $shipping_settings['orddd_shipping_based_delivery_timeslot_field_label'] ) && '' != $shipping_settings['orddd_shipping_based_delivery_timeslot_field_label'] ) {
			$shipping_time_field_label = $shipping_settings['orddd_shipping_based_delivery_timeslot_field_label'];
		} else {
			$shipping_time_field_label = self::orddd_get_option( 'orddd_delivery_timeslot_field_label', '', $vendor_id );
		}

		if ( '' === $shipping_time_field_label ) {
			$shipping_time_field_label = ORDDD_DELIVERY_TIMESLOT_FIELD_LABEL;
		}

		return $shipping_time_field_label;
	}

	/**
	 * Return the hiiden variables to be set for the custom delivery settings added in the admin
	 *
	 * @globals array $orddd_weekdays Weekdays array
	 * @globals array $orddd_shipping_days Shipping weekdays array
	 *
	 * @param array  $shipping_settings Custom settings added
	 * @param string $timeslot_field_mandatory Whether the timeslot field is mandatory or not
	 *
	 * @return string Hidden field to be set for the settings
	 * @since 3.0
	 */

	public static function orddd_get_shipping_hidden_variables( $shipping_settings, $timeslot_field_mandatory, $vendor_id = 0 ) {
		global $orddd_weekdays, $orddd_shipping_days;

		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );

		$new_array             = array();
		$weekdays_settings     = array();
		$delivery_type         = array();
		$delivery_dates_str    = '';
		$holidays_str          = '';
		$from_hours            = '';
		$from_mins             = '';
		$to_hours              = '';
		$to_mins               = '';
		$lockout_days_str      = '';
		$current_date          = gmdate( 'd', $current_time );
		$current_month         = gmdate( 'm', $current_time );
		$current_year          = gmdate( 'Y', $current_time );
		$current_weekday       = gmdate( 'w', $current_time );
		$minimum_delivery_time = '';

		$new_array['priority'] = isset( $shipping_settings['orddd_priority_for_delivery_schedule'] ) ? $shipping_settings['orddd_priority_for_delivery_schedule'] : 0;
		if ( isset( $shipping_settings['delivery_type'] ) ) {
			$delivery_type = $shipping_settings['delivery_type'];
		}

		// Recurring weekdays
		if ( isset( $delivery_type['weekdays'] ) && $delivery_type['weekdays'] == 'on' ) {
			if ( isset( $shipping_settings['weekdays'] ) ) {
				$weekdays_settings = $shipping_settings['weekdays'];
			}

			$alldays = array();
			foreach ( $orddd_weekdays as $n => $day_name ) {
				$weekday = isset( $weekdays_settings[ $n ] ) ? $weekdays_settings[ $n ] : array();

				// is_admin() return true when using ajax on frontend.
				$is_admin = is_admin() && ! defined( 'DOING_AJAX' );

				if ( ( isset( $weekday['enable'] ) && $weekday['enable'] == 'checked' ) || $is_admin ) {
					// all weekdays should be enabled for the admin
					$alldays[ $n ] = ( $is_admin ) ? 'checked' : $weekday['enable'];
				} else {
					$alldays[ $n ] = '';
				}
			}

			$alldayskeys = array_keys( $alldays );
			$checked     = 'No';
			foreach ( $alldayskeys as $key ) {
				if ( $alldays[ $key ] == 'checked' ) {
					$checked = 'Yes';
				}
			}

			if ( $checked == 'Yes' ) {
				$new_array['orddd_recurring_days'] = $delivery_type['weekdays'];
				foreach ( $alldayskeys as $key ) {
					$new_array[ $key ] = $alldays[ $key ];
				}
			} elseif ( $checked == 'No' && ! isset( $delivery_type['specific_dates'] ) ) {
				$new_array['orddd_recurring_days'] = '';
				foreach ( $alldayskeys as $key ) {
					$new_array[ $key ] = 'checked';
				}
			}

			if ( 'No' == $checked ) {
				$new_array['orddd_is_all_weekdays_disabled'] = 'yes';
			} else {
				$new_array['orddd_is_all_weekdays_disabled'] = 'no';
			}
		} else {
			$new_array['orddd_recurring_days']           = '';
			$new_array['orddd_is_all_weekdays_disabled'] = 'yes';
		}

		// Specific Delivery Dates
		if ( isset( $delivery_type['specific_dates'] ) && $delivery_type['specific_dates'] == 'on' ) {
			$new_array['orddd_specific_delivery_dates'] = $delivery_type['specific_dates'];
			$specific_days_settings                     = explode( ',', $shipping_settings['specific_dates'] );
			foreach ( $specific_days_settings as $sk => $sv ) {
				if ( $sv != '' ) {
					$sv                  = str_replace( '}', '', $sv );
					$sv                  = str_replace( '{', '', $sv );
					$specific_date_arr   = explode( ':', $sv );
					$delivery_dates_str .= '"' . $specific_date_arr[0] . '",';
				}
			}
			$delivery_dates_str = substr( $delivery_dates_str, 0, strlen( $delivery_dates_str ) - 1 );
			if ( false != $delivery_dates_str ) {
				$new_array['orddd_delivery_dates'] = $delivery_dates_str;
			} else {
				$new_array['orddd_delivery_dates'] = '';
			}
		} else {
			$new_array['orddd_delivery_dates']          = '';
			$new_array['orddd_specific_delivery_dates'] = '';
		}

		// Holidays
		if ( is_admin() ) { // Holidays should be enabled for the admin
			$holidays_str = '';
		} else {
			$holidays_str = self::orddd_get_custom_holidays( $shipping_settings, $vendor_id );
		}

		$new_array['orddd_delivery_date_holidays'] = $holidays_str;

		// Lockout Days
		if ( is_admin() ) { // Lockout days should not be applied for the admin.
			$new_array['orddd_lockout_days'] = '';
		} else {
			$lockout_days_str                = self::orddd_get_custom_lockout_days( $shipping_settings, $vendor_id );
			$new_array['orddd_lockout_days'] = $lockout_days_str;
		}

		// Time settings.
		$time_slider_enabled = '';
		if ( isset( $shipping_settings['time_settings'] ) ) {
			$time_settings = $shipping_settings['time_settings'];
			$from_hours = isset( $time_settings['from_hours'] ) && '' !== $time_settings['from_hours'] ? gmdate( 'H', strtotime( $time_settings['from_hours'] . ':' . $time_settings['from_mins'] ) ) : '';
			$from_mins = isset( $time_settings['from_hours'] ) && '' !== $time_settings['from_hours'] ? gmdate( 'i', strtotime( $time_settings['from_hours'] . ':' . $time_settings['from_mins'] ) ) : '';
            $to_hours   = isset( $time_settings['to_hours'] ) && '' !== $time_settings['to_hours'] ? gmdate( 'H', strtotime( $time_settings['to_hours'] . ':' . $time_settings['to_mins'] ) ) : '';
			
			if ( isset( $time_settings['enable'] ) && 'on' === $time_settings['enable'] && '' !== $from_hours && '' !== $to_hours ) {
				$from_hour_values = self::orddd_get_shipping_from_time( $time_settings, $shipping_settings, $holidays_str, $lockout_days_str, $vendor_id );
				if ( is_array( $from_hour_values ) && count( $from_hour_values ) ) {
					$orddd_min_hour = gmdate( 'H', strtotime( $from_hour_values['from_hours'] . ':' . $from_hour_values['from_minutes'] ) );
					$orddd_min_minute  = gmdate( 'i', strtotime( $from_hour_values['from_hours'] . ':' . $from_hour_values['from_minutes'] ) );
				}

				//$to_hours = $time_settings['to_hours'];
				$to_mins  = gmdate( 'i', strtotime( $time_settings['to_hours'] . ':' . $time_settings['to_mins'] ) );

				$new_array['orddd_min_hour']           = $orddd_min_hour;
				$new_array['orddd_min_minute']         = $orddd_min_minute;
				$new_array['orddd_min_hour_set']       = $from_hours;
				$new_array['orddd_min_mins_set']       = $from_mins;
				$new_array['orddd_max_hour_set']       = $to_hours;
				$new_array['orddd_max_mins_set']       = $to_mins;
				$new_array['orddd_enable_time_slider'] = 'on';
				$new_array['orddd_show_datepicker']    = 'datetimepicker';
				$time_slider_enabled                   = 'on';
			} else {
				$new_array['orddd_enable_time_slider'] = '';
				$new_array['orddd_show_datepicker']    = 'datepicker';
			}
		}

		$current_weekday = 'orddd_weekday_' . gmdate( 'w', $current_time );

		$same_day_cut_off = array();
		$next_day_cut_off = array();
		$same_day         = isset( $shipping_settings['same_day'] ) ? $shipping_settings['same_day'] : array();

		$weekday_based_schedule_enable = isset( $shipping_settings['weekday_based_schedule_enable'] ) ? $shipping_settings['weekday_based_schedule_enable'] : '';
		$weekday_based_schedule        = isset( $shipping_settings['weekday_based_schedule'] ) ? $shipping_settings['weekday_based_schedule'] : array();

		if ( 'on' === $weekday_based_schedule_enable && is_array( $weekday_based_schedule ) && count( $weekday_based_schedule ) > 0 ) {
			foreach ( $weekday_based_schedule as $key => $value ) {
				if ( $value['weekday'] === $current_weekday ) {
					$same_day_arr = '' != $value['same_day'] ? explode( ':', $value['same_day'] ) : array();

					$same_day_after_hours = isset( $same_day_arr[0] ) ? $same_day_arr[0] : 0;
					$same_day_after_mins  = isset( $same_day_arr[1] ) ? $same_day_arr[1] : 0;
					$same_day             = array(
						'after_hours'   => $same_day_after_hours,
						'after_minutes' => $same_day_after_mins,
					);
					break;
				}
			}
		}

		// Nexy Day Delivery.
		$next_day                                 = isset( $shipping_settings['next_day'] ) ? $shipping_settings['next_day'] : array();
		$new_array['orddd_before_cutoff_weekday'] = '';
		$new_array['orddd_after_cutoff_weekday']  = '';
		$new_array['is_nextday_cutoff_reached']	  = '';
		$new_array['is_sameday_cutoff_reached']	  = '';

		$weekday_based_schedule_enable = isset( $shipping_settings['weekday_based_schedule_enable'] ) ? $shipping_settings['weekday_based_schedule_enable'] : '';
		$weekday_based_schedule        = isset( $shipping_settings['weekday_based_schedule'] ) ? $shipping_settings['weekday_based_schedule'] : array();

		if ( 'on' === $weekday_based_schedule_enable && is_array( $weekday_based_schedule ) && count( $weekday_based_schedule ) > 0 ) {
			$next_weekday = 'orddd_weekday_' . gmdate( 'w', $current_time );
			foreach ( $weekday_based_schedule as $key => $value ) {
				if ( $value['weekday'] === $next_weekday ) {
					$next_day_arr = '' != $value['next_day'] ? explode( ':', $value['next_day'] ) : array();

					$next_day_after_hours = isset( $next_day_arr[0] ) ? $next_day_arr[0] : 0;
					$next_day_after_mins  = isset( $next_day_arr[1] ) ? $next_day_arr[1] : 0;
					$next_day             = array(
						'after_hours'   => $next_day_after_hours,
						'after_minutes' => $next_day_after_mins,
					);

					$new_array['orddd_before_cutoff_weekday'] = $value['weekday_before_cutoff'];
					$new_array['orddd_after_cutoff_weekday']  = $value['weekday_after_cutoff'];
					break;
				}
			}
		}

		// Same Day Delivery.
		if ( is_array( $same_day ) && count( $same_day ) > 0 ) {
			if ( isset( $same_day['after_hours'] ) && ( $same_day['after_hours'] == 0 || '' == $same_day['after_hours'] ) && isset( $same_day['after_minutes'] ) && ( $same_day['after_minutes'] == '0' || '' == $same_day['after_minutes'] ) ) {
				$new_array['orddd_custom_based_same_day_delivery'] = '';
			} else {
				$cut_off_hour      = $same_day['after_hours'];
				$cut_off_minute    = $same_day['after_minutes'];
				$cut_off_timestamp = gmmktime( $cut_off_hour, $cut_off_minute, 0, $current_month, $current_date, $current_year );
				if ( $cut_off_timestamp > $current_time ) {
					$new_array['is_sameday_cutoff_reached'] = 'no';
				} else {
					$new_array['is_sameday_cutoff_reached'] = 'yes';
				}
				$new_array['orddd_custom_based_same_day_delivery'] = 'on';
			}
		} else {
			$new_array['orddd_custom_based_same_day_delivery'] = '';
		}

		// Next Day Delivery.
		if ( is_array( $next_day ) && count( $next_day ) > 0 ) {
			if ( isset( $next_day['after_hours'] ) &&
				( $next_day['after_hours'] == 0 || '' == $next_day['after_hours'] ) &&
				isset( $next_day['after_minutes'] ) &&
				( '0' == $next_day['after_minutes'] || '00' === $next_day['after_minutes'] || '' === $next_day['after_minutes'] ) ) {

				$new_array['orddd_custom_based_next_day_delivery'] = '';
			} else {
				$cut_off_hour      = $next_day['after_hours'];
				$cut_off_minute    = $next_day['after_minutes'];
				$cut_off_timestamp = gmmktime( $cut_off_hour, $cut_off_minute, 0, $current_month, $current_date, $current_year );
				if ( $current_time >= $cut_off_timestamp ) {
					$new_array['is_nextday_cutoff_reached'] = 'yes';
				}
				$new_array['orddd_custom_based_next_day_delivery'] = 'on';
			}
		} else {
			$new_array['orddd_custom_based_next_day_delivery'] = '';
		}

		$disabled_days = self::orddd_get_shipping_disabled_days_str( $shipping_settings, $same_day, $next_day, $vendor_id );

		$disabled_days_str     = '';
		$disabled_weekdays_str = '';
		if ( '' !== $disabled_days ) {
			$disabled_days_arr     = explode( '&', $disabled_days );
			$disabled_days_str     = substr( $disabled_days_arr[0], 0, strlen( $disabled_days_arr[0] ) - 1 );
			$disabled_weekdays_str = substr( $disabled_days_arr[1], 0, strlen( $disabled_days_arr[1] ) - 1 );
		}

		// The disabled_days_str will be set to blank if we are editing the dates from edit order page in admin.
		// As we don't disable any days in the admin.
		global $typenow;
		if ( is_admin() && $typenow == 'shop_order' ) { // next day and same day cut off settings should not be applied for the admin
			$disabled_days_str     = '';
			$disabled_weekdays_str = '';
		}

		$new_array['orddd_disabled_days_str']     = $disabled_days_str;
		$new_array['orddd_disabled_weekdays_str'] = $disabled_weekdays_str;

		// Minimum Delivery Time.
		$minimum_time          = 0;
		$minimum_delivery_time = isset( $shipping_settings['minimum_delivery_time'] ) ? $shipping_settings['minimum_delivery_time'] : 0;

		if ( $minimum_time != '' && $minimum_time != 0 ) {
			$minimum_delivery_time = $minimum_time;
		} else {
			$weekday_based_schedule_enable = isset( $shipping_settings['weekday_based_schedule_enable'] ) ? $shipping_settings['weekday_based_schedule_enable'] : '';
			$weekday_based_schedule        = isset( $shipping_settings['weekday_based_schedule'] ) ? $shipping_settings['weekday_based_schedule'] : array();
			if ( 'on' === $weekday_based_schedule_enable && is_array( $weekday_based_schedule ) && count( $weekday_based_schedule ) > 0 ) {
				foreach ( $weekday_based_schedule as $key => $value ) {
					if ( $value['weekday'] === $current_weekday ) {
						$minimum_delivery_time = $value['minimum_time'];
						break;
					}
				}
			}
		}

		if ( '' == $minimum_delivery_time ) {
			$minimum_delivery_time = 0;
		}

		$min_date        = '';
		$minDate         = 0;
		$current_date    = gmdate( 'j-n-Y', $current_time );
		$current_hour    = gmdate( 'H', $current_time );
		$current_minute  = gmdate( 'i', $current_time );
		$current_weekday = gmdate( 'w', $current_time );
		$date_to_check   = gmdate( 'n-j-Y', $current_time );

		$new_array['orddd_next_day'] = gmdate( 'j-n-Y', strtotime( $current_date . ' +1 day' ) );

		$all_specific_dates = self::orddd_get_all_shipping_specific_dates( $shipping_settings );

		$weekdays_to_check = array();
		if ( isset( $shipping_settings['delivery_type']['weekdays'] ) && $shipping_settings['delivery_type']['weekdays'] == 'on' ) {
			if ( isset( $shipping_settings['weekdays'] ) ) {
				foreach ( $shipping_settings['weekdays'] as $sk => $sv ) {
					if ( isset( $sv['enable'] ) ) {
						$weekdays_to_check[] = $sk;
					}
				}
			}
		}

		if ( 'on' === get_option( 'orddd_enable_shipping_days' ) ) {
			if ( 'checked' != self::orddd_get_option( 'orddd_shipping_day_' . $current_weekday, '', $vendor_id ) ) {
				$current_time = strtotime( $current_date );
			}
		} elseif ( ! in_array( 'orddd_weekday_' . $current_weekday, $weekdays_to_check ) &&
				 ( ! isset( $shipping_settings['delivery_type']['specific_dates'] ) ||
				 ( isset( $shipping_settings['delivery_type']['specific_dates'] ) &&
				   'on' == $shipping_settings['delivery_type']['specific_dates'] &&
				   is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 &&
				   ! in_array( $date_to_check, $all_specific_dates ) ) ) ) {

			$current_time = strtotime( $current_date );
		}

		$delivery_time_seconds = $minimum_delivery_time * 60 * 60;

		$sameday_nextday_settings = array(
			'same_day' => $same_day,
			'next_day' => $next_day,
		);

		$min_date_array = self::get_min_date(
			$delivery_time_seconds,
			array(
				'enabled'    => $time_slider_enabled,
				'from_hours' => $from_hours,
				'from_mins'  => $from_mins,
				'to_hours'   => $to_hours,
				'to_mins'    => $to_mins,
			),
			$holidays_str,
			$lockout_days_str,
			$shipping_settings,
			$sameday_nextday_settings,
			$vendor_id
		);

		// check mindate is today.. if yes, then check if all time slots are past, if yes, then set mindate to tomorrow.
		if ( isset( $shipping_settings['time_slots'] ) && isset( $shipping_settings['enable_time_slot'] ) && $shipping_settings['time_slots'] != '' && 'on' === $shipping_settings['enable_time_slot'] ) {
			$last_slot_hrs           = $last_slot_min = 0;
			$current_date            = gmdate( 'j-n-Y', $current_time );
			$time_slots              = explode( '},', $shipping_settings['time_slots'] );
			$blank_lockout_time_slot = 'no';
			$lockout_arr             = array();
			foreach ( $time_slots as $tk => $tv ) {
				if ( $tv != '' ) {
					$timeslot_values = self::get_timeslot_values( $tv );
					$fh              = $fm = $th = $tm = '';
					if ( isset( $timeslot_values['time_slot'] ) ) {
						$time_slot_exploded = explode( ' - ', $timeslot_values['time_slot'] );
						$from_time_explode  = explode( ':', $time_slot_exploded[0] );
						$fh                 = gmdate( 'G', strtotime( $current_date . $from_time_explode[0] . ':00' ) );
						$fm_parent          = isset( $from_time_explode[1] ) ? $from_time_explode[1] : '';
						$fm                 = gmdate( 'i', strtotime( $current_date . '00:' . $fm_parent ) );

						if ( isset( $time_slot_exploded[1] ) ) {
							$to_time_explode = explode( ':', $time_slot_exploded[1] );
							$th              = gmdate( 'G', strtotime( $current_date . $to_time_explode[0] . ':00' ) );
							if ( isset( $to_time_explode[1] ) && ! empty( $to_time_explode[1] ) ) {
								$exploded_time_in_minute = $to_time_explode[1];
							} else {
								$exploded_time_in_minute = '00';
							}
							$tm = gmdate( 'i', strtotime( $current_date . '00:' . $exploded_time_in_minute ) );
						}
					}

					$min_time_on_last_slot = apply_filters( 'orddd_min_delivery_on_last_slot', false );
					$hours                 = $fh;
					$mins                  = $fm;

					if ( $min_time_on_last_slot ) {
						$hours = $th;
						$mins  = $tm;
					}

					if ( is_array( $timeslot_values['selected_days'] ) ) {
						if ( $timeslot_values['delivery_days_selected'] == 'weekdays' ) {
							$min_weekday = gmdate( 'w', strtotime( $min_date_array['min_date'] ) );
							$min_weekday = 'orddd_weekday_' . $min_weekday . '_custom_setting';
							if ( in_array( $min_weekday, $timeslot_values['selected_days'] ) ) {
								$current_slot_hrs  = $hours;
								$current_slot_mins = $mins;
								if ( $current_slot_hrs > $last_slot_hrs || ( $current_slot_hrs == $last_slot_hrs && $current_slot_mins > $last_slot_min ) ) {
									$last_slot_hrs = $current_slot_hrs;
									$last_slot_min = $current_slot_mins;
								}
							} elseif ( in_array( 'all', $timeslot_values['selected_days'] ) ) {
								$current_slot_hrs  = $hours;
								$current_slot_mins = $mins;

								if ( $current_slot_hrs > $last_slot_hrs || ( $current_slot_hrs == $last_slot_hrs && $current_slot_mins > $last_slot_min ) ) {
									$last_slot_hrs = $current_slot_hrs;
									$last_slot_min = $current_slot_mins;
								}
							}
						} elseif ( $timeslot_values['delivery_days_selected'] == 'specific_dates' ) {
							$check_min_date = gmdate( 'n-j-Y', strtotime( $min_date_array['min_date'] ) );
							if ( in_array( $check_min_date, $timeslot_values['selected_days'] ) ) {
								$current_slot_hrs  = $hours;
								$current_slot_mins = $mins;

								if ( $current_slot_hrs > $last_slot_hrs || ( $current_slot_hrs == $last_slot_hrs && $current_slot_mins > $last_slot_min ) ) {
									$last_slot_hrs = $current_slot_hrs;
									$last_slot_min = $current_slot_mins;
								}
							}
						}
					}
				}
			}
			if ( $last_slot_hrs != 0 ) {
				$last_slot       = $last_slot_hrs . ':' . trim( $last_slot_min );
				$min_hour_in_sec = orddd_calculate_cutoff_time_slots( $delivery_time_seconds, $current_time, $min_date_array ); // If some of the weekdays are disabled then then the difference between current time & min date will be greater than the actual MDT.
				$booking_date2   = $min_date_array['min_date'] . ' ' . $last_slot;

				$booking_date2 = gmdate( 'Y-m-d G:i', strtotime( $booking_date2 ) );
				$date2         = new DateTime( $booking_date2 );

				$booking_date1 = gmdate( 'Y-m-d G:i', $current_time );
				$date1         = new DateTime( $booking_date1 );

				// If the difference between min_date's last slot and current time is greater than the mdt then block the date.
				if ( version_compare( phpversion(), '5.3.0', '>' ) ) {
					$calculated_difference = $date2->diff( $date1 );
				} else {
					$calculated_difference = self::dateTimeDiff( $date2, $date1 );
				}

				if ( $calculated_difference->days > 0 ) {
					$days_in_hour             = $calculated_difference->h + ( $calculated_difference->days * 24 );
					$calculated_difference->h = $days_in_hour;
				}

				if ( $calculated_difference->i > 0 ) {
					$min_in_hour                      = $calculated_difference->h + ( $calculated_difference->i / 60 );
					$calculated_minimum_delivery_time = $min_in_hour * 60 * 60;
				} else {
					$calculated_minimum_delivery_time = $calculated_difference->h * 60 * 60;
				}

				if ( $calculated_difference->invert == 0 || $calculated_minimum_delivery_time < $min_hour_in_sec ) {
					$disabled_day = apply_filters( 'orddd_disable_day_when_all_time_slots_expired', true );
					if ( $disabled_day ){
						$min_date_array['min_date'] = gmdate( 'j-n-Y', strtotime( $min_date_array['min_date'] . '+1 day' ) );
					}
				}
			}
		}
		$new_array['orddd_current_day'] = $current_date;
		if ( isset( $min_date_array['min_date'] ) && ! empty( $min_date_array['min_date'] ) ) {
			$new_array['orddd_minimumOrderDays'] = $min_date_array['min_date'];
		}
		if ( isset( $min_date_array['current_date_to_check'] ) && ! empty( $min_date_array['current_date_to_check'] ) ) {
			$new_array['orddd_current_date_set'] = $min_date_array['current_date_to_check'];
		}

		// Number of Dates to choose
		if ( isset( $shipping_settings['number_of_dates'] ) ) {
			$new_array['orddd_number_of_dates'] = $shipping_settings['number_of_dates'];
		}

		if ( class_exists( 'WC_Subscriptions' ) && get_option( 'orddd_enable_woo_subscriptions_compatibility' ) == 'on' ) {
			if ( class_exists( 'ws_addon_for_orddd' ) ) {
				$subscrition_var = ws_addon_for_orddd::orddd_check_the_subscription_period();
			}
			if ( isset( $subscrition_var['orddd_start_date_for_subscription'] ) ) {
				$current_hour  = gmdate( 'H:i', $current_time );
				$min_timestamp = strtotime( $subscrition_var['orddd_start_date_for_subscription'] . ' ' . $current_hour );
				if ( isset( $shipping_settings['minimum_delivery_time'] ) ) {
					$minimum_delivery_time_sub = $shipping_settings['minimum_delivery_time'];
					if ( '' == $minimum_delivery_time_sub ) {
						$minimum_delivery_time_sub = 0;
					}
					$delivery_time_seconds = $minimum_delivery_time_sub * 60 * 60;
					if ( $shipping_settings['minimum_delivery_time'] != 0 && $shipping_settings['minimum_delivery_time'] != '' ) {
						$min_date_array = self::get_min_date(
							$delivery_time_seconds,
							array(
								'enabled'    => $time_slider_enabled,
								'from_hours' => $from_hours,
								'to_hours'   => $time_settings['to_hours'],
							),
							$holidays_str,
							$lockout_days_str,
							$shipping_settings,
							$sameday_nextday_settings,
							$vendor_id
						);
					}
				}
				if ( isset( $min_date_array['min_date'] ) && ! empty( $min_date_array['min_date'] ) ) {
					$new_array['orddd_start_date_for_subscription'] = $min_date_array['min_date'];
				}
			}
		}

		// Partially Booked dates.
		$partially_booked_dates_str                = str_replace( "'", "", orddd_widget::get_partially_booked_dates( '', $shipping_settings, $vendor_id ) );
		$available_deliveries_arr                  = explode( '&', $partially_booked_dates_str );
		$new_array['orddd_partially_booked_dates'] = htmlspecialchars( $available_deliveries_arr[0], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );

		if ( isset( $available_deliveries_arr[1] ) ) {
			$new_array['orddd_available_deliveries'] = htmlspecialchars( $available_deliveries_arr[1], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
		}

		$orddd_minimum_delivery_time              = self::orddd_get_shipping_minimum_delivery_time( $shipping_settings );
		$new_array['orddd_minimum_delivery_time'] = $orddd_minimum_delivery_time;

		$orddd_minimum_pickup_time                    = self::orddd_get_shipping_minimum_pickup_time( $shipping_settings );
		$new_array['orddd_minimum_pickup_time'] = $orddd_minimum_pickup_time;

		return $new_array;
	}

	/**
	 * Get Custom Delivery Settings holidays
	 */

	public static function orddd_get_custom_holidays( $shipping_settings, $vendor_id = 0 ) {
		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );
		$current_date = gmdate( 'j-n-Y', $current_time );
		$holidays_str = '';

		if ( isset( $shipping_settings['enable_global_holidays'] ) && 'checked' === $shipping_settings['enable_global_holidays'] ) {
			$holidays_arr = array();
			$holidays     = self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id );

			if ( '' !== $holidays && '{}' !== $holidays && '[]' !== $holidays && 'null' !== $holidays ) {
				$holidays_arr = json_decode( self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id ) );
			}

			foreach ( $holidays_arr as $k => $v ) {

				// Replace single quote in the holiday name with the html entities
				// @todo: Need to fix the double quotes issue in the holiday name.
				// An error comes in console when the holiday name contains double quotes in it.
				$name = str_replace( "'", '&apos;', $v->n );
				$name = str_replace( '"', '&quot;', $name );
				$name = str_replace( '/', ' ', $name );
				$name = str_replace( '-', ' ', $name );
				$name = str_replace( ':', '&#58;', $name );
				$name = trim( preg_replace( '/\t+/', '', $name ) );

				$date_arr              = explode( '-', $v->d );
				$holiday_date          = $date_arr[1] . '-' . $date_arr[0] . '-' . $date_arr[2];
				$holiday_timestamp     = strtotime( $holiday_date );
				$current_day_timestamp = strtotime( $current_date );

				if ( isset( $v->r_type ) && $v->r_type == 'on' ) {
					$holiday_date_arr = explode( '-', $v->d );
					$recurring_date   = $holiday_date_arr[0] . '-' . $holiday_date_arr[1];
					$holidays_str    .= '"' . $name . ':' . $recurring_date . '",';
				} else {
					if ( $holiday_timestamp < $current_day_timestamp ) {
						continue;
					}
					$holidays_str .= '"' . $name . ':' . $v->d . '",';
				}
			}
			$holidays_str = apply_filters( 'ordd_add_to_holidays_str', $holidays_str );
		}

		if ( isset( $shipping_settings['holidays'] ) && '' !== $shipping_settings['holidays'] ) {
			$holidays_arr     = array();
			$holiday_settings = explode( ',', $shipping_settings['holidays'] );

			foreach ( $holiday_settings as $hk => $hv ) {
				$hv          = str_replace( '}', '', $hv );
				$hv          = str_replace( '{', '', $hv );
				$holiday_arr = explode( ':', $hv );
				if ( isset( $holiday_arr[1] ) && $holiday_arr[1] != '' ) {

					// Replace single quote in the holiday name with the html entities
					// @todo: Need to fix the double quotes issue in the holiday name.
					// An error comes in console when the holiday name contains double quotes in it.
					// @todo: We shouldn't allow characters like / - and any other special chars in holiday name
					$holiday_name = str_replace( "'", '&apos;', $holiday_arr[0] );
					$holiday_name = str_replace( '"', '&quot;', $holiday_name );
					$holiday_name = str_replace( '/', ' ', $holiday_name );
					$holiday_name = str_replace( '-', ' ', $holiday_name );

					$date_arr = explode( '-', $holiday_arr[1] );
					if ( ! isset( $date_arr[0] ) || ! isset( $date_arr[1] ) || ! isset( $date_arr[2] ) ) {
						continue;
					}
					$holiday_date          = $date_arr[1] . '-' . $date_arr[0] . '-' . $date_arr[2];
					$holiday_timestamp     = strtotime( $holiday_date );
					$current_day_timestamp = strtotime( $current_date );

					$holiday_date_arr = explode( '-', $holiday_arr[1] );
					$recurring_date   = $holiday_date_arr[0] . '-' . $holiday_date_arr[1];

					if ( isset( $holiday_arr[2] ) && $holiday_arr[2] == 'on' ) {
						$holiday_date_arr = explode( '-', $holiday_arr[1] );
						$recurring_date   = $holiday_date_arr[0] . '-' . $holiday_date_arr[1];
						$holidays_str    .= '"' . trim( $holiday_name ) . ':' . $recurring_date . '",';
					} else {
						if ( $holiday_timestamp < $current_day_timestamp ) {
							continue;
						}
						$recurring_date = '';
						$holidays_str  .= '"' . trim( $holiday_name ) . ':' . $holiday_arr[1] . '",';
					}
				}
			}

			$holidays_str = apply_filters( 'ordd_add_to_custom_holiday_str', $holidays_str, $holiday_settings );
		}

		if ( strlen( $holidays_str ) > 0 ) {
			$holidays_str = substr( $holidays_str, 0, strlen( $holidays_str ) - 1 );
		}

		return $holidays_str;
	}

	/**
	 * Get Custom Delivery Settings Lockout Days
	 */

	public static function orddd_get_custom_lockout_days( $shipping_settings, $vendor_id = 0 ) {
		$lockout_days_str = '';

		$booked_days = ORDDD_Lockout_Days::orddd_get_custom_booked_dates( $shipping_settings, $vendor_id );

		foreach ( $booked_days as $booked_day ) {
			$lockout_days_str .= '"' . $booked_day . '",';
		}

		if ( isset( $shipping_settings['time_slots'] ) && $shipping_settings['time_slots'] != '' ) {
			$booked_timeslot_days = ORDDD_Lockout_Days::orddd_get_custom_booked_timeslots( $shipping_settings );
			$blocked_days         = ORDDD_Lockout_Days::orddd_get_custom_blocked_timeslots( $shipping_settings );

			foreach ( $booked_timeslot_days as $booked_day ) {
				$lockout_days_str .= '"' . $booked_day . '",';
			}

			foreach ( $blocked_days as $booked_day ) {
				$lockout_days_str .= '"' . $booked_day . '",';
			}
		}

		if ( '' !== $lockout_days_str ) {
			$lockout_days_str = substr( $lockout_days_str, 0, strlen( $lockout_days_str ) - 1 );
		}

		return $lockout_days_str;
	}

	/**
	 *
	 *
	 */
	public static function orddd_get_all_shipping_specific_dates( $shipping_settings ) {
		$all_specific_dates = array();
		if ( isset( $shipping_settings['delivery_type']['specific_dates'] ) && 'on' === $shipping_settings['delivery_type']['specific_dates'] ) {
			if ( isset( $shipping_settings['specific_dates'] ) && $shipping_settings['specific_dates'] != '' ) {
				$specific_days_settings = explode( ',', $shipping_settings['specific_dates'] );
				foreach ( $specific_days_settings as $sk => $sv ) {
					if ( $sv != '' ) {
						$sv                   = str_replace( '}', '', $sv );
						$sv                   = str_replace( '{', '', $sv );
						$specific_date_arr    = explode( ':', $sv );
						$all_specific_dates[] = $specific_date_arr[0];
					}
				}
			}
		}

		return $all_specific_dates;
	}

	/**
	 * Return the options to be set for the time sliders for custom delivery settings added in the admin
	 *
	 * @param array  $shipping_settings Custom settings added
	 * @param string $holidays_str Holidays added for the settings
	 * @param string $lockout_str Booked days for the settings
	 *
	 * @return string Options to be set for the time sliders settings
	 * @since 3.0
	 */

	public static function orddd_get_shipping_time_settings_variable( $shipping_settings, $holidays_str, $lockout_str, $vendor_id = 0 ) {
		$var_time   = '';
		$from_hours = '';
		$from_mins  = '';
		$time_settings_enabled = isset( $shipping_settings['time_settings']['enable'] ) ? $shipping_settings['time_settings']['enable'] : '';
		if ( isset( $shipping_settings['time_settings'] ) && 'on' === $time_settings_enabled ) {
			$time_settings = $shipping_settings['time_settings'];
			$from_hours = isset( $time_settings['from_hours'] ) && '' !== $time_settings['from_hours'] ? gmdate( 'H', strtotime( $time_settings['from_hours'] . ':' . $time_settings['from_mins'] ) ) : '';
            $to_hours   = isset( $time_settings['to_hours'] ) && '' !== $time_settings['to_hours'] ? gmdate( 'H', strtotime( $time_settings['to_hours'] . ':' . $time_settings['to_mins'] ) ) : '';
			if ( '' !== $from_hours && '' !== $to_hours ) {

				if ( '1' === get_option( 'orddd_delivery_time_format' ) ) {
					$time_show_format = ',"timeFormat": "hh:mm TT"';
				} else {
					$time_show_format = ',"timeFormat": "HH:mm"';
				}

				$from_hour_values = self::orddd_get_shipping_from_time( $time_settings, $shipping_settings, $holidays_str, $lockout_str, $vendor_id );
				if ( is_array( $from_hour_values ) && count( $from_hour_values ) ) {
					$from_hours = isset( $from_hour_values['from_hours'] ) && '' !== $from_hour_values['from_hours'] ? gmdate( 'H', strtotime( $from_hour_values['from_hours'] . ':' . $from_hour_values['from_minutes'] ) ) : '';
					$from_mins  = isset( $from_hour_values['from_minutes'] ) && '' !== $from_hour_values['from_minutes'] ? gmdate( 'i', strtotime( $from_hour_values['from_hours'] . ':' . $from_hour_values['from_minutes'] ) ) : '';
				}

				$to_mins  = isset( $time_settings['to_mins'] ) && '' !== $time_settings['to_mins'] ? gmdate( 'i', strtotime( $time_settings['to_hours'] . ':' . $time_settings['to_mins'] ) ) : '';
				$step_min = apply_filters( 'orddd_time_slider_minute_step', 5 );
				
				if ( '00' === $to_hours && '00' === $to_mins ) {
					$to_hours = '23';
					$to_mins  = '59';
				}

				$var_time = "{\"hourMin\":\"$from_hours\",\"minuteMin\":\"$from_mins\",\"hourMax\":\"$to_hours\",\"minuteMax\":\"$to_mins\", \"stepMinute\":\"$step_min\" $time_show_format}";
			}
		}
		return $var_time;
	}

	/**
	 * Return the from time set for the time sliders for custom delivery settings added in the admin
	 *
	 * @param array  $time_settings Settings for the time sliders
	 * @param array  $shipping_settings Custom settings added
	 * @param string $holidays_str Holidays added for the settings
	 * @param string $lockout_str Booked days for the settings
	 *
	 * @return string From time set for the time sliders
	 * @since 3.0
	 */

	public static function orddd_get_shipping_from_time( $time_settings, $shipping_settings, $holidays_str, $lockout_str, $vendor_id = 0 ) {
		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );

		$from_hour_values = array();
		$current_date     = gmdate( 'j-n-Y', $current_time );
		$current_hour     = gmdate( 'H', $current_time );
		$current_minute   = gmdate( 'i', $current_time );

		$minimum_time = orddd_get_higher_minimum_delivery_time( $vendor_id );

		if ( '' !== $minimum_time && 0 !== $minimum_time ) {
			$minimum_delivery_time = $minimum_time;
		} else {
			if ( isset( $shipping_settings['minimum_delivery_time'] ) ) {
				$minimum_delivery_time = $shipping_settings['minimum_delivery_time'];
				if ( '' == $minimum_delivery_time ) {
					$minimum_delivery_time = 0;
				}
			}
		}

		$delivery_time_seconds = floatval( $minimum_delivery_time ) * 60 * 60;

		$sameday_nextday_settings = array(
			'same_day' => $shipping_settings['same_day'],
			'next_day' => $shipping_settings['next_day'],
		);

		$next_day_enabled = false;
		if ( is_array( $shipping_settings ) &&
			count( $shipping_settings ) > 0 &&
			( ! isset( $sameday_nextday_settings['same_day'] ) ||
				( isset( $sameday_nextday_settings['same_day'] ) &&
					isset( $sameday_nextday_settings['same_day']['after_hours'] ) &&
					$sameday_nextday_settings['same_day']['after_hours'] == 0 &&
					isset( $sameday_nextday_settings['same_day']['after_minutes'] ) &&
					$sameday_nextday_settings['same_day']['after_minutes'] == 00
				) ||
				( isset( $sameday_nextday_settings['same_day'] ) &&
					is_array( $sameday_nextday_settings['same_day'] ) &&
					count( $sameday_nextday_settings['same_day'] ) == 0
				)
			) &&
			( ( isset( $sameday_nextday_settings['next_day'] ) &&
					isset( $sameday_nextday_settings['next_day']['after_hours'] ) &&
					$sameday_nextday_settings['next_day']['after_hours'] > 0 &&
					isset( $sameday_nextday_settings['next_day']['after_minutes'] ) &&
					$sameday_nextday_settings['next_day']['after_minutes'] != 00
				)
			)
		) {
			$next_day_enabled = true;
		}

		$min_date_array = self::get_min_date(
			$delivery_time_seconds,
			array(
				'enabled'    => 'on',
				'from_hours' => $time_settings['from_hours'],
				'from_mins'  => $time_settings['from_mins'],
				'to_hours'   => $time_settings['to_hours'],
				'to_mins'    => $time_settings['to_mins'],
			),
			$holidays_str,
			$lockout_str,
			$shipping_settings,
			array(),
			$vendor_id
		);

		if ( $time_settings['from_hours'] != '' ) {

			if ( $current_date == $min_date_array['min_date'] && $next_day_enabled ) {
				$from_hour_values['from_hours']   = $time_settings['from_hours'];
				$from_hour_values['from_minutes'] = $time_settings['from_mins'];
			} elseif ( ( $time_settings['from_hours'] < $current_hour || ( $time_settings['from_hours'] == $current_hour && $current_minute > 0 ) )
				&& $current_date == $min_date_array['min_date'] && ( $current_hour > $min_date_array['min_hour'] || ( $current_hour == $min_date_array['min_hour'] && $current_minute > $min_date_array['min_minute'] ) ) && $current_hour <= $time_settings['to_hours'] ) {
				$from_hour_values['from_hours']   = $current_hour;
				$from_hour_values['from_minutes'] = $current_minute;
			} elseif ( $time_settings['from_hours'] < $min_date_array['min_hour'] || ( ( $time_settings['from_hours'] == $min_date_array['min_hour'] && $min_date_array['min_minute'] > 0 ) && $min_date_array['min_hour'] <= $time_settings['to_hours'] ) ) {
				$from_hour_values['from_hours']   = $min_date_array['min_hour'];
				$from_hour_values['from_minutes'] = $min_date_array['min_minute'];
			} else {
				$from_hour_values['from_hours']   = $time_settings['from_hours'];
				$from_hour_values['from_minutes'] = $time_settings['from_mins'];
			}
		}

		return $from_hour_values;
	}

	/**
	 * Return the delivery date field mandatory checkbox value for custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 *
	 * @return string Delivery date field mandatory checkbox value
	 * @since 3.0
	 */

	public static function orddd_get_shipping_date_field_mandatory( $shipping_settings ) {
		$date_field_mandatory = '';
		if ( isset( $shipping_settings['date_mandatory_field'] ) && $shipping_settings['date_mandatory_field'] == 'checked' ) {
			$date_field_mandatory = $shipping_settings['date_mandatory_field'];
		}
		return $date_field_mandatory;
	}

	/**
	 * Check whether the time slod is enabled for the custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 *
	 * @return string 'on' if the timeslots are added for the custom settings, else blank.
	 * @since 3.0
	 */

	public static function orddd_is_shipping_timeslot_enable( $shipping_settings ) {
		$time_slots_enable = '';
		if ( isset( $shipping_settings['enable_time_slot'] ) && 'on' === $shipping_settings['enable_time_slot'] ) {
			$time_slots_enable = 'on';
		}
		return $time_slots_enable;
	}

	/**
	 * Return the delivery time field mandatory checkbox value for custom delivery settings added in the admin
	 *
	 * @param array $shipping_settings Custom settings added
	 *
	 * @return string Delivery time field mandatory checkbox value
	 * @since 3.0
	 */

	public static function orddd_get_shipping_time_field_mandatory( $shipping_settings ) {
		$timeslot_field_mandatory = '';
		if ( isset( $shipping_settings['enable_time_slot'] ) && 'on' === $shipping_settings['enable_time_slot'] ) {
			if ( isset( $shipping_settings['timeslot_mandatory_field'] ) && $shipping_settings['timeslot_mandatory_field'] == 'checked' ) {
				$timeslot_field_mandatory = $shipping_settings['timeslot_mandatory_field'];
			}
		}
		return $timeslot_field_mandatory;
	}

	/**
	 * Return the delivery dates to disabled when same or next day is enabled for custom delivery settings added in the admin
	 *
	 * @globals array $orddd_shipping_days Shipping weekdays array
	 * @globals array $orddd_weekdays Weekdays array
	 *
	 * @param array $shipping_settings Custom settings added
	 * @param array $same_day Same day settings added
	 * @param array $next_day Next day settings added
	 *
	 * @return string Delivery dates to disable in the calendar
	 * @since 3.0
	 */

	public static function orddd_get_shipping_disabled_days_str( $shipping_settings, $same_day, $next_day, $vendor_id = 0 ) {
		global $orddd_shipping_days, $orddd_weekdays;

		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		// Below function checks MDT in custom shipping methods where we have product categories (with & without shipping methods) and shipping classes.
		$minimum_time   	    	= 0;
		$minimum_delivery_time_secs = 0;
		$minimum_delivery_time      = 0;
		
		if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && ( 'product_categories' === $shipping_settings['delivery_settings_based_on'][0] || 'products' === $shipping_settings['delivery_settings_based_on'][0] ) ) {
			$minimum_time = orddd_get_higher_minimum_delivery_time( $vendor_id );
		}
		
		if ( $minimum_time != '' && $minimum_time != 0 ) {
			$minimum_delivery_time = $minimum_time;
		} else {
			if ( isset( $shipping_settings['minimum_delivery_time'] ) ) {
				$minimum_delivery_time = $shipping_settings['minimum_delivery_time'];
				if ( '' == $minimum_delivery_time ) {
					$minimum_delivery_time = 0;
				}
			}
		}

		$minimum_delivery_time_secs = $minimum_delivery_time * 60 * 60;
		$holidays_arr               = array();
		$holiday_date_arr           = array();
		if ( isset( $shipping_settings['enable_global_holidays'] ) && $shipping_settings['enable_global_holidays'] == 'checked' ) {
			$holidays = self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id );
			if ( $holidays != '' && $holidays != '{}' && $holidays != '[]' && $holidays != 'null' ) {
				$holidays_arr = json_decode( self::orddd_get_option( 'orddd_delivery_date_holidays', '', $vendor_id ) );
			}
			foreach ( $holidays_arr as $k => $v ) {
				$holiday_date_arr[] = $v->d;
			}
		}

		if ( isset( $shipping_settings['holidays'] ) && $shipping_settings['holidays'] != '' ) {
			$holiday_settings = explode( ',', $shipping_settings['holidays'] );
			foreach ( $holiday_settings as $hk => $hv ) {
				$hv          = str_replace( '}', '', $hv );
				$hv          = str_replace( '{', '', $hv );
				$holiday_arr = explode( ':', $hv );
				if ( isset( $holiday_arr[1] ) && $holiday_arr[1] != '' ) {
					$holiday_date_arr[] = $holiday_arr[1];
				}
			}
		}

		$current_time          = current_time( 'timestamp', $gmt );
		$disabled_days_str     = '';
		$disabled_weekdays_str = '';
		$current_date          = gmdate( 'd', $current_time );
		$current_month         = gmdate( 'm', $current_time );
		$current_year          = gmdate( 'Y', $current_time );

		if ( ( isset( $same_day['after_hours'] ) && ( $same_day['after_hours'] == 0 || '' === $same_day['after_hours'] ) && isset( $same_day['after_minutes'] ) && ( $same_day['after_minutes'] == 0 || '' === $same_day['after_minutes'] ) ) || ( ! isset( $same_day['after_hours'] ) ) ) {
		} else {
			$cut_off_hour      = $same_day['after_hours'];
			$cut_off_minute    = $same_day['after_minutes'];
			$cut_off_timestamp = gmmktime( $cut_off_hour, $cut_off_minute, 0, $current_month, $current_date, $current_year );

			$cut_off_with_min_time = $current_time + $minimum_delivery_time_secs;

			$enable_day_until_cutoff = apply_filters( 'orddd_enable_day_until_cutoff', false );
			if ( $enable_day_until_cutoff && ( 0 !== $minimum_delivery_time_secs || '' !== $minimum_delivery_time_secs ) ) {
				$cut_off_with_min_time = $current_time;
			}

			if ( 'on' === self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
				$days_disabled          = 'No';
				$orddd_weekdays_enabled = array();
				foreach ( $orddd_shipping_days as $s_key => $s_value ) {
					$day_check = self::orddd_get_option( $s_key, '', $vendor_id );
					if ( $day_check == 'checked' ) {
						$orddd_weekdays_enabled[ $s_key ] = $day_check;
					}
				}
				$current_day = gmdate( 'w', $cut_off_timestamp );
				if ( $cut_off_timestamp > $cut_off_with_min_time ) {
				} else {
					if ( ! in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ), $holiday_date_arr ) ) {
						$disabled_days_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
					}
				}
			} else {
				if ( $cut_off_timestamp > $cut_off_with_min_time ) {
				} else {
					if ( ! in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ), $holiday_date_arr ) ) {
						$disabled_days_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
					}
				}
			}
		}

		// Nexy Day Delivery.
		if ( isset( $shipping_settings['next_day'] ) ) {
			if ( 'on' === self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
				$days_disabled          = 'No';
				$orddd_weekdays_enabled = array();
				foreach ( $orddd_shipping_days as $s_key => $s_value ) {
					$day_check = self::orddd_get_option( $s_key, '', $vendor_id );
					if ( $day_check == 'checked' ) {
						$orddd_weekdays_enabled[ $s_key ] = $day_check;
					}
				}
			} else {
				$days_disabled          = 'No';
				$orddd_weekdays_enabled = array();
				if ( isset( $shipping_settings['delivery_type']['weekdays'] ) && 'on' === $shipping_settings['delivery_type']['weekdays'] ) {
					$weekdays = $shipping_settings['weekdays'];
					foreach ( $weekdays as $s_key => $s_value ) {
						if ( isset( $s_value['enable'] ) && $s_value['enable'] == 'checked' ) {
							$orddd_weekdays_enabled[ $s_key ] = $s_value['enable'];
						}
					}
				}
			}

			if ( isset( $next_day['after_hours'] ) && ( $next_day['after_hours'] == 0 || '' === $next_day['after_hours'] ) && isset( $next_day['after_minutes'] ) && ( $next_day['after_minutes'] == 0 || '' === $next_day['after_minutes'] ) || ( ! isset( $next_day['after_hours'] ) ) ) {
			} elseif ( is_array( $next_day ) && count( $next_day ) > 0 ) {
				$cut_off_hour      = $next_day['after_hours'];
				$cut_off_minute    = $next_day['after_minutes'];
				$cut_off_timestamp = gmmktime( $cut_off_hour, $cut_off_minute, 0, $current_month, $current_date, $current_year );

				$cut_off_with_min_time = $current_time + $minimum_delivery_time_secs;

				$next_date           = gmdate( 'w', $cut_off_timestamp + 86400 );
				$next_date_timestamp = $cut_off_timestamp + 86400;
				$next_day            = gmdate( 'Y-m-d', $next_date_timestamp );
				$day_after           = gmdate( 'Y-m-d', strtotime( $next_day . ' +1 day' ) );

				$all_specific_dates = self::orddd_get_all_shipping_specific_dates( $shipping_settings );

				if ( $current_time < $cut_off_timestamp && $cut_off_with_min_time < strtotime( $day_after ) ) {
					$current_weekday                 = gmdate( 'w', $current_time );
					$disabled_days_for_shipping_days = 'No';
					$j = $current_weekday;
					for (  $i = 0; $i <= 6; $i ++ ) {
						$is_day_disabled = 'no';
						if ( 'on' === get_option( 'orddd_enable_shipping_days' ) ) {
							if ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ! isset( $orddd_weekdays_enabled[ 'orddd_shipping_day_' . $j ] ) ) {
								$is_day_disabled = 'yes';
							}
						} else {
							if ( ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ! isset( $orddd_weekdays_enabled[ 'orddd_weekday_' . $j ] ) ) && ( ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 && ! in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ), $all_specific_dates ) ) || is_array( $all_specific_dates ) && count( $all_specific_dates ) == 0 ) ) {
								$is_day_disabled = 'yes';
							}
						}

						if ( $is_day_disabled == 'yes' ) {
							$disabled_days_for_shipping_days = 'Yes';
							$disabled_weekdays_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
							$current_time                    = strtotime( '+1 day', $current_time );
							$j                               = gmdate( 'w', $current_time );
						} else {
							if ( in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ), $holiday_date_arr ) ) {
								$consider_holiday_for_nextday_cutoff = apply_filters( 'orddd_consider_holiday_for_nextday_cutoff', false );
								if ( ! $consider_holiday_for_nextday_cutoff ) {
									//$next_date_timestamp = strtotime( '+1 day', $next_date_timestamp );
									$current_time = strtotime( '+1 day', $current_time );
									$j            = gmdate( 'w', $current_time );
								}
								
							} else {
								break;
							}
						}
					}

					if ( $days_disabled == 'Yes' ) {
						$disabled_days_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
					}
					// Disabled the current date in the calendar only when same day is disabled for delivery
					// and next day is enabled.
					if ( ( isset( $same_day['after_hours'] ) && ( $same_day['after_hours'] == 0 || '' === $same_day['after_hours'] ) && isset( $same_day['after_minutes'] ) && ( $same_day['after_minutes'] == 0 || '' === $same_day['after_minutes'] ) ) || ( ! isset( $same_day['after_hours'] ) ) ) {		
						$disabled_weekdays_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
					}
				} else {
					$j = $next_date;
					for ( $i = 0; $i <= 6; $i ++ ) {
						$is_day_disabled = 'no';
						if ( 'on' === get_option( 'orddd_enable_shipping_days' ) ) {
							if ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ! isset( $orddd_weekdays_enabled[ 'orddd_shipping_day_' . $j ] ) ) {
								$is_day_disabled = 'yes';
							}
						} else {
							if ( ( is_array( $orddd_weekdays_enabled ) && count( $orddd_weekdays_enabled ) > 0 && ! isset( $orddd_weekdays_enabled[ 'orddd_weekday_' . $j ] ) ) && ( ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 && ! in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ), $all_specific_dates ) ) || is_array( $all_specific_dates ) && count( $all_specific_dates ) == 0 ) ) {
								$is_day_disabled = 'yes';
							}
						}

						if ( $is_day_disabled == 'yes' ) {
							$days_disabled          = 'Yes';							
							$current_time           = strtotime( '+1 day', $current_time );
							$disabled_weekdays_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
							$next_date_timestamp    = strtotime( '+1 day', $next_date_timestamp );
							$j                      = gmdate( 'w', $next_date_timestamp );
						} else {
							if ( $current_time <= $next_date_timestamp ) {
								if ( in_array( gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $next_date_timestamp ), $holiday_date_arr ) ) {
									$days_disabled                       = 'Yes';
									$consider_holiday_for_nextday_cutoff = apply_filters( 'orddd_consider_holiday_for_nextday_cutoff', false );
									if ( ! $consider_holiday_for_nextday_cutoff ) {
										$next_date_timestamp = strtotime( '+1 day', $next_date_timestamp );
									}
									$current_time           = strtotime( '+1 day', $current_time );
									$j                      = gmdate( 'w', $current_time );
									$disabled_weekdays_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
								} else {
									$current_time           = strtotime( '+1 day', $current_time );
									$j                      = gmdate( 'w', $current_time );
									$disabled_weekdays_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $current_time ) . '",';
								}
							} else {
								break;
							}
						}
					}
					
					$disabled_days_str .= '"' . gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $next_date_timestamp ) . '",';					
				}
			}
		}

		if ( $disabled_weekdays_str == '' && $disabled_days_str == '' ) {
			return '';
		} else {
			return $disabled_days_str . '&' . $disabled_weekdays_str;
		}
	}

	/**
	 * Return the common delivery days/dates enabled when the multiple product categories/shipping classes are added to the cart
	 *
	 * @globals resource $woocommerce WooCommerce object
	 * @globals resource $wpdb WordPress object
	 * @globals array $orddd_weekdays Weekdays array
	 *
	 * @param string $orddd_shipping_id Shipping method selected
	 * @param bool   $is_ajax True if called from ajax, else false.
	 *
	 * @return array Common days array
	 * @since 6.2
	 */
	public static function orddd_common_delivery_days_for_product_category( $orddd_shipping_id, $pickup_location = '', $is_ajax = false ) {
		global $post;
		$categories_to_send     = '';
		$shipping_class_to_send = '';
		$products_to_send       = '';

		if ( isset( $post->post_type ) && ( $post->post_type == 'shop_order' || $post->post_type == 'shop_subscription' ) ) {
			$categories       = self::orddd_get_cart_product_categories( $post->ID );
			$products         = self::orddd_get_cart_products( $post->ID );
			$shipping_classes = self::orddd_get_cart_shipping_classes( $post->ID );
		} else {
			$categories       = self::orddd_get_cart_product_categories( '' );
			$products         = self::orddd_get_cart_products( '' );
			$shipping_classes = self::orddd_get_cart_shipping_classes( '' );
		}

		$shipping_settings_results = self::orddd_get_shipping_settings();
		$categories_checked        = array();
		$products_checked          = array();
		$shipping_classes_checked  = array();
		$settings_to_compare       = array();

		if ( is_array( $shipping_settings_results ) && count( $shipping_settings_results ) > 0 ) {
			foreach ( $shipping_settings_results as $skey => $svalue ) {
				$shipping_settings = get_option( $svalue->option_name );

				if ( isset( $shipping_settings['enable_shipping_based_delivery'] ) && isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'orddd_locations' === $shipping_settings['delivery_settings_based_on'][0] && isset( $shipping_settings['orddd_locations'] ) && ! empty( $shipping_settings['orddd_locations'] ) && ! empty( $shipping_settings['categories_for_pickup_locations'] ) ) {
					if ( in_array( $pickup_location, $shipping_settings['orddd_locations'] ) ) {
						foreach ( $categories as $pkey => $pvalue ) {
							if ( ! in_array( $pvalue, $categories_checked, true ) && in_array( $pvalue, $shipping_settings['categories_for_pickup_locations'], true ) ) {
								if ( is_array( $categories_checked ) && count( $categories_checked ) > 0 ) {
									$categories_checked = array_merge( $categories_checked, $shipping_settings['categories_for_pickup_locations'] );
								} else {
									$categories_checked = $shipping_settings['categories_for_pickup_locations'];
								}
								$settings_to_compare[] = $shipping_settings;
							}
						}
					}
				} elseif ( isset( $shipping_settings['enable_shipping_based_delivery'] ) && isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'products' && isset( $shipping_settings['products'] ) && ! empty( $shipping_settings['products'] ) ) {
					foreach ( $products as $pkey => $pvalue ) {
						if ( ! isset( $shipping_settings['shipping_methods_for_products'] ) || empty( $shipping_settings['shipping_methods_for_products'] ) ) {
							if ( ! in_array( $pvalue, $products_checked ) && in_array( $pvalue, $shipping_settings['products'] ) ) {
								if ( is_array( $products_checked ) && count( $products_checked ) > 0 ) {
									$products_checked = array_merge( $products_checked, $shipping_settings['products'] );
								} else {
									$products_checked = $shipping_settings['products'];
								}
								$settings_to_compare[] = $shipping_settings;
							}
						} elseif ( isset( $shipping_settings['shipping_methods_for_products'] ) && in_array( $orddd_shipping_id, $shipping_settings['shipping_methods_for_products'] ) ) {

							if ( ( ( isset( $products_checked[ $orddd_shipping_id ] ) && ! in_array( $pvalue, $products_checked[ $orddd_shipping_id ] ) ) || ! isset( $products_checked[ $orddd_shipping_id ] ) ) && in_array( $pvalue, $shipping_settings['products'] ) ) {

								// Add all the categories from the custom setting to categories_checked array so that the custom settings are not added twice.
								if ( isset( $products_checked[ $orddd_shipping_id ] ) ) {
									$products_checked[ $orddd_shipping_id ] = array_merge( $products_checked[ $orddd_shipping_id ], $shipping_settings['products'] );
								} else {
									$products_checked[ $orddd_shipping_id ] = $shipping_settings['products'];
								}

								$settings_to_compare[] = $shipping_settings;
							}
						}
					}
				} elseif ( isset( $shipping_settings['enable_shipping_based_delivery'] ) && 'on' === $shipping_settings['enable_shipping_based_delivery'] && isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'product_categories' && isset( $shipping_settings['product_categories'] ) ) {

					foreach ( $categories as $pkey => $pvalue ) {
						if ( ! isset( $shipping_settings['shipping_methods_for_categories'] ) || ( isset( $shipping_settings['shipping_methods_for_categories'] ) && empty( $shipping_settings['shipping_methods_for_categories'] ) ) ) {
							if ( ! in_array( $pvalue, $categories_checked ) && in_array( $pvalue, $shipping_settings['product_categories'] ) ) {
								if ( is_array( $categories_checked ) && count( $categories_checked ) > 0 ) {
									$categories_checked = array_merge( $categories_checked, $shipping_settings['product_categories'] );
								} else {
									$categories_checked = $shipping_settings['product_categories'];
								}
								$settings_to_compare[] = $shipping_settings;
							}
						} elseif ( isset( $shipping_settings['shipping_methods_for_categories'] ) && in_array( $orddd_shipping_id, $shipping_settings['shipping_methods_for_categories'] ) ) {

							if ( ( ( isset( $categories_checked[ $orddd_shipping_id ] ) && ! in_array( $pvalue, $categories_checked[ $orddd_shipping_id ] ) ) || ! isset( $categories_checked[ $orddd_shipping_id ] ) ) && in_array( $pvalue, $shipping_settings['product_categories'] ) ) {

								// Add all the categories from the custom setting to categories_checked array so that the custom settings are not added twice.
								if ( isset( $categories_checked[ $orddd_shipping_id ] ) ) {
									$categories_checked[ $orddd_shipping_id ] = array_merge( $categories_checked[ $orddd_shipping_id ], $shipping_settings['product_categories'] );
								} else {
									$categories_checked[ $orddd_shipping_id ] = $shipping_settings['product_categories'];
								}

								$settings_to_compare[] = $shipping_settings;
							}
						}
					}
				}
			}
		}

		$categories_to_send     = implode( ',', $categories );
		$products_to_send       = implode( ',', $products );
		$shipping_class_to_send = implode( ',', $shipping_classes );

		if ( is_array( $settings_to_compare ) && count( $settings_to_compare ) == 0 ) {
			foreach ( $shipping_classes as $sckey => $scvalue ) {
				if ( is_array( $shipping_settings_results ) && count( $shipping_settings_results ) > 0 ) {
					foreach ( $shipping_settings_results as $skey => $svalue ) {
						$shipping_settings = get_option( $svalue->option_name );
						if ( isset( $shipping_settings['enable_shipping_based_delivery'] ) && isset( $shipping_settings['delivery_settings_based_on'][0] ) && $shipping_settings['delivery_settings_based_on'][0] == 'shipping_methods' && isset( $shipping_settings['shipping_methods'] ) ) {
							if ( ! in_array( $scvalue, $shipping_classes_checked ) && in_array( $scvalue, $shipping_settings['shipping_methods'] ) ) {
								$shipping_classes_checked[] = $scvalue;
								$settings_to_compare[]      = $shipping_settings;
							}
						}
					}
				}
			}
		}
		$common_setting = self::orddd_common_delivery_days_for_product_category_common( $settings_to_compare, $categories_to_send, $shipping_class_to_send );
		return $common_setting;
	}

	/**
	 * Return the common delivery days/dates enabled when the multiple product categories/shipping classes are added to the cart for dropdown dates
	 *
	 * @globals resource $woocommerce WooCommerce object
	 * @globals resource $wpdb WordPress object
	 * @globals array $orddd_weekdays Weekdays array
	 *
	 * @param string $setting_ids Custom schedule ids.
	 * @param string $vendor_id Vendor id.
	 *
	 * @return array Common days array
	 * @since 6.2
	 */
	public static function orddd_common_delivery_days_for_product_category_dropdown( $setting_ids, $vendor_id ) {
		$settings_to_compare = array();

		foreach ( $setting_ids as $custom_setting_id ) {
			$settings_to_compare[] = orddd_common::orddd_get_option( 'orddd_shipping_based_settings_' . $custom_setting_id, '', $vendor_id );
		}
		$common_setting = self::orddd_common_delivery_days_for_product_category_common( $settings_to_compare );
		return $common_setting;
	}
	/**
	 * Return the common delivery days/dates enabled when the multiple product categories/shipping classes are added to the cart for dropdown dates
	 *
	 * @globals resource $woocommerce WooCommerce object
	 * @globals resource $wpdb WordPress object
	 * @globals array $orddd_weekdays Weekdays array
	 *
	 * @param string $settings_to_compare Custom schedule ids.
	 * @param string $categories_to_send Category ids.
	 * @param string $shipping_class_to_send shipping classes. 
	 *
	 * @return array Common days array
	 * @since 6.2
	 */
	public static function orddd_common_delivery_days_for_product_category_common( $settings_to_compare, $categories_to_send = '', $shipping_class_to_send = '' ) {
		global $post, $woocommerce, $wpdb, $orddd_weekdays;
		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time = current_time( 'timestamp', $gmt );
		$current_date = gmdate( 'j-n-Y', $current_time );

		$common_delivery_days  = array();
		$common_delivery_dates = array();
		$all_holidays          = array();
		$locked_days           = array();
		$is_common             = 'no';
		$is_days_common        = 'no';
		$delivery_days         = array();
		$enabled_weekdays      = array();
		$specific_dates        = array();
		$holidays              = array();
		$priority              = array();

		if ( is_array( $settings_to_compare ) && count( $settings_to_compare ) > 0 ) {
			$date_lockouts         = array();
			$min_date_lockout      = 0;
			$min_time_slot_lockout = 0;

			$checked_locked_days = array();
			$lockout_days_str    = '';
			$date_time_lockouts  = array();
			foreach ( $settings_to_compare as $ckey => $cvalue ) {
				if ( isset( $cvalue['date_lockout'] ) && $cvalue['date_lockout'] != '' ) {
					$date_lockouts[] = $cvalue['date_lockout'];
				}
				if ( isset( $cvalue['orddd_priority_for_delivery_schedule'] ) && $cvalue['orddd_priority_for_delivery_schedule'] != '' ) {
					$priority[] = $cvalue['orddd_priority_for_delivery_schedule'];
				}
				if ( isset( $cvalue['time_slots'] ) && $cvalue['time_slots'] != '' ) {
					$time_slots = explode( '},', $cvalue['time_slots'] );
					foreach ( $time_slots as $tk => $tv ) {
						if ( $tv != '' ) {
							$timeslot_values = self::get_timeslot_values( $tv );
							if ( is_array( $timeslot_values['selected_days'] ) && $timeslot_values['delivery_days_selected'] == 'weekdays' ) {
								foreach ( $timeslot_values['selected_days'] as $dkey => $dval ) {
									if ( $timeslot_values['lockout'] != '' && $timeslot_values['lockout'] != '0' ) {
										$date_time_lockouts[ $dval ][ $timeslot_values['time_slot'] ][] = $timeslot_values['lockout'];
									} elseif ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' && 'false' != get_option( 'orddd_global_lockout_time_slots' ) ) {
										$date_time_lockouts[ $dval ][ $timeslot_values['time_slot'] ][] = intval( get_option( 'orddd_global_lockout_time_slots' ) );
									}
								}
							} elseif ( is_array( $timeslot_values['selected_days'] ) && $timeslot_values['delivery_days_selected'] == 'specific_dates' ) {
								foreach ( $timeslot_values['selected_days'] as $dkey => $dval ) {
									if ( $timeslot_values['lockout'] != '' && $timeslot_values['lockout'] != '0' ) {
										$date_time_lockouts[ $dval ][ $timeslot_values['time_slot'] ][] = $timeslot_values['lockout'];
									} elseif ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' && 'false' != get_option( 'orddd_global_lockout_time_slots' ) ) {
										$date_time_lockouts[ $dval ][ $timeslot_values['time_slot'] ][] = get_option( 'orddd_global_lockout_time_slots' );
									}
								}
							}
						}
					}
				}
			}

			$min_time_lockout = array();
			foreach ( $date_time_lockouts as $dt => $dv ) {
				foreach ( $dv as $dvt => $dvv ) {
					$min_lockout = min( $dvv );
					if ( isset( $min_time_lockout[ $dt ] ) ) {
						$min_time_lockout[ $dt ] += $min_lockout;
					} else {
						$min_time_lockout[ $dt ] = $min_lockout;
					}
				}
			}

			if ( is_array( $date_lockouts ) && count( $date_lockouts ) > 0 ) {
				$min_date_lockout = min( $date_lockouts );
			}

			foreach ( $settings_to_compare as $ckey => $cvalue ) {
				$weekday_settings   = array();
				$all_specific_dates = array();
				if ( isset( $cvalue['delivery_type'] ) ) {
					$delivery_type = $cvalue['delivery_type'];
				}

				if ( isset( $delivery_type['weekdays'] ) && $delivery_type['weekdays'] == 'on' ) {
					foreach ( $orddd_weekdays as $n => $day_name ) {
						if ( isset( $cvalue['weekdays'][ $n ] ) ) {
							$weekday = $cvalue['weekdays'][ $n ];
							if ( isset( $weekday['enable'] ) && $weekday['enable'] == 'checked' ) {
								$weekday_settings[ $n ] = $weekday['enable'];
							}
						}
					}
					$delivery_days[] = $weekday_settings;
				}

				if ( isset( $delivery_type['specific_dates'] ) && $delivery_type['specific_dates'] == 'on' ) {
					$specific_days_settings = explode( ',', $cvalue['specific_dates'] );
					foreach ( $specific_days_settings as $sk => $sv ) {
						if ( $sv != '' ) {
							$sv                   = str_replace( '}', '', $sv );
							$sv                   = str_replace( '{', '', $sv );
							$specific_date_arr    = explode( ':', $sv );
							$all_specific_dates[] = $specific_date_arr[0];
						}
					}
					$specific_dates[] = $all_specific_dates;
				}

				$holidays_settings = explode( ',', $cvalue['holidays'] );
				foreach ( $holidays_settings as $sk => $sv ) {
					if ( $sv != '' ) {
						$sv          = str_replace( '}', '', $sv );
						$sv          = str_replace( '{', '', $sv );
						$holiday_arr = explode( ':', $sv );

						// Replace single quote in the holiday name with the html entities
						// @todo: Need to fix the double quotes issue in the holiday name.
						// An error comes in console when the holiday name contains double quotes in it.
						// @todo: We shouldn't allow characters like / - and any other special chars in holiday name
						$name = str_replace( "'", '&apos;', $holiday_arr[0] );
						$name = str_replace( '"', '&quot;', $name );
						$name = str_replace( '/', ' ', $name );
						$name = str_replace( '-', ' ', $name );
						$name = str_replace( ':', '&#58;', $name );
						$name = trim( preg_replace( '/\t+/', '', $name ) );

						$date_arr              = explode( '-', $holiday_arr[1] );
						$holiday_date          = $date_arr[1] . '-' . $date_arr[0] . '-' . $date_arr[2];
						$holiday_timestamp     = strtotime( $holiday_date );
						$current_day_timestamp = strtotime( $current_date );

						if ( $holiday_timestamp < $current_day_timestamp ) {
							continue;
						}

						$all_holidays[] = $name . ':' . $holiday_arr[1];
					}
				}
					// We do store the global holidays in the cache 'orddd_general_delivery_date_holidays' but it returns holidays in string, and we want it in array below.
				if ( isset( $cvalue['enable_global_holidays'] ) && $cvalue['enable_global_holidays'] == 'checked' ) {
						$holidays_arr = array();
						$holidays     = get_option( 'orddd_delivery_date_holidays' );
					if ( $holidays != '' && $holidays != '{}' && $holidays != '[]' && $holidays != 'null' ) {
						$holidays_arr = json_decode( get_option( 'orddd_delivery_date_holidays' ) );
					}
					foreach ( $holidays_arr as $k => $v ) {
						// Replace single quote in the holiday name with the html entities
						// @todo: Need to fix the double quotes issue in the holiday name.
						// An error comes in console when the holiday name contains double quotes in it.

						$name = str_replace( "'", '&apos;', $v->n );
						$name = str_replace( '"', '&quot;', $name );
						$name = str_replace( '/', ' ', $name );
						$name = str_replace( '-', ' ', $name );
						$name = str_replace( ':', '&#58;', $name );
						$name = trim( preg_replace( '/\t+/', '', $name ) );

						if ( isset( $v->r_type ) && $v->r_type == 'on' ) {
							$holiday_date_arr = explode( '-', $v->d );
							$recurring_date   = $holiday_date_arr[0] . '-' . $holiday_date_arr[1];
							$all_holidays[]   = $name . ':' . $recurring_date;
						} else {
							$all_holidays[] = $name . ':' . $v->d;
						}
					}
				}

				if ( isset( $cvalue['time_slots'] ) && $cvalue['time_slots'] != '' ) {
					if ( isset( $cvalue['orddd_lockout_time_slot'] ) ) {
						$lockout_time_arr = $cvalue['orddd_lockout_time_slot'];
						if ( $lockout_time_arr == '' || $lockout_time_arr == '{}' || $lockout_time_arr == '[]' || $lockout_time_arr == 'null' ) {
							$lockout_time_array = array();
						} else {
							$lockout_time_array = json_decode( $lockout_time_arr );
						}
					} else {
						$lockout_time_array = array();
					}

					foreach ( $lockout_time_array as $lk => $lv ) {
						$lockout_date = gmdate( 'n-j-Y', strtotime( $lv->d ) );
						if ( isset( $min_time_lockout[ $lv->d ] ) && $lv->o >= $min_time_lockout[ $lv->d ] && ! in_array( $lockout_date, $locked_days ) ) {
							$locked_days[] = $lockout_date;
						} else {
							$dw = gmdate( 'w', strtotime( $lv->d ) );
							if ( isset( $min_time_lockout['all'] ) && $lv->o >= $min_time_lockout['all'] && ! in_array( $lockout_date, $locked_days ) ) {
								$locked_days[] = $lockout_date;
							} elseif ( isset( $min_time_lockout[ 'orddd_weekday_' . $dw ][ $lv->t ] ) && $lv->o >= $min_time_lockout[ 'orddd_weekday_' . $dw ][ $lv->t ] && ! in_array( $lockout_date, $locked_days ) ) {
								$locked_days[] = $lockout_date;
							}
						}
					}
				} else {
					if ( isset( $cvalue['orddd_lockout_date'] ) ) {
						$lockout_date_array = $cvalue['orddd_lockout_date'];
						if ( $lockout_date_array == '' || $lockout_date_array == '{}' || $lockout_date_array == '[]' || $lockout_date_array == 'null' ) {
							$lockout_date_arr = array();
						} else {
							$lockout_date_arr = json_decode( $lockout_date_array );
						}
					} else {
						$lockout_date_arr = array();
					}

					foreach ( $lockout_date_arr as $k => $v ) {
						if ( $v->o >= $min_date_lockout && $min_date_lockout != 0 ) {
							$locked_days[] = $v->d;
						}
					}
				}
			}
		}

		if ( is_array( $settings_to_compare ) && count( $settings_to_compare ) > 1 ) {
			$common_delivery_days = self::orddd_get_common_delivery_days( $delivery_days );
			$days                 = array();
			if ( is_array( $common_delivery_days ) && count( $common_delivery_days ) > 0 ) {
				$days = array_keys( $common_delivery_days );
			} elseif ( is_array( $delivery_days ) && count( $delivery_days ) == 1 ) {
				$days = array_keys( $delivery_days[0] );
			}

			if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
				$common_delivery_dates = self::orddd_get_common_delivery_dates( $specific_dates, $days, $delivery_days );
			}
			$is_common        = 'yes';
			$highest_priority = is_array( $priority ) && count( $priority ) > 0 ? max( $priority ) : 0;
			if ( 0 !== (int) $highest_priority ) {
				$is_common = 'no';
			}

			if ( ( is_array( $common_delivery_dates ) && count( $common_delivery_dates ) > 0 ) || ( is_array( $common_delivery_days ) && count( $common_delivery_days ) > 0 ) ) {
				$is_days_common = 'yes';
			}
		}

		return array(
			'orddd_common_weekdays'                 => $common_delivery_days,
			'orddd_common_specific_dates'           => $common_delivery_dates,
			'orddd_common_holidays'                 => $all_holidays,
			'orddd_common_locked_days'              => $locked_days,
			'orddd_is_days_common'                  => $is_days_common,
			'orddd_categories_settings_common'      => $is_common,
			'orddd_category_settings_to_load'       => $categories_to_send,
			'orddd_shipping_class_settings_to_load' => $shipping_class_to_send,
		);
	}

	/**
	 * Return the common delivery specific dates enabled when the multiple product categories are added to the cart
	 *
	 * @param array $specific_dates Added specific dates for the custom delivery settings
	 * @param arrry $common_days Common delivery days
	 * @param array $delivery_days Weekdays enabled for the custom delivery settings.
	 *
	 * @return array Common specific dates array
	 * @since 6.2
	 */
	public static function orddd_get_common_delivery_dates( $specific_dates, $common_days, $delivery_days ) {
		$common_delivery_dates = array();
		if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
			$i = 0;
			$j = 1;
			if ( ( is_array( $specific_dates ) && count( $specific_dates ) == 1 ) &&
				( is_array( $delivery_days ) && count( $delivery_days ) == 0 ) ) {
				$common_delivery_dates = $specific_dates[0];
			} else {
				$common_delivery_dates = call_user_func_array( 'array_intersect', $specific_dates ); //phpcs:ignore

				$all_delivery_days = array_keys( array_reduce( $delivery_days, 'array_merge', array() ) );

				foreach ( $specific_dates as $sk => $sval ) {
					foreach ( $sval as $ckey => $cvalue ) {
						$value_split           = explode( '-', $cvalue );
						$specific_date_weekday = gmdate( 'w', strtotime( $value_split[1] . '-' . $value_split[0] . '-' . $value_split[2] ) );
						if ( in_array( 'orddd_weekday_' . $specific_date_weekday, $all_delivery_days ) ) {
							$common_delivery_dates[] = $cvalue;
						}
					}
				}
			}
		}
		return $common_delivery_dates;
	}


	/**
	 * Return the common delivery weekdays enabled when the multiple product categories are added to the cart
	 *
	 * @param array $delivery_days Weekdays enabled for the custom delivery settings.
	 *
	 * @return array Common delivery days array
	 * @since 6.2
	 */

	public static function orddd_get_common_delivery_days( $delivery_days ) {
		$common_delivery_days = array();
		if ( is_array( $delivery_days ) && count( $delivery_days ) > 0 ) {
			$common_delivery_days = call_user_func_array( 'array_intersect_key', $delivery_days );
		}
		return $common_delivery_days;
	}

	/**
	 * Return all custom settings added in admin
	 *
	 * @globals resource $wpdb WordPress object
	 *
	 * @return array All custom delivery settings added
	 * @since 3.0
	 * @since Updated 9.28.3
	 */
	public static function orddd_get_shipping_settings( $filter_inactive_schedules = 1, $vendor_id = 0, $from_admin = false ) {

		$results   = wp_cache_get( 'orddd_get_shipping_settings_result_' . $vendor_id );
		$is_vendor = ! ( 0 === $vendor_id || '' === $vendor_id || '0' === $vendor_id );
		if ( false === $results ) {
			global $wpdb;

			$results = array();

			if ( $is_vendor ) {
				$results = ORDDD_Integration_Vendors::modify_orddd_data( array(), 'orddd_get_shipping_settings_get_results', $vendor_id );
			} else {
				$shipping_based_settings_query = 'SELECT option_value, option_name FROM `' . $wpdb->prefix . "options` WHERE option_name LIKE 'orddd_shipping_based_settings_%' AND option_name != 'orddd_shipping_based_settings_option_key' ORDER BY option_id DESC";
				$results                       = $wpdb->get_results( $shipping_based_settings_query ); //phpcs:ignore
				$results                       = apply_filters( 'orddd_integration_vendors_modify_data', $results, 'orddd_get_shipping_settings_get_results' );
			}

			$inactive_custom_schedule_ids = array();
			$results3                     = array();
			$results4                     = array();
			if ( is_array( $results ) && count( $results ) > 0 ) {

				// by default, it will always filter the inactive schedules & remove them.
				if ( 1 === $filter_inactive_schedules ) {

					$results2 = array();

					if ( $is_vendor ) {
						$results2 = ORDDD_Integration_Vendors::modify_orddd_data( array(), 'orddd_get_shipping_settings_get_settings_status_query', $vendor_id );
					} else {
						$fetch_settings_status_query = 'SELECT option_value, option_name FROM `' . $wpdb->prefix . "options` WHERE option_name LIKE 'orddd_shipping_settings_status_%' ORDER BY option_id DESC";
						$results2                    = $wpdb->get_results( $fetch_settings_status_query ); //phpcs:ignore
						$results2                    = apply_filters( 'orddd_integration_vendors_modify_data', $results2, 'orddd_get_shipping_settings_get_settings_status_query' );
					}

					foreach ( $results2 as $key2 => $value2 ) {
						if ( 'inactive' === $value2->option_value ) {
							$inactive_arr                   = explode( 'orddd_shipping_settings_status_', $value2->option_name );
							$inactive_custom_schedule_ids[] = $inactive_arr[1];
						} else {
							// do nothing
						}
					}
				}

				foreach ( $results as $key => $value ) {
					$option_name           = $value->option_name;
					$custom_setting_id_arr = explode( 'orddd_shipping_based_settings_', $value->option_name );
					$custom_setting_id     = $custom_setting_id_arr[1];
					if ( ! in_array( $custom_setting_id, $inactive_custom_schedule_ids ) ) {
						$results3[ $key ] = $value;
					} else {
						$results4[ $key ] = $value;
					}
				}

				// Return active first and inactive last if current page is Order Delivery Date -> Custom Settings page
				if ( isset( $_GET['page'] ) && 'order_delivery_date' == $_GET['page'] &&
					isset( $_GET['action'] ) && 'delivery-schedule' == $_GET['action'] ) {
					$results = array_merge( $results3, $results4 );
				} else {
					$results = $results3;
				}
			}

			wp_cache_set( 'orddd_get_shipping_settings_result_' . $vendor_id, $results );
		}

		return $results;
	}

	/**
	 * Returns the count of settings added in admin, including the inactive ones
	 *
	 * @globals resource $wpdb WordPress object
	 *
	 * @return int Number of custom delivery settings added
	 * @since 9.6
	 */
	public static function orddd_get_shipping_settings_count() {

		global $wpdb;
		$shipping_based_settings_query = 'SELECT COUNT(option_name) as cnt FROM `' . $wpdb->prefix . "options` WHERE option_name LIKE 'orddd_shipping_based_settings_%' AND option_name != 'orddd_shipping_based_settings_option_key' ORDER BY option_id DESC";
		$results                       = $wpdb->get_results( $shipping_based_settings_query ); //phpcs:ignore
		$number_of_custom_settings     = $results[0]->cnt;
		return $number_of_custom_settings;
	}

	/**
	 * Return all product categories for the products added to the cart
	 *
	 * @globals resource $woocommerce WooCommerce object
	 *
	 * @param int $order_id Order ID if called from admin, else blank.
	 *
	 * @return array All product categories for the products added to the cart
	 * @since 7.5
	 */

	public static function orddd_get_cart_product_categories( $order_id ) {
		global $woocommerce;
		$categories  = array();
		$is_frontend = ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );

		if ( method_exists( 'WC', 'is_rest_api_request' ) ) {
			$is_frontend = $is_frontend && ! WC()->is_rest_api_request();
		}

		if ( $order_id == '' && $is_frontend ) {
			if ( ! isset( $woocommerce->cart ) ) {
				return array();
			}

			foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
				if ( ! isset( $values['bundled_by'] ) && ! isset( $values['composite_parent'] ) ) {
					if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
						$product_id = $values['data']->get_id();
					} else {
						$product_id = $values['data']->id;
					}
					if ( 'product_variation' == $values['data']->post_type ) {
						$product_id = $values['product_id'];
					}
					$terms    = wp_get_post_terms( $product_id, 'product_cat' );
					$terms_id = array();
					// get the category IDs
					if ( '' != $terms ) {
						foreach ( $terms as $term => $val ) {
							$terms_id[] = self::get_base_product_category( $val->term_id );
						}
						// For the category ID, get the slug and create an array
						foreach ( $terms_id as $term_id ) {
							$cat_name = self::ordd_get_cat_slug( $term_id );
							if ( ! in_array( $cat_name, $categories ) ) {
								$categories[] = $cat_name;
							}
						}
					}
				}
			}
		} else {
			$order = new WC_Order( $order_id );
			$items = $order->get_items();
			foreach ( $items as $key => $value ) {
				$product_id = $value['product_id'];
				$terms      = get_the_terms( $product_id, 'product_cat' );
				if ( $terms != '' ) {
					foreach ( $terms as $term => $val ) {
						$terms_id[] = self::get_base_product_category( $val->term_id );

						// For the category ID, get the slug and create an array
						foreach ( $terms_id as $term_id ) {
							$cat_name = self::ordd_get_cat_slug( $term_id );
							if ( ! in_array( $cat_name, $categories ) ) {
								$categories[] = $cat_name;
							}
						}
					}
				}
			}
		}

		return $categories;
	}

	/**
	 * Return all shipping classes for the products added to the cart
	 *
	 * @globals resource $woocommerce WooCommerce object
	 *
	 * @param bool $is_ajax True if called from ajax, else false.
	 * @param int  $order_id Order ID if called from admin, else blank.
	 *
	 * @return string All shipping classes for the products added to the cart
	 * @since 7.5
	 */

	public static function orddd_get_cart_shipping_classes( $order_id ) {
		global $woocommerce;
		$shipping_classes = array();
		$is_frontend      = ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );

		if ( '' === $order_id && $is_frontend ) {
			if ( ! isset( $woocommerce->cart ) ) {
				return array();
			}
			foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
				if ( ! isset( $values['bundled_by'] ) && ! isset( $values['composite_parent'] ) ) {
					$terms = array();
					if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
						$product_id = $values['data']->get_id();
					} else {
						$product_id = $values['data']->id;
					}
					if ( 'product_variation' == $values['data']->post_type ) {
						$variation_id = $values['variation_id'];
						$terms        = wp_get_post_terms( $variation_id, 'product_shipping_class' );
						$product_id   = $values['product_id'];
					}
					/**
					 * This condition will be executed when the shipping class for the variation product is same as parent
					 * product and for the simple product as well.
					 */
					if ( is_array( $terms ) && count( $terms ) == 0 ) {
						$terms = wp_get_post_terms( $product_id, 'product_shipping_class' );
					}

					$terms_id = array();
					// get the category IDs
					if ( '' != $terms ) {
						foreach ( $terms as $term => $val ) {
							$terms_id[] = self::get_base_shipping_class( $val->term_id );
						}
						// For the category ID, get the slug and create an array
						foreach ( $terms_id as $term_id ) {
							$cat_name = self::ordd_get_cat_slug( $term_id );
							if ( ! in_array( $cat_name, $shipping_classes ) ) {
								$shipping_classes[] = $cat_name;
							}
						}
					}
				}
			}
		} else {
			$order = new WC_Order( $order_id );
			$items = $order->get_items();
			foreach ( $items as $key => $value ) {
				$product_id = $value['product_id'];
				$terms      = get_the_terms( $product_id, 'product_shipping_class' );
				if ( $terms != '' ) {
					foreach ( $terms as $term => $val ) {
						foreach ( $terms as $term => $val ) {
							$terms_id[] = self::get_base_shipping_class( $val->term_id );
						}
						// For the shipping class ID, get the slug and create an array
						foreach ( $terms_id as $term_id ) {
							$cat_name = self::ordd_get_shipping_class_slug( $term_id );
							if ( ! in_array( $cat_name, $shipping_classes ) ) {
								$shipping_classes[] = $cat_name;
							}
						}
					}
				}
			}
		}

		return $shipping_classes;
	}

	public static function orddd_get_cart_products( $order_id ) {
		global $woocommerce;
		$products    = array();
		$is_frontend = ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		if ( method_exists( 'WC', 'is_rest_api_request' ) ) {
			$is_frontend = $is_frontend && ! WC()->is_rest_api_request();
		}

		if ( $order_id == '' && $is_frontend ) {
			if ( ! isset( $woocommerce->cart ) ) {
				return array();
			}
			foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
				if ( ! isset( $values['bundled_by'] ) && ! isset( $values['composite_parent'] ) ) {
					if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
						$product_id = $values['data']->get_id();
					} else {
						$product_id = $values['data']->id;
					}
					if ( 'product_variation' == $values['data']->post_type ) {
						$product_id = $values['product_id'];
					}

					$products[] = $product_id;
				}
			}
		} else {
			$order = new WC_Order( $order_id );
			$items = $order->get_items();
			foreach ( $items as $key => $value ) {
				$product_id = $value['product_id'];
				$products[] = $product_id;

			}
		}

		return $products;
	}

	/**
	 * Returns the Shipping Class ID in the base language
	 *
	 * @param string $shipping_class_id Translated Shipping Class ID
	 *
	 * @return string Shipping class id
	 * @since 7.7
	 */
	public static function get_base_shipping_class( $shipping_class_id ) {

		$base_shipping_class = $shipping_class_id;

		// If WPML is enabled, the make sure that the base language product ID is used to calculate the availability
		if ( function_exists( 'icl_object_id' ) ) {
			global $sitepress;
			global $polylang;

			if ( isset( $polylang ) ) {
				$default_lang = pll_current_language();
			} else {
				$default_lang = $sitepress->get_default_language();
			}

			$base_shipping_class = icl_object_id( $shipping_class_id, 'product_shipping_class', true, $default_lang );
			// The base product ID is blanks when the product is being created.
			if ( ! isset( $base_shipping_class ) || ( isset( $base_shipping_class ) && $base_shipping_class == '' ) ) {
				$base_shipping_class = $shipping_class_id;
			}
		}
		return $base_shipping_class;
	}

	/**
	 * Returns the Shipping Class Slug for the passed ID
	 *
	 * @param string $shipping_class_id Shipping Class ID
	 *
	 * @return string Shipping class id
	 * @since 7.7
	 */
	public static function ordd_get_shipping_class_slug( $shipping_class_id ) {
		$shipping_class_slug = '';

		if ( $shipping_class_id > 0 ) {
			global $wpdb;

			$query = 'SELECT slug FROM `' . $wpdb->prefix . 'terms` WHERE term_id = %d';

			$results = $wpdb->get_results( $wpdb->prepare( $query, $shipping_class_id ) ); //phpcs:ignore

			$shipping_class_slug = $results[0]->slug;
		}
		return $shipping_class_slug;

	}

	/**
	 * Returns all the user roles are available on the site.
	 *
	 * @return array Roles array.
	 * @since 7.7
	 */
	public static function ordd_get_user_roles() {
		$roles = array();
		$user_roles = wp_roles()->roles;

		foreach ( $user_roles as $role_key => $role_value ) {
			$roles[$role_key] = $role_value['name'];
		}
		$roles['guest'] = esc_html__( 'Guest', 'order-delivery-date' );
		return $roles;

	}

	/**
	 * Returns current user role.
	 *
	 * @return array Role array.
	 * @since 7.7
	 */
	public static function orddd_get_current_user_role() {
		$roles = wp_get_current_user()->roles;

		if ( ! is_user_logged_in() ) {
			$roles[] = 'guest';
		}
		return $roles;

	}
	/**
	 * Checks if there is a Virtual/Featured product in cart and delivery is enable for these products or not
	 *
	 * @globals resource $woocommerce WooCommerce object
	 *
	 * @return string 'yes' if delivery is enabled, else 'no'.
	 *
	 * @since 2.7.6
	 */
	public static function orddd_is_delivery_enabled( $vendor_id = 0 ) {
		global $woocommerce;
		$delivery_enabled = 'yes';
		if ( self::orddd_get_option( 'orddd_no_fields_for_virtual_product', '', $vendor_id ) == 'on' && self::orddd_get_option( 'orddd_no_fields_for_featured_product', '', $vendor_id  ) == 'on' ) {
			if ( isset( $woocommerce->cart ) ) {
				foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
					$product_id = $values['product_id'];
					$_product   = wc_get_product( $product_id );
					if ( $_product->is_virtual() == false && $_product->is_featured() == false ) {
						$delivery_enabled = 'yes';
						break;
					} else {
						$delivery_enabled = 'no';
					}
				}
			}
		} elseif ( self::orddd_get_option( 'orddd_no_fields_for_virtual_product', '', $vendor_id  ) == 'on' && self::orddd_get_option( 'orddd_no_fields_for_featured_product', '', $vendor_id  ) != 'on' ) {
			if ( isset( $woocommerce->cart ) ) {
				foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
					$_product = $values['data'];
					if ( $_product->is_virtual() == false ) {
						$delivery_enabled = 'yes';
						break;
					} else {
						$delivery_enabled = 'no';
					}
				}
			}
		} elseif ( self::orddd_get_option( 'orddd_no_fields_for_virtual_product', '', $vendor_id  ) != 'on' && self::orddd_get_option( 'orddd_no_fields_for_featured_product', '', $vendor_id  ) == 'on' ) {
			if ( isset( $woocommerce->cart ) ) {
				foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
					$product_id = $values['product_id'];
					$_product   = wc_get_product( $product_id );
					if ( $_product->is_featured() == false ) {
						$delivery_enabled = 'yes';
						break;
					} else {
						$delivery_enabled = 'no';
					}
				}
			}
		} else {
			$delivery_enabled = 'yes';
		}
		return $delivery_enabled;
	}

	/**
	 * Returns the address key of the address package from shipping multiple addresses plugin
	 *
	 * @param array $package Shipping Address content
	 *
	 * @return string Address key
	 * @since 4.7
	 */

	public static function orddd_get_address_key( $package ) {
		$address_key = '';
		$user        = wp_get_current_user();
		if ( $user->ID != 0 ) {
			$addresses = get_user_meta( $user->ID, 'wc_other_addresses', true );
			if ( ! $addresses ) {
				$addresses = array();
			}

			$default_address = array(
				'first_name'          => get_user_meta( $user->ID, 'shipping_first_name', true ),
				'last_name'           => get_user_meta( $user->ID, 'shipping_last_name', true ),
				'company'             => get_user_meta( $user->ID, 'shipping_company', true ),
				'address_1'           => get_user_meta( $user->ID, 'shipping_address_1', true ),
				'address_2'           => get_user_meta( $user->ID, 'shipping_address_2', true ),
				'city'                => get_user_meta( $user->ID, 'shipping_city', true ),
				'state'               => get_user_meta( $user->ID, 'shipping_state', true ),
				'postcode'            => get_user_meta( $user->ID, 'shipping_postcode', true ),
				'country'             => get_user_meta( $user->ID, 'shipping_country', true ),
				'shipping_first_name' => get_user_meta( $user->ID, 'shipping_first_name', true ),
				'shipping_last_name'  => get_user_meta( $user->ID, 'shipping_last_name', true ),
				'shipping_company'    => get_user_meta( $user->ID, 'shipping_company', true ),
				'shipping_address_1'  => get_user_meta( $user->ID, 'shipping_address_1', true ),
				'shipping_address_2'  => get_user_meta( $user->ID, 'shipping_address_2', true ),
				'shipping_city'       => get_user_meta( $user->ID, 'shipping_city', true ),
				'shipping_state'      => get_user_meta( $user->ID, 'shipping_state', true ),
				'shipping_postcode'   => get_user_meta( $user->ID, 'shipping_postcode', true ),
				'shipping_country'    => get_user_meta( $user->ID, 'shipping_country', true ),
				'default_address'     => true,
			);
		} else {
			// guest address - using sessions to store the address
			$addresses = ( wcms_session_isset( 'user_addresses' ) ) ? wcms_session_get( 'user_addresses' ) : array();
		}

		$full_address = wcms_get_address( $package['destination'] );
		foreach ( $addresses as $key => $value ) {
			if ( $value['shipping_first_name'] == $full_address['first_name'] && $value['shipping_last_name'] == $full_address['last_name'] &&
				$value['shipping_company'] == $full_address['company'] && $value['shipping_address_1'] == $full_address['address_1'] &&
				$value['shipping_address_2'] == $full_address['address_2'] && $value['shipping_city'] == $full_address['city'] &&
				$value['shipping_state'] == $full_address['state'] && $value['shipping_postcode'] == $full_address['postcode'] &&
				$value['shipping_country'] == $full_address['country'] ) {
					$address_key = $key;
			}
		}
		return $address_key;
	}

	/**
	 * Returns the addresses stored in session from shipping multiple addresses plugin
	 *
	 * @return bool True if addresses are present in session, else false.
	 *
	 * @since 4.7
	 */

	public static function get_wcms_session() {
		$sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
		if ( $sess_cart_addresses && ! empty( $sess_cart_addresses ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns the total number of orders to be exported to the google calendar
	 *
	 * @globals resource $wpdb WordPress object
	 *
	 * @return array Total orders to export
	 *
	 * @since 4.0
	 */

	public static function orddd_get_total_orders_to_export() {
		$total_orders_to_export = array();
		$event_orders           = get_option( 'orddd_event_order_ids' );
		if ( $event_orders == '' || $event_orders == '{}' || $event_orders == '[]' || $event_orders == 'null' ) {
			$event_orders = array();
		}

		$results = self::orddd_get_all_future_orders();
		foreach ( $results as $key => $value ) {
			if ( ! in_array( $value->ID, $event_orders ) ) {
				$total_orders_to_export[] = $value->ID;
			}
		}
		return $total_orders_to_export;
	}


	public static function orddd_delete_exported_events() {
		$event_orders = get_option( 'orddd_event_order_ids', true );

		if ( is_array( $event_orders ) && count( $event_orders ) > 0 ) {
			foreach ( $event_orders as $key => $value ) {
				// code...
				$gcal = new OrdddGcal();
				$gcal->delete_event( $value );

			}
		}

		update_option( 'orddd_event_order_ids', '' );

	}
	/**
	 * Return all orders with future deliveries
	 *
	 * @return array All order with future deliveries.
	 * @since 8.6
	 */

	public static function orddd_get_all_future_orders( $exclude_status = array( 'wc-on-hold', 'wc-cancelled', 'wc-failed', 'wc-refunded', 'trash' ), $date = '' ) {
		global $wpdb;

		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time = current_time( 'timestamp', $gmt );
		$current_date = gmdate( 'Y-m-d', $current_time );
		$current_time = strtotime( $current_date );

		$exclude_status = apply_filters( 'orddd_exclude_future_order_status', $exclude_status );
		$status_str     = implode( "', '", $exclude_status );

		$vendor_id = orddd_common::orddd_get_vendor_id();
		if ( $vendor_id ) {
			$order_meta_key = '_orddd_timestamp_' . $vendor_id;
		} else {
			$order_meta_key = '_orddd_timestamp';
		}

		$order_table      = 'posts';
		$order_meta_table = 'postmeta';
		$post_type        = 'post_type';
		$post_status      = 'post_status';
		$post_id          = 'post_id';
		$id               = 'ID';
		$date_created_gmt = 'post_date_gmt';
		
		if ( ORDDD_Functions::is_hpos_enabled() ) {
			$order_table      = 'wc_orders';
			$order_meta_table = 'wc_orders_meta';
			$post_type        = 'type';
			$post_status      = 'status';
			$post_id          = 'order_id';
			$id               = 'id';
			$date_created_gmt = 'date_created_gmt';
		}

		$orddd_query = 'SELECT wp.' . $id . ' as ID, wp.' . $post_status . ' as post_status, wp.' . $date_created_gmt . ' as date_created
		FROM `' . $wpdb->prefix . $order_table . '` wp
		INNER JOIN `' . $wpdb->prefix . $order_meta_table . "` wpm ON ( wpm.{$post_id} = wp.{$id} AND wpm.meta_key = '{$order_meta_key}' AND wpm.meta_value >= '{$current_time}' )";
		if ( is_admin() && isset( $_GET['page'] ) && 'order_delivery_date' === $_GET['page'] ) {
			$orddd_query = 'SELECT wp.' . $id . ' as ID, wp.' . $post_status . ' as post_status
			FROM `' . $wpdb->prefix . $order_table . '` wp
			INNER JOIN `' . $wpdb->prefix . $order_meta_table . "` wpm ON ( wpm.{$post_id} = wp.{$id} AND wpm.meta_key like '{$order_meta_key}%' AND wpm.meta_value >= '{$current_time}' )";
		}
		$orddd_query = apply_filters( 'reminder_join_filter', $orddd_query );

		if ( '' !== $date ) {
			$orddd_query .= " WHERE {$post_type} = 'shop_order' AND {$date_created_gmt} >= '" . $date . "' AND {$post_status} NOT IN ( '". $status_str . "' ) ORDER BY wp." . $id . " DESC";
		} else {
			$orddd_query .= " WHERE {$post_type} = 'shop_order' AND {$post_status} NOT IN ( '". $status_str . "' ) ORDER BY wp." . $id . " DESC";
		}
		
		$orddd_query  = apply_filters( 'reminder_where_filter', $orddd_query );

		$results = $wpdb->get_results( $orddd_query ); //phpcs:ignore

		return $results;
	}

	/**
	 * Return all orders with past deliveries
	 *
	 * @return array All order with past deliveries.
	 * @since 11.2.0
	 */
	public static function orddd_get_all_past_orders( $exclude_status = array( 'wc-on-hold', 'wc-cancelled', 'wc-failed', 'wc-refunded', 'trash' ), $date = '' ) {
		global $wpdb;

		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time = current_time( 'timestamp', $gmt );
		$current_date = gmdate( 'Y-m-d H:i:s', $current_time );
		$current_time = strtotime( $current_date );

		$exclude_status = apply_filters( 'orddd_exclude_future_order_status', $exclude_status );
		$status_str     = implode( "', '", $exclude_status );

		$vendor_id = orddd_common::orddd_get_vendor_id();
		if ( $vendor_id ) {
			$order_meta_key = '_orddd_timestamp_' . $vendor_id;
		} else {
			$order_meta_key = '_orddd_timestamp';
		}

		$order_table      = 'posts';
		$order_meta_table = 'postmeta';
		$post_type        = 'post_type';
		$post_status      = 'post_status';
		$post_id          = 'post_id';
		$id               = 'ID';
		$date_created_gmt = 'post_date_gmt';

		
		if ( ORDDD_Functions::is_hpos_enabled() ) {
			$order_table      = 'wc_orders';
			$order_meta_table = 'wc_orders_meta';
			$post_type        = 'type';
			$post_status      = 'status';
			$post_id          = 'order_id';
			$id               = 'id';
			$date_created_gmt = 'date_created_gmt';
		}

		$orddd_query = 'SELECT wp.' . $id . ' as ID, wp.' . $post_status . ' as post_status, wp.' . $date_created_gmt . ' as date_created
		FROM `' . $wpdb->prefix . $order_table . '` wp
		INNER JOIN `' . $wpdb->prefix . $order_meta_table . "` wpm ON ( wpm.{$post_id} = wp.{$id} AND wpm.meta_key = '{$order_meta_key}' AND wpm.meta_value < '{$current_time}' )";
		if ( is_admin() && isset( $_GET['page'] ) && 'order_delivery_date' === $_GET['page'] ) {
			$orddd_query = 'SELECT wp.' . $id . ' as ID, wp.' . $post_status . ' as post_status
			FROM `' . $wpdb->prefix . $order_table . '` wp
			INNER JOIN `' . $wpdb->prefix . $order_meta_table . "` wpm ON ( wpm.{$post_id} = wp.{$id} AND wpm.meta_key like '{$order_meta_key}%' AND wpm.meta_value < '{$current_time}' )";
		}
		$orddd_query = apply_filters( 'reminder_join_filter', $orddd_query );
		if ( '' !== $date ) {
			$orddd_query .= " WHERE wp.{$post_type} = 'shop_order' AND wp.{$date_created_gmt} >= '" . $date . "' AND wp.{$post_status} NOT IN ( '". $status_str . "' ) ORDER BY wp." . $id . " DESC";
		} else {
			$orddd_query .= " WHERE wp.{$post_type} = 'shop_order' AND wp.{$post_status} NOT IN ( '". $status_str . "' )";
		}
		
		$orddd_query  = apply_filters( 'reminder_where_filter', $orddd_query );

		$results = $wpdb->get_results( $orddd_query ); //phpcs:ignore

		return $results;
	}

	/**
	 * Returns the total product quantities added to the cart
	 *
	 * @globals resource $woocommerce WooCommerce object
	 *
	 * @return array Total product quantities
	 *
	 * @since 4.1
	 */
	public static function orddd_get_total_product_quantities( $called_from = '', $order_id = '', $vendor_id = 0, $vendors = array() ) {
		global $woocommerce;
		$product_quantities = 0;
		if ( get_option( 'orddd_lockout_date_quantity_based' ) == 'on' ) {
			if ( is_admin() || $called_from || ( '' !== $order_id ) ) {

				if ( '' === $order_id && isset( $_POST['orddd_order_id'] ) ) {
					$order_id = $_POST['orddd_order_id']; //phpcs:ignore
				}
				$order = new WC_Order( $order_id );
				$items = $order->get_items();
				foreach ( $items as $key => $value ) {
					$product_id = $value['product_id'];
					$product    = wc_get_product( $product_id );

					if ( ( 'on' === get_option( 'orddd_no_fields_for_virtual_product' ) && $product->is_virtual() ) || ( 'on' === get_option( 'orddd_no_fields_for_featured_product' ) && $product->is_featured() ) ) {
						continue;
					}

					if ( isset( $value['qty'] ) ) {
						$product_quantities += $value['qty'];
					}
				} 
			} else {
				foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
					$product_id = $values['product_id'];
					$product    = wc_get_product( $product_id );

					if ( ( 'on' === get_option( 'orddd_no_fields_for_virtual_product' ) && $product->is_virtual() ) || ( 'on' === get_option( 'orddd_no_fields_for_featured_product' ) && $product->is_featured() ) ) {
						continue;
					}
					$do_count = true;
					$product_vendor_id = get_post_field( 'post_author', $product_id );
					$user              = get_userdata( $product_vendor_id );
					$user_roles        = $user->roles;
					if ( count( $vendors ) > 1 && ! ( ( (int) $vendor_id === (int) $values['data']->post->post_author ) || ( 0 == $vendor_id && in_array( 'administrator', $user_roles, true ) ) ) ) {
							$do_count = false;
					}

					if ( isset( $values['quantity'] ) && $do_count ) {
						$product_quantities += $values['quantity'];
					}
				}
			}
		} else {
			$product_quantities = 1;
		}
		return $product_quantities;
	}

	/**
	 * Check the quantity of individual categories.
	 *
	 * @param array  $categories_array Categories from custom settings.
	 * @param string $called_from from where the function is called.
	 * @param string $order_id Order ID.
	 * @return int
	 */
	public static function orddd_get_total_quantities_for_categories( $categories_array, $type, $called_from = '', $order_id = '' ) {
		global $woocommerce;
		$product_quantities = 0;

		if ( is_admin() || $called_from ) {
			if ( '' === $order_id && isset( $_POST['orddd_order_id'] ) ) {
				$order_id = $_POST['orddd_order_id'];//phpcs:ignore
			}
			$order = new WC_Order( $order_id );
			$items = $order->get_items();
			foreach ( $items as $key => $value ) {
				$product_id = $value['product_id'];
				$product    = wc_get_product( $product_id );

				if ( ( 'on' === get_option( 'orddd_no_fields_for_virtual_product' ) && $product->is_virtual() ) || ( 'on' === get_option( 'orddd_no_fields_for_featured_product' ) && $product->is_featured() ) ) {
					continue;
				}

				$terms = get_the_terms( $product_id, $type );

				if ( is_array( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term => $val ) {
						$cat_slug = self::ordd_get_cat_slug( $val->term_id );

						if ( in_array( $cat_slug, $categories_array ) ) {

							if ( isset( $value['quantity'] ) ) {
								$product_quantities += $value['quantity'];
							}
						}
					}
				}
			}
		} else {
			if ( isset( $woocommerce->cart ) ) {
				foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
					$product_id = $values['product_id'];
					$product    = wc_get_product( $product_id );

					if ( ( 'on' === get_option( 'orddd_no_fields_for_virtual_product' ) && $product->is_virtual() ) || ( 'on' === get_option( 'orddd_no_fields_for_featured_product' ) && $product->is_featured() ) ) {
						continue;
					}

					$terms = get_the_terms( $product_id, $type );

					if ( is_array( $terms ) && ! empty( $terms ) ) {
						foreach ( $terms as $term => $val ) {
							$cat_slug = self::ordd_get_cat_slug( $val->term_id );

							if ( in_array( $cat_slug, $categories_array ) ) {
								if ( isset( $values['quantity'] ) ) {
									$product_quantities += $values['quantity'];
								}
							}
						}
					}
				}
			}
		}

		return $product_quantities;
	}

	/**
	 * Checks if the settings for shipping methods are added in custom delivery settings
	 *
	 * @globals resource $wpdb WordPress object
	 *
	 * @return string 'yes' if settings are added, else no.
	 *
	 * @since 3.0
	 */
	public static function orddd_get_shipping_method_enabled() {

		$results          = self::orddd_get_shipping_settings();
		$shipping_enabled = '';
		foreach ( $results as $key => $value ) {
			$var               = $var_time = $delivery_dates_str = $setting_to_load_value = '';
			$shipping_settings = get_option( $value->option_name );
			if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) &&
				$shipping_settings['delivery_settings_based_on'][0] == 'shipping_methods' ) {
				$shipping_enabled = 'yes';
			}
		}
		return $shipping_enabled;
	}

	/**
	 * Convert php date formats mysql date formats
	 *
	 * @return string Mysql date format
	 *
	 * @since 4.0
	 */

	public static function str_to_date_format() {
		$date_format = get_option( 'orddd_delivery_date_format' );
		switch ( $date_format ) {
			case 'mm/dd/y':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'dd/mm/y':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'dd/mm/yy':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'y/mm/dd':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'mm/dd/y, D':
				$day_str   = str_replace( 'D', '%a', $date_format );
				$date_str  = str_replace( 'dd', '%d', $day_str );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'dd.mm.y':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'y.mm.dd':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'yy-mm-dd':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'dd-mm-y':
				$date_str  = str_replace( 'dd', '%d', $date_format );
				$month_str = str_replace( 'mm', '%m', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'd M, y':
				$date_str  = str_replace( 'd', '%e', $date_format );
				$month_str = str_replace( 'M', '%b', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'd M, yy':
				$date_str  = str_replace( 'd', '%e', $date_format );
				$month_str = str_replace( 'M', '%b', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'd MM, y':
				$date_str  = str_replace( 'd', '%e', $date_format );
				$month_str = str_replace( 'MM', '%M', $date_str );
				$year_str  = str_replace( 'y', '%y', $month_str );
				break;
			case 'd MM, yy':
				$date_str  = str_replace( 'd', '%e', $date_format );
				$month_str = str_replace( 'MM', '%M', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'DD, d MM, yy':
				$day_str   = str_replace( 'DD', '%W', $date_format );
				$date_str  = str_replace( 'd', '%e', $day_str );
				$month_str = str_replace( 'MM', '%M', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'DD d MM yy':
				$day_str   = str_replace( 'DD', '%W', $date_format );
				$date_str  = str_replace( 'd', '%e', $day_str );
				$month_str = str_replace( 'MM', '%M', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'DD d MM':
				$day_str   = str_replace( 'DD', '%W', $date_format );
				$date_str  = str_replace( 'd', '%e', $day_str );
				$month_str = str_replace( 'MM', '%M', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'D, M d, yy':
				$day_str   = str_replace( 'D', '%a', $date_format );
				$date_str  = str_replace( 'd', '%e', $day_str );
				$month_str = str_replace( 'M', '%b', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'DD, M d, yy':
				$day_str   = str_replace( 'DD', '%W', $date_format );
				$date_str  = str_replace( 'd', '%e', $day_str );
				$month_str = str_replace( 'M', '%b', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'DD, MM d, yy':
				$day_str   = str_replace( 'DD', '%W', $date_format );
				$date_str  = str_replace( 'd', '%e', $day_str );
				$month_str = str_replace( 'MM', '%M', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
			case 'D, MM d, yy':
				$day_str   = str_replace( 'D', '%a', $date_format );
				$date_str  = str_replace( 'd', '%e', $day_str );
				$month_str = str_replace( 'MM', '%M', $date_str );
				$year_str  = str_replace( 'yy', '%Y', $month_str );
				break;
		}
		return $year_str;
	}

	/**
	 * Return formatted shipping custom address
	 *
	 * @param int $user_id User ID
	 * @return string Formatted address
	 *
	 * @since 4.7
	 */

	public static function orddd_get_formatted_shipping_customer_address( $user_id ) {
		$address  = '';
		$address .= get_user_meta( $user_id, 'shipping_first_name', true );
		$address .= ' ';
		$address .= get_user_meta( $user_id, 'shipping_last_name', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_company', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_address_1', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_address_2', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_city', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_state', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_postcode', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_country', true );
		return $address;
	}

	/**
	 * Return the time slots in array format for the custom delivery settings
	 *
	 * @param string $time_slot String for the time slots added for the custom delivery settings
	 * @return array
	 *
	 * @since 2.7
	 */
	public static function get_timeslot_values( $time_slot ) {
		$time_slot_values   = array();
		$allpos             = array();
		$delivery_days_arr  = array();
		$time_slot_selected = '';
		$offset             = 0;
		$allpos             = array();
		$time_slot_str      = str_replace( '}', '', $time_slot );
		$time_slot_str      = str_replace( '{', '', $time_slot_str );
		$time_slot_arr      = array();

		if ( '' !== $time_slot ) {
			$time_slot_arr = explode( ':', $time_slot_str );
		}

		if ( is_array( $time_slot_arr ) && count( $time_slot_arr ) > 0 ) {
			foreach ( $time_slot_arr as $key => $value ) {
				switch ( $key ) {
					case 0:
						$time_slot_values['delivery_days_selected'] = $value;
						break;
					case 1:
						$time_slot_values['selected_days'] = explode( ',', $value );
						break;
					case 2:
						if ( empty( $time_slot_arr[4] ) ) {
							$time_slot_values['time_slot'] =  $value . ':' . trim( $time_slot_arr[3], ' -' );
						} else {
							$time_slot_values['time_slot'] = implode( ':', array( $value, $time_slot_arr[3], $time_slot_arr[4] ) );
						}
						break;
					case 5:
						$time_slot_values['lockout'] = $value;
						break;
					case 6:
						$time_slot_values['additional_charges'] = $value;
						break;
					case 7:
						if ( is_array( $time_slot_arr ) && count( $time_slot_arr ) > 8 ) {
							$time_slot_values['charges_type']             = $value;
							$time_slot_values['additional_charges_label'] = $time_slot_arr[8];
						} else {
							$time_slot_values['charges_type']             = '';
							$time_slot_values['additional_charges_label'] = $value;
						}
						break;
					default:
						break;
				}
			}
		}

		if ( empty( $time_slot_values ) ) {
			$time_slot_values = array(
				'delivery_days_selected'   => '',
				'selected_days'            => array(),
				'time_slot'                => '',
				'lockout'                  => '',
				'additional_charges'       => '',
				'charges_type'             => '',
				'additional_charges_label' => '',
			);
		}

		return $time_slot_values;
	}

	/**
	 * Return all the time slots added for a day/date
	 *
	 * @globals resource $wpdb WordPress object
	 *
	 * @param string $current_date Selected date
	 * @param int    $dw Weekday of current selected date
	 * @param string $delivery_date Selected date in n-j-Y format
	 *
	 * @return array
	 *
	 * @since 2.7
	 */
	public static function orddd_get_timeslots( $current_date, $dw, $delivery_date, $custom_settings, $vendor_id = 0 ) {
		global $wpdb;

		$arr1               = array();
		$time_slots         = array();
		$all_time_slots     = array();
		$delivery_dates_str = '';
		$shipping_based     = 'No';
		$min_lockout        = 0;

		$time_format_for_lockout = 'H:i';
		$time_format_to_show     = self::orddd_get_time_format();

		if ( is_array( $custom_settings ) && count( $custom_settings ) > 0 ) {
			$shipping_based = 'Yes';
		}

		if ( 'Yes' === $shipping_based ) {
			// If count of $custom_settings variable is greater than 1, it means that combination of custom settings isthere.
			if ( is_array( $custom_settings ) && count( $custom_settings ) > 1 ) {
				foreach ( $custom_settings as $skey => $sval ) {
					$time_slot_arr = explode( '},', $sval['time_slots'] );

					foreach ( $time_slot_arr as $tsk => $tsv ) {
						if ( '' !== $tsv ) {
							$timeslot_values = self::get_timeslot_values( $tsv );
							// Fetch all the time slots for the selected weekday or the date for all the combination of settings.
							if ( isset( $timeslot_values['selected_days'] ) && ( in_array( 'orddd_weekday_' . $dw . '_custom_setting', $timeslot_values['selected_days'] ) || in_array( $delivery_date, $timeslot_values['selected_days'] ) ) ) {
								$all_time_slots[] = $timeslot_values;
							}
						}
					}
				}

				$cnt = array_count_values( array_column( $all_time_slots, 'time_slot' ) );
				// loop over existing array.
				foreach ( $all_time_slots as $k => $v ) {
						// if the count for this value is more than 1 (meaning value has a duplicate).
					if ( $cnt[ $v['time_slot'] ] >= count( $custom_settings ) ) {
						// Fetch the minimum lockout of the time slots.
						if ( $min_lockout > $v['lockout'] ) {
							$min_lockout = $v['lockout'];
						}
						$time_slots[ $k ] = $v;
					}
				}
			} elseif ( is_array( $custom_settings ) && count( $custom_settings ) == 1 ) {
				foreach ( $custom_settings as $skey => $sval ) {
					if ( isset( $sval['time_slots'] ) ) {
						$timeslots = explode( '},', $sval['time_slots'] );
						foreach ( $timeslots as $tsk => $tsv ) {
							if ( '' !== $tsv ) {
								$timeslot_values = self::get_timeslot_values( $tsv );
								$time_slot_arr   = explode( ' - ', $timeslot_values['time_slot'] );
								$time_slots[]    = $timeslot_values;
							}
						}
					}
				}
			}

			if ( is_array( $time_slots ) && count( $time_slots ) > 0 ) {
				foreach ( $time_slots as $tk => $tv ) {
					$time_slot_arr = explode( ' - ', $tv['time_slot'] );
					$from_time     = gmdate( $time_format_for_lockout, strtotime( $time_slot_arr[0] ) );
					if ( isset( $time_slot_arr[1] ) && ':' !== $time_slot_arr[1] ) {
						$to_time       = gmdate( $time_format_for_lockout, strtotime( $time_slot_arr[1] ) );
						$time_slot_val = $from_time . ' - ' . $to_time;
					} else {
						$time_slot_val = $from_time;
					}

					if ( is_array( $tv['selected_days'] ) ) {
						foreach ( $tv['selected_days'] as $dkey => $dval ) {
							if ( $min_lockout != 0 ) {
								$arr1[ $dval ][ $time_slot_val ] = $min_lockout;
							} else {
								$arr1[ $dval ][ $time_slot_val ] = $tv['lockout'];
							}
						}
					}
				}
			}
		}

		if ( 'No' == $shipping_based ) {
			$delivery_dates_arr = $temp_arr = array();
			$existing_timeslots_arr = ORDDD_Functions::orddd_get_timeslots( $vendor_id );
			$delivery_dates_str = self::orddd_get_option( 'orddd_delivery_dates', '', $vendor_id );
			if ( $delivery_dates_str != '' && $delivery_dates_str != '{}' && $delivery_dates_str != '[]' && $delivery_dates_str != 'null' ) {
				$delivery_dates_arr = json_decode( self::orddd_get_option( 'orddd_delivery_dates', '', $vendor_id ) );
			}

			foreach ( $delivery_dates_arr as $key => $value ) {
				foreach ( $value as $k => $v ) {
					if ( $k == 'date' ) {
						$temp_arr[] = $v;
					}
				}
			}

			if ( isset( $existing_timeslots_arr ) ) {
				foreach ( $existing_timeslots_arr as $k => $v ) {
					$from_time = $v->fh . ':' . $v->fm;
					// $ft = gmdate( $time_format_to_show, strtotime( $from_time ) );
					$ft = gmdate( $time_format_for_lockout, strtotime( $from_time ) );
					if ( ! ( $v->th == '00' && '00' === $v->tm ) && '' !== $v->th ) {
						$to_time = $v->th . ':' . $v->tm;
						// $tt = gmdate( $time_format_to_show, strtotime( $to_time ) );
						$tt  = gmdate( $time_format_for_lockout, strtotime( $to_time ) );
						$key = $ft . ' - ' . $tt;
					} elseif ( '00' === $v->th && '00' === $v->tm  ) {
						$tt = '00:00';
						if ( '1' === $time_format_for_lockout ) {
							$tt = '12:00 AM';
						}
	                    $key = $ft . " - " . $tt; // phpcs:ignore
					} else {
						$key = $ft;
					}
					if ( gettype( json_decode( $v->dd ) ) == 'array' &&
						 count( json_decode( $v->dd ) ) > 0 &&
						 self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) == 'on' ) {

						$dd = json_decode( $v->dd );
						if ( is_array( $dd ) && count( $dd ) > 0 ) {
							foreach ( $dd as $dkey => $dval ) {
								$arr1[ $dval ][ $key ] = $v->lockout;
							}
						}
					} else {
						if ( gettype( json_decode( $v->dd ) ) == 'array' && count( json_decode( $v->dd ) ) > 0 ) {
							$dd = json_decode( $v->dd );
							foreach ( $dd as $dkey => $dval ) {
								if ( $dval == 'orddd_weekday_' . $dw || $dval == 'all' ) {
									$arr1[ $dval ][ $key ] = $v->lockout;
								}
							}
						} else {
							if ( $v->dd == 'orddd_weekday_' . $dw || $v->dd == 'all' ) {
								$arr1[ $v->dd ][ $key ] = $v->lockout;
							}
						}
					}
				}
			}
		}

		return $arr1;
	}

	/**
	 * Compare strings.
	 *
	 * @param array $val1 Array 1 value to compare
	 * @param array $val2 Array 2 value to compare
	 *
	 * @return string Compared value
	 * @since 7.6
	 *
	 * @todo Unused function need to check and remove it
	 */
	public static function orddd_compareDeepValue( $val1, $val2 ) {
		return strcmp( $val1['time_slot'], $val2['time_slot'] );
	}

	/**
	 * Return time slot charges added for a time slot
	 *
	 * @globals resource $wpdb WordPress object
	 *
	 * @param string $time_slot Time slot
	 *
	 * @return string
	 *
	 * @since 4.3
	 */

	public static function orddd_get_timeslot_charges( $setting_id, $time_slot, $current_date = false, $vendor_id = 0, $common_ids = array() ) {
		$timeslot_charges      = 0;
		$timeslot_charges_type = 'fixed';

		$shipping_based              = 'No';
		$currency_symbol             = get_woocommerce_currency_symbol();
		$time_format_to_show         = self::orddd_get_time_format();
		$time_slot_charges_lable_str = '';

		$custom_setting_present = false;
		if ( is_array( $common_ids ) && count( $common_ids ) > 1 ) {
			foreach ( $common_ids as $id ) {				
				if ( 0 != $id ) {
					$option_name                             = 'orddd_shipping_based_settings_' . $id;
					$custom_settings                         = orddd_custom_delivery_functions::orddd_get_delivery_schedule_settings_by_id( $id );
					$custom_setting_present                  = true;
				}
			}
		} else {
			if ( 0 != $setting_id ) {
				$option_name            = 'orddd_shipping_based_settings_' . $setting_id;
				$custom_settings        = orddd_custom_delivery_functions::orddd_get_delivery_schedule_settings_by_id( $setting_id, $vendor_id );
				$custom_setting_present = true;
			}
		}

		// TODO: the below foreach loop could be removed & instead we can only fetch the settings for this particular time slot. The foreach loop running for every time slot
		if ( $custom_setting_present ) {
			if ( isset( $custom_settings['time_slots'] ) ) {
				$time_slots = explode( '},', $custom_settings['time_slots'] );
				foreach ( $time_slots as $tk => $tv ) {
					if ( $tv != '' ) {
						$timeslot_values = self::get_timeslot_values( $tv );
						$time_slot_arr   = explode( ' - ', $timeslot_values['time_slot'] );

						$from_time = gmdate( $time_format_to_show, strtotime( $time_slot_arr[0] ) );
						if ( isset( $time_slot_arr[1] ) && ':' !== $time_slot_arr[1] ) {
							$to_time       = gmdate( $time_format_to_show, strtotime( $time_slot_arr[1] ) );
							$time_slot_val = $from_time . ' - ' . $to_time;
						} else {
							$time_slot_val = $from_time;
						}

						if ( $timeslot_values['delivery_days_selected'] == 'weekdays' ) {

							$weekday = gmdate( 'w', strtotime( $current_date ) );
							foreach ( $timeslot_values['selected_days'] as $key => $val ) {
								if ( $time_slot == $time_slot_val &&
									( $val == 'orddd_weekday_' . $weekday . '_custom_setting'
										|| $val == 'all' ) ) {
									$timeslot_charges = $timeslot_values['additional_charges'];

									if ( '' == $timeslot_values['additional_charges_label'] ) {
										$time_slot_charges_lable_str = 'Time Slot Charges';
									} else {
										$time_slot_charges_lable_str = $timeslot_values['additional_charges_label'];
									}
									$timeslot_charges_type = $timeslot_values['charges_type'];
								}
							}
						} elseif ( $timeslot_values['delivery_days_selected'] == 'specific_dates' ) {
							foreach ( $timeslot_values['selected_days'] as $key => $val ) {
								$specific_delivery_date = gmdate( 'n-j-Y', strtotime( $current_date ) );
								if ( $time_slot == $time_slot_val && $val == $specific_delivery_date ) {
									$timeslot_charges = $timeslot_values['additional_charges'];

									if ( '' == $timeslot_values['additional_charges_label'] ) {
										$time_slot_charges_lable_str = 'Time Slot Charges';
									} else {
										$time_slot_charges_lable_str = $timeslot_values['additional_charges_label'];
									}
									$timeslot_charges_type = $timeslot_values['charges_type'];
								}
							}
						}
					}
				}
			}
			if ( 'asap' === $time_slot ) {
				$timeslot_charges_type       = isset( $custom_settings['orddd_asap_charges_type'] ) ? $custom_settings['orddd_asap_charges_type'] : '';
				$timeslot_charges            = isset( $custom_settings['orddd_asap_charges'] ) ? $custom_settings['orddd_asap_charges'] : '';
				$time_slot_charges_lable_str = isset( $custom_settings['orddd_asap_charges_label'] ) && '' !== $custom_settings['orddd_asap_charges_label'] ? $custom_settings['orddd_asap_charges_label'] : 'Time Slot Charges';
			}
		} else {
			$existing_timeslots_arr = ORDDD_Functions::orddd_get_timeslots( $vendor_id );
			if ( isset( $existing_timeslots_arr ) ) {
				foreach ( $existing_timeslots_arr as $k => $v ) {
					$from_time = $v->fh . ':' . $v->fm;				

					$ft = gmdate( $time_format_to_show, strtotime( $from_time ) );
					if ( ! ( $v->th == '00' && '00' === $v->tm ) && '' !== $v->th ) {
						$to_time = $v->th . ':' . $v->tm;
						// $tt = gmdate( $time_format_to_show, strtotime( $to_time ) );
						$tt  = gmdate( $time_format_to_show, strtotime( $to_time ) );
						$key = $ft . ' - ' . $tt;
					} elseif ( '00' === $v->th && '00' === $v->tm  ) {
						$tt = '00:00';
						if ( '1' === $time_format_to_show ) {
							$tt = '12:00 AM';
						}
	                    $key = $ft . " - " . $tt; // phpcs:ignore
					} else {
						$key = $ft;
					}

					$date_to_check    = gmdate( 'n-j-Y', strtotime( $current_date ) );
					$current_weekday  = gmdate( 'w', strtotime( $current_date ) );
					$selected_weekday = 'orddd_weekday_' . $current_weekday;

					// Check for Multiple values of specific dates or weekdays and fetch the time slot charges
					// and labels for them.
					if ( gettype( json_decode( $v->dd ) ) == 'array' && count( json_decode( $v->dd ) ) > 0 ) {
						$dd = json_decode( $v->dd );
						if ( is_array( $dd ) && count( $dd ) > 0 ) {
							foreach ( $dd as $dkey => $dval ) {
								if ( $time_slot == $key &&
									( $dval == $date_to_check ||
										( $dval == $selected_weekday || $dval == 'all' )
									)
								) {
									$timeslot_charges = isset( $v->additional_charges ) ? $v->additional_charges : '';

									$timeslot_charges_type = isset( $v->charges_type ) ? $v->charges_type : '';

									if ( isset( $v->additional_charges_label ) && '' == $v->additional_charges_label ) {
										$time_slot_charges_lable_str = 'Time Slot Charges';
									} else {
										$time_slot_charges_lable_str = $v->additional_charges_label;
									}
								}
							}
						}
					} elseif ( $time_slot == $key && ( $v->dd == $selected_weekday || $v->dd == 'all' ) ) {
						$timeslot_charges = $v->additional_charges;
						if ( '' == $v->additional_charges_label ) {
							$time_slot_charges_lable_str = 'Time Slot Charges';
						} else {
							$time_slot_charges_lable_str = $v->additional_charges_label;
						}
						$timeslot_charges_type = $v->charges_type;
					}
				}
			}

			if ( 'asap' === $time_slot ) {

				$timeslot_charges_type       = self::orddd_get_option( 'orddd_asap_charges_type', '', $vendor_id );
				$timeslot_charges            = self::orddd_get_option( 'orddd_asap_charges', '', $vendor_id );
				$time_slot_charges_lable_str = self::orddd_get_option( 'orddd_asap_charges_label', '', $vendor_id );
				if ( '' === $time_slot_charges_lable_str ) {
					$time_slot_charges_lable_str = 'Time Slot Charges';
				}
			}
		}

		if ( get_option( 'orddd_enable_tax_calculation_for_delivery_charges' ) == 'on' && '' !== $timeslot_charges && 'yes' !== get_option( 'woocommerce_prices_include_tax' ) ) {
			$orddd_display_tax_in_timeslot_charges = apply_filters( 'orddd_display_tax_in_timeslot_charges', 'yes' );

			$orddd_calc_taxes = get_option( 'woocommerce_calc_taxes' );
			if ( isset( $orddd_calc_taxes ) && 'yes' == $orddd_calc_taxes && 'yes' == $orddd_display_tax_in_timeslot_charges && 'percentage' !== $timeslot_charges_type ) {
				$shipping_tax_class    = get_option( 'woocommerce_shipping_tax_class' );
				$orddd_wc_get_tax_rate = WC_Tax::get_rates();
				if ( isset( $shipping_tax_class ) && '' != $shipping_tax_class && 'inherit' !== $shipping_tax_class ) {
					$orddd_wc_get_tax_rate = WC_Tax::get_rates( $shipping_tax_class );
				}

				$orddd_recalculated_timeslot_tax = WC_Tax::calc_tax( $timeslot_charges, $orddd_wc_get_tax_rate );

				if ( is_array( $orddd_recalculated_timeslot_tax ) && count( $orddd_recalculated_timeslot_tax ) > 0 ) {
					foreach ( $orddd_recalculated_timeslot_tax as $tax_key => $tax_value ) {
						$timeslot_charges = (float) $timeslot_charges + $tax_value;
					}
				}
			}
		}

		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			if ( $WOOCS->is_multiple_allowed ) {
				$timeslot_charges = $WOOCS->woocs_exchange_value( floatval( $timeslot_charges ) );
			}
		}

		$timeslot_charges_str = '';
		if ( '' !== $timeslot_charges ) {

			if ( '' != $time_slot_charges_lable_str ) {
				$timeslot_charges_str = $time_slot_charges_lable_str;
			}

			$timeslot_charges_display_zero = apply_filters( 'orddd_charges_display_zero', false );

			if ( $timeslot_charges == 0 && !$timeslot_charges_display_zero ) {
				return $timeslot_charges_str;
			}
			if ( ',' === get_option( 'woocommerce_price_decimal_sep', '.' ) ) {
				$timeslot_charges = (float) str_replace( ',', '.', $timeslot_charges );
			}

			// Format the time slot charges as per the WooCommerce Decimal Seperator, Thousand Seperator and Number of Decimals.
			$timeslot_charges = number_format(
				$timeslot_charges,
				wc_get_price_decimals(),
				wc_get_price_decimal_separator(),
				wc_get_price_thousand_separator()
			);

			if ( 'percentage' !== $timeslot_charges_type ) {
				if ( in_array( get_option( 'woocommerce_currency_pos' ), array( 'right', 'right_space' ) ) ) {
					$timeslot_charges_str .= ': ' . $timeslot_charges . ' ' . $currency_symbol;
				} else {
					$timeslot_charges_str .= ': ' . $currency_symbol . '' . $timeslot_charges;
				}
			} elseif ( 'percentage' === $timeslot_charges_type ) {
				if ( strpos( $timeslot_charges, '.' ) !== false ) {
					$timeslot_charges = rtrim( rtrim( $timeslot_charges, '0' ), '.' );
				}
				$timeslot_charges_str .= ': ' . $timeslot_charges . '%';
			}
		}
		return $timeslot_charges_str;
	}

	/**
	 * Return the number of orders placed for a time slot for a date
	 *
	 * @globals resource $wpdb WordPress object
	 *
	 * @return array
	 *
	 * @since 2.7
	 */
	public static function orddd_get_timeslot_lockout( $shipping_settings_to_check, $vendor_id = 0 ) {
		global $wpdb;
		$arr2                = array();
		$shipping_based      = 'No';
		$time_format_to_show = self::orddd_get_time_format();

		if ( is_array( $shipping_settings_to_check ) && count( $shipping_settings_to_check ) > 0 ) {
			$shipping_based = 'Yes';
		}

		if ( 'Yes' == $shipping_based ) {
			$lockout_time_arr = array();
			foreach ( $shipping_settings_to_check as $skey => $sval ) {
				if ( isset( $sval['orddd_lockout_time_slot'] ) ) {
					$lockout_time = $sval['orddd_lockout_time_slot'];
					if ( $lockout_time != '' && $lockout_time != '{}' &&
						$lockout_time != '[]' && $lockout_time != 'null' ) {

						$lockout_array = json_decode( $lockout_time, true );
						foreach ( $lockout_array as $lkey => $lval ) {
							$lockout_time_arr[] = $lval;
						}
					}
				}
			}

			if ( is_array( $lockout_time_arr ) && count( $lockout_time_arr ) > 0 ) {
				foreach ( $lockout_time_arr as $k => $v ) {

					$v['t'] = self::orddd_change_time_slot_format( $v['t'], $time_format_to_show );
					if ( isset( $arr2[ $v['d'] ][ $v['t'] ] ) ) {
						$arr2[ $v['d'] ][ $v['t'] ] = $arr2[ $v['d'] ][ $v['t'] ] + $v['o'];
					} else {
						$arr2[ $v['d'] ][ $v['t'] ] = $v['o'];
					}
				}
			}
		}

		if ( 'No' == $shipping_based ) {
			$lockout_time     = self::orddd_get_option( 'orddd_lockout_time_slot', '', $vendor_id );
			$lockout_time_arr = json_decode( $lockout_time, true );
			if ( is_array( $lockout_time_arr ) && count( $lockout_time_arr ) > 0 ) {
				foreach ( $lockout_time_arr as $k => $v ) {
					// add the timeslot in the array with the set time format.
					$v['t']                     = self::orddd_change_time_slot_format( $v['t'], $time_format_to_show );
					$arr2[ $v['d'] ][ $v['t'] ] = $v['o'];
				}
			}
		}
		return $arr2;
	}

	/**
	 * Return all the time slots that are blocked or disabled from the admin interface
	 *
	 * @return array
	 *
	 * @since 4.7
	 */

	public static function orddd_get_disabled_timeslot( $vendor_id = 0 ) {
		$disable_days = array();

		$time_format_to_show = self::orddd_get_time_format();

		$existing_timeslots_str = self::orddd_get_option( 'orddd_disable_time_slot_log', '', $vendor_id );
		$existing_timeslots_arr = array();
		if ( 'null' === $existing_timeslots_str || '' === $existing_timeslots_str || '{}' === $existing_timeslots_str || '[]' === $existing_timeslots_str ) {
			$existing_timeslots_arr = array();
		} else {
			$existing_timeslots_arr = json_decode( $existing_timeslots_str );
		}

		if ( is_array( $existing_timeslots_arr ) && count( $existing_timeslots_arr ) > 0 ) {
			foreach ( $existing_timeslots_arr as $k => $v ) {
				if ( isset( $v->dtv ) && $v->dtv == 'dates' ) {
					$date_explode  = explode( '-', $v->dd );
					$date_to_check = gmdate( 'n-j-Y', gmmktime( 0, 0, 0, $date_explode[0], $date_explode[1], $date_explode[2] ) );
				} else {
					$date_to_check = $v->dd;
				}
				$time_slots = json_decode( $v->ts );
				foreach ( $time_slots as $time_key => $time_value ) {
					$time_slot_arr = explode( ' - ', $time_value );
					$from_time     = gmdate( $time_format_to_show, strtotime( $time_slot_arr[0] ) );
					if ( isset( $time_slot_arr[1] ) ) {
						$to_time       = gmdate( $time_format_to_show, strtotime( $time_slot_arr[1] ) );
						$time_slot_val = $from_time . ' - ' . $to_time;
					} else {
						$time_slot_val = $from_time;
					}
					if ( 'asap' === $time_value || 'As Soon As Possible.' === $time_value ) {
						$time_slot_val = $time_value;
					}
					$disable_days[ $date_to_check ][] = $time_slot_val;
				}
			}
		}
		return $disable_days;
	}

	/**
	 * Return all the time slots disabled for a date/weekday
	 *
	 * @param int    $dw Weekday of the selected delivery date
	 * @param string $delivery_date Selected delivery date
	 * @param array  $disable_days All the disabled time slots
	 *
	 * @return array
	 * @since 4.7
	 */
	public static function get_timeslot_to_disable( $dw, $delivery_date, $disable_days ) {
		$time_slots_to_disable = array();
		if ( array_key_exists( 'orddd_weekday_' . $dw, $disable_days ) ) {
			foreach ( $disable_days[ 'orddd_weekday_' . $dw ] as $dw_key => $dw_value ) {
				$time_slots_to_disable[] = $dw_value;
			}
		}

		if ( array_key_exists( 'all', $disable_days ) ) {
			foreach ( $disable_days['all'] as $all_key => $all_value ) {
				$time_slots_to_disable[] = $all_value;
			}
		}

		if ( array_key_exists( $delivery_date, $disable_days ) ) {
			foreach ( $disable_days[ $delivery_date ] as $date_key => $date_value ) {
				$time_slots_to_disable[] = $date_value;
			}
		}
		return $time_slots_to_disable;
	}

	/**
	 * Return the minimum delivery date & time
	 *
	 * @globals array $orddd_shipping_days Shipping weekdays array
	 * @globals array $orddd_weekdays Weekdays array
	 *
	 * @param int   $delivery_time_seconds Minimum delivery time in seconds
	 * @param array $delivery_time_enabled Time slider settings
	 * @param str   $holidays_str Added holidays
	 * @param str   $lockout_str Booked days
	 * @param array $shipping_settings Custom delivery settings
	 *
	 * @return array
	 * @since 1.0
	 */

	public static function get_min_date( $delivery_time_seconds, $delivery_time_enabled, $holidays_str, $lockout_str, $shipping_settings = array(), $sameday_nextday_settings = array(), $vendor_id = 0 ) {
		global $orddd_shipping_days, $orddd_weekdays;
		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time = current_time( 'timestamp', $gmt );
		if ( ! $vendor_id ) {
			$vendor_id    = self::orddd_get_vendor_id();
		}

		$min_hour                   = $min_minute = 0;
		$current_date               = gmdate( 'j-n-Y', $current_time );
		$current_time_check         = strtotime( $current_date );
		$date_to_check              = gmdate( 'n-j-Y', $current_time );
		$current_hour               = gmdate( 'H', $current_time );
		$current_minute             = gmdate( 'i', $current_time );
		$current_weekday            = gmdate( 'w', $current_time );
		$current_date_time          = strtotime( $current_date );
		$current_date_time_to_check = strtotime( $current_date );
		$current_weekday_to_check   = gmdate( 'w', $current_time );
		$same_day_cut_off           = 0;
		$next_day_cut_off           = 0;

		$business_opening_time = orddd_get_business_opening_time( $current_date );
		$business_closing_time = orddd_get_business_closing_time( $current_date );

		$all_specific_dates       = array();
		$weekdays_to_check        = array();
		$is_all_disable_weekdays  = 'Yes';
		$custom_weekdays_disabled = 'No';
		$holidays                 = array();
		$bookedDays               = array();

		$holidays_arr = explode( ',', $holidays_str );
		foreach ( $holidays_arr as $hkey => $hval ) {
			$hval           = str_replace( '"', '', $hval );
			$hval           = str_replace( '\\', '', $hval );
			$holidays_arr_1 = explode( ':', $hval );
			if ( isset( $holidays_arr_1[1] ) ) {
				$holidays[] = $holidays_arr_1[1];
			}
		}

		$lockout_arr = explode( ',', $lockout_str );
		foreach ( $lockout_arr as $lkey => $lval ) {
			$lval         = str_replace( '"', '', $lval );
			$lval         = str_replace( '\\', '', $lval );
			$bookedDays[] = $lval;
		}

		if ( is_array( $shipping_settings ) && count( $shipping_settings ) > 0 && ! isset( $shipping_settings['enable_shipping_based_delivery'] ) ) {
			return array(
				'min_date'              => $current_date,
				'min_hour'              => $min_hour,
				'min_minute'            => $min_minute,
				'current_date_to_check' => $current_date,
			);
		}

		if ( is_array( $shipping_settings ) && count( $shipping_settings ) > 0 ) {
			// Custom Settings
			if ( isset( $shipping_settings['delivery_type']['specific_dates'] ) && $shipping_settings['delivery_type']['specific_dates'] == 'on' ) {
				if ( isset( $shipping_settings['specific_dates'] ) && $shipping_settings['specific_dates'] != '' ) {
					$specific_days_settings = explode( ',', $shipping_settings['specific_dates'] );
					foreach ( $specific_days_settings as $sk => $sv ) {
						if ( $sv != '' ) {
							$sv                   = str_replace( '}', '', $sv );
							$sv                   = str_replace( '{', '', $sv );
							$specific_date_arr    = explode( ':', $sv );
							$all_specific_dates[] = $specific_date_arr[0];
						}
					}
				}
			}

			if ( isset( $shipping_settings['delivery_type']['weekdays'] ) && $shipping_settings['delivery_type']['weekdays'] == 'on' ) {
				if ( isset( $shipping_settings['weekdays'] ) ) {
					foreach ( $shipping_settings['weekdays'] as $sk => $sv ) {
						if ( isset( $sv['enable'] ) && 'checked' === $sv['enable'] ) {
							$weekdays_to_check[ $sk ] = $sv['enable'];
							$is_all_disable_weekdays  = 'No';
						}
					}
				}
			}
			// If both weekdays & specific dates are disabled then we consider global weekdays. So $is_all_disable_weekdays will be set to 'No'.
			if ( ! isset( $shipping_settings['delivery_type']['specific_dates'] ) && ! isset( $shipping_settings['delivery_type']['weekdays'] ) ) {
				$custom_weekdays_disabled = 'Yes';
				$is_all_disable_weekdays  = 'No';
			}
		} else {
			// Global Settings
			$delivery_dates_arr = array();
			foreach ( $orddd_weekdays as $n => $day_name ) {
				$weekdays_to_check[ $n ] = self::orddd_get_option( $n, '', $vendor_id );
				if ( $weekdays_to_check[ $n ] == 'checked' ) {
					$is_all_disable_weekdays = 'No';
				}
			}

			if ( 'on' == self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) ) {
				$delivery_dates = self::orddd_get_option( 'orddd_delivery_dates', '', $vendor_id );
				if ( $delivery_dates != '' && $delivery_dates != '{}' && $delivery_dates != '[]' && $delivery_dates != 'null' ) {
					$delivery_dates_arr = json_decode( self::orddd_get_option( 'orddd_delivery_dates', '', $vendor_id ) );
				}
			}

			foreach ( $delivery_dates_arr as $k => $v ) {
				$all_specific_dates[] = $v->date;
			}
		}

		$is_all_past_specific_dates = self::orddd_get_if_past_specific_dates( $all_specific_dates );
		$delivery_day_3             = '';
		if ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 ) {
			$specific_dates_sorted_array = array();
			$past_dates                  = array();

			foreach ( $all_specific_dates as $specific_key => $specific_val ) {
				$split_delivery_date_1 = explode( '-', $specific_val );
				if ( isset( $split_delivery_date_1[0] ) && isset( $split_delivery_date_1[1] ) && isset( $split_delivery_date_1[2] ) ) {
					$delivery_day_1 = strtotime( $split_delivery_date_1[1] . '-' . $split_delivery_date_1[0] . '-' . $split_delivery_date_1[2] );
				}
				$specific_dates_sorted_array[] = $delivery_day_1;
			}

			sort( $specific_dates_sorted_array );
			$count = 0;
			if ( is_array( $specific_dates_sorted_array ) && count( $specific_dates_sorted_array ) ) {
				$count = count( $specific_dates_sorted_array );
			}
			for ( $i = 0; $i < $count; $i++ ) {
				if ( $specific_dates_sorted_array[ $i ] >= $current_date_time ) {
					$delivery_day_3 = $specific_dates_sorted_array[ $i ];
					break;
				}
			}
			$highest_delivery_date = end( $specific_dates_sorted_array );
		}

		$is_all_shipping_days_disabled = 'Yes';
		if ( 'on' == self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
			foreach ( $orddd_shipping_days as $s_key => $s_value ) {
				$day_check = self::orddd_get_option( $s_key, '', $vendor_id );
				if ( $day_check == 'checked' ) {
					$is_all_shipping_days_disabled = 'No';
				}
			}
		}

		$orddd_min_hours_for_holidays = 'no';
		if ( has_filter( 'orddd_to_calculate_minimum_hours_for_holidays' ) ) {
			$orddd_min_hours_for_holidays = apply_filters( 'orddd_to_calculate_minimum_hours_for_holidays', $orddd_min_hours_for_holidays );
		}

		if ( 'on' == self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
			if ( ( 'checked' != self::orddd_get_option( 'orddd_shipping_day_' . $current_weekday, '', $vendor_id ) || in_array( $date_to_check, $holidays ) || in_array( $date_to_check, $bookedDays ) ) && $is_all_shipping_days_disabled == 'No' ) {
				$current_time = $business_opening_time;
			}
		} elseif ( 'No' == $is_all_disable_weekdays &&
					is_array( $shipping_settings ) && count( $shipping_settings ) == 0 &&
					( ( 'checked' != self::orddd_get_option( 'orddd_weekday_' . $current_weekday, '', $vendor_id ) &&
					  ( 'on' == self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) &&
					  is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 &&
					  ! in_array( $date_to_check, $all_specific_dates ) ) ) ||
					  ( 'checked' != self::orddd_get_option( 'orddd_weekday_' . $current_weekday, '', $vendor_id ) &&
					  'on' != self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) ) ||
					  ( in_array( $date_to_check, $holidays ) && 'yes' !== $orddd_min_hours_for_holidays ) ||
					  in_array( $date_to_check, $bookedDays )
					) ) {
			$current_time = strtotime( $current_date );
		} elseif ( 'No' == $is_all_disable_weekdays && is_array( $shipping_settings ) && count( $shipping_settings ) > 0 && ! isset( $weekdays_to_check[ 'orddd_weekday_' . $current_weekday ] ) && ( ! isset( $shipping_settings['delivery_type']['specific_dates'] ) || ( isset( $shipping_settings['delivery_type']['specific_dates'] ) && 'on' == $shipping_settings['delivery_type']['specific_dates'] && is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 && ! in_array( $date_to_check, $all_specific_dates ) ) ) && ! isset( $shipping_settings[0]['delivery_type']['weekdays'] ) ) {
			$current_time = strtotime( $current_date );
		} elseif ( 'Yes' == $is_all_disable_weekdays && 'on' == self::orddd_get_option( 'orddd_enable_specific_delivery_dates', '', $vendor_id ) && is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 && ! in_array( $date_to_check, $all_specific_dates ) && '' !== $delivery_day_3 ) {

			//$current_time = strtotime( $current_date );
			$current_time = $delivery_day_3;
		}

		// Default Min date calculation to false
		$calculate_min = false;

		if ( $delivery_time_seconds != 0 && $delivery_time_seconds != '' ) {
			$calculate_min = true;
		}

		if ( 'on' === self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
			$calculate_min = true;
		}

		// Min Date calculation
		if ( $calculate_min ) {
			if ( $current_time >= $business_closing_time ) {
				$current_date = gmdate( 'd-m-Y', strtotime( '+1 day', $current_time ) );
				$current_time = orddd_get_business_opening_time( $current_date, $vendor_id ); // next day's opening time
			}
			$cut_off_timestamp = $current_time + $delivery_time_seconds;
			$mdt_timestamp     = $cut_off_timestamp;
			$mdt_date          = gmdate( 'd-m-Y', $cut_off_timestamp );
			$cut_off_date      = gmdate( 'd-m-Y', $cut_off_timestamp );
			$cut_off_date_time = strtotime( $cut_off_date );

			$business_closing_time = orddd_get_business_closing_time( $current_date, $vendor_id  );
			$business_opening_time = orddd_get_business_opening_time( $current_date, $vendor_id  );
			$closing_time_passed   = false;
			$delivery_time_count   = 0;

			// If the current time + MDT is greater than closing time then calculate the number of hours until the closing time and then calculate the remaining hours from next day opening time.
			while ( $delivery_time_count <= $delivery_time_seconds ) {
				$current_date = gmdate( 'd-m-Y', strtotime( '+1 day', $current_time ) ); // phpcs:ignore
				if ( $business_closing_time < $current_time ) {
					$business_closing_time = orddd_get_business_closing_time( $current_date, $vendor_id  );
				}
				$secs_for_current = $business_closing_time - $current_time;
				$secs_remaining   = $delivery_time_seconds - ( $delivery_time_count + $secs_for_current );

				$delivery_time_count += $secs_for_current;

				if ( $delivery_time_count > $delivery_time_seconds ) {
					break;
				}

				$current_time          = orddd_get_business_opening_time( $current_date, $vendor_id  );
				$business_closing_time = orddd_get_business_closing_time( $current_date, $vendor_id  );
				$cut_off_date_time     = strtotime( $current_date );
				$cut_off_date          = gmdate( 'd-m-Y', $cut_off_date_time );
				$cut_off_timestamp     = $current_time + $secs_remaining;
			}

			for ( $i = $current_weekday; $current_time_check <= $cut_off_date_time; $i++ ) {
				if ( $i >= 0 ) {
					if ( 'on' == self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) && $is_all_shipping_days_disabled == 'No' ) {
						$day = 'orddd_shipping_day_' . $current_weekday;
					} else {
						$day = 'orddd_weekday_' . $current_weekday;
					}

					$weekday_disabled = 'no';

					if ( is_array( $shipping_settings ) && count( $shipping_settings ) > 0 ) {
						if ( 'on' == self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) ) {
							if ( '' == self::orddd_get_option( $day, '', $vendor_id ) && $is_all_shipping_days_disabled == 'No' ) {
								$weekday_disabled = 'yes';
							}
						} elseif ( isset( $shipping_settings['delivery_type']['weekdays'] ) && ! isset( $weekdays_to_check[ 'orddd_weekday_' . $current_weekday ] ) ) {
							$weekday_disabled = 'yes';
						}
					} else {
						if ( '' == self::orddd_get_option( $day, '', $vendor_id ) ) {
							$weekday_disabled = 'yes';
						}
					}

					if ( 'yes' == $weekday_disabled && 'No' == $is_all_disable_weekdays ) {
						$increment_delay_day = 'no';
						if ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 ) {
							if ( 'Yes' == $is_all_past_specific_dates && 'Yes' == $is_all_disable_weekdays ) {
								$min_date = $current_date;
								break;
							} elseif ( 'Yes' == $is_all_past_specific_dates && 'No' == $is_all_disable_weekdays ) {
								$increment_delay_day = 'yes';
							} else {
								$m = gmdate( 'n', $current_time_check );
								$d = gmdate( 'j', $current_time_check );
								$y = gmdate( 'Y', $current_time_check );

								if ( ! in_array( $m . '-' . $d . '-' . $y, $all_specific_dates ) && 'No' == $is_all_disable_weekdays ) {
									$increment_delay_day = 'yes';
								} elseif ( $delivery_day_3 != '' ) {
									if ( $delivery_day_3 != $cut_off_timestamp && $cut_off_timestamp < $delivery_day_3 ) {
										$cut_off_date_time = strtotime( '+1 day', $cut_off_date_time );
										$cut_off_timestamp = strtotime( '+1 day', $cut_off_timestamp );
									} elseif ( $delivery_day_3 != $cut_off_timestamp && $cut_off_timestamp > $delivery_day_3 && $cut_off_timestamp < $highest_delivery_date ) {
										$cm = gmdate( 'n', $cut_off_timestamp );
										$cd = gmdate( 'j', $cut_off_timestamp );
										$cy = gmdate( 'Y', $cut_off_timestamp );
										if ( ! in_array( $cm . '-' . $cd . '-' . $cy, $all_specific_dates ) ) {
											$increment_delay_day = 'yes';
										}
									} elseif ( $delivery_day_3 != $cut_off_timestamp && $cut_off_timestamp > $delivery_day_3 && 'No' == $is_all_disable_weekdays ) {
										$current_time_check = strtotime( '+1 day', $current_time_check );
										$current_weekday    = gmdate( 'w', $current_time_check );
									} else {
										break;
									}
								} else {
									break;
								}
							}
						} else {
							$increment_delay_day = 'yes';
						}

						if ( 'yes' == $increment_delay_day ) {
							$current_date_time_to_check = strtotime( '+1 day', $current_date_time_to_check );
							$current_weekday_to_check   = gmdate( 'w', $current_date_time_to_check );

							$cut_off_date_time = strtotime( '+1 day', $cut_off_date_time );
							$cut_off_timestamp = strtotime( '+1 day', $cut_off_timestamp );
						}
						$mdt_timestamp      = $cut_off_timestamp;
						$mdt_date           = gmdate( 'd-m-Y', $cut_off_timestamp );
						$current_time_check = strtotime( '+1 day', $current_time_check );
						$current_weekday    = gmdate( 'w', $current_time_check );
					} else {

						if ( $current_time_check <= $cut_off_date_time ) {
							$m = gmdate( 'n', $current_time_check );
							$d = gmdate( 'j', $current_time_check );
							$y = gmdate( 'Y', $current_time_check );

							$orddd_disable_for_holidays = 'no';
							if ( has_filter( 'orddd_to_calculate_minimum_hours_for_holidays' ) ) {
								$orddd_disable_for_holidays = apply_filters( 'orddd_to_calculate_minimum_hours_for_holidays', $orddd_disable_for_holidays );
							}

							// re-calculate the Minimum Delivery time (in days): to include holidays that are disabled for delivery
							if ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 && 'Yes' == $is_all_disable_weekdays && 'No' == $is_all_past_specific_dates && 'Yes' === $weekday_disabled ) {
								if ( $orddd_disable_for_holidays != 'yes' && in_array( $m . '-' . $d . '-' . $y, $holidays ) ) {
									$cut_off_date_time = strtotime( '+1 day', $cut_off_date_time );
									$cut_off_timestamp = strtotime( '+1 day', $cut_off_timestamp );
								} elseif ( in_array( $m . '-' . $d . '-' . $y, $bookedDays ) ) {
									$cut_off_date_time = strtotime( '+1 day', $cut_off_date_time );
									$cut_off_timestamp = strtotime( '+1 day', $cut_off_timestamp );
								} else {
									/*
									 Commented by Vishal for Github issue #3385
									// it comes in this 'if' only if the specific date is also added as a holiday in the same custom delivery schedule. It comes here only when the custom delivery schedule whose number of dates to choose is lesser and whose minimum delivery time is higher has the specific date added as a holiday. It doesn't come here if the same criteria is present in the custom delivery schedule whose number of dates to choose is on the higher side & minimum delivery time is less or 0.
									if( !in_array( $m . "-" . $d . "-" . $y, $all_specific_dates ) ) {
										$cut_off_date_time = strtotime( "+1 day", $cut_off_date_time );
										$cut_off_timestamp = strtotime( "+1 day", $cut_off_timestamp );
									} else */ if ( $delivery_day_3 != '' ) {
										if ( $delivery_day_3 != $cut_off_timestamp && $cut_off_timestamp < $delivery_day_3 ) {
											$cut_off_date_time = strtotime( '+1 day', $cut_off_date_time );
											$cut_off_timestamp = strtotime( '+1 day', $cut_off_timestamp );
										} elseif ( $delivery_day_3 != $cut_off_timestamp && $cut_off_timestamp > $delivery_day_3 && $cut_off_timestamp < $highest_delivery_date ) {
											$cm = gmdate( 'n', $cut_off_timestamp );
											$cd = gmdate( 'j', $cut_off_timestamp );
											$cy = gmdate( 'Y', $cut_off_timestamp );
											if ( ! in_array( $cm . '-' . $cd . '-' . $cy, $all_specific_dates ) ) {
												$increment_delay_day = 'yes';
											}
										} else {
											break;
										}
									}
								}
							} else {
								if ( $orddd_disable_for_holidays != 'yes' && in_array( $m . '-' . $d . '-' . $y, $holidays ) ) {
									$cut_off_date_time = strtotime( '+1 day', $cut_off_date_time );
									$cut_off_timestamp = strtotime( '+1 day', $cut_off_timestamp );
								}
							}

							// if ( 'yes' == $weekday_disabled ) {
								$mdt_timestamp = $cut_off_timestamp;
								$mdt_date      = gmdate( 'd-m-Y', $cut_off_timestamp );
							// }

							$current_time_check = strtotime( '+1 day', $current_time_check );
							$current_weekday    = gmdate( 'w', $current_time_check );
						}
					}
				}
			}

			if ( isset( $delivery_time_enabled['enabled'] ) && 'on' == $delivery_time_enabled['enabled'] ) {
				if ( isset( $delivery_time_enabled['from_hours'] ) && isset( $delivery_time_enabled['to_mins'] ) && isset( $delivery_time_enabled['to_hours'] ) && $delivery_time_enabled['to_hours'] <= gmdate( 'H', $cut_off_timestamp ) && $delivery_time_enabled['to_mins'] < gmdate( 'i', $cut_off_timestamp ) ) {
					$cut_off_date_time = strtotime( '+1 day', $cut_off_date_time );
					$cut_off_time_date = gmdate( 'd-m-Y', $cut_off_timestamp );
					$cut_off_timestamp = strtotime( $cut_off_time_date . ' ' . $delivery_time_enabled['from_hours'] . $delivery_time_enabled['from_mins'] );
				}
			}

			$cut_off_date          = gmdate( 'd-m-Y', $cut_off_date_time );
			$min_date              = gmdate( 'j-n-Y', $cut_off_date_time );
			$current_date_to_check = gmdate( 'j-n-Y', $current_date_time_to_check );

			if ( strtotime( $mdt_date ) < strtotime( $cut_off_date )  || ( $delivery_time_seconds === 0 && strtotime( $mdt_date ) > $current_time ) ) {
				$min_hour   = gmdate( 'H', $cut_off_date_time );
				$min_minute = gmdate( 'i', $cut_off_date_time );
			} else {
				$min_hour   = gmdate( 'H', $cut_off_timestamp );
				$min_minute = gmdate( 'i', $cut_off_timestamp );
			}
		} else {
			$min_date              = gmdate( 'j-n-Y', $current_time );
			$current_date_to_check = $current_date;
			if ( $calculate_min ) {
				$current_timestamp = $current_time;
				for ( $i = $current_weekday; ; $i++ ) {
					if ( $i >= 0 ) {
						if ( 'on' == self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id ) && $is_all_shipping_days_disabled == 'No' ) {
							$day = 'orddd_shipping_day_' . $current_weekday;
						} else {
							$day = 'orddd_weekday_' . $current_weekday;
						}

						$weekday_disabled = 'no';
						if ( is_array( $shipping_settings ) && count( $shipping_settings ) > 0 ) {
							if ( isset( $shipping_settings['delivery_type']['weekdays'] ) && ! isset( $weekdays_to_check[ 'orddd_weekday_' . $current_weekday ] ) ) {
								$weekday_disabled = 'yes';
							}
						} else {
							if ( '' == self::orddd_get_option( $day, '', $vendor_id ) ) {
								$weekday_disabled = 'yes';
							}
						}

						if ( 'yes' == $weekday_disabled && 'No' == $is_all_disable_weekdays ) {
							$increment_delay_day = 'no';
							if ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 ) {
								if ( 'Yes' == $is_all_past_specific_dates && 'Yes' == $is_all_disable_weekdays ) {
									$min_date = $current_date;
									break;
								} elseif ( 'Yes' == $is_all_past_specific_dates && 'No' == $is_all_disable_weekdays ) {
									$increment_delay_day = 'yes';
								} else {
									$m = gmdate( 'n', $current_time );
									$d = gmdate( 'j', $current_time );
									$y = gmdate( 'Y', $current_time );
									if ( ! in_array( $m . '-' . $d . '-' . $y, $all_specific_dates ) && 'No' == $is_all_disable_weekdays ) {
										$increment_delay_day = 'yes';
									} elseif ( $delivery_day_3 != '' ) {
										if ( $delivery_day_3 != $current_time && $current_time < $delivery_day_3 ) {
											$increment_delay_day = 'yes';
										} else {
											break;
										}
									} else {
										break;
									}
								}
							} else {
								if ( 'No' == $is_all_disable_weekdays ) {
									$increment_delay_day = 'yes';
								} else {
									break;
								}
							}

							if ( 'yes' == $increment_delay_day ) {
								$current_time    = strtotime( '+1 day', $current_time );
								$current_weekday = gmdate( 'w', $current_time );
							}
						} elseif ( 'yes' == $weekday_disabled && 'Yes' == $is_all_disable_weekdays ) {
							// re-calculate the Minimum Delivery time (in days): to include holidays that are disabled for delivery
							if ( $current_timestamp <= $current_time ) {
								$m = gmdate( 'n', $current_time );
								$d = gmdate( 'j', $current_time );
								$y = gmdate( 'Y', $current_time );

								if ( is_array( $all_specific_dates ) && count( $all_specific_dates ) > 0 ) {
									if ( ! in_array( $m . '-' . $d . '-' . $y, $all_specific_dates ) && 'Yes' == $is_all_disable_weekdays && '' == $is_all_past_specific_dates ) {
										$current_time = strtotime( '+1 day', $current_time );
									} elseif ( $delivery_day_3 != '' ) {
										if ( $delivery_day_3 != $current_time && $current_time < $delivery_day_3 ) {
											$current_time = strtotime( '+1 day', $current_time );
										} else {
											$orddd_disable_for_holidays = 'no';
											if ( has_filter( 'orddd_to_calculate_minimum_hours_for_holidays' ) ) {
												$orddd_disable_for_holidays = apply_filters( 'orddd_to_calculate_minimum_hours_for_holidays', $orddd_disable_for_holidays );
											}

											if ( ( $orddd_disable_for_holidays != 'yes' && in_array( $m . '-' . $d . '-' . $y, $holidays ) ) || in_array( $m . '-' . $d . '-' . $y, $bookedDays ) ) {
												$current_time = strtotime( '+1 day', $current_time );
											} else {
												break;
											}
										}
									}
								}

								$current_timestamp = strtotime( '+1 day', $current_timestamp );
								$current_weekday   = gmdate( 'w', $current_timestamp );
							} else {
								break;
							}
						} else {
							$m = gmdate( 'n', $current_time );
							$d = gmdate( 'j', $current_time );
							$y = gmdate( 'Y', $current_time );

							$orddd_disable_for_holidays = 'no';
							if ( has_filter( 'orddd_to_calculate_minimum_hours_for_holidays' ) ) {
								$orddd_disable_for_holidays = apply_filters( 'orddd_to_calculate_minimum_hours_for_holidays', $orddd_disable_for_holidays );
							}

							if ( ( $orddd_disable_for_holidays != 'yes' && in_array( $m . '-' . $d . '-' . $y, $holidays ) ) || in_array( $m . '-' . $d . '-' . $y, $bookedDays ) ) {
								$current_time = strtotime( '+1 day', $current_time );
							} else {
								break;
							}
						}
					}
				}

				if ( isset( $delivery_time_enabled['enabled'] ) && 'on' == $delivery_time_enabled['enabled'] ) {
					if ( isset( $delivery_time_enabled['from_hours'] ) && isset( $delivery_time_enabled['to_mins'] ) && isset( $delivery_time_enabled['to_hours'] ) && $delivery_time_enabled['to_hours'] <= gmdate( 'H', $cut_off_timestamp ) && $delivery_time_enabled['to_mins'] < gmdate( 'i', $cut_off_timestamp ) ) {
						$current_time           = strtotime( '+1 day', $current_time );
						$current_date           = gmdate( 'j-n-Y', $current_time );
						$current_time_date      = gmdate( 'd-m-Y', $current_time );
						$current_date_timestamp = strtotime( $current_time_date . ' ' . $delivery_time_enabled['from_hours'] . ':' . $delivery_time_enabled['from_mins'] );
						$min_date               = gmdate( 'j-n-Y', $current_date_timestamp );
						$min_hour               = gmdate( 'H', $current_date_timestamp );
						$min_minute             = gmdate( 'i', $current_date_timestamp );
					}
				} else {
					$min_date   = gmdate( 'j-n-Y', $current_time );
					$min_hour   = gmdate( 'H', $current_time );
					$min_minute = gmdate( 'i', $current_time );
				}
			}
		}

		$return_values = array( 'min_date' => $min_date, 'min_hour' => $min_hour, 'min_minute' => $min_minute, 'current_date_to_check' => $current_date_to_check );

		$return_values = apply_filters(
			'orddd_modify_min_date',
			$return_values,
			array(
				'delivery_time_seconds'    => $delivery_time_seconds,
				'delivery_time_enabled'    => $delivery_time_enabled,
				'holidays_str'             => $holidays_str,
				'lockout_str'              => $lockout_str,
				'shipping_settings'        => $shipping_settings,
				'sameday_nextday_settings' => $sameday_nextday_settings,
			)
		);

	    return $return_values;
	}

	/**
	 * Checks if all the added specific dates are past dates or not.
	 *
	 * @param array $specific_dates Added specific dates
	 *
	 * @return string 'Yes' if all dates added are past, else 'No'.
	 * @since 7.5
	 */

	public static function orddd_get_if_past_specific_dates( $specific_dates ) {
		$gmt = false;

		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );

		$is_all_past_dates = 'No';
		$future_dates      = array();

		$today = gmdate( ORDDD_HOLIDAY_DATE_FORMAT , $current_time ); // phpcs:ignore
		if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
			foreach ( $specific_dates as $specific_key => $specific_val ) {
				$split_delivery_date_1 = explode( '-', $specific_val );
				if ( isset( $split_delivery_date_1[0] ) && isset( $split_delivery_date_1[1] ) && isset( $split_delivery_date_1[2] ) ) {
					$specific_date_time = strtotime( $split_delivery_date_1[1] . '-' . $split_delivery_date_1[0] . '-' . $split_delivery_date_1[2] );
					$specic_date        = gmdate( ORDDD_HOLIDAY_DATE_FORMAT, $specific_date_time );
					if ( $specific_date_time >= $current_time || $today === $specic_date ) {
						$future_dates[] = $specific_val;
					}
				}
			}

			if ( count( $future_dates ) == 0 ) {
				$is_all_past_dates = 'Yes';
			}
			return $is_all_past_dates;
		}
	}

	/**
	 * Checks if all the added specific dates are past dates or not.
	 *
	 * @globals resource $wpdb WordPress object
	 * @globals array $orddd_weekdays Weekdays array
	 *
	 * @param string $time_slot_for_order Already added time slot if accessed from edit order page
	 *
	 * @return array All time slots for the selected delivery date
	 * @since 2.7
	 */
	public static function orddd_get_timeslot_display( $delivery_date, $setting_id, $common_ids = array(), $vendor_id = 0 ) {
		global $wpdb, $orddd_weekdays;
		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time            = current_time( 'timestamp', $gmt );
		$current_day             = gmdate( 'j-n-Y', $current_time );
		$current_weekday		 = gmdate( 'w', $current_time );
		$delivery_weekday        = gmdate( 'w', strtotime( $delivery_date ) );
		$delivery_date_njy       = gmdate( 'n-j-Y', strtotime( $delivery_date ) );
		$selected_date           = gmdate( 'j-n-Y', strtotime( $delivery_date ) );
		$time_format_to_show     = self::orddd_get_time_format();
		$custom_setting_present  = false;
		$custom_settings         = array();
		$custom_settings_to_load = array();
		$arr1                    = array();
		$arr2                    = array();
		$arr3                    = array();
		$subscription_var        = array();
		$min_hour_in_sec         = orddd_get_minimum_delivery_time( $vendor_id );
		$asap_option             = false;

		$orddd_enable_shipping_days  = self::orddd_get_option( 'orddd_enable_shipping_days', '', $vendor_id );
		$orddd_business_opening_time = self::orddd_get_option( 'orddd_business_opening_time', '', $vendor_id  );
		$same_day_timestamp          = strtotime( $orddd_business_opening_time );

		if ( $same_day_timestamp > $current_time && $current_day === $selected_date && 'on' === $orddd_enable_shipping_days && '' !== $orddd_business_opening_time ) {
			$current_time = $same_day_timestamp;
		}

		if ( is_array( $common_ids ) && count( $common_ids ) > 1 ) {
			$min_hour_common = 0 ;
			foreach ( $common_ids as $id ) {				
				if ( 0 != $id ) {
					$option_name                             = 'orddd_shipping_based_settings_' . $id;
					$custom_settings                         = orddd_custom_delivery_functions::orddd_get_delivery_schedule_settings_by_id( $id );
					$custom_settings_to_load[ $option_name ] = $custom_settings;
					$custom_setting_present                  = true;
					$min_hour_new                            = $custom_settings['minimum_delivery_time'];
					if ( $min_hour_new > $min_hour_common) {
						$min_hour_common = $min_hour_new;
					}
				}
			}
			$custom_settings['minimum_delivery_time'] = $min_hour_common;
		} else {
			if ( 0 != $setting_id ) {
				$option_name                             = 'orddd_shipping_based_settings_' . $setting_id;
				$custom_settings                         = orddd_custom_delivery_functions::orddd_get_delivery_schedule_settings_by_id( $setting_id, $vendor_id );
				$custom_settings_to_load[ $option_name ] = $custom_settings;
				$custom_setting_present                  = true;
			}
		}

		if ( $custom_setting_present ) {
			$min_hour_in_sec     = isset( $custom_settings['minimum_delivery_time'] ) && '' !== $custom_settings['minimum_delivery_time'] ? $custom_settings['minimum_delivery_time'] * 60 * 60 : 0;
			$holidays_str        = self::orddd_get_custom_holidays( $custom_settings, $vendor_id );
			$lockout_str         = self::orddd_get_custom_lockout_days( $custom_settings, $vendor_id );
			$time_slider_enabled = '';
			$from_hours          = '';
			$to_hours            = '';
			$from_mins           = '';
			$to_mins             = '';

			// Check weekday based schedule
			$weekday_based_schedule_enable = isset( $custom_settings['weekday_based_schedule_enable'] ) ? $custom_settings['weekday_based_schedule_enable'] : '';
			$weekday_based_schedule        = isset( $custom_settings['weekday_based_schedule'] ) ? $custom_settings['weekday_based_schedule'] : array();

			if ( 'on' === $weekday_based_schedule_enable && is_array( $weekday_based_schedule ) && count( $weekday_based_schedule ) > 0 ) {
				foreach ( $weekday_based_schedule as $key => $value ) {
					if ( $value['weekday'] === 'orddd_weekday_' . $current_weekday ) {
						$min_delivery_time = isset( $value['minimum_time'] ) && '' !== $value['minimum_time'] ? $value['minimum_time'] : 0;
						$min_hour_in_sec = $min_delivery_time * 60 * 60;
						break;
					}
				}
			}

			if ( isset( $custom_settings['timeslot_asap_option'] ) && 'checked' == $custom_settings['timeslot_asap_option'] ) {
				if ( $selected_date === $current_day ) {
					$asap_option = true;
				}
			}

			if ( isset( $custom_settings['time_settings'] ) ) {
				$time_settings = $custom_settings['time_settings'];
				if ( isset( $time_settings['from_hours'] ) && $time_settings['from_hours'] != 0
					&& isset( $time_settings['to_hours'] ) && $time_settings['to_hours'] != 0 ) {
					$from_hours          = $time_settings['from_hours'];
					$from_mins           = $time_settings['from_mins'];
					$to_hours            = $time_settings['to_hours'];
					$to_mins             = $time_settings['to_mins'];
					$time_slider_enabled = $time_settings['enable'];
				}
			}

			$same_day_cut_off = orddd_get_highest_same_day( $vendor_id );
			if ( is_array( $same_day_cut_off ) && count( $same_day_cut_off ) > 0 &&
				isset( $same_day_cut_off['same_day_disabled'] ) &&
				$same_day_cut_off['same_day_disabled'] == 'no' ) {
				$same_day = $same_day_cut_off;
			} elseif ( is_array( $same_day_cut_off ) && count( $same_day_cut_off ) == 0 ) {
				if ( isset( $custom_settings['same_day'] ) ) {
					$same_day = $custom_settings['same_day'];
				}
			}

			// Nexy Day Delivery
			
			$next_day_cut_off = orddd_get_highest_next_day( $vendor_id );
			if ( is_array( $next_day_cut_off ) && count( $next_day_cut_off ) > 0 && isset( $next_day_cut_off['next_day_disabled'] ) && $next_day_cut_off['next_day_disabled'] == 'no' ) {
				$next_day = $next_day_cut_off;
				$same_day = array();
			} elseif ( is_array( $next_day_cut_off ) && count( $next_day_cut_off ) == 0 ) {
				if ( isset( $custom_settings['next_day'] ) ) {
					$next_day = $custom_settings['next_day'];
				}
			}

			$sameday_nextday_settings = array(
				'same_day' => $same_day,
				'next_day' => $next_day,
			);

			$min_date_array = self::get_min_date(
				$min_hour_in_sec,
				array(
					'enabled'    => $time_slider_enabled,
					'from_hours' => $from_hours,
					'from_mins'  => $from_mins,
					'to_hours'   => $to_hours,
					'to_mins'    => $to_mins,
				),
				$holidays_str,
				$lockout_str,
				$custom_settings,
				$sameday_nextday_settings,
				$vendor_id
			);
		} else {
			$holidays_str   = ORDDD_Functions::orddd_get_holidays_str( $vendor_id );
			$lockout_str    = ORDDD_Functions::orddd_get_booked_days_str( $vendor_id );
			$min_date_array = self::get_min_date(
				$min_hour_in_sec,
				array(
					'enabled'    => self::orddd_get_option( 'orddd_enable_delivery_time', $vendor_id ),
					'from_hours' => self::orddd_get_option( 'orddd_delivery_from_hours', $vendor_id ),
					'to_hours'   => self::orddd_get_option( 'orddd_delivery_to_hours', $vendor_id ),
				),
				$holidays_str,
				$lockout_str,
				array(),
				array(),
				$vendor_id
			);
			if ( 'checked' == self::orddd_get_option( 'orddd_time_slot_asap', '', $vendor_id ) ) {
				if ( $selected_date === $current_day ) {
					$asap_option = true;
				}
			}

			// if ( isset( $_POST[ 'datepicker' ] ) && 'pickup' == $_POST[ 'datepicker' ] ) {
			// $min_date_array['min_date'] = $_POST['min_date'];
			// $min_date_array['min_hour'] = $_POST['min_hour'];
			// $min_date_array['min_minute'] = $_POST['min_minute'];
			// }
		}

		if ( $current_day === $selected_date ) {
			$asap_option = apply_filters( 'orddd_asap_for_today', $asap_option, $current_day );
		}

		$asap_option = apply_filters( 'orddd_asap_for_all_days', $asap_option, $selected_date );

		$min_date_array = apply_filters( 'orddd_modify_min_date_array', $min_date_array );

		$current_date_to_check = $min_date_array['current_date_to_check'];

		if ( isset( $current_date_to_check ) && $current_date_to_check == $current_day && $min_hour_in_sec != '' && $min_hour_in_sec != 0 ) {
			$today = gmdate( 'Y-m-d G:i', $current_time );
		} else {
			if ( isset( $current_date_to_check ) && $min_hour_in_sec != '' && $min_hour_in_sec != 0 ) {
				$date_arr = explode( '-', $current_date_to_check );
				$today    = gmdate( 'Y-m-d', strtotime( $date_arr[2] . '-' . $date_arr[1] . '-' . $date_arr[0] ) );
			} else {
				$today = gmdate( 'Y-m-d', $current_time );
			}
		}

		$date1 = new DateTime( $today );

		if ( isset( $selected_date ) && $selected_date == $current_day && $min_hour_in_sec == 0 ) {
			$last_slot = gmdate( 'G:i', $current_time );
		} elseif ( isset( $selected_date ) && $selected_date == $current_day && $min_hour_in_sec > 0 && '' !== $orddd_business_opening_time ) {
			$last_slot = gmdate( 'G:i', $current_time );
		} else {
			$last_slot = $min_date_array['min_hour'] . ':' . $min_date_array['min_minute'];
		}

		$ordd_date_two = $min_date_array['min_date'] . ' ' . $last_slot;
		$ordd_date_two = gmdate( 'Y-m-d G:i', strtotime( $ordd_date_two ) );
		$date2         = new DateTime( $ordd_date_two );

		if ( version_compare( phpversion(), '5.3.0', '>' ) ) {
			$difference = $date2->diff( $date1 );
		} else {
			$difference = self::dateTimeDiff( $date2, $date1 );
		}

		if ( $difference->days > 0 ) {
			$days_in_hour  = $difference->h + ( $difference->days * 24 );
			$difference->h = $days_in_hour;
		}

		if ( $difference->i > 0 ) {
			$min_in_hour              = $difference->h + ( $difference->i / 60 );
			$diff_min_hour_in_seconds = $min_in_hour * 60 * 60;
		} else {
			$diff_min_hour_in_seconds = $difference->h * 60 * 60;
		}

		$min_hour_in_sec = $diff_min_hour_in_seconds > $min_hour_in_sec ? ( $diff_min_hour_in_seconds ) : $min_hour_in_sec;

		$time_slots_to_show_timestamp = array( 'NA' => __( 'No time slots are available', 'order-delivery-date' ) );

		$disable_days          = self::orddd_get_disabled_timeslot( $vendor_id );
		$time_slots_to_disable = self::get_timeslot_to_disable( $delivery_weekday, $delivery_date_njy, $disable_days );
		$arr1                  = self::orddd_get_timeslots( $selected_date, $delivery_weekday, $delivery_date_njy, $custom_settings_to_load, $vendor_id );
		$arr2                  = self::orddd_get_timeslot_lockout( $custom_settings_to_load, $vendor_id );

		if ( $asap_option && ! ( in_array( 'asap', $time_slots_to_disable ) || in_array( 'As Soon As Possible.', $time_slots_to_disable ) ) ) { //phpcs:ignore
			$time_slots_to_show_timestamp['asap'] = __( 'As Soon As Possible', 'order-delivery-date' );
		}

		if ( $custom_setting_present ) {
			$shipping_days = 'Yes';
			if ( array_key_exists( $delivery_date_njy, $arr1 ) ) {
				$arr3 = $arr1[ $delivery_date_njy ];
			} elseif ( array_key_exists( 'orddd_weekday_' . $delivery_weekday . '_custom_setting', $arr1 ) || array_key_exists( 'all', $arr1 ) ) {
				if ( array_key_exists( 'orddd_weekday_' . $delivery_weekday . '_custom_setting', $arr1 ) ) {
					$arr3 = array_merge( $arr3, $arr1[ 'orddd_weekday_' . $delivery_weekday . '_custom_setting' ] );
				}
				if ( array_key_exists( 'all', $arr1 ) ) {
					$arr3 = array_merge( $arr3, $arr1['all'] );
				}
			}
		} else {
			$alldays = array();
			foreach ( $orddd_weekdays as $n => $day_name ) {
				$alldays[ $n ] = self::orddd_get_option( $n, '', $vendor_id );
			}

			$alldayskeys = array_keys( $alldays );
			$checked     = 'No';
			foreach ( $alldayskeys as $key ) {
				if ( $alldays[ $key ] == 'checked' ) {
					$checked = 'Yes';
				}
			}

			if ( array_key_exists( $delivery_date_njy, $arr1 ) && self::orddd_get_option( 'orddd_enable_specific_delivery_dates','', $vendor_id ) == 'on' ) {
				$arr3 = $arr1[ $delivery_date_njy ];
			} elseif ( array_key_exists( 'orddd_weekday_' . $delivery_weekday, $arr1 ) || array_key_exists( 'all', $arr1 ) ) {
				if ( array_key_exists( 'orddd_weekday_' . $delivery_weekday, $arr1 ) ) {
					$arr3 = array_merge( $arr3, $arr1[ 'orddd_weekday_' . $delivery_weekday ] );
				}
				if ( array_key_exists( 'all', $arr1 ) ) {
					$arr3 = array_merge( $arr3, $arr1['all'] );
				}
			}
		}

		$dmy = gmdate( 'd' ) . ' ' . gmdate( 'M' ) . ' ' . gmdate( 'Y' );
		foreach ( $arr3 as $key => $lockout ) {
			if ( $custom_setting_present ) {
				if ( $lockout == '' || $lockout == '0' || $lockout == ':' ) {
					$lockout          = 'false' != get_option( 'orddd_global_lockout_time_slots' ) ? get_option( 'orddd_global_lockout_time_slots' ) : '';
					$lockout_time     = isset( $custom_settings['orddd_lockout_time_slot'] ) ? $custom_settings['orddd_lockout_time_slot'] : '';
					$lockout_time_arr = json_decode( $lockout_time );

					if ( is_array( $lockout_time_arr ) && count( $lockout_time_arr ) > 0 ) {
						foreach ( $lockout_time_arr as $k => $v ) {

							$v->t = self::orddd_change_time_slot_format( $v->t, $time_format_to_show );

							if ( isset( $arr2[ $v->d ][ $v->t ] ) ) {
								$arr2[ $v->d ][ $v->t ] = $arr2[ $v->d ][ $v->t ];
							} else {
								$arr2[ $v->d ][ $v->t ] = $v->o;
							}
						}
					}
				}
			}

			$include_time_slot = 'no';
			$time_slot_locked  = 'no';
			$time_arr          = explode( ' - ', $key );
			$tstamp_from       = strtotime( $dmy . ' ' . $time_arr[0] );
			$tstamp_to         = '';
			if ( isset( $time_arr[1] ) ) {
				$tstamp_to = strtotime( $dmy . ' ' . $time_arr[1] );
			}

			$key = self::orddd_change_time_slot_format( $key, $time_format_to_show );

			// We use this filter to overwrite the timeslot lockout with any other value
			// it could either be Global lockout (currently applicable only for custom settings)
			// or any other custom value
			$lockout = apply_filters( 'orddd_overwrite_timeslot_lockout', $lockout );
			if ( $lockout != '' && $lockout != '0' && $lockout != ':' ) {
				if ( isset( $arr2[ $selected_date ][ $key ] ) &&
					 $arr2[ $selected_date ][ $key ] >= $lockout ) {
					// if it comes here, then it means the time slot for the selected date is full
					if ( ( is_array( $subscription_var ) && count( $subscription_var ) > 0 &&
						isset( $subscription_var['orddd_if_renewal_subscription'] ) &&
						$subscription_var['orddd_if_renewal_subscription'] == 'yes' ) ) {
						$include_time_slot = 'yes';
					}
					$time_slot_locked = 'yes';
				}
			}

			if ( ! in_array( $key, $time_slots_to_disable ) && $time_slot_locked == 'no' ) {
				$date  = $selected_date . ' ' . $time_arr[0];
				$date3 = new DateTime( $date );

				$min_time_on_last_slot = apply_filters( 'orddd_min_delivery_on_last_slot', false );

				if ( $min_time_on_last_slot ) {
					$date     = $selected_date . ' ' . $time_arr[1];
					$date3str = strtotime( $date );

					$ordd_date_two = gmdate( 'j-n-Y H:i', strtotime( $ordd_date_two ) );
					$date2str      = strtotime( $ordd_date_two );

					if ( ( $selected_date == $min_date_array['min_date'] && $date2str < $date3str ) || $selected_date != $min_date_array['min_date'] ) {
						$include_time_slot = 'yes';
					}
				} else {
					if ( version_compare( phpversion(), '5.3.0', '>' ) ) {
						$difference = $date3->diff( $date1 );
					} else {
						$difference = self::dateTimeDiff( $date3, $date1 );
					}

					if ( $difference->days > 0 ) {
						$days_in_hour  = $difference->h + ( $difference->days * 24 );
						$difference->h = $days_in_hour;
					}

					if ( $difference->i > 0 ) {
						$min_in_hour          = $difference->h + ( $difference->i / 60 );
						$diff_hour_in_seconds = $min_in_hour * 60 * 60;
					} else {
						$diff_hour_in_seconds = $difference->h * 60 * 60;
					}

					if ( $difference->invert == 0 || $diff_hour_in_seconds < $min_hour_in_sec ) {
						$include_time_slot = 'no';
					} else {
						$include_time_slot = 'yes';
					}
				}
			}

			if ( 'yes' == $include_time_slot ) {
				$time_slots_to_show_timestamp[ $key ] = $tstamp_from;
			}
		}

		return $time_slots_to_show_timestamp;
	}

	/**
	 * This function will return the differance days between two dates.
	 *
	 * @param resource $date1 Date 1 to compare
	 * @param resource $date2 Date 2 to compare
	 *
	 * @return resource Result of the difference between the dates.
	 * @since 5.5
	 */
	public static function dateTimeDiff( $date1, $date2 ) {
		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time = current_time( 'timestamp', $gmt );

		$one    = $date1->format( 'U' );
		$two    = $date2->format( 'U' );
		$invert = false;
		if ( $one > $two ) {
			list($one, $two) = array( $two, $one );
			$invert          = true;
		}

		$key = array( 'y', 'm', 'd', 'h', 'i', 's' );
		$a   = array_combine( $key, array_map( 'intval', explode( ' ', gmdate( 'Y m d H i s', $one ) ) ) );
		$b   = array_combine( $key, array_map( 'intval', explode( ' ', gmdate( 'Y m d H i s', $two ) ) ) );

		$result = new stdClass();
		$date   = ( gmdate( 'd', $current_time ) ) - 1;

		$result->y      = $b['y'] - $a['y'];
		$result->m      = $b['m'] - $a['m'];
		$result->d      = $date;
		$result->h      = $b['h'] - $a['h'];
		$result->i      = $b['i'] - $a['i'];
		$result->s      = $b['s'] - $a['s'];
		$result->invert = $invert ? 1 : 0;
		$result->days   = intval( abs( ( $one - $two ) / 86400 ) );

		if ( $invert ) {
			self::orddd_date_normalize( $a, $result );
		} else {
			self::orddd_date_normalize( $b, $result );
		}
		return $result;
	}

	/**
	 * Calculates the difference between the dates.
	 *
	 * @param string  $start
	 * @param string  $end
	 * @param int adj
	 * @param string  $a
	 * @param string  $b
	 * @param array   $result
	 *
	 * @return array $result
	 * @since 5.5
	 */
	public static function orddd_date_range_limit( $start, $end, $adj, $a, $b, $result ) {
		$result = (array) $result;
		if ( $result[ $a ] < $start ) {
			$result[ $b ] -= intval( ( $start - $result[ $a ] - 1 ) / $adj ) + 1;
			$result[ $a ] += $adj * intval( ( $start - $result[ $a ] - 1 ) / $adj + 1 );
		}

		if ( $result[ $a ] >= $end ) {
			$result[ $b ] += intval( $result[ $a ] / $adj );
			$result[ $a ] -= $adj * intval( $result[ $a ] / $adj );
		}
		return $result;
	}

	/**
	 * Calculates the Range Limit.
	 *
	 * @param array $base
	 * @param array $result
	 *
	 * @return array $result
	 * @since 5.5
	 */
	public static function orddd_date_range_limit_days( $base, $result ) {
		$days_in_month_leap = array( 31, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
		$days_in_month      = array( 31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

		self::orddd_date_range_limit( 1, 13, 12, 'm', 'y', $base );

		$year  = $base['y'];
		$month = $base['m'];

		if ( ! $result['invert'] ) {
			while ( $result['d'] < 0 ) {
				$month--;
				if ( $month < 1 ) {
					$month += 12;
					$year--;
				}
				$leapyear = $year % 400 == 0 || ( $year % 100 != 0 && $year % 4 == 0 );
				$days     = $leapyear ? $days_in_month_leap[ $month ] : $days_in_month[ $month ];

				$result['d'] += $days;
				$result['m']--;
			}
		} else {
			while ( $result['d'] < 0 ) {
				$leapyear = $year % 400 == 0 || ( $year % 100 != 0 && $year % 4 == 0 );
				$days     = $leapyear ? $days_in_month_leap[ $month ] : $days_in_month[ $month ];

				$result['d'] += $days;
				$result['m']--;

				$month++;
				if ( $month > 12 ) {
					$month -= 12;
					$year++;
				}
			}
		}
		return $result;
	}

	/**
	 * Normalize the Date.
	 *
	 * @param array $base
	 * @param array $result
	 *
	 * @return array $result
	 * @since 5.5
	 */

	public static function orddd_date_normalize( $base, $result ) {
		$result = self::orddd_date_range_limit( 0, 60, 60, 's', 'i', $result );
		$result = self::orddd_date_range_limit( 0, 60, 60, 'i', 'h', $result );
		$result = self::orddd_date_range_limit( 0, 24, 24, 'h', 'd', $result );
		$result = self::orddd_date_range_limit( 0, 12, 12, 'm', 'y', $result );

		$result = self::orddd_date_range_limit_days( $base, $result );

		$result = self::orddd_date_range_limit( 0, 12, 12, 'm', 'y', $result );

		return $result;
	}

	/**
	 * Get previous added delivery charges labels for an order.
	 *
	 * @param int $order_id Order ID
	 * @return array Previous added delivery charges labels
	 * @since 5.7
	 */
	public static function orddd_previous_charges_label( $order_id ) {
		$previous_charges_labels = array();
		$previous_order_date     = $previous_order_weekday_check = $previous_order_h_date = $previous_order_timeslot = $previous_charges_label = '';

		$order = wc_get_order( $order_id );
		$data  = $order->get_meta();
		if ( isset( $data['_orddd_timestamp'][0] ) && $data['_orddd_timestamp'][0] != '' ) {
			$previous_charges_labels['previous_order_h_date']        = gmdate( 'd-m-Y', $data['_orddd_timestamp'][0] );
			$previous_charges_labels['previous_order_date_check']    = gmdate( 'n-j-Y', $data['_orddd_timestamp'][0] );
			$previous_charges_labels['previous_order_weekday_check'] = gmdate( 'w', $data['_orddd_timestamp'][0] );
		}

		if ( isset( $data[ get_option( 'orddd_delivery_date_field_label' ) ][0] ) && $data[ get_option( 'orddd_delivery_date_field_label' ) ][0] != '' ) {
			$previous_charges_labels['previous_order_date'] = $data[ get_option( 'orddd_delivery_date_field_label' ) ][0];
		}

		if ( isset( $data[ get_option( 'orddd_delivery_timeslot_field_label' ) ][0] ) && $data[ get_option( 'orddd_delivery_timeslot_field_label' ) ][0] != '' ) {
			$previous_charges_labels['previous_order_timeslot'] = $data[ get_option( 'orddd_delivery_timeslot_field_label' ) ][0];
		}
		return $previous_charges_labels;
	}

	/**
	 * Update delivery charges for edited delivery date on edit order page.
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @param int    $order_id Order ID
	 * @param string $delivery_date Selected delivery date
	 * @param string $time_slot Selected time slot
	 *
	 * @since 5.7
	 */
	public static function orddd_update_delivery_charges( $delivery_date, $time_slot, $order_id = '' ) {
		global $wpdb;

		$delivery_charges          = 'no';
		$same_day_delivery_charges = 'no';
		$next_day_delivery_charges = 'no';
		$time_slot_charges         = 'no';
		$time_slot_charges         = 'no';
		$product_id                = '';
		$shipping_method           = '';

		$ordd_get_day_wise_setting      = get_option( 'orddd_enable_day_wise_settings' );
		$enable_shipping_based_delivery = ORDDD_Functions::orddd_shipping_based_delivery_enabled();
		$charges_arr                    = self::orddd_get_delivery_date_charges( $delivery_date, $time_slot, $order_id );
		$order                          = wc_get_order( $order_id );
		$get_order_item_ids_query       = 'SELECT * FROM `' . $wpdb->prefix . 'woocommerce_order_items` WHERE order_id = %d';
		$results_order_item_ids         = $wpdb->get_results( $wpdb->prepare( $get_order_item_ids_query, $order_id ) ); //phpcs:ignore
		$ordd_tax_item_id               = 0;
		$ordd_calc_taxes                = get_option( 'woocommerce_calc_taxes' );
		$ordd_prices_include_tax        = get_option( 'woocommerce_prices_include_tax' );
		$ordd_recalculated_tax_amount   = 0;
		$ordd_wc_get_tax_rate           = '';
		if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes ) {
			$ordd_wc_get_tax_rate = WC_Tax::get_rates();
		}

		foreach ( $results_order_item_ids as $key => $value ) {

			if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes ) {
				if ( $value->order_item_type == 'tax' ) {

					$ordd_tax_item_id = $value->order_item_id;

					$ordd_subtotal = $order->get_subtotal();
					if ( isset( $charges_arr['charges_label'] ) &&
						isset( $charges_arr['fees'] ) && $charges_arr['fees'] > 0 && 'on' == $ordd_get_day_wise_setting ) {
						$ordd_subtotal += $charges_arr['fees'];
					}

					if ( isset( $charges_arr['timeslot_charges_label'] ) &&
						isset( $charges_arr['time_slot_fees'] ) && $charges_arr['time_slot_fees'] > 0 ) {
						$ordd_subtotal += $charges_arr['time_slot_fees'];
					}

					$ordd_recalculated_tax = WC_Tax::calc_tax( $ordd_subtotal, WC_Tax::get_rates() );

					if ( is_array( $ordd_recalculated_tax ) && count( $ordd_recalculated_tax ) > 0 && isset( $ordd_recalculated_tax [1] ) ) {
						$ordd_recalculated_tax_amount = $ordd_recalculated_tax [1];
						wc_update_order_item_meta( $ordd_tax_item_id, 'tax_amount', $ordd_recalculated_tax_amount );

						/**
						 * Excluding & Including tax
						 */
						if ( isset( $ordd_prices_include_tax ) && ( 'no' == $ordd_prices_include_tax || 'yes' == $ordd_prices_include_tax ) ) {
							$ordd_update_order_total = $ordd_subtotal + $ordd_recalculated_tax_amount;
							$order->update_meta_data( '_order_total', $ordd_update_order_total );
						}
					}
				}
			}
			if ( $value->order_item_type == 'fee' && $value->order_item_name == $charges_arr['charges_label'] && $value->order_id == $order_id ) {
				// Update the delivery charges for the new date.
				$order_item_id = $value->order_item_id;
				if ( ( $charges_arr['fees'] > 0 && 'on' == $ordd_get_day_wise_setting ) || ( $charges_arr['fees'] > 0 && 'on' == $enable_shipping_based_delivery ) ) {

					$ordd_delivery_charges = $charges_arr['fees'];

					if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
						isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

						$ordd_recalculated_deliverey_tax = WC_Tax::calc_tax( $ordd_delivery_charges, $ordd_wc_get_tax_rate );

						if ( is_array( $ordd_recalculated_deliverey_tax ) && count( $ordd_recalculated_deliverey_tax ) > 0 ) {
							$ordd_recalculated_delivery_tax_amount = $ordd_recalculated_deliverey_tax [1];
							$ordd_delivery_charges                += $ordd_recalculated_delivery_tax_amount;
						}
					}

					wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_delivery_charges );
					wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_delivery_charges );
					wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_delivery_charges );

					$order = wc_get_order( $order_id );

					// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
					$item = $order->get_item( $order_item_id, true );
					$item->set_amount( $ordd_delivery_charges );
					$item->set_total( $ordd_delivery_charges );

					$previous_total = $order->get_meta( '_order_total', true );

					$order_total = $previous_total - $charges_arr['previous_charges_amount'];

					$new_order_total = $order_total + $charges_arr['fees'];
					if ( 'yes' != $ordd_calc_taxes ) {
						$order->update_meta_data( '_order_total', $new_order_total );
					}

					$order->calculate_totals();

				}
				$delivery_charges = 'yes';
			} elseif ( $value->order_item_type == 'fee' && isset( $charges_arr['previous_charges_label'] ) && $value->order_item_name == $charges_arr['previous_charges_label'] && $value->order_id == $order_id ) {
				// Reset the order totals if the new date does not have delivery charges and the previous date had charges.
				$previous_total = $order->get_meta( '_order_total', true );
				$order_total    = $previous_total - $charges_arr['previous_charges_amount'];
				$order->update_meta_data( '_order_total', $order_total );

				wc_delete_order_item( $value->order_item_id );
			}

			if ( $value->order_item_type == 'fee' && $value->order_item_name == $charges_arr['timeslot_charges_label'] && $value->order_id == $order_id ) {
				// Update the time slot charges for the new time slot selected where the previous time slot also had some charges.
				$order_item_id = $value->order_item_id;

				if ( $charges_arr['time_slot_fees'] > 0 ) {

					$ordd_timeslot_charges = $charges_arr['time_slot_fees'];

					if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
						isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

						$ordd_recalculated_timeslot_tax = WC_Tax::calc_tax( $ordd_timeslot_charges, $ordd_wc_get_tax_rate );

						if ( is_array( $ordd_recalculated_timeslot_tax ) && count( $ordd_recalculated_timeslot_tax ) > 0 ) {
							$ordd_recalculated_timeslot_tax_amount = $ordd_recalculated_timeslot_tax [1];
							$ordd_timeslot_charges                += $ordd_recalculated_timeslot_tax_amount;
						}
					}

					wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_timeslot_charges );
					wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_timeslot_charges );
					wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_timeslot_charges );

					$order = wc_get_order( $order_id );

					// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
					$item = $order->get_item( $order_item_id, true );
					$item->set_amount( $ordd_timeslot_charges );
					$item->set_total( $ordd_timeslot_charges );

					/**
					 * If TAX setting is not enabled then only update the order total.
					 */
					if ( 'yes' != $ordd_calc_taxes ) {
						$previous_timeslot_charges = isset( $charges_arr['previous_timeslot_charges_amount'] ) ? $charges_arr['previous_timeslot_charges_amount'] : 0;
						$previous_total            = $order->get_meta( '_order_total', true );
						$order_total               = floatval( $previous_total ) - floatval( $previous_timeslot_charges );
						$new_order_total           = $order_total + $charges_arr['time_slot_fees'];

						$order->update_meta_data( '_order_total', $new_order_total );
					}

					$order->calculate_totals();
				}
				$time_slot_charges = 'yes';
			} elseif ( $value->order_item_type == 'fee' && isset( $charges_arr['previous_timeslot_charges_label'] ) && $value->order_item_name == $charges_arr['previous_timeslot_charges_label'] && $value->order_id == $order_id ) {
				// Reset the totals if the new time slot does not have charges.
				$previous_total = $order->get_meta( '_order_total', true );
				$order_total    = $previous_total - $charges_arr['previous_timeslot_charges_amount'];
				$order->update_meta_data( '_order_total', $order_total );

				wc_delete_order_item( $value->order_item_id );
			}

			// Update same day delivery charges and update the order total
			if ( $value->order_item_type == 'fee' && $value->order_item_name == 'Same Day Delivery Charges' ) {
				$order_item_id = $value->order_item_id;
				if ( $charges_arr['same_day_fees'] > 0 ) {

					$ordd_sameday_charges = $charges_arr['same_day_fees'];

					if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
						isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

						$ordd_recalculated_sameday_tax = WC_Tax::calc_tax( $ordd_sameday_charges, $ordd_wc_get_tax_rate );

						if ( is_array( $ordd_recalculated_sameday_tax ) && count( $ordd_recalculated_sameday_tax ) > 0 ) {
							$ordd_recalculated_same_tax_amount = $ordd_recalculated_sameday_tax [1];
							$ordd_sameday_charges             += $ordd_recalculated_same_tax_amount;
						}
					}

					wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_sameday_charges );
					wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_sameday_charges );
					wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_sameday_charges );

					$order = wc_get_order( $order_id );

					// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
					$item = $order->get_item( $order_item_id, true );
					$item->set_amount( $ordd_sameday_charges );
					$item->set_total( $ordd_sameday_charges );

					if ( 'yes' != $ordd_calc_taxes ) {
						$order_total     = $order->get_meta( '_order_total', true );
						$new_order_total = $order_total + $charges_arr['same_day_fees'];

						$order->update_meta_data( '_order_total', $new_order_total );
					}

					$order->calculate_totals();
				} else {
					if ( 'yes' != $ordd_calc_taxes ) {
						$previous_same_day_charges = wc_get_order_item_meta( $order_item_id, '_line_total', true );
						$order_total               = $order->get_meta( '_order_total', true );
						$new_order_total           = $order_total - $previous_same_day_charges;
						$order->update_meta_data( '_order_total', $new_order_total );
					}
					wc_delete_order_item( $order_item_id );
				}
				$same_day_delivery_charges = 'yes';
			}

			// Update next day delivery charges and update the order total
			if ( $value->order_item_type == 'fee' && $value->order_item_name == 'Next Day Delivery Charges' ) {
				$order_item_id = $value->order_item_id;
				if ( $charges_arr['next_day_fees'] > 0 ) {

					$ordd_nextday_charges = $charges_arr['next_day_fees'];

					if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
						isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

						$ordd_recalculated_nextday_tax = WC_Tax::calc_tax( $ordd_nextday_charges, $ordd_wc_get_tax_rate );

						if ( is_array( $ordd_recalculated_nextday_tax ) && count( $ordd_recalculated_nextday_tax ) > 0 ) {
							$ordd_recalculated_nextday_tax_amount = $ordd_recalculated_nextday_tax [1];
							$ordd_nextday_charges                += $ordd_recalculated_nextday_tax_amount;
						}
					}

					wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_nextday_charges );
					wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_nextday_charges );
					wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_nextday_charges );

					$order = wc_get_order( $order_id );

					// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
					$item = $order->get_item( $order_item_id, true );
					$item->set_amount( $ordd_nextday_charges );
					$item->set_total( $ordd_nextday_charges );

					if ( 'yes' != $ordd_calc_taxes ) {
						$order_total     = $order->get_meta( '_order_total', true );
						$new_order_total = $order_total + $charges_arr['next_day_fees'];

						$order->update_meta_data( '_order_total', $new_order_total );
					}

					$order->calculate_totals();
				} else {
					if ( 'yes' != $ordd_calc_taxes ) {
						$previous_next_day_charges = wc_get_order_item_meta( $order_item_id, '_line_total', true );
						$order_total               = $order->get_meta( '_order_total', true );
						$new_order_total           = $order_total - $previous_next_day_charges;

						$order->update_meta_data( '_order_total', $new_order_total );
					}
					wc_delete_order_item( $order_item_id );
				}
				$next_day_delivery_charges = 'yes';
			}
		}

		if ( ( $delivery_charges == 'no' && 'on' == $ordd_get_day_wise_setting ) || ( $delivery_charges == 'no' && 'on' == $enable_shipping_based_delivery ) ) {
			// Add new delivery charges in the order if the new date has additional charges whereas the old one didn't have.
			if ( $charges_arr['fees'] > 0 ) {
				$args = array(
					'order_item_name' => $charges_arr['charges_label'],
					'order_item_type' => 'fee',
				);
				wc_add_order_item( $order_id, $args ); // create a new line item of type 'fee'
				$get_last_order_item_ids_query = 'SELECT order_item_id FROM `' . $wpdb->prefix . 'woocommerce_order_items` ORDER BY order_item_id DESC LIMIT 1';
				$results_last_order_item_id    = $wpdb->get_results( $get_last_order_item_ids_query ); //phpcs:ignore
				$order_item_id                 = $results_last_order_item_id[0]->order_item_id;

				$ordd_delivery_charges = $charges_arr['fees'];

				if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
					isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

					$ordd_recalculated_deliverey_tax = WC_Tax::calc_tax( $ordd_delivery_charges, $ordd_wc_get_tax_rate );

					if ( is_array( $ordd_recalculated_deliverey_tax ) && count( $ordd_recalculated_deliverey_tax ) > 0 ) {
						$ordd_recalculated_delivery_tax_amount = $ordd_recalculated_deliverey_tax [1];
						$ordd_delivery_charges                += $ordd_recalculated_delivery_tax_amount;
					}
				}

				wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_delivery_charges );
				wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_delivery_charges );
				wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_delivery_charges );

				$order = wc_get_order( $order_id );

				// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
				$item = $order->get_item( $order_item_id, true );
				$item->set_amount( $ordd_delivery_charges );
				$item->set_total( $ordd_delivery_charges );
				// wc_update_order_item_meta( $order_item_id, "_line_tax", 0 );
				// wc_update_order_item_meta( $order_item_id, "_line_subtotal_tax", 0 );
				if ( 'yes' != $ordd_calc_taxes ) {
					$previous_charges = isset( $charges_arr['previous_charges_amount'] ) ? $charges_arr['previous_charges_amount'] : 0;
					$previous_total   = $order->get_meta( '_order_total', true );
					$order_total      = $previous_total - $previous_charges;
					$new_order_total  = $order_total + $charges_arr['fees'];

					$order->update_meta_data( '_order_total', $new_order_total );
				}

				$order->calculate_totals();
			}
		}

		if ( $same_day_delivery_charges == 'no' ) {
			if ( $charges_arr['same_day_fees'] > 0 ) {
				$args = array(
					'order_item_name' => 'Same Day Delivery Charges',
					'order_item_type' => 'fee',
				);
				wc_add_order_item( $order_id, $args );
				$get_last_order_item_ids_query = 'SELECT order_item_id FROM `' . $wpdb->prefix . 'woocommerce_order_items` ORDER BY order_item_id DESC LIMIT 1';
				$results_last_order_item_id    = $wpdb->get_results( $get_last_order_item_ids_query ); //phpcs:ignore
				$order_item_id                 = $results_last_order_item_id[0]->order_item_id;

				$ordd_sameday_charges = $charges_arr['same_day_fees'];

				if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
					isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

					$ordd_recalculated_sameday_tax = WC_Tax::calc_tax( $ordd_sameday_charges, $ordd_wc_get_tax_rate );

					if ( is_array( $ordd_recalculated_sameday_tax ) && count( $ordd_recalculated_sameday_tax ) > 0 ) {
						$ordd_recalculated_same_tax_amount = $ordd_recalculated_sameday_tax [1];
						$ordd_sameday_charges             += $ordd_recalculated_same_tax_amount;
					}
				}

				wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_sameday_charges );
				wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_sameday_charges );
				wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_sameday_charges );

				$order = wc_get_order( $order_id );

				// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
				$item = $order->get_item( $order_item_id, true );
				$item->set_amount( $ordd_sameday_charges );
				$item->set_total( $ordd_sameday_charges );

				// wc_update_order_item_meta( $order_item_id, "_line_tax", 0 );
				// wc_update_order_item_meta( $order_item_id, "_line_subtotal_tax", 0 );
				if ( 'yes' != $ordd_calc_taxes ) {
					$order_total     = $order->get_meta( '_order_total', true );
					$new_order_total = $order_total + $charges_arr['same_day_fees'];

					$order->update_meta_data( '_order_total', $new_order_total );
				}

				$order->calculate_totals();
			}
		}

		if ( $next_day_delivery_charges == 'no' ) {
			if ( $charges_arr['next_day_fees'] > 0 ) {
				$args = array(
					'order_item_name' => 'Next Day Delivery Charges',
					'order_item_type' => 'fee',
				);
				wc_add_order_item( $order_id, $args );
				$get_last_order_item_ids_query = 'SELECT order_item_id FROM `' . $wpdb->prefix . 'woocommerce_order_items` ORDER BY order_item_id DESC LIMIT 1';
				$results_last_order_item_id    = $wpdb->get_results( $get_last_order_item_ids_query ); //phpcs:ignore
				$order_item_id                 = $results_last_order_item_id[0]->order_item_id;

				$ordd_nextday_charges = $charges_arr['next_day_fees'];

				if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
					isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

					$ordd_recalculated_nextday_tax = WC_Tax::calc_tax( $ordd_nextday_charges, $ordd_wc_get_tax_rate );

					if ( is_array( $ordd_recalculated_nextday_tax ) && count( $ordd_recalculated_nextday_tax ) > 0 ) {
						$ordd_recalculated_nextday_tax_amount = $ordd_recalculated_nextday_tax [1];
						$ordd_nextday_charges                += $ordd_recalculated_nextday_tax_amount;
					}
				}

				wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_nextday_charges );
				wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_nextday_charges );
				wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_nextday_charges );

				$order = wc_get_order( $order_id );

				// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
				$item = $order->get_item( $order_item_id, true );
				$item->set_amount( $ordd_nextday_charges );
				$item->set_total( $ordd_nextday_charges );
				// wc_update_order_item_meta( $order_item_id, "_line_tax", 0 );
				// wc_update_order_item_meta( $order_item_id, "_line_subtotal_tax", 0 );
				if ( 'yes' != $ordd_calc_taxes ) {
					$order_total     = $order->get_meta( '_order_total', true );
					$new_order_total = $order_total + $charges_arr['next_day_fees'];

					$order->update_meta_data( '_order_total', $new_order_total );
				}

				$order->calculate_totals();
			}
		}

		if ( $time_slot_charges == 'no' ) {
			// Add new time slot charges in the order if the new timeslot has additional charges whereas the old one didn't have.

			if ( $charges_arr['time_slot_fees'] > 0 ) {
				$args = array(
					'order_item_name' => $charges_arr['timeslot_charges_label'],
					'order_item_type' => 'fee',
				);
				wc_add_order_item( $order_id, $args );
				$get_last_order_item_ids_query = 'SELECT order_item_id FROM `' . $wpdb->prefix . 'woocommerce_order_items` ORDER BY order_item_id DESC LIMIT 1';
				$results_last_order_item_id    = $wpdb->get_results( $get_last_order_item_ids_query ); //phpcs:ignore
				$order_item_id                 = $results_last_order_item_id[0]->order_item_id;

				$ordd_timeslot_charges = $charges_arr['time_slot_fees'];

				if ( isset( $ordd_calc_taxes ) && 'yes' == $ordd_calc_taxes &&
					isset( $ordd_prices_include_tax ) && 'yes' == $ordd_prices_include_tax ) {

					$ordd_recalculated_timeslot_tax = WC_Tax::calc_tax( $ordd_timeslot_charges, $ordd_wc_get_tax_rate );
					if ( is_array( $ordd_recalculated_timeslot_tax ) && count( $ordd_recalculated_timeslot_tax ) > 0 ) {
						$ordd_recalculated_timeslot_tax_amount = $ordd_recalculated_timeslot_tax [1];
						$ordd_timeslot_charges                += $ordd_recalculated_timeslot_tax_amount;
					}
				}

				wc_update_order_item_meta( $order_item_id, '_line_total', $ordd_timeslot_charges );
				wc_update_order_item_meta( $order_item_id, '_line_subtotal', $ordd_timeslot_charges );
				wc_update_order_item_meta( $order_item_id, '_fee_amount', $ordd_timeslot_charges );

				$order = wc_get_order( $order_id );

				// Set the item's amount and total separately so that the total will be calculated correctly using calculate_totals()
				$item = $order->get_item( $order_item_id, true );
				$item->set_amount( $ordd_timeslot_charges );
				$item->set_total( $ordd_timeslot_charges );
				// wc_update_order_item_meta( $order_item_id, "_line_tax", 0 );
				// wc_update_order_item_meta( $order_item_id, "_line_subtotal_tax", 0 );

				if ( 'yes' != $ordd_calc_taxes ) {
					$previous_timeslot_charges = isset( $charges_arr['previous_timeslot_charges_amount'] ) ? $charges_arr['previous_timeslot_charges_amount'] : 0;
					$previous_total            = $order->get_meta( '_order_total', true );
					$order_total               = $previous_total - $previous_timeslot_charges;

					$new_order_total = $order_total + $charges_arr['time_slot_fees'];

					$order->update_meta_data( '_order_total', $new_order_total );
				}
				$order->calculate_totals();
			}
		}
		$order->save();
	}

	/**
	 * Get the delivery charges of the selected date and time
	 *
	 * @gloabls resource $orddd_weekdays Weekdays array
	 * @globals resource $wpdb WordPress Object
	 *
	 * @param int    $order_id Order ID
	 * @param string $delivery_date Selected delivery date
	 * @param string $time_slot Selected time slot
	 *
	 * @return array Charges to be added for a delivery date & time
	 * @since 5.7
	 */
	public static function orddd_get_delivery_date_charges( $delivery_date, $time_slot, $order_id = '' ) {
		global $wpdb, $orddd_weekdays;
		if ( '' == $delivery_date ) {
			return;
		}
		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time = current_time( 'timestamp', $gmt );

		$shipping_based_timeslot_fees = $shipping_based_fees = 'No';
		$charges_arr                  = array(
			'time_slot_fees'         => 0,
			'timeslot_charges_label' => '',
			'next_day_fees'          => 0,
			'same_day_fees'          => 0,
			'fees'                   => 0,
			'charges_label'          => '',
		);

		$day          = $timeslot_selected = $shipping_class_to_load = '';
		$current_date = gmdate( 'j-n-Y', $current_time );
		$next_day     = gmdate( 'j-n-Y', strtotime( '+1 day', strtotime( $current_date ) ) );

		$previous_charges_labels = self::orddd_previous_charges_label( $order_id );

		if ( '' != $time_slot ) {
			$time_slot_arr = explode( ' - ', $time_slot );
			$from_time     = gmdate( 'G:i', strtotime( $time_slot_arr[0] ) );
			if ( isset( $time_slot_arr[1] ) && $time_slot_arr[1] != '' ) {
				$to_time           = gmdate( 'G:i', strtotime( $time_slot_arr[1] ) );
				$timeslot_selected = $from_time . ' - ' . $to_time;
			} else {
				$timeslot_selected = $from_time;
			}
		}

		$shipping_based       = $specific_fees = $specific_timeslot_fees = 'No';
		$delivery_dates_array = array();

		$results                     = self::orddd_get_shipping_settings();
		$shipping_settings           = array();
		$custom_delivery_schedule_id = orddd_custom_delivery_functions::orddd_get_delivery_schedule_id( $order_id );

		if ( 0 != $custom_delivery_schedule_id ) {
			$shipping_settings = orddd_common::orddd_get_option( 'orddd_shipping_based_settings_' . $custom_delivery_schedule_id );
		}

		if ( is_array( $shipping_settings ) && count( $shipping_settings ) > 0 ) {
			$shipping_methods_enabled = self::orddd_get_shipping_method_enabled();

			// Time slot charges
			if ( isset( $shipping_settings['time_slots'] ) && $shipping_settings['time_slots'] != '' ) {
				$time_slot_settings = explode( '},', $shipping_settings['time_slots'] );
				$time_slot_str      = '';
				$i                  = 0;
				foreach ( $time_slot_settings as $hk => $hv ) {
					if ( $hv != '' ) {
						$timeslot_values          = self::get_timeslot_values( $hv );
						$additional_charges       = $timeslot_values['additional_charges'];
						$additional_charges_label = $timeslot_values['additional_charges_label'];
						if ( $timeslot_values['delivery_days_selected'] == 'weekdays' ) {
							$weekday = gmdate( 'w', strtotime( $delivery_date ) );
							foreach ( $timeslot_values['selected_days'] as $key => $val ) {
								if ( $timeslot_selected == $timeslot_values['time_slot'] && ( $val == 'orddd_weekday_' . $weekday . '_custom_setting' || $val == 'all' ) ) {
									if ( $additional_charges > 0 && $additional_charges != '' ) {
										$charges_arr['timeslot_charges_label'] = $additional_charges_label;
										if ( $additional_charges > 0 && $additional_charges != '' ) {
											$charges_arr['time_slot_fees'] = $additional_charges;
											if ( $charges_arr['timeslot_charges_label'] == '' ) {
												$charges_arr['timeslot_charges_label'] = 'Time Slot Charges';
											}
										}
									}
								}
								$i++;
							}
						} elseif ( $timeslot_values['delivery_days_selected'] == 'specific_dates' ) {
							foreach ( $timeslot_values['selected_days'] as $key => $val ) {
								$specific_delivery_date = gmdate( 'n-j-Y', strtotime( $delivery_date ) );
								if ( $timeslot_selected == $timeslot_values['time_slot'] && $val == $specific_delivery_date ) {
									$charges_arr['timeslot_charges_label'] = $additional_charges_label;
									if ( $additional_charges > 0 && $additional_charges != '' ) {
										$charges_arr['time_slot_fees'] = $additional_charges;
										if ( $charges_arr['timeslot_charges_label'] == '' ) {
											$charges_arr['timeslot_charges_label'] = 'Time Slot Charges';
										}
									}
								}
							}
						}

						if ( isset( $previous_charges_labels['previous_order_timeslot'] ) ) {
							$previous_time_slot_arr = explode( ' - ', $previous_charges_labels['previous_order_timeslot'] );
							$previous_from_time     = gmdate( 'G:i', strtotime( $previous_time_slot_arr[0] ) );
							if ( isset( $previous_time_slot_arr[1] ) && $previous_time_slot_arr[1] != '' ) {
								$previous_to_time           = gmdate( 'G:i', strtotime( $previous_time_slot_arr[1] ) );
								$previous_timeslot_selected = $previous_from_time . ' - ' . $previous_to_time;
							} else {
								$previous_timeslot_selected = $previous_from_time;
							}

							if ( $timeslot_values['time_slot'] == $previous_timeslot_selected ) {
								$charges_arr['previous_timeslot_charges_label']  = $additional_charges_label;
								$charges_arr['previous_timeslot_charges_amount'] = $additional_charges;
								if ( $charges_arr['previous_timeslot_charges_label'] == '' ) {
									$charges_arr['previous_timeslot_charges_label'] = 'Time Slot Charges';
								}
							}
						}
					}
				}
				$shipping_based_timeslot_fees = 'Yes';
			}

			// Delivery charges
			if ( isset( $shipping_settings['delivery_type'] ) ) {
				$delivery_type = $shipping_settings['delivery_type'];
			}

			if ( isset( $delivery_type['specific_dates'] ) && $delivery_type['specific_dates'] == 'on' ) {
				$specific_days_settings = explode( ',', rtrim( $shipping_settings['specific_dates'], ',' ) );
				if ( ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
					$date = gmdate( 'n-j-Y', strtotime( $delivery_date ) );
				} else {
					$date = '';
				}

				foreach ( $specific_days_settings as $sk => $sv ) {
					if ( $sv != '' ) {
						$specific_delivery_str = str_replace( '}', '', $sv );
						$specific_delivery_str = str_replace( '{', '', $specific_delivery_str );
						$specific_date_arr     = explode( ':', $specific_delivery_str );
						$fees                  = $specific_date_arr[1];
						if ( $date == $specific_date_arr[0] ) {
							if ( has_filter( 'orddd_add_delivery_date_fees' ) ) {
								$fees = apply_filters( 'orddd_add_delivery_date_fees', $delivery_date, $fees );
							}
							if ( $fees > 0 ) {
								$specific_charges_label       = $specific_date_arr[2];
								$charges_arr['fees']          = $fees;
								$charges_arr['charges_label'] = $specific_charges_label;
								if ( $specific_charges_label == '' ) {
									$charges_arr['charges_label'] = 'Delivery Charges';
								}
								$specific_fees = 'Yes';
							}
						}

						if ( isset( $previous_charges_labels['previous_order_date_check'] ) && $specific_date_arr[0] == $previous_charges_labels['previous_order_date_check'] ) {
							$previous_charges_label                 = $specific_date_arr[2];
							$charges_arr['previous_charges_label']  = $previous_charges_label;
							$charges_arr['previous_charges_amount'] = $fees;
							if ( $charges_arr['previous_charges_label'] == '' ) {
								$charges_arr['previous_charges_label'] = 'Delivery Charges';
							}
						}
						$delivery_dates_array[] = $specific_date_arr[0];
					}
				}

				if ( $specific_fees == 'No' && ! in_array( $date, $delivery_dates_array ) ) {
					$day = gmdate( 'w', strtotime( $delivery_date ) );
					if ( isset( $delivery_type['weekdays'] ) && $delivery_type['weekdays'] == 'on' ) {
						$weekdays_settings = $shipping_settings['weekdays'];
						foreach ( $orddd_weekdays as $n => $day_name ) {
							if ( $n == 'orddd_weekday_' . $day ) {
								$weekday = $weekdays_settings[ $n ];
								if ( isset( $weekday['additional_charges'] ) && $weekday['additional_charges'] != '' && $weekday['additional_charges'] != 0 ) {
									if ( isset( $weekday['delivery_charges_label'] ) && $weekday['delivery_charges_label'] != '' ) {
										$charges_arr['charges_label'] = $weekday['delivery_charges_label'];
									} else {
										$charges_arr['charges_label'] = 'Delivery Charges';
									}
									$charges_arr['fees'] = $weekday['additional_charges'];
								}
							}

							if ( $n == 'orddd_weekday_' . $previous_charges_labels['previous_order_weekday_check'] ) {
								$weekday = $weekdays_settings[ $n ];
								if ( isset( $weekday['additional_charges'] ) && $weekday['additional_charges'] != '' && $weekday['additional_charges'] != 0 ) {
									$charges_arr['previous_charges_label']  = $weekday['delivery_charges_label'];
									$charges_arr['previous_charges_amount'] = $weekday['additional_charges'];
									if ( $charges_arr['previous_charges_label'] == '' ) {
										$charges_arr['previous_charges_label'] = 'Delivery Charges';
									}
								}
							}
						}
					}
				}
			} elseif ( isset( $delivery_type['weekdays'] ) && $delivery_type['weekdays'] == 'on' ) {
				if ( ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
					$day = gmdate( 'w', strtotime( $delivery_date ) );
				} else {
					$day = '';
				}
				$weekdays_settings = $shipping_settings['weekdays'];
				foreach ( $orddd_weekdays as $n => $day_name ) {
					if ( $n == 'orddd_weekday_' . $day ) {
						$weekday = $weekdays_settings[ $n ];
						if ( isset( $weekday['additional_charges'] ) && $weekday['additional_charges'] != '' && $weekday['additional_charges'] != 0 ) {
							if ( isset( $weekday['delivery_charges_label'] ) && $weekday['delivery_charges_label'] != '' ) {
								$charges_arr['charges_label'] = $weekday['delivery_charges_label'];
							} else {
								$charges_arr['charges_label'] = 'Delivery Charges';
							}
							$charges_arr['fees'] = $weekday['additional_charges'];
						}
					}

					if ( $n == 'orddd_weekday_' . $previous_charges_labels['previous_order_weekday_check'] ) {
						$weekday = $weekdays_settings[ $n ];
						if ( isset( $weekday['additional_charges'] ) && $weekday['additional_charges'] != '' && $weekday['additional_charges'] != 0 ) {
							$charges_arr['previous_charges_label']  = $weekday['delivery_charges_label'];
							$charges_arr['previous_charges_amount'] = $weekday['additional_charges'];
							if ( $charges_arr['previous_charges_label'] == '' ) {
								$charges_arr['previous_charges_label'] = 'Delivery Charges';
							}
						}
					}
				}
			}

			if ( isset( $shipping_settings['same_day'] ) ) {
				$same_day = $shipping_settings['same_day'];
				if ( isset( $same_day['after_hours'] ) && $same_day['after_hours'] == 0 && isset( $same_day ['after_minutes'] ) && $same_day['after_minutes'] == '00' ) {
				} else {
					if ( isset( $same_day['additional_charges'] ) && $same_day['additional_charges'] != 0 && $same_day['additional_charges'] != '' ) {
						if ( $current_date == $delivery_date ) {
							$charges_arr['same_day_fees'] = $same_day['additional_charges'];
						}
					}
				}
			}

			if ( isset( $shipping_settings['next_day'] ) ) {
				$next_day_setting = $shipping_settings['next_day'];
				if ( isset( $next_day_setting['after_hours'] ) && $next_day_setting['after_hours'] == 0 && isset( $next_day_setting ['after_minutes'] ) && $next_day_setting['after_minutes'] == '00' ) {
				} else {
					if ( isset( $next_day_setting['additional_charges'] ) && $next_day_setting['additional_charges'] != 0 && $next_day_setting['additional_charges'] != '' ) {
						if ( $next_day == $delivery_date && ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
							$charges_arr['next_day_fees'] = $next_day_setting['additional_charges'];
						}
					}
				}
			}
			
			$shipping_based_fees = 'Yes';
		}

		if ( 'No' == $shipping_based_timeslot_fees && '' != $time_slot ) {
			$timeslot_log_arr = ORDDD_Functions::orddd_get_timeslots();

			foreach ( $timeslot_log_arr as $k => $v ) {
				$ft = $v->fh . ':' . trim( $v->fm );
				if ( $v->th != 00 ) {
					$tt            = $v->th . ':' . trim( $v->tm );
					$time_slot_key = $ft . ' - ' . $tt;
				} else {
					$time_slot_key = $from_time;
				}

				if ( gettype( json_decode( $v->dd ) ) == 'array' && count( json_decode( $v->dd ) ) > 0 && get_option( 'orddd_enable_specific_delivery_dates' ) == 'on' ) {
					$dd = json_decode( $v->dd );
					if ( is_array( $dd ) && count( $dd ) > 0 ) {
						foreach ( $dd as $dkey => $dval ) {
							$specific_delivery_date = gmdate( 'n-j-Y', strtotime( $delivery_date ) );
							if ( $timeslot_selected == $time_slot_key && $dval == $specific_delivery_date ) {
								$additional_charges                    = $v->additional_charges;
								$charges_arr['timeslot_charges_label'] = $v->additional_charges_label;
								if ( $additional_charges > 0 && $additional_charges != '' ) {
									$charges_arr['time_slot_fees'] = $additional_charges;
									if ( $charges_arr['timeslot_charges_label'] == '' ) {
										$charges_arr['timeslot_charges_label'] = 'Time Slot Charges';
									}
									$specific_timeslot_fees = 'Yes';
								}
							}

							if ( ( isset( $previous_timeslot_selected ) &&
								$time_slot_key == $previous_timeslot_selected ) &&
								$delivery_date == $previous_charges_labels['previous_order_date_check'] ) {
								$charges_arr['previous_timeslot_charges_label']  = $v->additional_charges_label;
								$charges_arr['previous_timeslot_charges_amount'] = $v->additional_charges;
								if ( $charges_arr['previous_timeslot_charges_label'] == '' ) {
									$charges_arr['previous_timeslot_charges_label'] = 'Time Slot Charges';
								}
							}
						}
					}
				}

				if ( 'No' == $specific_timeslot_fees ) {
					$weekday = gmdate( 'w', strtotime( $delivery_date ) );
					if ( gettype( json_decode( $v->dd ) ) == 'array' && count( json_decode( $v->dd ) ) > 0 ) {
						$dd = json_decode( $v->dd );
						foreach ( $dd as $dkey => $dval ) {
							if ( $timeslot_selected == $time_slot_key && ( $dval == 'orddd_weekday_' . $weekday || $dval == 'all' ) ) {
								$additional_charges                    = $v->additional_charges;
								$charges_arr['timeslot_charges_label'] = $v->additional_charges_label;
								if ( $additional_charges > 0 && $additional_charges != '' ) {
									$charges_arr['time_slot_fees'] = $additional_charges;
									if ( $charges_arr['timeslot_charges_label'] == '' ) {
										$charges_arr['timeslot_charges_label'] = 'Time Slot Charges';
									}
								}
							}

							if ( isset( $previous_charges_labels['previous_order_timeslot'] ) ) {
								$previous_time_slot_arr = explode( ' - ', $previous_charges_labels['previous_order_timeslot'] );
								$previous_from_time     = gmdate( 'G:i', strtotime( $previous_time_slot_arr[0] ) );
								if ( isset( $previous_time_slot_arr[1] ) && $previous_time_slot_arr[1] != '' ) {
									$previous_to_time           = gmdate( 'G:i', strtotime( $previous_time_slot_arr[1] ) );
									$previous_timeslot_selected = $previous_from_time . ' - ' . $previous_to_time;
								} else {
									$previous_timeslot_selected = $previous_from_time;
								}

								if ( $time_slot_key == $previous_timeslot_selected ) {
									$charges_arr['previous_timeslot_charges_label']  = $v->additional_charges_label;
									$charges_arr['previous_timeslot_charges_amount'] = $v->additional_charges;
									if ( $charges_arr['previous_timeslot_charges_label'] == '' ) {
										$charges_arr['previous_timeslot_charges_label'] = 'Time Slot Charges';
									}
								}
							}
						}
					} else {
						if ( $timeslot_selected == $time_slot_key && ( $v->dd == 'orddd_weekday_' . $weekday || $v->dd == 'all' ) ) {
							$additional_charges                    = $v->additional_charges;
							$charges_arr['timeslot_charges_label'] = $v->additional_charges_label;
							if ( $additional_charges > 0 && $additional_charges != '' ) {
								$charges_arr['time_slot_fees'] = $additional_charges;
								if ( $charges_arr['timeslot_charges_label'] == '' ) {
									$charges_arr['timeslot_charges_label'] = 'Time Slot Charges';
								}
							}
						}

						if ( isset( $previous_charges_labels['previous_order_timeslot'] ) ) {
							$previous_time_slot_arr = explode( ' - ', $previous_charges_labels['previous_order_timeslot'] );
							$previous_from_time     = gmdate( 'G:i', strtotime( $previous_time_slot_arr[0] ) );
							if ( isset( $previous_time_slot_arr[1] ) && $previous_time_slot_arr[1] != '' ) {
								$previous_to_time           = gmdate( 'G:i', strtotime( $previous_time_slot_arr[1] ) );
								$previous_timeslot_selected = $previous_from_time . ' - ' . $previous_to_time;
							} else {
								$previous_timeslot_selected = $previous_from_time;
							}
							if ( $time_slot_key == $previous_timeslot_selected ) {
								$charges_arr['previous_timeslot_charges_label']  = $v->additional_charges_label;
								$charges_arr['previous_timeslot_charges_amount'] = $v->additional_charges;
								if ( $charges_arr['previous_timeslot_charges_label'] == '' ) {
									$charges_arr['previous_timeslot_charges_label'] = 'Time Slot Charges';
								}
							}
						}
					}
				}
			}
		}

		if ( 'No' == $shipping_based_fees ) {
			$delivery_dates_array = array();
			$date                 = '';
			if ( ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
				$date = gmdate( 'n-j-Y', strtotime( $delivery_date ) );
			}

			if ( get_option( 'orddd_enable_specific_delivery_dates' ) == 'on' ) {
				$delivery_dates = get_option( 'orddd_delivery_dates' );
				if ( $delivery_dates != '' && $delivery_dates != '{}' && $delivery_dates != '[]' && $delivery_dates != 'null' ) {
					$delivery_dates_arr = json_decode( get_option( 'orddd_delivery_dates' ) );
				} else {
					$delivery_dates_arr = array();
				}
				if ( is_array( $delivery_dates_arr ) && count( $delivery_dates_arr ) > 0 ) {
					if ( ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
						foreach ( $delivery_dates_arr as $key => $value ) {
							foreach ( $value as $k => $v ) {
								$temp_arr[ $k ] = $v;
							}

							if ( $date == $temp_arr['date'] ) {
								$charges_arr['fees']          = $temp_arr['fees'];
								$charges_arr['charges_label'] = $temp_arr['label'];
								if ( $temp_arr['label'] == '' ) {
									$charges_arr['charges_label'] = 'Delivery Charges';
								}

								if ( $temp_arr['fees'] > 0 ) {
									$specific_fees = 'Yes';
								}
							}

							if ( isset( $previous_charges_labels['previous_order_date_check'] ) && $temp_arr['date'] == $previous_charges_labels['previous_order_date_check'] ) {
								$charges_arr['previous_charges_label']  = $temp_arr['label'];
								$charges_arr['previous_charges_amount'] = $temp_arr['fees'];
								if ( $charges_arr['previous_charges_label'] == '' ) {
									$charges_arr['previous_charges_label'] = 'Delivery Charges';
								}
							}
							$delivery_dates_array[] = $temp_arr['date'];
						}
					}
				}
			}

			if ( $specific_fees == 'No' && ! in_array( $date, $delivery_dates_array ) ) {
				if ( ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
					$day = gmdate( 'w', strtotime( $delivery_date ) );
				}

				$fee_var = 'additional_charges_orddd_weekday_' . $day;

				$charges_arr['charges_label'] = get_option( 'delivery_charges_label_orddd_weekday_' . $day );
				if ( $charges_arr['charges_label'] == '' ) {
					$charges_arr['charges_label'] = 'Delivery Charges';
				}

				$previous_fee_var                       = isset( $previous_charges_labels['previous_order_weekday_check'] ) ? 'additional_charges_orddd_weekday_' . $previous_charges_labels['previous_order_weekday_check'] : '';
				$charges_arr['previous_charges_label']  = isset( $previous_charges_labels['previous_order_weekday_check'] ) ? get_option( 'delivery_charges_label_orddd_weekday_' . $previous_charges_labels['previous_order_weekday_check'] ) : '';
				$charges_arr['previous_charges_amount'] = get_option( $previous_fee_var );
				if ( $charges_arr['previous_charges_label'] == '' ) {
					$charges_arr['previous_charges_label'] = 'Delivery Charges';
				}

				$charges_arr['fees'] = get_option( $fee_var );
			}

			$same_day_cutoff_enabled = ORDDD_Functions::orddd_same_day_cutoff_enabled();
			$next_day_cutoff_enabled = ORDDD_Functions::orddd_next_day_cutoff_enabled();

			if ( 'on' === $same_day_cutoff_enabled ) {
				if ( ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
					if ( $current_date == $delivery_date ) {
						$charges_arr['same_day_fees'] = get_option( 'orddd_same_day_additional_charges' );
					}
				}
			}

			if ( 'on' === $next_day_cutoff_enabled ) {
				if ( ! preg_match( '/[A-Za-z]/', $delivery_date ) ) {
					if ( $next_day == $delivery_date ) {
						$charges_arr['next_day_fees'] = get_option( 'orddd_next_day_additional_charges' );
					}
				}
			}
		}
		return $charges_arr;
	}

	/**
	 * Get the delivery charges of the selected date and time
	 *
	 * @param resource $order WC_Order object
	 *
	 * @return string Shipping method for the order
	 * @since 5.7
	 */

	public static function orddd_get_shipping_method_for_order( $order ) {
		$order_shipping_method_id = '';
		$shipping_items           = $order->get_items( 'shipping' );
		foreach ( $shipping_items as $el ) {
			$order_shipping_method_id = $el['method_id'];
			if ( isset( $el['pickup_location'] ) && '' != $el['pickup_location'] ) {
				$shipping_methods_values  = $el['pickup_location'];
				$pickup_location_arr      = unserialize( $shipping_methods_values, ['allowed_classes' => false] );
				$order_shipping_method_id = 'orddd_pickup_location_' . $pickup_location_arr['id'];
			}
		}
		return $order_shipping_method_id;
	}

	/**
	 * Get service name for the USPS services from WooCommerce USPS shipping plugin
	 *
	 * @param string $usps_service_id USPS service id
	 *
	 * @return string Service name
	 * @since 6.1
	 */
	public static function orddd_get_shipping_service_name( $usps_service_id ) {
		switch ( $usps_service_id ) {
			case 'D_FIRST_CLASS':
				$usps_service_name = 'First-Class Mail®';
				break;
			case 'D_EXPRESS_MAIL':
				$usps_service_name = 'Priority Mail Express™';
				break;
			case 'D_STANDARD_POST':
				$usps_service_name = 'Retail Ground™';
				break;
			case 'D_MEDIA_MAIL':
				$usps_service_name = 'Media Mail';
				break;
			case 'D_LIBRARY_MAIL':
				$usps_service_name = 'Library Mail';
				break;
			case 'D_PRIORITY_MAIL':
				$usps_service_name = 'Priority Mail®';
				break;
			case 'I_EXPRESS_MAIL':
				$usps_service_name = 'Priority Mail Express International™';
				break;
			case 'I_PRIORITY_MAIL':
				$usps_service_name = 'Priority Mail International®';
				break;
			case 'I_GLOBAL_EXPRESS':
				$usps_service_name = 'Global Express Guaranteed® (GXG)';
				break;
			case 'I_FIRST_CLASS':
				$usps_service_name = 'First Class Mail® International';
				break;
			case 'I_POSTCARDS':
				$usps_service_name = 'International Postcards';
				break;
		}
		return $usps_service_name;
	}

	/**
	 * Get service name for the fedex services
	 *
	 * @param string $fedex_services_id Fedex service id
	 *
	 * @return string Service name
	 * @since 7.2
	 */
	public static function orddd_get_fedex_service_name( $fedex_services_id ) {
		switch ( $fedex_services_id ) {
			case 'FIRST_OVERNIGHT':
				$fedex_services_name = 'FedEx First Overnight';
				break;
			case 'PRIORITY_OVERNIGHT':
				$fedex_services_name = 'FedEx Priority Overnight';
				break;
			case 'STANDARD_OVERNIGHT':
				$fedex_services_name = 'FedEx Standard Overnight';
				break;
			case 'FEDEX_2_DAY_AM':
				$fedex_services_name = 'FedEx 2Day A.M';
				break;
			case 'FEDEX_2_DAY':
				$fedex_services_name = 'FedEx 2Day';
				break;
			case 'FEDEX_EXPRESS_SAVER':
				$fedex_services_name = 'FedEx Express Saver';
				break;
			case 'GROUND_HOME_DELIVERY':
				$fedex_services_name = 'FedEx Ground Home Delivery';
				break;
			case 'FEDEX_GROUND':
				$fedex_services_name = 'FedEx Ground';
				break;
			case 'INTERNATIONAL_ECONOMY':
				$fedex_services_name = 'FedEx International Economy';
				break;
			case 'INTERNATIONAL_FIRST':
				$fedex_services_name = 'FedEx International First';
				break;
			case 'INTERNATIONAL_PRIORITY':
				$fedex_services_name = 'FedEx International Priority';
				break;
			case 'EUROPE_FIRST_INTERNATIONAL_PRIORITY':
				$fedex_services_name = 'FedEx Europe First International Priority';
				break;
			case 'FEDEX_1_DAY_FREIGHT':
				$fedex_services_name = 'FedEx 1 Day Freight';
				break;
			case 'FEDEX_2_DAY_FREIGHT':
				$fedex_services_name = 'FedEx 2 Day Freight';
				break;
			case 'FEDEX_3_DAY_FREIGHT':
				$fedex_services_name = 'FedEx 3 Day Freight';
				break;
			case 'INTERNATIONAL_ECONOMY_FREIGHT':
				$fedex_services_name = 'FedEx Economy Freight';
				break;
			case 'INTERNATIONAL_PRIORITY_FREIGHT':
				$fedex_services_name = 'FedEx Priority Freight';
				break;
			case 'FEDEX_FREIGHT':
				$fedex_services_name = 'Fedex Freight';
				break;
			case 'FEDEX_NATIONAL_FREIGHT':
				$fedex_services_name = 'FedEx National Freight';
				break;
			case 'INTERNATIONAL_GROUND':
				$fedex_services_name = 'FedEx International Ground';
				break;
			case 'SMART_POST':
				$fedex_services_name = 'FedEx Smart Post';
				break;
			case 'FEDEX_FIRST_FREIGHT':
				$fedex_services_name = 'FedEx First Freight';
				break;
			case 'FEDEX_FREIGHT_ECONOMY':
				$fedex_services_name = 'FedEx Freight Economy';
				break;
			case 'FEDEX_FREIGHT_PRIORITY':
				$fedex_services_name = 'FedEx Freight Priority';
				break;
			case 'FEDEX_DISTANCE_DEFERRED':
				$fedex_services_name = 'FedEx Distance Deferred';
				break;
			case 'FEDEX_NEXT_DAY_EARLY_MORNING':
				$fedex_services_name = 'FedEx Next Day Early Morning';
				break;
			case 'FEDEX_NEXT_DAY_MID_MORNING':
				$fedex_services_name = 'FedEx Next Day Mid Morning';
				break;
			case 'FEDEX_NEXT_DAY_AFTERNOON':
				$fedex_services_name = 'FedEx Next Day Afternoon';
				break;
			case 'FEDEX_NEXT_DAY_END_OF_DAY':
				$fedex_services_name = 'FedEx Next Day End of Day';
				break;
			default:
				$fedex_services_name = '';
				break;
		}
		return $fedex_services_name;
	}

	/**
	 * Get service name for the ups services
	 *
	 * @param string $fedex_services_id Fedex service id
	 *
	 * @return string Service name
	 * @since 8.6
	 */
	public static function orddd_get_ups_service_name( $ups_services_key ) {
		switch ( $ups_services_key ) {
			// Domestic.
			case '12':
				$ups_services_name = '3 Day Select (UPS)';
				break;
			case '03':
				$ups_services_name = 'Ground (UPS)';
				break;
			case '02':
				$ups_services_name = '2nd Day Air (UPS)';
				break;
			case '59':
				$ups_services_name = '2nd Day Air AM (UPS)';
				break;
			case '01':
				$ups_services_name = 'Next Day Air (UPS)';
				break;
			case '13':
				$ups_services_name = 'Next Day Air Saver (UPS)';
				break;
			case '14':
				$ups_services_name = 'Next Day Air Early AM (UPS)';
				break;
			// International.
			case '11':
				$ups_services_name = 'Standard (UPS)';
				break;
			case '07':
				$ups_services_name = 'Worldwide Express (UPS)';
				break;
			case '54':
				$ups_services_name = 'Worldwide Express Plus (UPS)';
				break;
			case '08':
				$ups_services_name = 'Worldwide Expedited Standard (UPS)';
				break;
			case '65':
				$ups_services_name = 'Worldwide Saver (UPS)';
				break;
			default:
				$ups_services_name = '';
				break;
		}
		return $ups_services_name;
	}

	/**
	 * Get order details to be exported to the google calendar
	 *
	 * @param int     $order_id Order ID
	 * @param boolean $from_order_page true/false
	 *
	 * @return array Order details
	 * @since 6.5
	 */
	public static function orddd_get_event_details( $order_id, $from_order_page = false ) {
		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$current_time = current_time( 'timestamp', $gmt );

		// Taking the start time for current day so that it doesn't return a value greater then timestamp for the order so as to allow same day order to sync in Google calendar
		$current_time_start = strtotime( gmdate( 'd M, Y 00:01:01', $current_time ) );

		$order = wc_get_order( $order_id );
		$data  = ORDDD_Functions::get_order_meta( $order_id, $order );

		$delivery_date_formatted = self::orddd_get_order_delivery_date( $order_id );
		$delivery_date_timestamp = '';
		if ( isset( $data[ '_orddd_timestamp' ] ) ) {
			$delivery_date_timestamp = $data[ '_orddd_timestamp' ][0];
		}
		$time_slot = self::orddd_get_order_timeslot( $order_id );

		$orddd         = new stdClass();
		$event_details = array();

		// It checks if the function was called from order action or not and if yes then it will not check the date is of future or not and will send that order in google calendar.
		if ( $from_order_page ) {
			$current_time_start = $delivery_date_timestamp;
		}
		if ( $delivery_date_timestamp != '' &&
			$delivery_date_timestamp >= $current_time_start ) {

			$delivery_date                     = gmdate( 'd-m-Y', $delivery_date_timestamp );
			$event_details['h_deliverydate']   = $delivery_date;
			$event_details['_orddd_timestamp'] = $delivery_date_timestamp;
			$event_details['e_deliverydate']   = $delivery_date_formatted;
		}

		if ( isset( $event_details['h_deliverydate'] ) && $event_details['h_deliverydate'] != '' ) {
			if ( $time_slot != '' &&
				$time_slot != 'NA' &&
				$time_slot != 'choose' &&
				$time_slot != 'select' ) {
				if ( 'As Soon As Possible.' === $time_slot ) {
					$event_details['time_slot'] = $time_slot;
				} else {
					$timeslot  = explode( ' - ', $time_slot );
					$from_time = gmdate( 'H:i', strtotime( $timeslot[0] ) );
					if ( isset( $timeslot[1] ) && $timeslot[1] != '' ) {
						$to_time   = gmdate( 'H:i', strtotime( $timeslot[1] ) );
						$time_slot = $from_time . ' - ' . $to_time;
					} else {
						$time_slot = $from_time;
					}
					$event_details['time_slot'] = $time_slot;
				}
			}

			if ( isset( $data['_billing_email'][0] ) ) {
				$event_details['billing_email'] = $data['_billing_email'][0];
			} else {
				$event_details['billing_email'] = '';
			}

			if ( isset( $data['_shipping_first_name'][0] ) ) {
				$event_details['shipping_first_name'] = $data['_shipping_first_name'][0];
			} else {
				$event_details['shipping_first_name'] = '';
			}

			if ( isset( $data['_billing_first_name'][0] ) ) {
				$event_details['billing_first_name'] = $data['_billing_first_name'][0];
			} else {
				$event_details['billing_first_name'] = '';
			}

			if ( isset( $data['_shipping_last_name'][0] ) ) {
				$event_details['shipping_last_name'] = $data['_shipping_last_name'][0];
			} else {
				$event_details['shipping_last_name'] = '';
			}

			if ( isset( $data['_billing_last_name'][0] ) ) {
				$event_details['billing_last_name'] = $data['_billing_last_name'][0];
			} else {
				$event_details['billing_last_name'] = '';
			}

			if ( isset( $data['_shipping_address_1'][0] ) ) {
				$event_details['shipping_address_1'] = $data['_shipping_address_1'][0];
			} else {
				$event_details['shipping_address_1'] = '';
			}

			if ( isset( $data['_billing_address_1'][0] ) ) {
				$event_details['billing_address_1'] = $data['_billing_address_1'][0];
			} else {
				$event_details['billing_address_1'] = '';
			}

			if ( isset( $data['_shipping_address_2'][0] ) ) {
				$event_details['shipping_address_2'] = $data['_shipping_address_2'][0];
			} else {
				$event_details['shipping_address_2'] = '';
			}

			if ( isset( $data['_billing_address_2'][0] ) ) {
				$event_details['billing_address_2'] = $data['_billing_address_2'][0];
			} else {
				$event_details['billing_address_2'] = '';
			}

			if ( isset( $data['_shipping_city'][0] ) ) {
				$event_details['shipping_city'] = $data['_shipping_city'][0];
			} else {
				$event_details['shipping_city'] = '';
			}

			if ( isset( $data['_billing_city'][0] ) ) {
				$event_details['billing_city'] = $data['_billing_city'][0];
			} else {
				$event_details['billing_city'] = '';
			}

			if ( isset( $data['_billing_phone'][0] ) ) {
				$event_details['billing_phone'] = $data['_billing_phone'][0];
			} else {
				$event_details['billing_phone'] = '';
			}

			$customer_note = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_customer_note() : $order->customer_note;
			if ( isset( $customer_note ) ) {
				$event_details['order_comments'] = $customer_note;
			} else {
				$event_details['order_comments'] = '';
			}

			if ( '' != $order->get_payment_method_title() ) {
				$event_details['payment_method_title'] = $order->get_payment_method_title();
			} else {
				$event_details['payment_method_title'] = '';
			}

			if ( '' != $order->get_shipping_method() ) {
				$event_details['shipping_method_title'] = $order->get_shipping_method();
			} else {
				$event_details['shipping_method_title'] = '';
			}


			$event_details[ 'shipping_phone' ]   = isset( $data[ '_shipping_phone' ][ 0 ] ) ? $data[ '_shipping_phone' ][ 0 ] : '';
			$event_details[ 'shipping_company' ] = isset( $data[ 'shipping_company' ][ 0 ] ) ? $data[ 'shipping_company' ][ 0 ] : '';
			$event_details[ 'shipping_postcode' ]   = isset( $data[ 'shipping_postcode' ][ 0 ] ) ?  $data[ 'shipping_postcode' ][ 0 ] : '';

			$pickup_locations_label = '' != get_option( 'orddd_location_field_label' ) ? get_option( 'orddd_location_field_label' ) : 'Pickup Location';
			if ( isset( $data[ $pickup_locations_label ][0] ) ) {
				$event_details['pickup_location'] = $data[ $pickup_locations_label ][0];
			} else {
				$event_details['pickup_location'] = '';
			}

			$event_details['order_weblink'] = $order->get_edit_order_url();
			$event_details['order_status']  = $order->get_status();

			// add other tyche plugin fields
			$has_order_deposit = $order->get_meta( '_has_order_deposit', true );
			if ( 'yes' === $has_order_deposit ) {
				$future_payments                  = $order->get_meta( $order_id, '_future_payments', true );
				$event_details['future_payments'] = $future_payments;

				$deposit_payment                  = $order->get_meta( $order_id, '_deposit', true );
				$event_details['deposit_payment'] = $deposit_payment;
			}
		}

		return $event_details;
	}

	/**
	 * Return estimated shipping date for text block
	 *
	 * @globals array $orddd_date_formats Date formats array
	 * @param int $delivery_time_seconds Minimum Delivery Time in hours
	 *
	 * @return string Estimated shipping date
	 * @since 6.6
	 */
	public static function orddd_get_text_block_shipping_date( $delivery_time_seconds, $vendor_id = 0 ) {
		global $orddd_date_formats, $orddd_shipping_days;

		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}

		$selected_date_format = get_option( 'orddd_delivery_date_format' );
		$date_format          = '';
		if ( isset( $orddd_date_formats[ $selected_date_format ] ) ) {
			$date_format = $orddd_date_formats[ $selected_date_format ];
		}
		$holidays_str   = ORDDD_Functions::orddd_get_holidays_str( $vendor_id );
		$min_date_array = self::get_min_date(
			$delivery_time_seconds,
			array(
				'enabled'    => self::orddd_get_option( 'orddd_enable_delivery_time', '', $vendor_id ),
				'from_hours' => self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id  ),
				'to_hours'   => self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id  ),
				'from_mins'  => self::orddd_get_option( 'orddd_delivery_from_mins', '', $vendor_id  ),
				'to_mins'    => self::orddd_get_option( 'orddd_delivery_to_mins', '', $vendor_id  ),
			),
			$holidays_str,
			'',
			array(),
			array(),
			$vendor_id
		);
		$cut_off_date      = $min_date_array['min_date'];
		$cut_off_date_time = strtotime( $cut_off_date );


		$shipping_date        = gmdate( $date_format, $cut_off_date_time );
		$hidden_shipping_date = gmdate( 'j-n-Y', $cut_off_date_time );

		return array(
			'shipping_date'        => $shipping_date,
			'hidden_shipping_date' => $hidden_shipping_date,
		);
	}

	/**
	 * Check if the delivery calendar is enabled for the custom setting
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @return string 'yes' if delivery calendar is enabled, else 'no'.
	 * @since 6.6
	 */
	public static function orddd_is_delivery_calendar_enabled_for_custom_delivery( $vendor_id = 0) {

		$orddd_is_delivery_calendar_enabled_for_custom = wp_cache_get( 'orddd_is_delivery_calendar_enabled_for_custom_result_' . $vendor_id );
		if ( false === $orddd_is_delivery_calendar_enabled_for_custom ) {
			global $wpdb;

			$orddd_is_delivery_calendar_enabled_for_custom = 'no';
			$results                                       = self::orddd_get_shipping_settings( 1, $vendor_id );
			$shipping_settings                             = array();
			if ( is_array( $results ) && count( $results ) > 0 ) {
				foreach ( $results as $key => $value ) {
					$shipping_settings = orddd_common::orddd_get_option( $value->option_name, '', $vendor_id  );
					if ( isset( $shipping_settings['orddd_delivery_checkout_options'] ) && $shipping_settings['orddd_delivery_checkout_options'] == 'delivery_calendar' ) {
						$orddd_is_delivery_calendar_enabled_for_custom = 'yes';
						break;
					}
				}
				wp_cache_set( 'orddd_is_delivery_calendar_enabled_for_custom_result', $orddd_is_delivery_calendar_enabled_for_custom );
			}
		}
		return $orddd_is_delivery_calendar_enabled_for_custom;
	}

	/**
	 * Check if the text block is enabled for the custom setting
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @return string 'yes' if text block is enabled, else 'no'.
	 * @since 6.6
	 */
	public static function orddd_is_text_block_enabled_for_custom_delivery( $vendor_id = 0) {
		global $wpdb;
		$orddd_is_text_block_enabled_for_custom = 'no';
		$results                                = self::orddd_get_shipping_settings( 1, $vendor_id );
		$shipping_settings                      = array();
		if ( is_array( $results ) && count( $results ) > 0 ) {
			foreach ( $results as $key => $value ) {
				$shipping_settings = orddd_common::orddd_get_option( $value->option_name, '', $vendor_id );
				if ( ! isset( $shipping_settings['enable_shipping_based_delivery'] ) ) {
					continue;
				}
				if ( isset( $shipping_settings['orddd_delivery_checkout_options'] ) && $shipping_settings['orddd_delivery_checkout_options'] == 'text_block' ) {
					$orddd_is_text_block_enabled_for_custom = 'yes';
					break;
				}
			}
		}
		return $orddd_is_text_block_enabled_for_custom;
	}

	/**
	 * Get the delivery date field label for the custom setting on checkout page.
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @param string $shipping_method Selected shipping method
	 * @param string $product_category Product categories of the product added to the cart.
	 * @param string $shipping_class Shipping class assigned to the product.
	 *
	 * @return string Delivery date field label.
	 * @since 7.5
	 */
	public static function orddd_get_delivery_date_field_label( $shipping_method, $products, $product_category, $shipping_class, $location, $order_id = '', $vendor_id = 0 ) {
		global $wpdb;
		$delivery_date_field_label = self::orddd_get_option( 'orddd_delivery_date_field_label', '', $vendor_id );
		if ( function_exists( 'icl_object_id' ) && $order_id != '' && is_admin() ) {
			global $polylang;
			if ( isset( $polylang ) ) {
				$ord_lang                  = pll_get_post_language( $order_id );
				$delivery_date_field_label = pll_translate_string( $delivery_date_field_label, $ord_lang );
			}

			$order = wc_get_order( $order_id );
			$is_wpml_langauge = $order->get_meta( 'wpml_language' );

			if ( isset( $is_wpml_langauge[0] ) ) {
				// Date Field Label Translation
				$date_string_id            = icl_get_string_id( $delivery_date_field_label, 'admin_texts_orddd_delivery_date_field_label', 'orddd_delivery_date_field_label' );
				$translation_results       = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT value
                                                      FROM {$wpdb->prefix}icl_string_translations
                                                      WHERE string_id=%d AND language=%s",
						$date_string_id,
						$is_wpml_langauge[0]
					)
				);
				$delivery_date_field_label = $translation_results;
			}
		}
		
		$custom_settings = orddd_custom_delivery_functions::orddd_get_applied_custom_schedules( $location, $shipping_method, $products, $product_category, $shipping_class, '', $vendor_id );

		if ( isset( $custom_settings['orddd_shipping_based_delivery_date_field_label'] ) && '' !== $custom_settings['orddd_shipping_based_delivery_date_field_label'] ) {
			$delivery_date_field_label = $custom_settings['orddd_shipping_based_delivery_date_field_label'];
		}

		if ( $delivery_date_field_label == '' ) {
			$delivery_date_field_label = 'Delivery Date';
		}

		return $delivery_date_field_label;
	}

	/**
	 * Get the delivery time field label for the custom setting on checkout page.
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @param string $shipping_method Selected shipping method
	 * @param string $product_category Product categories of the product added to the cart.
	 * @param string $shipping_class Shipping class assigned to the product.
	 *
	 * @return string Delivery time field label.
	 * @since 7.5
	 */
	public static function orddd_get_delivery_time_field_label( $shipping_method, $products, $product_category, $shipping_class, $location, $order_id = '', $vendor_id = 0 ) {
		global $wpdb;
		$delivery_time_field_label = self::orddd_get_option( 'orddd_delivery_timeslot_field_label','', $vendor_id );

		if ( function_exists( 'icl_object_id' ) && $order_id != '' && is_admin() ) {
			global $polylang;

			if ( isset( $polylang ) ) {
				$ord_lang                  = pll_get_post_language( $order_id );
				$delivery_time_field_label = pll_translate_string( $delivery_time_field_label, $ord_lang );
			}

			$order = wc_get_order( $order_id );
			$is_wpml_langauge = $order->get_meta( 'wpml_language' );

			if ( isset( $is_wpml_langauge[0] ) ) {

				// Time Field Label Translation
				$time_string_id            = icl_get_string_id( $delivery_time_field_label, 'admin_texts_orddd_delivery_timeslot_field_label', 'orddd_delivery_timeslot_field_label' );
				$time_translation_results  = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT value
                                                      FROM {$wpdb->prefix}icl_string_translations
                                                      WHERE string_id=%d AND language=%s",
						$time_string_id,
						$is_wpml_langauge[0]
					)
				);
				$delivery_time_field_label = $time_translation_results;
			}
		}

		$custom_settings = orddd_custom_delivery_functions::orddd_get_applied_custom_schedules( $location, $shipping_method, $products, $product_category, $shipping_class, '', $vendor_id );

		if ( isset( $custom_settings['orddd_shipping_based_delivery_timeslot_field_label'] ) && '' !== $custom_settings['orddd_shipping_based_delivery_timeslot_field_label'] ) {
			$delivery_time_field_label = $custom_settings['orddd_shipping_based_delivery_timeslot_field_label'];
		}

		if ( $delivery_time_field_label == '' ) {
			$delivery_time_field_label = 'Time Slot';
		}

		return $delivery_time_field_label;
	}

	/**
	 * Get the selected location for an order.
	 *
	 * @param int $order_id Order ID
	 *
	 * @since 8.4
	 */
	public static function orddd_get_order_location( $order_id, $vendor_id = 0 ) {
		$order_location_key = '_orddd_location';
		if ( $vendor_id > 0 ) {
			$order_location_key = '_orddd_location_' . $vendor_id;
		}
		$order = wc_get_order( $order_id );

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return; // Exit if not an order.
		}

		$location = $order->get_meta( $order_location_key, true );
		return $location;
	}

	/**
	 * Get the selected location for an order.
	 *
	 * @param int $order_id Order ID
	 *
	 * @since 8.4
	 */
	public static function orddd_get_order_formatted_location( $order_id ) {
		$locations_label = '' != get_option( 'orddd_location_field_label' ) ? get_option( 'orddd_location_field_label' ) : 'Pickup Location';

		$order    = wc_get_order( $order_id );
		$location = $order->get_meta( $locations_label, true );
		return $location;
	}

	/**
	 * Get the selected shipping method for an order.
	 *
	 * @globals resource $wpdb WordPress Object
	 *
	 * @param int $order_id Order ID
	 *
	 * @return string Shipping method
	 * @since 7.5
	 */
	public static function orddd_get_order_shipping_method( $order_id ) {
		global $wpdb;
		$shipping_method = '';
		$shipping_label  = '';

		// TODO: Replace this query with WooCommerce function
		$query   = 'SELECT a.meta_key, a.meta_value, b.order_item_name FROM `' . $wpdb->prefix . 'woocommerce_order_itemmeta` AS a, `' . $wpdb->prefix . "woocommerce_order_items` AS b WHERE a.order_item_id = b.order_item_id AND b.order_item_type = 'shipping' AND b.order_id = " . $order_id;
		$results = $wpdb->get_results( $query ); //phpcs:ignore

		if ( isset( $results ) ) {
			$method_id = '';
			foreach ( $results as $key => $value ) {
				if ( isset( $value->order_item_name ) ) {
					$shipping_label = $value->order_item_name;
				}

				if ( 'method_id' == $value->meta_key ) {
					$method_id = $value->meta_value;
				}
				if ( 'instance_id' == $value->meta_key ) {
					$instance_id = $value->meta_value;
				}

				$shipping_method = $method_id;
				if ( isset( $instance_id ) && '' != $instance_id ) {
					$shipping_method .= ':' . $instance_id;
				}
			}

			$zone_details = explode( '-', self::orddd_get_zone_id_for_order( $order_id ) );
			$zone_id      = $zone_details[0];
			if ( '' != $zone_id ) {
				if ( false !== strpos( $shipping_method, 'usps' ) ) {
					$shipping_method = $zone_id . ':' . $shipping_method;
				} elseif ( strpos( $shipping_method, 'wf_fedex_woocommerce_shipping' ) === false && false !== strpos( $shipping_method, 'fedex' ) && is_array( explode( ':', $shipping_method ) ) && count( explode( ':', $shipping_method ) ) < 3 ) {
					$shipping_method = $zone_id . ':' . $shipping_method;
				} elseif ( false !== strpos( $shipping_method, 'table_rate' ) && false === strpos( $shipping_method, 'tree_table_rate' ) ) {
					$option_settings             = get_option( 'woocommerce_table_rate_' . $instance_id . '_settings' );
					$table_rate_shipping_classes = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->prefix . "woocommerce_shipping_table_rates` WHERE shipping_method_id = {$instance_id} ORDER BY rate_order ASC;" ); //phpcs:ignore
					foreach ( $table_rate_shipping_classes as $tkey => $tvalue ) {
						if ( '' == $option_settings['calculation_type'] && ( $tvalue->rate_label == $shipping_label || '' == $tvalue->rate_label ) ) {
							$shipping_method = $shipping_method . ':' . $tvalue->rate_id;
							break;
						} else {
							$shipping_method = $shipping_method;
						}
					}
				}
			}

			if ( false !== strpos( $shipping_method, 'flexible_shipping' ) ) {
				$flexible_methods = get_option( 'flexible_shipping_methods_' . $instance_id );
				if ( ! empty( $flexible_methods ) ) {
					foreach ( $flexible_methods as $flexible_methods_key => $flexible_methods_value ) {
						$shipping_method = $flexible_methods_value['id_for_shipping'];
					}
				}
			}

			if ( false !== strpos( $shipping_method, 'ups' ) ) {
				$shipping_method = self::orddd_get_ups_shipping_method( $order_id, $shipping_method, $instance_id );
			}
			// fetch shipping method of the WooCommerce Table Rate Shipping plugin by Bolder Elements
			if ( false !== strpos( $shipping_method, 'betrs_shipping' ) ) {
				$shipping_method = self::orddd_get_betrs_shipping_method( $order_id, $shipping_method, $instance_id );
			}

			// fetch shipping method of the WooCommerce Advanced Shipping plugin
			if ( false !== strpos( $shipping_method, 'advanced_shipping' ) ) {
				$shipping_method = self::orddd_get_advance_shipping_method( $order_id, $shipping_method, $instance_id );
			}
		}
		return $shipping_method;
	}

	/**
	 * Get the shipping method of the WooCommerce UPS Shipping by WooCommerce
	 *
	 * @param int    $order_id Order ID
	 * @param string $shipping_method Shipping method
	 * @param int    $instance_id Instance ID
	 *
	 * @return string Shipping method
	 * @since 8.6
	 */
	public static function orddd_get_ups_shipping_method( $order_id, $shipping_method, $instance_id ) {
		global $wpdb;
		$order      = new WC_Order( $order_id );
		$ups_result = $wpdb->get_results( 'SELECT order_item_name FROM `' . $wpdb->prefix . "woocommerce_order_items` WHERE order_item_type = 'shipping' AND order_id = " . $order_id ); //phpcs:ignore
		if ( isset( $ups_result ) ) {
			foreach ( $ups_result as $ups_key => $ups_value ) {
				$ups_service_name = $ups_value->order_item_name;
			}
		}

		$ups_settings = get_option( 'woocommerce_ups_' . $instance_id . '_settings' );
		$ups_services = array();
		$key          = '';

		if ( isset( $ups_settings['services'] ) ) {
			$ups_services = $ups_settings['services'];

			foreach ( $ups_services as $ups_services_key => $ups_services_value ) {
				$ups_services_enabled = $ups_services_value['enabled'];
				if ( '1' == $ups_services_enabled ) {
					$ups_services_name = $ups_services_value['name'];
					if ( '' == $ups_services_name ) {
						$ups_services_name = self::orddd_get_ups_service_name( $ups_services_key );
					}
					if ( $ups_service_name == $ups_services_name ) {
						$key = $ups_services_key;
						break;
					}
				}
			}
			$shipping_method .= ':' . $key;
		}

		return $shipping_method;
	}

	/**
	 * Get the shipping method of the WooCommerce Table Rate Shipping plugin by Bolder Elements
	 *
	 * @param int    $order_id Order ID
	 * @param string $shipping_method Shipping method
	 * @param int    $instance_id Instance ID
	 *
	 * @return string Shipping method
	 * @since 8.6
	 */
	public static function orddd_get_betrs_shipping_method( $order_id, $shipping_method, $instance_id ) {
		global $wpdb;
		$order        = new WC_Order( $order_id );
		$betrs_result = $wpdb->get_results( 'SELECT order_item_name FROM `' . $wpdb->prefix . "woocommerce_order_items` WHERE order_item_type = 'shipping' AND order_id = " . $order_id ); //phpcs:ignore
		if ( isset( $betrs_result ) ) {
			foreach ( $betrs_result as $betrs_key => $betrs_value ) {
				$betrs_service_name = $betrs_value->order_item_name;
			}
		}
		$betrs_options_save_name = 'betrs_shipping_options-' . $instance_id;
		$betrs_shipping_options  = get_option( $betrs_options_save_name );

		$betrs_settings = $betrs_shipping_options['settings'];
		foreach ( $betrs_settings as $betrs_settings_key => $betrs_settings_value ) {
			$betrs_title = $betrs_settings_value['title'];
			if ( '' == $betrs_title ) {
				$betrs_title .= 'Table Rate';
			}

			if ( $betrs_service_name == $betrs_title ) {
				$key = $betrs_settings_value['option_id'];
			}
		}

		$shipping_method .= '-' . $key;
		return $shipping_method;
	}

	/**
	 * Get the shipping method of the WooCommerce Advance Shipping plugin
	 *
	 * @param int    $order_id Order ID
	 * @param string $shipping_method Shipping method
	 * @param int    $instance_id Instance ID
	 *
	 * @return string Shipping method
	 * @since 8.6
	 */
	public static function orddd_get_advance_shipping_method( $order_id, $shipping_method, $instance_id ) {
		global $wpdb;
		$order        = new WC_Order( $order_id );
		$adv_shipping = $wpdb->get_results( 'SELECT order_item_name FROM `' . $wpdb->prefix . "woocommerce_order_items` WHERE order_item_type = 'shipping' AND order_id = " . $order_id ); //phpcs:ignore
		if ( isset( $adv_shipping ) ) {
			foreach ( $adv_shipping as $adv_shipping_key => $adv_shipping_value ) {
				$adv_shipping_service_name = $adv_shipping_value->order_item_name;
			}
		}

		$methods = get_posts(
			array(
				'posts_per_page' => '-1',
				'post_type'      => 'was',
				'post_status'    => array( 'publish' ),
				'order'          => 'ASC',
			)
		);
		if ( is_array( $methods ) && count( $methods ) > 0 && is_plugin_active( 'woocommerce-advanced-shipping/woocommerce-advanced-shipping.php' ) ) {
			foreach ( $methods as $method ) {
				$method_details = get_post_meta( $method->ID, '_was_shipping_method', true );
				if ( $adv_shipping_service_name == $method_details['shipping_title'] ) {
					$shipping_method = $method->ID;
				}
			}
		}

		// Advanced Flat Rate Shipping Method WooCommerce by Multidots.
		$methods = get_posts(
			array(
				'posts_per_page' => '-1',
				'post_type'      => 'wc_afrsm',
				'post_status'    => array( 'publish' ),
				'order'          => 'ASC',
			)
		);

		if ( is_array( $methods ) && count( $methods ) > 0 && ( is_plugin_active( 'woo-extra-flat-rate/advanced-flat-rate-shipping-for-woocommerce.php.php' ) || is_plugin_active( 'advanced-flat-rate-shipping-for-woocommerce/advanced-flat-rate-shipping-for-woocommerce.php' ) ) ) {
			foreach ( $methods as $method ) {
				$method_title       = $method->post_title;
				$shipping_methods[] = array(
					'title'      => $method_title,
					'method_key' => $method->ID,
				);
			}
		}

		return $shipping_method;
	}

	/**
	 * Returns the Product Category ID in the base language
	 *
	 * @param int $cat_id Translated Product category ID
	 * @since 7.6.0
	 */
	public static function get_base_product_category( $cat_id ) {
		$base_cat = $cat_id;

		// If WPML is enabled, the make sure that the base language product ID is used to calculate the availability
		if ( function_exists( 'icl_object_id' ) ) {
			global $sitepress;
			global $polylang;

			if ( isset( $polylang ) ) {
				$default_lang = pll_current_language();
			} else {
				$default_lang = $sitepress->get_default_language();
			}

			$base_cat = icl_object_id( $cat_id, 'category', true, $default_lang );
			// The base product ID is blanks when the product is being created.
			if ( ! isset( $base_cat ) || ( isset( $base_cat ) && $base_cat == '' ) ) {
				$base_cat = $cat_id;
			}
		}
		return $base_cat;
	}

	/**
	 * Returns the Category Slug for the passed ID
	 *
	 * @param int $category_id Product category ID
	 * @since 7.6
	 */
	public static function ordd_get_cat_slug( $category_id ) {

		$cat_slug = '';

		if ( $category_id > 0 ) {
			global $wpdb;

			$query = 'SELECT slug FROM `' . $wpdb->prefix . 'terms` WHERE term_id = %d';

			$results = $wpdb->get_results( $wpdb->prepare( $query, $category_id ) );//phpcs:ignore

			$cat_slug = $results[0]->slug;
		}
		return $cat_slug;

	}

	/**
	 * Return the selected time format under Appearance link.
	 *
	 * @return string Time format.
	 * @since 8.0
	 */

	public static function orddd_get_time_format() {
		$time_format_to_show = 'H:i';
		$time_format         = get_option( 'orddd_delivery_time_format' );
		if ( $time_format == '1' ) {
			$time_format_to_show = 'h:i A';
		}
		return $time_format_to_show;
	}

	/**
	 * Return the the hook to display the delivery date fields on the checkout page.
	 *
	 * @since 8.1
	 */
	public static function orddd_get_shopping_cart_hook() {
		if ( has_filter( 'orddd_shopping_cart_hook' ) ) {
			$orddd_shopping_cart_hook = apply_filters( 'orddd_shopping_cart_hook', '' );
		} else {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			$is_amazon_plugin_active = is_plugin_active( 'woocommerce-gateway-amazon-payments-advanced/woocommerce-gateway-amazon-payments-advanced.php' );
			if ( 1 == $is_amazon_plugin_active && get_option( 'orddd_amazon_payments_advanced_gateway_compatibility' ) == 'on' ) {
				$orddd_shopping_cart_hook = ORDDD_SHOPPING_CART_HOOK_AMAZON;
			} else {
				$orddd_shopping_cart_hook = ORDDD_SHOPPING_CART_HOOK;
			}
		}
		return $orddd_shopping_cart_hook;
	}

	/**
	 * Return the hook to display the order type 'Pickup' & 'Delivery' options.
	 *
	 * @return void
	 */
	public static function orddd_get_order_type_field_hook() {
		$order_type_field_position = orddd_common::orddd_get_option( 'orddd_order_type_field_position', 'before_billing_section' );
		$field_position = '';

		if ( 'before_billing_section' === $order_type_field_position ) {
			$field_position = is_plugin_active( 'flux-checkout-premium/flux-checkout.php' ) ? 'woocommerce_checkout_before_customer_details' : 'woocommerce_checkout_billing';
		} elseif ( 'before_delivery_fields' === $order_type_field_position ) {
			$field_position = self::orddd_get_shopping_cart_hook();
		} elseif ( 'custom' === $order_type_field_position ) {
			$field_position = orddd_common::orddd_get_option( 'orddd_custom_hook_for_order_type_field' );
		}

		return $field_position;
	}

	public static function orddd_is_view_subscription_page() {
		$is_view_subscription_page = false;
		if ( class_exists( 'WC_Subscriptions' ) && function_exists( 'wcs_is_view_subscription_page' ) && wcs_is_view_subscription_page() ) {
			$is_view_subscription_page = true;
		}
		return $is_view_subscription_page;
	}

	/**
	 * Compares the timeslots added.
	 *
	 * @param array $a First time slot of the array to compare.
	 * @param array $b Second time slot of the array to compare.
	 * @return bool Return true id the time slot 1 is greater than time slot 2 else false.
	 *
	 * @since 8.5
	 */
	public static function orddd_custom_sort( $a, $b ) {
		$tstamp_from_1 = 0;
		$tstamp_from_2 = 0;
		if ( isset( $a->fh ) ) {
			$tstamp_from_1 = strtotime( gmdate( 'd' ) . ' ' . gmdate( 'M' ) . ' ' . gmdate( 'Y' ) . ' ' . $a->fh . ':' . $a->fm );
			$tstamp_from_2 = strtotime( gmdate( 'd' ) . ' ' . gmdate( 'M' ) . ' ' . gmdate( 'Y' ) . ' ' . $b->fh . ':' . $b->fm );
		} else {
			$timeslot_values_a = self::get_timeslot_values( $a );
			$timeslot_values_b = self::get_timeslot_values( $b );
			$time_slot_1       = explode( ' - ', $timeslot_values_a['time_slot'] );
			$time_slot_2       = explode( ' - ', $timeslot_values_b['time_slot'] );
			$tstamp_from_1     = strtotime( gmdate( 'd' ) . ' ' . gmdate( 'M' ) . ' ' . gmdate( 'Y' ) . ' ' . $time_slot_1[0] );
			$tstamp_from_2     = strtotime( gmdate( 'd' ) . ' ' . gmdate( 'M' ) . ' ' . gmdate( 'Y' ) . ' ' . $time_slot_2[0] );
		}

		return (int) ( $tstamp_from_1 > $tstamp_from_2 );
	}

	/**
	 * Compares the specific dates added.
	 *
	 * @param array $a First value of the specific dates array to compare.
	 * @param array $b Second value of the specific dates array to compare.
	 * @return bool Return true if the specific date 1 is greater than soecific date 2 else false.
	 *
	 * @since 8.5
	 */
	public static function orddd_sort_specific_dates( $a, $b ) {
		$tstamp_1 = 0;
		$tstamp_2 = 0;
		if ( is_object( $a ) ) {
			$date_1   = explode( '-', $a->date );
			$tstamp_1 = strtotime( $date_1[1] . '-' . $date_1[0] . '-' . $date_1[2] );

			$date_2   = explode( '-', $b->date );
			$tstamp_2 = strtotime( $date_2[1] . '-' . $date_2[0] . '-' . $date_2[2] );
		} else {
			if ( $a != '' ) {
				$sv_str_a            = str_replace( '}', '', $a );
				$sv_str_a            = str_replace( '{', '', $sv_str_a );
				$specific_date_arr_a = explode( ':', $sv_str_a );
				$date_1              = explode( '-', $specific_date_arr_a[0] );
				$tstamp_1            = strtotime( $date_1[1] . '-' . $date_1[0] . '-' . $date_1[2] );
			}

			if ( $b != '' ) {
				$sv_str_b            = str_replace( '}', '', $b );
				$sv_str_b            = str_replace( '{', '', $sv_str_b );
				$specific_date_arr_b = explode( ':', $sv_str_b );
				$date_2              = explode( '-', $specific_date_arr_b[0] );
				if ( ! isset( $date_2[2] ) ) {
					$date_2[2] = '';
				}
				$tstamp_2 = strtotime( $date_2[1] . '-' . $date_2[0] . '-' . $date_2[2] );
			}
		}

		return (int) ( $tstamp_1 > $tstamp_2 );
	}


	/**
	 * Converts timeslot from one format to another. If no format is specified, it will convert
	 * timeslot to 24 hour format. We convert all timeslots to 24 hour format so they can be
	 * compared properly for their lockout values.
	 *
	 * @param string $timeslot Timeslot to format, example: 05:00 PM - 06:00 PM
	 * @param string $timeslot_format Timeslot format, default = H:i, or else it can be: h:i A
	 * @return string Returns updated timeslot in the new format
	 *
	 * @since 9.6
	 */
	public static function orddd_change_time_slot_format( $timeslot, $timeslot_format = 'H:i' ) {

		$timeslot_new    = '';
		$dmy             = gmdate( 'd' ) . ' ' . gmdate( 'M' ) . ' ' . gmdate( 'Y' );
		$time_arr        = explode( ' - ', $timeslot );
		$tstamp_from     = strtotime( $dmy . ' ' . $time_arr[0] );
		$start_time_slot = gmdate( $timeslot_format, $tstamp_from );
		$tstamp_to       = '';
		$end_time_slot   = '';
		if ( isset( $time_arr[1] ) ) {
			$tstamp_to     = strtotime( $dmy . ' ' . $time_arr[1] );
			$end_time_slot = gmdate( $timeslot_format, $tstamp_to );
		}

		if ( $end_time_slot != '' ) {
			$timeslot_new = $start_time_slot . ' - ' . $end_time_slot;
		} else {
			$timeslot_new = $start_time_slot;
		}

		return $timeslot_new;
	}

	/**
	 * Returns the array of individual product quantities added to the cart.
	 * This function is added so that proper max. deliveries can be checked when the lockout is
	 * set to be based on product quantity instead of orders.
	 *
	 * @globals resource $woocommerce WooCommerce object
	 *
	 * @return array Individual product quantities
	 *
	 * @since 9.13
	 */
	public static function orddd_get_individual_product_quantities( $called_from = '' ) {
		global $woocommerce;
		$product_quantities = array();
		if ( get_option( 'orddd_lockout_date_quantity_based' ) == 'on' ) {
			if ( is_admin() || $called_from ) {
				if ( isset( $_POST['order_id'] ) ) {
					$order_id = $_POST['order_id'];//phpcs:ignore
				} else {
					$order_id = '';
				}
				$order = new WC_Order( $order_id );
				$items = $order->get_items();
				foreach ( $items as $key => $value ) {
					if ( isset( $value['quantity'] ) ) {
						$product_quantities[ $value['product_id'] ] = $value['quantity'];
					}
				}
			} else {
				foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
					if ( isset( $values['quantity'] ) ) {
						$product_quantities[ $values['product_id'] ] = $values['quantity'];
					}
				}
			}
		} else {
			$product_quantities[] = 1;
		}
		return $product_quantities;
	}


	/**
	 * Checks if any of the passed shipping methods have custom delivery schedule or not
	 *
	 * @param array Array of shipping methods.
	 *
	 * @return boolean true|false Return true if yes, else false
	 * @since 9.18.0
	 */
	public static function orddd_shipping_method_is_custom_check( $shipping_methods = array(), $vendor_id = 0 ) {
		$results = self::orddd_get_shipping_settings( 1, $vendor_id );

		foreach ( $results as $key => $value ) {
			$option_name                = $value->option_name;
			$shipping_settings          = self::orddd_get_option( $option_name, '', $vendor_id );
			$delivery_settings_based_on = isset( $shipping_settings['delivery_settings_based_on'][0] ) ? $shipping_settings['delivery_settings_based_on'][0] : '';

			$custom_setting_id_arr = explode( 'orddd_shipping_based_settings_', $option_name );
			$custom_setting_id     = $custom_setting_id_arr[1];

			if ( 'shipping_methods' === $delivery_settings_based_on ) {
				foreach ( $shipping_methods as $skey => $shipping_method_value ) {
					if ( in_array( $shipping_method_value, $shipping_settings['shipping_methods'] ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}


	/**
	 * Get all shipping method names that have custom settings
	 *
	 * @return array Array of shipping method names that have custom settings enabled
	 * @since 9.18.0
	 */
	public static function orddd_get_shipping_methods_with_custom_settings( $vendor_id = 0 ) {

		$shipping_methods_with_custom_settings = array();
		$results                               = self::orddd_get_shipping_settings( 1, $vendor_id );

		foreach ( $results as $key => $value ) {
			$option_name                = $value->option_name;
			$shipping_settings          = self::orddd_get_option( $option_name, '', $vendor_id );
			$delivery_settings_based_on = isset( $shipping_settings['delivery_settings_based_on'][0] ) ? $shipping_settings['delivery_settings_based_on'][0] : '';

			if ( 'shipping_methods' === $delivery_settings_based_on ) {
				foreach ( $shipping_settings['shipping_methods'] as $skey => $shipping_method_value ) {
					if ( ! in_array( $shipping_method_value, $shipping_methods_with_custom_settings ) ) {
						$shipping_methods_with_custom_settings[] = $shipping_method_value;
					}
				}
			}
		}

		return $shipping_methods_with_custom_settings;
	}


	 /**
	  * Calculate the number of days from the min date to the selected delivery date.
	  *
	  * @return int
	  * @since 9.25.0
	  */
	public static function orddd_get_next_available_day( $delivery_date, $shipping_settings_to_check, $vendor_id = 0 ) {

		if ( ! $delivery_date ) {
			return;
		}

		$gmt = false;
		if ( has_filter( 'orddd_gmt_calculations' ) ) {
			$gmt = apply_filters( 'orddd_gmt_calculations', '' );
		}
		$current_time            = current_time( 'timestamp', $gmt );
		$current_date            = gmdate( 'n-j-Y', $current_time );
		$current_timestamp       = strtotime( $current_date );
		$delivery_date_timestamp = strtotime( $delivery_date );
		$hidden_date             = gmdate( 'Y-m-d' );
		$holidays_str            = ORDDD_Functions::orddd_get_holidays_str( $vendor_id );
		$holidays                = ORDDD_Functions::orddd_get_holidays_array( $holidays_str );
		$delivery_time_seconds   = orddd_get_minimum_delivery_time( $vendor_id );
		$min_date_array          = self::get_min_date(
			$delivery_time_seconds,
			array(
				'enabled'    => self::orddd_get_option( 'orddd_enable_delivery_time', '', $vendor_id ),
				'from_hours' => self::orddd_get_option( 'orddd_delivery_from_hours', '', $vendor_id  ),
				'to_hours'   => self::orddd_get_option( 'orddd_delivery_to_hours', '', $vendor_id  ),
				'from_mins'  => self::orddd_get_option( 'orddd_delivery_from_mins', '', $vendor_id  ),
				'to_mins'    => self::orddd_get_option( 'orddd_delivery_to_mins', '', $vendor_id  ),
			),
			$holidays_str,
			'',
			array(),
			array(),
			$vendor_id
		);

		$shipping_settings = array();
		if ( is_array( $shipping_settings_to_check ) && count( $shipping_settings_to_check ) > 0 ) {
			$shipping_settings = $shipping_settings_to_check;
			$holidays_str      = self::orddd_get_custom_holidays( $shipping_settings, $vendor_id );
			$holidays          = ORDDD_Functions::orddd_get_holidays_array( $holidays_str );

			$minimum_time = orddd_get_higher_minimum_delivery_time( $vendor_id );

			$minimum_delivery_time = 0;

			if ( $minimum_time != '' && $minimum_time != 0 ) {
				$minimum_delivery_time = $minimum_time;
			} else {
				if ( isset( $shipping_settings['minimum_delivery_time'] ) ) {
					$minimum_delivery_time = $shipping_settings['minimum_delivery_time'];
					if ( '' == $minimum_delivery_time ) {
						$minimum_delivery_time = 0;
					}
				}
			}
			$delivery_time_seconds = floatval( $minimum_delivery_time ) * 60 * 60;

			$min_date_array = self::get_min_date(
				$delivery_time_seconds,
				array(
					'enabled'    => '',
					'from_hours' => '',
					'from_mins'  => '',
					'to_hours'   => '',
					'to_mins'    => '',
				),
				$holidays_str,
				'',
				$shipping_settings,
				array(),
				$vendor_id
			);
		}

		$min_date           = $min_date_array['min_date'];
		$min_date_timestamp = strtotime( $min_date );
		$no_of_days         = 0;

		while ( $min_date_timestamp < $delivery_date_timestamp ) {
			$current_weekday = gmdate( 'w', $min_date_timestamp );
			$current_date    = gmdate( 'n-j-Y', $min_date_timestamp );

			if ( isset( $shipping_settings ) && count( $shipping_settings ) > 0 && isset( $shipping_settings['delivery_type']['weekdays'] ) && 'on' == $shipping_settings['delivery_type']['weekdays'] ) {
				$weekdays = $shipping_settings['weekdays'];
				if ( isset( $weekdays[ 'orddd_weekday_' . $current_weekday ]['enable'] ) && 'checked' !== $weekdays[ 'orddd_weekday_' . $current_weekday ]['enable'] ) {
					$min_date_timestamp = strtotime( '+1 day', $min_date_timestamp );
					continue;
				}
			} elseif ( 'checked' !== get_option( 'orddd_weekday_' . $current_weekday ) ) {
				$min_date_timestamp = strtotime( '+1 day', $min_date_timestamp );
				continue;
			}

			if ( is_array( $holidays ) && count( $holidays ) > 0 && in_array( $current_date, $holidays ) ) {
				$min_date_timestamp = strtotime( '+1 day', $min_date_timestamp );
				continue;
			}

			$no_of_days++;
			$min_date_timestamp = strtotime( '+1 day', $min_date_timestamp );
		}

		return $no_of_days;
	}

	/**
	 * Updates the Delivery Date and its Label in Order Meta table.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $label Label for Delivery Date.
	 * @param string $date Delivery Date.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_update_meta_delivery_date_and_label( $order_id, $label, $date, $vendor_id = 0 ) {
		$date  = esc_attr( $date );
		$order = wc_get_order( $order_id );
		$order->update_meta_data( $label, $date );
		$suffix = '';
		if ( ORDDD_Integration_Vendors::is_user_a_vendor( $vendor_id ) ) {
			$suffix .= '_' . $vendor_id;
		}
		$order->update_meta_data( '_orddd_delivery_date' . $suffix, $date );
		$order->update_meta_data( '_orddd_delivery_date_label' . $suffix, $label );

		$meta_id = self::orddd_get_meta_id_from_meta_key( $order_id, $label );
		$order->update_meta_data( '_orddd_delivery_date_meta_id' . $suffix, $meta_id );
		$order->save();
	}

	/**
	 * Updates the Time Slot and its Label in the Order Meta table.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $label Label for Time Slot.
	 * @param string $time_slot Time Slot.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_update_meta_time_slot_and_label( $order_id, $label, $time_slot, $vendor_id = 0 ) {
		$time_slot = esc_attr( $time_slot );
		$order = wc_get_order( $order_id );
		$order->update_meta_data( $label, $time_slot );
		$suffix = '';
		if ( ORDDD_Integration_Vendors::is_user_a_vendor( $vendor_id ) ) {
			$suffix .= '_' . $vendor_id;
		}
		$order->update_meta_data( '_orddd_time_slot' . $suffix, $time_slot );
		$order->update_meta_data( '_orddd_time_slot_label' . $suffix, $label );

		$meta_id = self::orddd_get_meta_id_from_meta_key( $order_id, $label );
		$order->update_meta_data( '_orddd_time_slot_meta_id' . $suffix, $meta_id );
		$order->save();
	}

	/**
	 * Retrieves Meta ID from saved meta.
	 *
	 * @param int    $post_id Post ID for Order.
	 * @param string $meta_key Key for Meta property.
	 * @return int Meta ID.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_get_meta_id_from_meta_key( $post_id, $meta_key ) {
		global $wpdb;
		return (int) $meta_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
				$post_id,
				$meta_key
			)
		);
	}

	/**
	 * Retrieves Holidays from a setting string.
	 *
	 * @param string  $holidays_string holiday string from setting.
	 * @param boolean $common if it is from common setting.
	 * @return array Holidays array.
	 *
	 * @since 9.28.9
	 */
	public static function orddd_get_holiday_from_settings( $holidays_string = '', $common = false ) {
		$holidays = array();
		if ( $common ) {
			$holidays_arr = json_decode( get_option( 'orddd_delivery_date_holidays' ) );
			if ( ! empty( $holidays_arr ) ) {
				foreach ( $holidays_arr as $v ) {
					$holidays[] = $v->d;
				}
				if ( '' === $holidays_string ) {
					$new_holidays = array();
					foreach ( $holidays as $holiday ) {
						$hol_array      = explode( '-', $holiday );
						$new_holidays[] = $hol_array[1] . '-' . $hol_array[0] . '-' . $hol_array[2];
					}
					return $new_holidays;
				}
			}
		}
		$holidays_string = str_replace( '}', '', $holidays_string );
		$holidays_string = str_replace( '{', '', $holidays_string );
		$holidays_array  = explode( ',', $holidays_string );
		if ( count( $holidays_array ) > 0 ) {
			foreach ( $holidays_array as $k => $sv ) {
				$holiday_arr = explode( ':', $sv );
				if ( ! empty( $holiday_arr[1] ) ) {
					$holidays[] = $holiday_arr[1];
				}
			}
		}
		$new_holidays = array();
		foreach ( $holidays as $holiday ) {
			$hol_array      = explode( '-', $holiday );
			$new_holidays[] = $hol_array[1] . '-' . $hol_array[0] . '-' . $hol_array[2];
		}
		return $new_holidays;
	}

	/**
	 * Saves option data to options table - saves for the admin or vendor.
	 *
	 * @param string $option_key Option Key.
	 * @param string $option_data Option Data.
	 * @param bool   $update_vendor Condition to update data in vendor table.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_update_option( $option_key, $option_data, $update_vendor = false ) {

		$is_vendor = apply_filters( 'orddd_integration_vendors_is_vendor', false );

		if ( ! $is_vendor ) {
			update_option( $option_key, $option_data );
		}

		if ( $update_vendor ) {
			do_action( 'orddd_integration_vendors_update_data', $option_data, $option_key );
		}
	}

	/**
	 * Retrieves option data for the admin or vendor.
	 *
	 * @param string $option_key Option Key.
	 * @param string $default_data Data to return in case value not found.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_get_option( $option_key, $default_data = '', $vendor_id = '' ) {

		$option_data = '';

		if ( '' !== $vendor_id ) {
			if ( 0 == $vendor_id || ! self::is_vendor_module_enabled_for_option( $option_key ) ) {
				$option_data = get_option( $option_key, $default_data );
			} elseif ( ! is_multisite() ) {
				$option_data = ORDDD_Integration_Vendors::get_vendor_data( $option_key, $vendor_id, 'orddd_get_option' );
			} else {
				$option_data = get_blog_option( $vendor_id, $option_key, $default_data );
			}
			return $option_data;
		}

		$is_vendor   = apply_filters( 'orddd_integration_vendors_is_vendor', false );
		$option_data = get_option( $option_key, $default_data );

		if ( $is_vendor && self::is_vendor_module_enabled_for_option( $option_key ) ) {
			$option_data = apply_filters( 'orddd_integration_vendors_modify_data', $option_data, 'orddd.express.get_' . $option_key );

			if ( '' === $option_data ) {
				$option_data = $default_data;
			}
		}

		return $option_data;
	}

	/**
	 * Deletes option data for the admin or vendor.
	 *
	 * @param string $option_key Option Key.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_delete_option( $option_key ) {

		$is_vendor = apply_filters( 'orddd_integration_vendors_is_vendor', false );

		if ( $is_vendor ) {
			do_action( 'orddd_integration_vendors_delete_data', $option_key );
		} else {
			delete_option( $option_key );
		}
	}

	/**
	 * Adds secondary params to each Vendor params in orddd_params.
	 *
	 * @param array $orddd_params Vendor Params.
	 * @param array $params Params to be added to Vendor Params.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_merge_params( $orddd_params, $params ) {

		$new_orddd_params = array();

		foreach ( $orddd_params as $vendor_id => $_params ) {
			$new_orddd_params[ $vendor_id ] = array_merge( $_params, $params );
		}

		return $new_orddd_params;
	}

	/**
	 * Gets all Vendors ( that created products ) from Products in the Cart.
	 *
	 * @return array Array of Vendors.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_get_vendors_from_products_in_cart() {

		global $woocommerce;
		$vendors = array();

		$is_plugin_active = ORDDD_Integration_Vendors::is_vendor_plugin_active();

		if ( isset( $woocommerce->cart ) && $is_plugin_active ) {
			foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
				$product_id = $values['product_id'];
				$user_id    = get_post_field( 'post_author', $product_id );

				// Check if User is a Vendor.
				if ( ORDDD_Integration_Vendors::is_user_a_vendor( $user_id ) ) {
					$vendor_id = $user_id;
				} else {
					$vendor_id = 0; // Default.
				}

				$vendors[] = (int) $vendor_id;
			}
		} else {
			$vendors = array(0);
		}
		if ( empty( $vendors ) ) {
			$vendors = array(0);
		}

		$vendors = array_unique( $vendors );
		return $vendors;
	}

	/**
	 * Checks if the Vendor Module that returns the selected option is enabled or disabled.
	 *
	 * @param string $key Key for selected option to be returned.
	 * @return bool Whether to return Vendor Data ( if Vendor Module is enabled ) or return the general data in a case where it is disabled.
	 *
	 * @since 9.28.3
	 */
	public static function is_vendor_module_enabled_for_option( $key ) {

		$is_enabled = true;

		switch ( $key ) {

			// Delivery Schedule.
			case 'orddd_lockout_date_after_orders':
				$is_enabled = 'on' === get_option( 'orddd_vendor_setting_delivery_schedule' );
		}

		return $is_enabled;
	}

	/**
	 * Gets the Vendor ID of the current user.
	 *
	 * @return int Returns 0 for Admin and > 0 for Vendors.
	 *
	 * @since 9.28.3
	 */
	public static function orddd_get_vendor_id() {

		$current_user_id = get_current_user_id();
		$vendor_id = ( isset( $_REQUEST['vendor_id'] ) && $_REQUEST['vendor_id'] > 0 ) ? $_REQUEST['vendor_id'] : 0; //phpcs:ignore
		if ( ! $current_user_id && $vendor_id ) {
			$current_user_id = $_REQUEST['vendor_id'];//phpcs:ignore
		}
		return ORDDD_Integration_Vendors::is_user_a_vendor( $current_user_id ) ? $current_user_id : $vendor_id;
	}

	/**
	 * Sorts the time slot array.
	 *
	 * @param string $slot1 Start time.
	 * @param string $slot2 End time.
	 * @return bool
	 */
	public static function compare_time_slots( $slot1, $slot2 ) {
		// Extract start and end times from the time slots.
		if ( isset( explode( ' - ', $slot1 )[1] ) ) {
			list( $start1, $end1 ) = explode( ' - ', $slot1 );
		} else {
			list( $start1 ) = explode( ' - ', $slot1 );
		}
		if ( isset( explode( ' - ', $slot2 )[1] ) ) {
			list( $start2, $end2 ) = explode( ' - ', $slot2 );
		} else {
			list( $start2 ) = explode( ' - ', $slot2 );
		}
	
		// Convert start and end times to timestamps for comparison.
		$startTimestamp1 = strtotime( $start1 );
		$startTimestamp2 = strtotime( $start2 );
	
		// Compare start times.
		$startTimeComparison = $startTimestamp1 - $startTimestamp2;
	
		// If start times are the same, compare end times.
		if ( $startTimeComparison == 0 && isset( $end1 ) && isset( $end2 ) ) {
			$endTimestamp1 = strtotime( $end1 );
			$endTimestamp2 = strtotime( $end2 );
	
			return $endTimestamp1 - $endTimestamp2;
		}
	
		return $startTimeComparison;
	}

	/**
	 * Checks if WC Blocks local pickup is enabled.
	 *
	 * @return bool True if local pickup is enabled.
	 */
	public static function is_local_pickup_enabled() {
		$pickup_location_settings = get_option(
			'woocommerce_pickup_location_settings',
			[
				'enabled' => 'no',
			]
		);

		if ( empty( $pickup_location_settings['enabled'] ) ) {
			$pickup_location_settings['enabled'] = 'no';
		}

		return wc_string_to_bool( $pickup_location_settings['enabled'] );
	}

	/**
	 * Checks if order placed in the last 2 weeks or not for timezone related issues.
	 *
	 * @return bool True if order from last 2 weeks.
	 */
	public static function is_order_from_past_2_weeks( $order ) {
		if ( ! $order ) {
			return false;
		}
		$order_date_obj = $order->get_date_created();

		if ( $order_date_obj ) {
		    $order_timestamp = $order_date_obj->getTimestamp();

		    // Manually set "now" time (e.g., for testing or specific comparison)
		    $from_timestamp = strtotime( '2025-05-16 00:00:00' );
			$to_timestamp   = strtotime( '2025-05-28 23:59:59' );

		    if ( $order_timestamp >= $from_timestamp && $order_timestamp <= $to_timestamp ) {
		        return true;
		    } else {
		        return false;
		    }
		}
		return false;
	}

	/**
	 * Check time slider enabled or not for order using order id.
	 *
	 * @param int $order_id Order ID
	 * @param int $vendor_id vendor id for an order
	 * @since 2.6.3
	 */
	public static function orddd_check_order_time_slider_enabled( $order_id, $vendor_id = 0  ) {
		$time_slider_enabled      = false;
		$delivery_schedule_id     = orddd_custom_delivery_functions::orddd_get_delivery_schedule_id( $order_id, $vendor_id );
		$custom_settings          = orddd_custom_delivery_functions::orddd_get_delivery_schedule_settings_by_id( $delivery_schedule_id, $vendor_id );
		$time_slider_enabled      = isset( $custom_settings['time_settings']['enable'] ) && 'on' === $custom_settings['time_settings']['enable'] ? true : false;
		if ( '' === $delivery_schedule_id || 0 === (int)$delivery_schedule_id ) {
			$time_slider_enabled = 'on' === get_option( 'orddd_enable_delivery_time' ) ? true : false;
		}
		return $time_slider_enabled;
	}
}

$orddd_common = new orddd_common();
