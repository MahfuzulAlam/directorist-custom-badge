<?php
/**
 * Conditions class for Directorist Custom Badges.
 *
 * Evaluates whether a badge's conditions are satisfied for a given listing.
 * Supports meta-based and pricing-plan-based conditions with AND / OR relation.
 *
 * @package    Directorist_Custom_Badge
 * @author     wpwax
 * @since      3.0.0
 * @version    3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Directorist_Custom_Badges_Conditions
 *
 * Provides static methods for evaluating badge visibility conditions.
 * All public entry points accept raw data and sanitize / validate internally.
 */
class Directorist_Custom_Badges_Conditions {

	// =========================================================================
	// Public API
	// =========================================================================

	/**
	 * Determine whether a badge's conditions are met for a given listing.
	 *
	 * @param array $badge_data  Badge configuration. Expected keys:
	 *                           - conditions        {array}  List of condition arrays.
	 *                           - condition_relation {string} 'AND' (default) or 'OR'.
	 * @param int   $listing_id  WordPress post ID of the listing.
	 * @return bool True when conditions pass, false otherwise.
	 */
	public static function check_conditions( $badge_data, $listing_id ) {
		$listing_id = absint( $listing_id );

		if ( 0 === $listing_id ) {
			return false;
		}

		if ( empty( $badge_data['conditions'] ) || ! is_array( $badge_data['conditions'] ) ) {
			return false;
		}

		$relation = self::sanitize_relation(
			isset( $badge_data['condition_relation'] ) ? $badge_data['condition_relation'] : 'AND'
		);

		$results = self::evaluate_conditions( $badge_data['conditions'], $listing_id );

		// No processable conditions found – treat as not met.
		if ( empty( $results ) ) {
			return false;
		}

		if ( 'OR' === $relation ) {
			return in_array( true, $results, true );   // At least one must pass.
		}

		return ! in_array( false, $results, true );    // All must pass.
	}

	// =========================================================================
	// Condition dispatching
	// =========================================================================

	/**
	 * Iterate over each condition and collect boolean results.
	 *
	 * Unknown condition types are silently skipped so that future types
	 * do not break existing badge configurations.
	 *
	 * @param array $conditions List of condition configuration arrays.
	 * @param int   $listing_id Validated post ID.
	 * @return bool[] One boolean per evaluated condition.
	 */
	private static function evaluate_conditions( array $conditions, $listing_id ) {
		$results = array();

		foreach ( $conditions as $condition ) {
			if ( ! is_array( $condition ) || ! isset( $condition['type'] ) ) {
				continue;
			}

			switch ( $condition['type'] ) {
				case 'meta':
					$results[] = self::check_meta_condition( $condition, $listing_id );
					break;

				case 'pricing_plan':
					$results[] = self::check_pricing_plan_condition( $condition, $listing_id );
					break;

				default:
					// Unknown type – skip without failing the whole badge.
					break;
			}
		}

		return $results;
	}

	// =========================================================================
	// Meta condition
	// =========================================================================

	/**
	 * Evaluate a single meta-based condition for a listing.
	 *
	 * @param array $condition  Condition configuration. Expected keys:
	 *                          - meta_key   {string} Post-meta key.
	 *                          - meta_value {string} Target value for comparison.
	 *                          - compare    {string} Operator: =, !=, >, >=, <, <=,
	 *                                                LIKE, NOT LIKE, IN, NOT IN,
	 *                                                EXISTS, NOT EXISTS.
	 *                          - type_cast  {string} CHAR (default) | NUMERIC |
	 *                                                DECIMAL | DATE | DATETIME | BOOLEAN.
	 * @param int   $listing_id Validated post ID.
	 * @return bool
	 */
	public static function check_meta_condition( $condition, $listing_id ) {
		if ( ! is_array( $condition ) ) {
			return false;
		}

		$meta_key = isset( $condition['meta_key'] ) ? sanitize_text_field( (string) $condition['meta_key'] ) : '';

		if ( '' === $meta_key ) {
			return false;
		}

		$compare_op    = isset( $condition['compare'] )    ? strtoupper( trim( (string) $condition['compare'] ) )    : '=';
		$compare_value = isset( $condition['meta_value'] ) ? $condition['meta_value']                                : '';
		$type_cast     = isset( $condition['type_cast'] )  ? strtoupper( trim( (string) $condition['type_cast'] ) )  : 'CHAR';

		// -----------------------------------------------------------------
		// EXISTS / NOT EXISTS: resolved via dedicated WordPress API so we
		// can correctly distinguish "key absent" from "key present but empty".
		// -----------------------------------------------------------------
		if ( 'EXISTS' === $compare_op ) {
			return metadata_exists( 'post', $listing_id, $meta_key );
		}

		if ( 'NOT EXISTS' === $compare_op ) {
			return ! metadata_exists( 'post', $listing_id, $meta_key );
		}

		$meta_value = get_post_meta( $listing_id, $meta_key, true );

		// -----------------------------------------------------------------
		// Cast both sides to the same type before comparing.
		// -----------------------------------------------------------------
		list( $meta_value, $compare_value ) = self::cast_values(
			$meta_value,
			$compare_value,
			$type_cast,
			$compare_op
		);

		return self::compare_values( $meta_value, $compare_value, $compare_op );
	}

	// =========================================================================
	// Type casting
	// =========================================================================

	/**
	 * Cast meta_value and compare_value to the same comparable type.
	 *
	 * @param mixed  $meta_value    Raw meta value retrieved from the database.
	 * @param mixed  $compare_value Target value from the condition configuration.
	 * @param string $type_cast     Type hint: CHAR, NUMERIC, DECIMAL, DATE,
	 *                              DATETIME, or BOOLEAN.
	 * @param string $compare_op    Comparison operator (used to decide correct
	 *                              empty-array handling for IN / NOT IN).
	 * @return array Two-element array: [ $meta_value, $compare_value ].
	 */
	private static function cast_values( $meta_value, $compare_value, $type_cast, $compare_op ) {
		switch ( $type_cast ) {
			case 'NUMERIC':
			case 'DECIMAL':
				$meta_value    = is_numeric( $meta_value )    ? (float) $meta_value    : 0.0;
				$compare_value = is_numeric( $compare_value ) ? (float) $compare_value : 0.0;
				break;

			case 'DATE':
			case 'DATETIME':
				$meta_value    = ! empty( $meta_value )    ? (int) strtotime( (string) $meta_value )    : 0;
				$compare_value = ! empty( $compare_value ) ? (int) strtotime( (string) $compare_value ) : 0;
				break;

			case 'BOOLEAN':
				$meta_value    = Directorist_Custom_Badges_Helper::convert_to_boolean( $meta_value );
				$compare_value = Directorist_Custom_Badges_Helper::convert_to_boolean( $compare_value );
				break;

			default: // CHAR – string comparison.
				if ( is_array( $meta_value ) ) {
					// Serialised/array meta: keep as array so IN / NOT IN work correctly.
				} elseif ( '' === $meta_value || null === $meta_value || false === $meta_value ) {
					// Empty or missing meta: use an empty array for IN / NOT IN so
					// in_array() returns the semantically correct result without hacks.
					// For all other operators, normalise to an empty string.
					$meta_value = in_array( $compare_op, array( 'IN', 'NOT IN' ), true )
						? array()
						: '';
				} else {
					$meta_value = (string) $meta_value;
				}

				$compare_value = (string) $compare_value;
				break;
		}

		return array( $meta_value, $compare_value );
	}

	// =========================================================================
	// Value comparison
	// =========================================================================

	/**
	 * Apply a comparison operator to two already-cast values.
	 *
	 * Because both values are cast to the same type before this method is
	 * called, all equality checks use strict comparison (===).
	 *
	 * @param mixed  $meta_value    Already-cast meta value.
	 * @param mixed  $compare_value Already-cast comparison target.
	 * @param string $compare_op    Operator string.
	 * @return bool
	 */
	private static function compare_values( $meta_value, $compare_value, $compare_op ) {
		switch ( $compare_op ) {
			case '=':
				return $meta_value === $compare_value;

			case '!=':
				return $meta_value !== $compare_value;

			case '>':
				return $meta_value > $compare_value;

			case '>=':
				return $meta_value >= $compare_value;

			case '<':
				return $meta_value < $compare_value;

			case '<=':
				return $meta_value <= $compare_value;

			case 'LIKE':
				return self::compare_like( $meta_value, $compare_value );

			case 'NOT LIKE':
				return self::compare_not_like( $meta_value, $compare_value );

			case 'IN':
				return self::compare_in( $meta_value, $compare_value );

			case 'NOT IN':
				return self::compare_not_in( $meta_value, $compare_value );

			default:
				// Fallback for any unrecognised operator.
				return $meta_value === $compare_value;
		}
	}

	/**
	 * LIKE: check whether $search appears within $meta_value.
	 *
	 * When $meta_value is an array (serialised meta), checks array membership.
	 *
	 * @param mixed  $meta_value The stored meta value (string or array).
	 * @param string $search     The substring / value to look for.
	 * @return bool
	 */
	private static function compare_like( $meta_value, $search ) {
		if ( is_array( $meta_value ) ) {
			return in_array( $search, $meta_value, true );
		}

		$meta_value = (string) $meta_value;
		$search     = (string) $search;

		if ( '' === $meta_value || '' === $search ) {
			return false;
		}

		return false !== strpos( $meta_value, $search );
	}

	/**
	 * NOT LIKE: check whether $search is absent from $meta_value.
	 *
	 * When $meta_value is an array (serialised meta), checks array non-membership.
	 *
	 * @param mixed  $meta_value The stored meta value (string or array).
	 * @param string $search     The substring / value to look for.
	 * @return bool
	 */
	private static function compare_not_like( $meta_value, $search ) {
		if ( is_array( $meta_value ) ) {
			return ! in_array( $search, $meta_value, true );
		}

		$meta_value = (string) $meta_value;
		$search     = (string) $search;

		// An absent / empty meta value cannot contain the search string.
		if ( '' === $meta_value ) {
			return true;
		}

		// Every string technically "contains" an empty needle – treat as match.
		if ( '' === $search ) {
			return false;
		}

		return false === strpos( $meta_value, $search );
	}

	/**
	 * IN: check whether $needle exists within $meta_value.
	 *
	 * Handles both array-type meta (serialised fields) and plain strings.
	 *
	 * @param mixed  $meta_value The stored meta value (string or array).
	 * @param string $needle     The value to find.
	 * @return bool
	 */
	private static function compare_in( $meta_value, $needle ) {
		if ( is_array( $meta_value ) ) {
			return in_array( $needle, $meta_value, true );
		}

		$meta_value = (string) $meta_value;

		if ( '' === $meta_value ) {
			return false;
		}

		return false !== strpos( $meta_value, (string) $needle );
	}

	/**
	 * NOT IN: check whether $needle is absent from $meta_value.
	 *
	 * Handles both array-type meta (serialised fields) and plain strings.
	 *
	 * @param mixed  $meta_value The stored meta value (string or array).
	 * @param string $needle     The value to look for.
	 * @return bool
	 */
	private static function compare_not_in( $meta_value, $needle ) {
		if ( is_array( $meta_value ) ) {
			return ! in_array( $needle, $meta_value, true );
		}

		$meta_value = (string) $meta_value;

		// An empty meta value is definitively not IN any set.
		if ( '' === $meta_value ) {
			return true;
		}

		return false === strpos( $meta_value, (string) $needle );
	}

	// =========================================================================
	// Pricing-plan condition
	// =========================================================================

	/**
	 * Evaluate a pricing-plan-based condition for a listing.
	 *
	 * Requires the ATBDP_Pricing_Plans class to be present.
	 *
	 * @param array $condition  Condition configuration. Expected keys:
	 *                          - plan_status_condition {string} 'user_active_plan' or
	 *                                                           'listing_has_plan' (default).
	 *                          - plan_id               {int}    Pricing plan term ID (0 = any).
	 *                          - compare               {string} Operator for plan comparison.
	 * @param int   $listing_id Validated post ID.
	 * @return bool
	 */
	public static function check_pricing_plan_condition( $condition, $listing_id ) {
		if ( ! class_exists( 'ATBDP_Pricing_Plans' ) ) {
			return false;
		}

		if ( ! is_array( $condition ) ) {
			return false;
		}

		// Default to listing-level check when the key is absent.
		if ( ! isset( $condition['plan_status_condition'] ) ) {
			$condition['plan_status_condition'] = 'listing_has_plan';
		}

		return self::dispatch_plan_status_check( $condition, $listing_id );
	}

	/**
	 * Route to the appropriate plan-status sub-check.
	 *
	 * @param array $condition  Validated condition configuration.
	 * @param int   $listing_id Validated post ID.
	 * @return bool
	 */
	private static function dispatch_plan_status_check( array $condition, $listing_id ) {
		$status_type = sanitize_text_field( (string) $condition['plan_status_condition'] );
		$plan_id     = isset( $condition['plan_id'] ) ? absint( $condition['plan_id'] ) : 0;
		$compare_op  = isset( $condition['compare'] ) ? sanitize_text_field( (string) $condition['compare'] ) : '=';

		switch ( $status_type ) {
			// Does the listing's author currently have at least one active plan?
			case 'user_active_plan':
				return self::check_user_active_plan( $listing_id, $plan_id );

			// Is the listing itself assigned to the specified plan?
			case 'listing_has_plan':
				return Directorist_Custom_Badges_Helper::listing_has_plan( $listing_id, $plan_id, $compare_op );

			default:
				return false;
		}
	}

	/**
	 * Check whether the listing's author has an active pricing plan.
	 *
	 * @param int $listing_id Post ID.
	 * @param int $plan_id    Plan term ID to check. Pass 0 to match any plan.
	 * @return bool
	 */
	private static function check_user_active_plan( $listing_id, $plan_id ) {
		$listing = get_post( $listing_id );

		if ( ! $listing || ! $listing->post_author ) {
			return false;
		}

		return Directorist_Custom_Badges_Helper::user_has_active_plans(
			(int) $listing->post_author,
			$plan_id
		);
	}

	// =========================================================================
	// Utility helpers
	// =========================================================================

	/**
	 * Normalise a raw condition-relation value to either 'AND' or 'OR'.
	 *
	 * Any value that is not recognised as 'OR' is treated as the safer
	 * default 'AND' (all conditions must pass).
	 *
	 * @param string $relation Raw value from badge configuration.
	 * @return string 'AND' or 'OR'.
	 */
	private static function sanitize_relation( $relation ) {
		$relation = strtoupper( trim( (string) $relation ) );
		return in_array( $relation, array( 'AND', 'OR' ), true ) ? $relation : 'AND';
	}
}
