<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Service;

use WebpConverterVendor\MattPlugins\DeactivationModal\Hookable;
use WebpConverterVendor\MattPlugins\DeactivationModal\Modal;
use WebpConverterVendor\MattPlugins\DeactivationModal\Model\FormOptions;
use WebpConverterVendor\MattPlugins\DeactivationModal\Model\FormTemplate;
use WebpConverterVendor\MattPlugins\DeactivationModal\Model\FormValues;
/**
 * Prints the deactivation modal template on the plugin list page.
 */
class TemplateGeneratorService implements Hookable
{
    /**
     * @var string
     */
    private $plugin_slug;
    /**
     * @var FormTemplate
     */
    private $form_template;
    /**
     * @var FormOptions
     */
    private $form_options;
    /**
     * @var FormValues
     */
    private $form_values;
    public function __construct(string $plugin_slug, FormTemplate $form_template, FormOptions $form_options, FormValues $form_values)
    {
        $this->plugin_slug = $plugin_slug;
        $this->form_template = $form_template;
        $this->form_options = $form_options;
        $this->form_values = $form_values;
    }
    /**
     * {@inheritdoc}
     */
    public function hooks()
    {
        add_action('admin_print_footer_scripts-plugins.php', [$this, 'load_template'], 0);
    }
    public function load_template()
    {
        $params = ['plugin_slug' => $this->plugin_slug, 'form_template' => $this->form_template, 'form_options' => $this->form_options, 'form_values' => $this->form_values];
        \extract($params);
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        require_once Modal::MODAL_TEMPLATE_PATH;
    }
}
