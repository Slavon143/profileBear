<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PWBE_DB' ) ) :

final class PWBE_DB {

    private static $use_mysqli = null;

    public static function query( $sql ) {
        global $wpdb;

        PWBE_DB::init();

        if ( PWBE_DB::$use_mysqli ) {
            return mysqli_query( $wpdb->dbh, $sql );
        } else {
            return mysql_query( $sql, $wpdb->dbh );
        }
    }

    public static function fetch_object( $result ) {
        global $wpdb;

        PWBE_DB::init();

        if ( PWBE_DB::$use_mysqli && $result instanceof mysqli_result) {
            return mysqli_fetch_object( $result );
        } else if ( is_resource( $result ) ) {
            return mysql_fetch_object( $result );
        }
    }

    public static function free_result( $result ) {
        global $wpdb;

        PWBE_DB::init();

        if ( PWBE_DB::$use_mysqli && $result instanceof mysqli_result) {
            mysqli_free_result( $result );
            $result = null;

            // Sanity check before using the handle
            if ( empty( $wpdb->dbh ) || !( $wpdb->dbh instanceof mysqli ) ) {
                return;
            }

            // Clear out any results from a multi-query
            while ( mysqli_more_results( $wpdb->dbh ) ) {
                mysqli_next_result( $wpdb->dbh );
            }

        } else if ( is_resource( $result ) ) {
            mysql_free_result( $result );
        }
    }

    public static function num_rows( $result ) {
        global $wpdb;

        PWBE_DB::init();

        if ( PWBE_DB::$use_mysqli && $result instanceof mysqli_result) {
            return mysqli_num_rows( $result );
        } else if ( is_resource( $result ) ) {
            return mysql_num_rows( $result );
        }
    }

    public static function error() {
        global $wpdb;

        PWBE_DB::init();

        if ( PWBE_DB::$use_mysqli ) {
            return mysqli_error( $wpdb->dbh );
        } else {
            return mysql_error( $wpdb->dbh );
        }
    }

    private static function init() {
        if ( PWBE_DB::$use_mysqli !== null ) {
            return;
        }

        if ( defined( 'PW_BULK_EDIT_USE_MYSQLI' ) ) {
            PWBE_DB::$use_mysqli = PW_BULK_EDIT_USE_MYSQLI;
            return;
        }

        // Default
        PWBE_DB::$use_mysqli = false;

        if ( function_exists( 'mysqli_connect' ) ) {
            if ( defined( 'WP_USE_EXT_MYSQL' ) ) {
                PWBE_DB::$use_mysqli = ! WP_USE_EXT_MYSQL;
            } elseif ( version_compare( phpversion(), '5.5', '>=' ) || ! function_exists( 'mysql_connect' ) ) {
                PWBE_DB::$use_mysqli = true;
            } elseif ( false !== strpos( $GLOBALS['wp_version'], '-' ) ) {
                PWBE_DB::$use_mysqli = true;
            }
        }
    }
}

endif;

?>