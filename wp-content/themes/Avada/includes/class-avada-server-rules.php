<?php
/**
 * Adds / removes server rules.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      7.4
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

define( 'AVADA_MARKER_BEGIN_GZIP_OUTPUT', '# BEGIN_AVADA_GZIP_OUTPUT' );
define( 'AVADA_MARKER_END_GZIP_OUTPUT', '# END_AVADA_GZIP_OUTPUT' );

define( 'AVADA_MARKER_BEGIN_COMPRESSION', '# BEGIN_AVADA_GZIP_COMPRESSION' );
define( 'AVADA_MARKER_END_COMPRESSION', '# END_AVADA_GZIP_COMPRESSION' );

/**
 * Adds / removes server rules.
 */
class Avada_Server_Rules {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 6.2
	 * @var object
	 */
	private static $instance;

	/**
	 * WP Filesystem object.
	 *
	 * @access private
	 * @since 7.4
	 * @var object
	 */
	private $wp_filesystem;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 7.4
	 * @return void
	 */
	private function __construct() {
		$this->wp_filesystem = Fusion_Helper::init_filesystem();
		add_filter( 'updated_option', [ $this, 'maybe_change_server_rules' ], 10, 3 );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 7.4
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Avada_Server_Rules();
		}
		return self::$instance;
	}

	/**
	 * Adds / Removes server rules based on GO value.
	 *
	 * @access public
	 * @since 7.4
	 * @param string $option    Name of the updated option.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 * @return mixed
	 */
	public function maybe_change_server_rules( $option, $old_value, $value ) {

		if ( 'fusion_options' === $option && isset( $value['gzip_status'] ) ) {
			$remove_rules = '0' === $value['gzip_status'];
			$this->rewrite_htaccess( $remove_rules );
		}

		return $value;
	}

	/**
	 * Checks if Apache server.
	 *
	 * @since 7.4
	 * @return bool
	 */
	public function is_apache() {

		// Assume Apache when can't be detected, since it is most common.
		if ( empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return true;
		}

		return isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stristr( $_SERVER['SERVER_SOFTWARE'], 'Apache' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Modify .htaccess file
	 *
	 * @since 7.4
	 * @param bool $remove Whether to remove or add rules.
	 * @return bool
	 */
	public function rewrite_htaccess( $remove = false ) {

		if ( $this->is_htaccess_writable() ) {
			$has_change    = false;
			$htaccess_file = $this->get_htaccess_file();
			$rules         = $this->wp_filesystem->get_contents( $htaccess_file );

			if ( ! $remove ) {
				$rules = trim( $rules );

				if ( false === strpos( $rules, AVADA_MARKER_BEGIN_GZIP_OUTPUT ) ) {
					$rules     .= $this->get_gzip_htaccess();
					$has_change = true;
				}

				if ( false === strpos( $rules, 'mod_deflate.c' ) && false === strpos( $rules, 'mod_gzip.c' ) ) {
					$rules     .= $this->get_mod_rewrite();
					$has_change = true;
				}
			} else {

				if ( false !== strpos( $rules, AVADA_MARKER_BEGIN_GZIP_OUTPUT ) ) {
					$starts_at      = strpos( $rules, AVADA_MARKER_BEGIN_GZIP_OUTPUT );
					$ends_at        = strpos( $rules, AVADA_MARKER_END_GZIP_OUTPUT, $starts_at );
					$text_do_delete = substr( $rules, $starts_at, ( $ends_at + strlen( AVADA_MARKER_END_GZIP_OUTPUT ) ) - $starts_at );
					$rules          = str_replace( $text_do_delete, '', $rules );
					$has_change     = true;
				}

				if ( false !== strpos( $rules, AVADA_MARKER_BEGIN_COMPRESSION ) ) {
					$starts_at      = strpos( $rules, AVADA_MARKER_BEGIN_COMPRESSION );
					$ends_at        = strpos( $rules, AVADA_MARKER_END_COMPRESSION, $starts_at );
					$text_do_delete = substr( $rules, $starts_at, ( $ends_at + strlen( AVADA_MARKER_END_COMPRESSION ) ) - $starts_at );
					$rules          = str_replace( $text_do_delete, '', $rules );
					$has_change     = true;
				}
			}

			if ( true === $has_change ) {
				return $this->wp_filesystem->put_contents( $htaccess_file, trim( $rules ) );
			}
		}

	}

	/**
	 * Checks if .htaccess file is writable.
	 *
	 * @since 7.4
	 * @return bool
	 */
	public function is_htaccess_writable() {

		if ( ! $this->is_apache() ) {
			return false;
		}

		$htaccess_file = $this->get_htaccess_file();
		return $this->wp_filesystem->exists( $htaccess_file ) && $this->wp_filesystem->is_writable( $htaccess_file );
	}

	/**
	 * Get .htaccess path.
	 *
	 * @since 7.4
	 * @return string
	 */
	public function get_htaccess_file() {
		return get_home_path() . '.htaccess';
	}

	/**
	 * Get gzip htaccess rules.
	 *
	 * @since 7.4
	 * @return string
	 */
	private function get_gzip_htaccess() {
		return PHP_EOL . AVADA_MARKER_BEGIN_GZIP_OUTPUT . PHP_EOL .
		'<IfModule mod_rewrite.c>
    <Files *.js.gz>
    AddType "text/javascript" .gz
    AddEncoding gzip .gz
    </Files>
    <Files *.css.gz>
    AddType "text/css" .gz
    AddEncoding gzip .gz
    </Files>
    <Files *.svg.gz>
    AddType "image/svg+xml" .gz
    AddEncoding gzip .gz
    </Files>
    <Files *.json.gz>
    AddType "application/json" .gz
    AddEncoding gzip .gz
    </Files>
    # Serve pre-compressed gzip assets
    RewriteCond %{HTTP:Accept-Encoding} gzip
    RewriteCond %{REQUEST_FILENAME}.gz -f
    RewriteRule ^(.*)$ $1.gz [QSA,L]
</IfModule>'
		. PHP_EOL . AVADA_MARKER_END_GZIP_OUTPUT . PHP_EOL;
	}

		/**
		 * Get mod_rewrite htaccess rules.
		 *
		 * @since 7.4
		 * @return string
		 */
	private function get_mod_rewrite() {

		return PHP_EOL . AVADA_MARKER_BEGIN_COMPRESSION . PHP_EOL .
		'<IfModule mod_deflate.c>
    #add content typing
    AddType application/x-gzip .gz .tgz
    AddEncoding x-gzip .gz .tgz
    # Insert filters
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/x-httpd-php
    AddOutputFilterByType DEFLATE application/x-httpd-fastphp
    AddOutputFilterByType DEFLATE image/svg+xml
    # Drop problematic browsers
    BrowserMatch ^Mozilla/4 gzip-only-text/html
    BrowserMatch ^Mozilla/4\.0[678] no-gzip
    BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
    <IfModule mod_headers.c>
        # Make sure proxies don\'t deliver the wrong content
        Header append Vary User-Agent env=!dont-vary
    </IfModule>
</IfModule>
# END GZIP COMPRESSION
## EXPIRES CACHING ##

<IfModule mod_expires.c>
ExpiresActive On
ExpiresDefault "access plus 1 week"
ExpiresByType image/jpg "access plus 1 year"
ExpiresByType image/jpeg "access plus 1 year"
ExpiresByType image/gif "access plus 1 year"
ExpiresByType image/png "access plus 1 year"
ExpiresByType image/svg+xml "access plus 1 month"
ExpiresByType text/css "access plus 1 month"
ExpiresByType text/html "access plus 1 minute"
ExpiresByType text/plain "access plus 1 month"
ExpiresByType text/x-component "access plus 1 month"
ExpiresByType text/javascript "access plus 1 month"
ExpiresByType text/x-javascript "access plus 1 month"
ExpiresByType application/pdf "access plus 1 month"
ExpiresByType application/javascript "access plus 1 months"
ExpiresByType application/x-javascript "access plus 1 months"
ExpiresByType application/x-shockwave-flash "access plus 1 month"
ExpiresByType image/x-icon "access plus 1 year"
ExpiresByType application/xml "access plus 0 seconds"
ExpiresByType application/json "access plus 0 seconds"
ExpiresByType application/ld+json "access plus 0 seconds"
ExpiresByType application/xml "access plus 0 seconds"
ExpiresByType text/xml "access plus 0 seconds"
ExpiresByType application/x-web-app-manifest+json "access plus 0 seconds"
ExpiresByType text/cache-manifest "access plus 0 seconds"
ExpiresByType audio/ogg "access plus 1 month"
ExpiresByType video/mp4 "access plus 1 month"
ExpiresByType video/ogg "access plus 1 month"
ExpiresByType video/webm "access plus 1 month"
ExpiresByType application/atom+xml "access plus 1 hour"
ExpiresByType application/rss+xml "access plus 1 hour"
ExpiresByType application/font-woff "access plus 1 month"
ExpiresByType application/vnd.ms-fontobject "access plus 1 month"
ExpiresByType application/x-font-ttf "access plus 1 month"
ExpiresByType font/opentype "access plus 1 month"
</IfModule>
#Alternative caching using Apache`s "mod_headers", if it`s installed.
#Caching of common files - ENABLED
<IfModule mod_headers.c>
<FilesMatch "\.(ico|pdf|flv|swf|js|css|gif|png|jpg|jpeg|ico|txt|html|htm)$">
Header set Cache-Control "max-age=2592000, public"
</FilesMatch>
</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(js|css|xml|gz)$">
    Header append Vary Accept-Encoding
    </FilesMatch>
# Set Keep Alive Header
Header set Connection keep-alive
</IfModule>

<IfModule mod_gzip.c>
    mod_gzip_on Yes
    mod_gzip_dechunk Yes
    mod_gzip_item_include file \.(html?|txt|css|js|php|pl)$
    mod_gzip_item_include handler ^cgi-script$
    mod_gzip_item_include mime ^text/.*
    mod_gzip_item_include mime ^application/x-javascript.*
    mod_gzip_item_exclude mime ^image/.*
    mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
</IfModule>

# If your server don`t support ETags deactivate with "None" (and remove header)
<IfModule mod_expires.c>
    <IfModule mod_headers.c>
    Header unset ETag
    </IfModule>
    FileETag None
</IfModule>
## EXPIRES CACHING ##'
		. PHP_EOL . AVADA_MARKER_END_COMPRESSION . PHP_EOL;
	}

}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
