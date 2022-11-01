<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal;

use WebpConverterVendor\MattPlugins\DeactivationModal\Model\FormOptions;
use WebpConverterVendor\MattPlugins\DeactivationModal\Model\FormTemplate;
use WebpConverterVendor\MattPlugins\DeactivationModal\Model\FormValues;
use WebpConverterVendor\MattPlugins\DeactivationModal\Service\AssetsPrinterService;
use WebpConverterVendor\MattPlugins\DeactivationModal\Service\TemplateGeneratorService;
/**
 * Manages the modal displayed when the plugin is deactivated.
 */
class Modal
{
    const MODAL_TEMPLATE_PATH = __DIR__ . '/../templates/modal.php';
    const MODAL_ASSETS_PATH_CSS = __DIR__ . '/../assets/build/css/styles.css';
    const MODAL_ASSETS_PATH_JS = __DIR__ . '/../assets/build/js/scripts.js';
    /**
     * @var string
     */
    private $plugin_slug;
    /**
     * @var string
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
    /**
     * @param string       $plugin_slug   Example: "plugin-name".
     * @param FormTemplate $form_template Information about the deactivation modal template.
     * @param FormOptions  $form_options  List of plugin deactivation reasons to choose from.
     * @param FormValues   $form_values   Values sent in the request that reports the plugin deactivation.
     */
    public function __construct(string $plugin_slug, FormTemplate $form_template, FormOptions $form_options, FormValues $form_values)
    {
        $this->plugin_slug = $plugin_slug;
        $this->form_template = $form_template;
        $this->form_options = $form_options;
        $this->form_values = $form_values;
        (new AssetsPrinterService($this->plugin_slug))->hooks();
        (new TemplateGeneratorService($this->plugin_slug, $this->form_template, $this->form_options, $this->form_values))->hooks();
    }
}
