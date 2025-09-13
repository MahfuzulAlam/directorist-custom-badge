<?php

/**
 * Dashboard CSV Export Class
 * Handles CSV export functionality for user dashboard listings
 * 
 * @author  wpWax
 * @since   1.0
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Publishing_Directory_Dashboard_CSV_Export' ) ) {

    class Publishing_Directory_Dashboard_CSV_Export {

        /**
         * Initialize the class
         */
        public function __construct() {
            add_action( 'wp_ajax_publishing_directory_download_csv', [ $this, 'handle_csv_download' ] );
            add_action( 'wp_ajax_nopriv_publishing_directory_download_csv', [ $this, 'handle_csv_download' ] );
        }

        /**
         * Handle CSV download request
         */
        public function handle_csv_download() {
            // Verify nonce for security
            if ( ! wp_verify_nonce( $_POST['nonce'], 'publishing_directory_csv_download' ) ) {
                wp_die( 'Security check failed' );
            }

            // Check if user is logged in
            if ( ! is_user_logged_in() ) {
                wp_die( 'You must be logged in to download CSV' );
            }

            $current_user_id = get_current_user_id();
            $csv_content = $this->get_user_listings_csv_content( $current_user_id );

            if ( empty( $csv_content ) ) {
                wp_die( 'No listings found to export' );
            }

            // Set headers for file download
            $filename = 'my-listings-' . date( 'Y-m-d-H-i-s' ) . '.csv';
            
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Cache-Control: no-cache, no-store, must-revalidate' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            echo $csv_content;
            exit;
        }

        /**
         * Get CSV content for user's listings
         */
        public function get_user_listings_csv_content( $user_id ) {
            $listings_data = $this->get_user_listings_data( $user_id );

            if ( empty( $listings_data ) ) {
                return '';
            }

            $contents = '';

            foreach ( $listings_data as $index => $row ) {
                if ( $index === 0 ) {
                    $contents .= join( ',', array_keys( $row ) ) . "\n";
                }

                $row_content = '';

                foreach ( $row as $row_key => $row_value ) {
                    $row_content__ = '';

                    if ( is_bool( $row_value ) || is_int( $row_value ) || is_double( $row_value ) || is_string( $row_value ) ) {
                        $row_content__ = $row_value;
                    }

                    if ( is_array( $row_value ) ) {
                        $row_content__ = maybe_serialize( $row_value );
                    }

                    $row_content__ = str_replace( '"', "'", $row_content__ );
                    $row_content__ = '"' . $row_content__ . '",';
                    $row_content .= $row_content__;
                }
                $contents .= rtrim( $row_content, ',' ) . "\n";
            }

            return $contents;
        }

        /**
         * Get user's listings data
         */
        public function get_user_listings_data( $user_id ) {
            $listings_data = [];

            $listings = new WP_Query([
                'post_type'      => ATBDP_POST_TYPE,
                'posts_per_page' => -1,
                'post_status'    => ['publish', 'pending', 'draft', 'private', 'expired'],
                'author'         => $user_id,
            ]);

            $field_map = [
                'native_field' => [
                    'verify'      => 'verifyNativeField',
                    'update_data' => 'updateNativeFieldData',
                ],
                'taxonomy_field' => [
                    'verify'      => 'verifyTaxonomyField',
                    'update_data' => 'updateTaxonomyFieldData',
                ],
                'listing_image_module_field' => [
                    'verify'      => 'verifyListingImageModuleField',
                    'update_data' => 'updateListingImageModuleFieldsData',
                ],
                'price_module_field' => [
                    'verify'      => 'verifyPriceModuleField',
                    'update_data' => 'updatePriceModuleFieldData',
                ],
                'map_module_field' => [
                    'verify'      => 'verifyMapModuleField',
                    'update_data' => 'updateMapModuleFieldData',
                ],
                'meta_key_field' => [
                    'verify'      => 'verifyMetaKeyField',
                    'update_data' => 'updateMetaKeyFieldData',
                ],
            ];

            $tr_lengths = [];

            if ( $listings->have_posts() ) {
                while ( $listings->have_posts() ) {
                    $listings->the_post();

                    $row = [];
                    $row['id'] = get_the_ID();
                    $row['directory'] = $this->get_directory_slug_by_id( get_the_id() );
                    $row['status'] = get_post_status( get_the_ID() );
                    $row['publish_date'] = get_the_date( 'Y-m-d H:i:s', get_the_ID() );
                    $row['expiry_date'] = get_post_meta( get_the_ID(), '_expiry_date', true );

                    $directory_type_id = get_post_meta( get_the_ID(), '_directory_type', true );
                    $submission_form   = get_term_meta( $directory_type_id, 'submission_form_fields', true );

                    if ( is_array( $submission_form ) && ! empty( $submission_form['fields'] ) ) {
                        foreach ( $submission_form['fields'] as $field_key => $field_args ) {
                            foreach ( $field_map as $field_map_key => $field_map_args ) {
                                $verify      = $field_map_args[ 'verify' ];
                                $update_data = $field_map_args[ 'update_data' ];

                                if ( $this->$verify( $field_args ) ) {
                                    $row = $this->$update_data( $row, $field_key, $field_args );
                                    break;
                                }
                            }
                        }
                    }

                    // Add qualified badges data
                    $qualified_badges = get_post_meta( get_the_ID(), '_qualified_badges', true );
                    if ( is_array( $qualified_badges ) ) {
                        $row['qualified_badges'] = implode( ', ', $qualified_badges );
                    } else {
                        $row['qualified_badges'] = $qualified_badges;
                    }

                    $max_row_length = count( array_keys( $row ) );
                    $tr_lengths[] = $max_row_length;
                    $listings_data[] = $row;
                }
                wp_reset_postdata();
            }

            $listings_data = $this->justifyDataTableRow( $listings_data, $tr_lengths );

            return $listings_data;
        }

        /**
         * Justify data table rows to have consistent columns
         */
        public function justifyDataTableRow( $data_table = [], $tr_lengths = [] ) {
            if ( empty( $data_table ) ) {
                return $data_table;
            }
            if ( ! is_array( $data_table ) ) {
                return $data_table;
            }

            $max_tr_val   = max( $tr_lengths );
            $max_tr_index = array_search( $max_tr_val, $tr_lengths );
            $modal_tr     = $data_table[ $max_tr_index ];

            $justify_table = [];
            foreach ( $data_table as $row ) {
                $tr = [];

                foreach ( $modal_tr as $row_key => $row_value ) {
                    $tr[ $row_key ] = ( isset( $row[ $row_key ] ) ) ? $row[ $row_key ] : '';
                }

                $justify_table[] = $tr;
            }

            return $justify_table;
        }

        // ================[ Submission Form Fields Helper Methods ]================
        
        public function verifyNativeField( $args = [] ) {
            if ( ! is_array( $args ) ) {
                return false;
            }
            if ( empty( $args['widget_group'] ) ) {
                return false;
            }
            if ( empty( $args['widget_name'] ) ) {
                return false;
            }
            if ( empty( $args['field_key'] ) ) {
                return false;
            }
            if ( 'preset' !== $args['widget_group'] ) {
                return false;
            }

            $native_fields = [ 'listing_title', 'listing_content' ];

            if ( ! in_array( $args['field_key'], $native_fields ) ) {
                return false;
            }

            return true;
        }

        public function updateNativeFieldData( array $row = [], string $field_key = '', array $field_args = [] ) {
            $field_data_map = [
                'listing_title'   => 'get_the_title',
                'listing_content' => 'get_the_content',
            ];

            $field_key = $field_args['field_key'];
            $content = call_user_func( $field_data_map[ $field_key ] );

            $row[ $field_key ] = $this->escape_data( $content );

            return $row;
        }

        public function verifyTaxonomyField( $args = [] ) {
            if ( ! is_array( $args ) ) {
                return false;
            }
            if ( empty( $args['widget_group'] ) ) {
                return false;
            }
            if ( empty( $args['widget_name'] ) ) {
                return false;
            }
            if ( empty( $args['field_key'] ) ) {
                return false;
            }
            if ( 'preset' !== $args['widget_group'] ) {
                return false;
            }

            $taxonomy = [ 'category', 'location', 'tag' ];

            if ( ! in_array( $args['widget_name'], $taxonomy ) ) {
                return false;
            }

            return true;
        }

        public function updateTaxonomyFieldData( array $row = [], string $field_key = '', array $field_args = [] ) {
            $term_map = [
                'category' => ATBDP_CATEGORY,
                'location' => ATBDP_LOCATION,
                'tag'      => ATBDP_TAGS,
            ];

            $row[ $field_key ] = $this->get_term_names( get_the_ID(), $term_map[ $field_args['widget_name'] ] );

            return $row;
        }

        public function verifyListingImageModuleField( $args = [] ) {
            if ( ! is_array( $args ) ) {
                return false;
            }
            if ( empty( $args['widget_group'] ) ) {
                return false;
            }
            if ( empty( $args['widget_name'] ) ) {
                return false;
            }
            if ( empty( $args['field_key'] ) ) {
                return false;
            }
            if ( 'preset' !== $args['widget_group'] ) {
                return false;
            }
            if ( 'listing_img' !== $args['field_key'] ) {
                return false;
            }

            return true;
        }

        public function updateListingImageModuleFieldsData( array $row = [], string $field_key = '', array $field_args = [] ) {
            $preview_image  = directorist_get_listing_preview_image( get_the_ID() );
            $gallery_images = directorist_get_listing_gallery_images( get_the_ID() );

            if ( empty( $preview_image ) && empty( $gallery_images ) ) {
                return $row;
            }

            $image_urls = [];
            $image_url  = wp_get_attachment_image_url( $preview_image, 'full' );

            if ( $image_url ) {
                $image_urls[] = $image_url;
            }

            foreach ( $gallery_images as $image ) {
                if ( $image === $preview_image ) {
                    continue;
                }
                
                $image_url = wp_get_attachment_image_url( $image, 'full' );
                if ( $image_url ) {
                    $image_urls[] = $image_url;
                }
            }

            $row[ $field_args['field_key'] ] = implode( ',', $image_urls );

            return $row;
        }

        public function verifyMetaKeyField( $args = [] ) {
            if ( ! is_array( $args ) ) {
                return false;
            }
            if ( empty( $args['widget_group'] ) ) {
                return false;
            }
            if ( empty( $args['widget_name'] ) ) {
                return false;
            }
            if ( empty( $args['field_key'] ) ) {
                return false;
            }

            return true;
        }

        public function updateMetaKeyFieldData( array $row = [], string $field_key = '', array $field_args = [] ) {
            $value = get_post_meta( get_the_id(), '_' . $field_args['field_key'], true );
            $row[ $field_args['field_key'] ] = $this->escape_data( $value );

            return $row;
        }

        public function verifyPriceModuleField( $args = [] ) {
            if ( ! is_array( $args ) ) {
                return false;
            }
            if ( empty( $args['widget_group'] ) ) {
                return false;
            }
            if ( empty( $args['widget_name'] ) ) {
                return false;
            }
            if ( 'pricing' !== $args['widget_name'] ) {
                return false;
            }

            return true;
        }

        public function updatePriceModuleFieldData( array $row = [], string $field_key = '', array $field_args = [] ) {
            $row[ 'price' ] = $this->escape_data( get_post_meta( get_the_id(), '_price', true ) );
            $row[ 'price_range' ] = $this->escape_data( get_post_meta( get_the_id(), '_price_range', true ) );
            $row[ 'atbd_listing_pricing' ] = $this->escape_data( get_post_meta( get_the_id(), '_atbd_listing_pricing', true ) );

            return $row;
        }

        public function verifyMapModuleField( $args = [] ) {
            if ( ! is_array( $args ) ) {
                return false;
            }
            if ( empty( $args['widget_group'] ) ) {
                return false;
            }
            if ( empty( $args['widget_name'] ) ) {
                return false;
            }
            if ( 'map' !== $args['widget_name'] ) {
                return false;
            }

            return true;
        }

        public function updateMapModuleFieldData( array $row = [], string $field_key = '', array $field_args = [] ) {
            $row[ 'hide_map' ] = get_post_meta( get_the_id(), '_hide_map', true );
            $row[ 'manual_lat' ] = $this->escape_data( get_post_meta( get_the_id(), '_manual_lat', true ) );
            $row[ 'manual_lng' ] = $this->escape_data( get_post_meta( get_the_id(), '_manual_lng', true ) );

            return $row;
        }

        // ================[ Helper Methods ]================

        public function get_directory_slug_by_id( $id = 0 ) {
            $directory_type_id   = get_post_meta( $id, '_directory_type', true );
            $directory_type      = ( ! empty( $directory_type_id ) ) ? get_term_by( 'id', $directory_type_id, ATBDP_DIRECTORY_TYPE ) : '';
            $directory_type_slug = ( ! empty( $directory_type ) && is_object( $directory_type ) ) ? $directory_type->slug : '';

            return $directory_type_slug;
        }

        public function get_term_names( $post_id = 0, $taxonomy = '' ) {
            $terms = get_the_terms( $post_id, $taxonomy );

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                return '';
            }

            return join( ',', wp_list_pluck( $terms, 'name' ) );
        }

        /**
         * Escape a string to be used in a CSV context
         */
        public function escape_data( $data ) {
            if ( ! is_string( $data ) ) {
                return $data;
            }

            $active_content_triggers = [ '=', '+', '-', '@' ];

            if ( in_array( mb_substr( $data, 0, 1 ), $active_content_triggers, true ) ) {
                $data = "'" . $data;
            }

            return $data;
        }
    }

    // Initialize CSV Export functionality
    new Publishing_Directory_Dashboard_CSV_Export();
}