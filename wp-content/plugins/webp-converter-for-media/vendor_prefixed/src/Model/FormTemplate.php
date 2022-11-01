<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Model;

/**
 * Stores information about the deactivation modal template.
 */
class FormTemplate
{
    /**
     * @var string
     */
    private $api_url;
    /**
     * @var string
     */
    private $form_title;
    /**
     * @var string
     */
    private $form_desc;
    /**
     * @var string
     */
    private $button_submit_label;
    /**
     * @var string
     */
    private $button_skip_label;
    /**
     * @var string|null
     */
    private $logo_url;
    /**
     * @var string
     */
    private $field_name_reason = 'request_reason';
    /**
     * @var string
     */
    private $field_name_comment = 'request_comment_%s';
    public function __construct(string $api_url, string $form_title, string $form_desc, string $button_submit_label, string $button_skip_label, string $logo_url = null)
    {
        $this->api_url = $api_url;
        $this->form_title = $form_title;
        $this->form_desc = $form_desc;
        $this->button_submit_label = $button_submit_label;
        $this->button_skip_label = $button_skip_label;
        $this->logo_url = $logo_url;
    }
    public function get_api_url() : string
    {
        return $this->api_url;
    }
    public function get_form_title() : string
    {
        return $this->form_title;
    }
    public function get_form_desc() : string
    {
        return $this->form_desc;
    }
    public function get_button_submit_label() : string
    {
        return $this->button_submit_label;
    }
    public function get_button_skip_label() : string
    {
        return $this->button_skip_label;
    }
    /**
     * @return string|null
     */
    public function get_logo_url()
    {
        return $this->logo_url;
    }
    public function get_field_name_reason() : string
    {
        return $this->field_name_reason;
    }
    public function get_field_name_comment() : string
    {
        return $this->field_name_comment;
    }
}
