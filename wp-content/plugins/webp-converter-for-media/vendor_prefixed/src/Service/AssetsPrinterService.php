<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Service;

use WebpConverterVendor\MattPlugins\DeactivationModal\Hookable;
use WebpConverterVendor\MattPlugins\DeactivationModal\Modal;
/**
 * Prints the needed contents of CSS and JS files on the plugin list page.
 */
class AssetsPrinterService implements Hookable
{
    const PLUGIN_NAME_VARIABLE = '{__PLUGIN_SLUG__}';
    /**
     * @var string
     */
    private $plugin_slug;
    public function __construct(string $plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;
    }
    /**
     * {@inheritdoc}
     */
    public function hooks()
    {
        add_action('admin_print_styles-plugins.php', [$this, 'load_styles']);
        add_action('admin_print_footer_scripts-plugins.php', [$this, 'load_scripts']);
    }
    public function load_styles()
    {
        ?>
		<style id="deactivation-modal-css_<?php 
        echo esc_attr($this->plugin_slug);
        ?>">
			<?php 
        $plugin_slug = $this->plugin_slug;
        include_once Modal::MODAL_ASSETS_PATH_CSS;
        ?>
		</style>
		<?php 
    }
    public function load_scripts()
    {
        ?>
		<script id="deactivation-modal-js_<?php 
        echo esc_attr($this->plugin_slug);
        ?>">
			<?php 
        $plugin_slug = $this->plugin_slug;
        include_once Modal::MODAL_ASSETS_PATH_JS;
        ?>
		</script>
		<?php 
    }
}
