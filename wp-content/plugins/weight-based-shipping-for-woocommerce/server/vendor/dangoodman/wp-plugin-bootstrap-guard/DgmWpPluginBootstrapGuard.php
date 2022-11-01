<?php /** @noinspection NestedPositiveIfStatementsInspection */

class WbsVendors_DgmWpPluginBootstrapGuard
{
    /**
     * @param string $pluginName
     * @param string $phpVersion
     * @param string $wpVersion
     * @param string|null $wcVersion
     * @param string $bootstrapScript
     * @param []string $phpExt
     * @return void
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public static function checkPrerequisitesAndBootstrap($pluginName,
                                                          $phpVersion,
                                                          $wpVersion,
                                                          $wcVersion,
                                                          $bootstrapScript,
                                                          $phpExt = null)
    {
        $instance = new self($pluginName, $phpVersion, $wpVersion, $wcVersion, $bootstrapScript, $phpExt);
        $instance->checkAndBoot();
    }

    /**
     * @internal
     * @return void
     */
    public function _showNotices()
    {
        $this->showNotices($this->errors, 'error');
        $this->showNotices($this->warnings, 'warning');
    }

    /**
     * @internal
     * @return void
     */
    public function _checkWoocommerceVersionAndBootstrap()
    {
        $wcVersion = defined('WC_VERSION') ? WC_VERSION : null;

        if (!isset($wcVersion) || version_compare($wcVersion, $this->wcVersion, '<')) {
            $this->errors[] =
                "You are running an outdated WooCommerce version".(isset($wcVersion) ? " ".$wcVersion : null).".
                 {pluginName} requires WooCommerce {wcVersion}+.
                 Consider updating to a modern WooCommerce version.";
            return;
        }

        /** @noinspection PhpIncludeInspection */
        include($this->bootstrapScript);
    }

    /**
     * @param string $pluginName
     * @param string $phpVersion
     * @param string $wpVersion
     * @param string|null $wcVersion
     * @param string $bootstrapScript
     * @param []string $phpExt
     * @return void
     */
    private function __construct($pluginName, $phpVersion, $wpVersion, $wcVersion, $bootstrapScript, $phpExt = null)
    {
        $this->pluginName = $pluginName;
        $this->phpVersion = $phpVersion;
        $this->wpVersion = $wpVersion;
        $this->wcVersion = $wcVersion;
        $this->bootstrapScript = $bootstrapScript;
        $this->phpExt = $phpExt;

        // Hook admin_notices always since errors can be added lately
        add_action('admin_notices', array($this, '_showNotices'));
    }

    /**
     * @return void
     */
    private function checkAndBoot()
    {
        $this->errors = array();
        $this->warnings = array();

        if (version_compare($phpv = PHP_VERSION, $this->phpVersion, '<')) {
            $this->errors[] =
                "You are running an outdated PHP version {$phpv}. 
                 {pluginName} requires PHP {phpVersion}+. 
                 Contact your hosting support to switch to a newer PHP version.";

        }

        if (isset($this->phpExt) && !empty($this->phpExt)) {
            foreach ($this->phpExt as $ext) {
                if (!extension_loaded($ext)) {
                    $this->errors[] =
                        "{pluginName} requires {$ext} PHP extension. 
                        It is disabled at the moment.
                        Please revisit your PHP settings to enable it.";
                }
            }
        }

        global $wp_version;
        if (isset($wp_version) && version_compare($wp_version, $this->wpVersion, '<')) {
            $this->errors[] =
                "You are running an outdated WordPress version {$wp_version}.
                 {pluginName} is tested with WordPress {wpVersion}+.
                 Consider updating to a modern WordPress version.";
        }

        if (isset($this->wcVersion)) {
            if (!self::isWoocommerceActive()) {
                $this->errors[] =
                    "WooCommerce is not active. 
                     {pluginName} requires WooCommerce to be installed and activated.";
            } else {
                if (defined('WC_VERSION') || did_action('woocommerce_loaded')) {
                    $this->_checkWoocommerceVersionAndBootstrap();
                } else {
                    add_action('woocommerce_loaded', array($this, '_checkWoocommerceVersionAndBootstrap'));
                }
            }
        }

        if ($this->errors) {
            return;
        }

        if (!class_exists('WbsVendors_DgmWpDismissibleNotices')) {
            require_once(__DIR__.'/DgmWpDismissibleNotices.php');
        }

        if (!WbsVendors_DgmWpDismissibleNotices::isNoticeDismissed($noticeId = 'dgm-zend-guard-loader')) {
            if (version_compare($phpv = PHP_VERSION, $minphpv = '5.4', '<') && self::isZendGuardLoaderActive()) {
                $this->warnings[$noticeId] =
                    "You are running PHP version {$phpv} with Zend Guard Loader extension active.
                    This server configuration might not be compatible with {pluginName}.
                    If you are getting 500 Internal Server Error or 503 Service Unavailable 
                    errors on Cart or Checkout pages when the plugin is active, disable
                    Zend Guard Loader or update your PHP version to {$minphpv}+.";
            }
        }

        if ($this->warnings) {
            WbsVendors_DgmWpDismissibleNotices::init();
        }
    }

    private function showNotices($notices, $kind)
    {
        if ($notices) {
            ?>
                <?php foreach ($notices as $dismissId => $notice): ?>
                    <?php
                        $dismissClass = null;
                        $dismissAttr = null;
                        if (is_string($dismissId) && !empty($dismissId)) {
                            $dismissClass = "is-dismissible";
                            $dismissAttr = "data-dismissible=".esc_html($dismissId);
                        }
                    ?>
                    <div class="notice notice-<?php echo esc_html($kind) ?> <?php echo $dismissClass ?>"
                        <?php echo $dismissAttr ?>
                    >
                        <?php
                            $notice = strtr($notice, array(
                                '{pluginName}' => $this->pluginName,
                                '{phpVersion}' => $this->phpVersion,
                                '{wpVersion}' => $this->wpVersion,
                                '{wcVersion}' => $this->wcVersion,
                            ));
                        ?>
                        <p><?php echo esc_html($notice) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php
        }
    }

    private static function isWoocommerceActive()
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            return true;
        }

        return false;
    }

    private static function getPhpIniBool($name, $default = null)
    {
        $value = ini_get($name);

        if ($value === false) {
            return $default;
        }

        if ((int)$value > 0) {

            $value = true;

        } else {

            $lowered = strtolower($value);

            if (in_array($lowered, array('true', 'on', 'yes'), true)) {
                $value = true;
            } else {
                $value = false;
            }
        }

        return $value;
    }

    private static function isZendGuardLoaderActive()
    {
        return
            in_array('Zend Guard Loader', get_loaded_extensions(), true) &&
            self::getPhpIniBool('zend_loader.enable', true);
    }


    private $pluginName;
    private $phpVersion;
    private $wpVersion;
    private $wcVersion;
    private $bootstrapScript;
    private $phpExt;

    private $errors;
    private $warnings;
}