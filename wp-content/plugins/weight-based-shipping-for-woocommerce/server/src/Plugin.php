<?php
namespace Wbs;

use wbs;
use Wbs\Common\Once;
use Wbs\Migrations\ConfigStorage;
use Wbs\Services\ApiService;
use Wbs\Services\LegacyConfigService;
use WbsVendors\Dgm\PluginServices\ServiceInstaller;
use WbsVendors\Dgm\Shengine\Migrations\MigrationService;
use WbsVendors\Dgm\Shengine\Migrations\Storage\WordpressOptions;
use WC_Cache_Helper;


/**
 * @property-read PluginMeta $meta
 * @property-read LegacyConfigService $legacyConfig
 */
class Plugin
{   
    const ID = 'wbs';

    /**
     * @param string $entrypoint
     * @return void
     */
    static public function setupOnce($entrypoint)
    {
        if (!isset(self::$instance)) {
            $plugin = new Plugin($entrypoint);
            $plugin->setup();
            self::$instance = $plugin;
        }
    }

    /**
     * @return self
     */
    static public function instance()
    {
        return self::$instance;
    }

    /**
     * @param string $entrypoint
     */
    public function __construct($entrypoint)
    {
        $entrypoint = wp_normalize_path($entrypoint);

        $this->entrypoint = $entrypoint;
        $this->root = $root = dirname($this->entrypoint).'/server';
        $this->meta = new PluginMeta($entrypoint, $root);

        $this->legacyConfigFactory = new Once(function() { return new LegacyConfigService(); });
    }

    public function setup()
    {
        register_activation_hook($this->entrypoint, array($this, '__resetShippingCache'));
        register_deactivation_hook($this->entrypoint, array($this, '__resetShippingCache'));

        add_filter('woocommerce_shipping_methods', array($this, '__woocommerceShippingMethods'));
        add_filter('plugin_action_links_' . plugin_basename($this->entrypoint), array($this, '__pluginActionLinks'));

        ServiceInstaller::create()->installIfReady(
            $this->createMigrationService(),
            new ApiService($this->legacyConfigFactory)
        );

        add_action('woocommerce_init', function() {
            if (function_exists('wc_get_shipping_method_count') && wc_get_shipping_method_count(true) == 0) {
                $trv = WC_Cache_Helper::get_transient_version('shipping');
                if (version_compare(WC()->version, '3.6.0', '>=')) {
                    set_transient(
                        'wc_shipping_method_count_legacy',
                        array('value' => 1, 'version' => $trv),
                        DAY_IN_SECONDS * 30
                    );
                } else {
                    set_transient(
                        'wc_shipping_method_count_1_' . $trv,
                        1,
                        DAY_IN_SECONDS * 30
                    );
                }
            }
        });
    }


    /**
     * @internal
     */
    function __woocommerceShippingMethods(/** @noinspection PhpDocSignatureInspection */ $shippingMethods)
    {
        $shippingMethods[self::ID] = self::wc26plus() ? ShippingMethod::class : wbs::class;
        return $shippingMethods;
    }

    /**
     * @internal
     */
    function __pluginActionLinks(/** @noinspection PhpDocSignatureInspection */ $links)
    {
        $newLinks = array();
        if (self::wc26plus()) {
            $newLinks[self::shippingUrl()] = 'Shipping zones';
            $newLinks[self::shippingUrl(self::ID)] = 'Global shipping rules';
        } else {
            $newLinks[self::shippingUrl(wbs::class)] = 'Settings';
        }

        foreach ($newLinks as $url => &$text) {
            $text = '<a href="'.esc_html($url).'">'.esc_html($text).'</a>';
        }

        array_splice($links, 0, 0, $newLinks);

        return $links;
    }

    /**
     * @internal
     */
    function __resetShippingCache()
    {
        $reset = function() {
            WC_Cache_Helper::get_transient_version('shipping', true);
        };

        if (did_action('woocommerce_init')) {
            $reset();
        } else {
            add_action('woocommerce_init', $reset);
        }
    }

    /**
     * @param string $property
     * @return mixed|null
     * @internal
     */
    function __get($property)
    {
        switch ((string)$property) {
            case 'legacyConfig':
                return call_user_func($this->legacyConfigFactory);
            case 'meta':
                return $this->meta;
            default:
                trigger_error("Undefined property '{$property}'", E_USER_NOTICE);
                return null;
        }
    }


    /** @var self */
    private static $instance;
    
    /** @var string */
    private $entrypoint;
    
    /** @var string */
    private $root;

    /** @var callable */
    private $legacyConfigFactory;
    
    /** @var PluginMeta */
    private $meta;


    private function createMigrationService()
    {
        global $wpdb;

        $options = new WordpressOptions($wpdb);

        return new MigrationService(
            $this->meta->version,
            $options->bind('wbs_config_version'),
            $this->meta->paths->root.'/migrations',
            new ConfigStorage('wbs\\_%config', $options)
        );
    }

    private static function shippingUrl($section = null)
    {
        $query = array(
            "page" => "wc-settings",
            "tab" => "shipping",
        );

        if (isset($section)) {
            $query['section'] = $section;
        }

        $query = http_build_query($query, '', '&');

        return admin_url("admin.php?{$query}");
    }

    public static function wc26plus() {
        return !defined('WC_VERSION') || version_compare(WC_VERSION, '2.6.0', '>=');
    }

    public static function wc441plus() {
        return !defined('WC_VERSION') || version_compare(WC_VERSION, '4.4.1', '>=');
    }
}