<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Model;

/**
 * Stores information about the selectable reason of the plugin deactivation.
 */
class FormOption
{
    /**
     * @var string Key of the deactivation reason.
     */
    private $key;
    /**
     * @var int Order priority (ascending).
     */
    private $priority;
    /**
     * @var string Label of the reason option.
     */
    private $label;
    /**
     * @var callable|null A function that returns a message visible after selecting the reason (may contain HTML).
     */
    private $message;
    /**
     * @var string|null Label of additional question (visible after selecting the reason in the form).
     */
    private $question;
    /**
     * @param string   $key      Key of the deactivation reason.
     * @param int      $priority Order priority (ascending).
     * @param string   $label    Label of the reason option.
     * @param callable $message  A function that returns a message visible after selecting the reason (may contain
     *                           HTML).
     * @param string   $question Label of additional question (visible after selecting the reason in the form).
     */
    public function __construct(string $key, int $priority, string $label, callable $message = null, string $question = null)
    {
        $this->key = $key;
        $this->priority = $priority;
        $this->label = $label;
        $this->message = $message;
        $this->question = $question;
    }
    public function get_key() : string
    {
        return $this->key;
    }
    public function get_priority() : int
    {
        return $this->priority;
    }
    /**
     * @param int $priority Order priority (ascending).
     */
    public function set_priority(int $priority) : self
    {
        $this->priority = $priority;
        return $this;
    }
    public function get_label() : string
    {
        return $this->label;
    }
    /**
     * @param string $label Label of the reason option.
     */
    public function set_label(string $label) : self
    {
        $this->label = $label;
        return $this;
    }
    /**
     * @return callable|null
     */
    public function get_message()
    {
        return $this->message;
    }
    /**
     * @param string|null $message Message visible after selecting the reason in the form (may contain HTML).
     */
    public function set_message(string $message = null) : self
    {
        $this->message = $message;
        return $this;
    }
    /**
     * @return string|null
     */
    public function get_question()
    {
        return $this->question;
    }
    /**
     * @param string|null $question Label of additional question (visible after selecting the reason in the form).
     */
    public function set_question(string $question = null) : self
    {
        $this->question = $question;
        return $this;
    }
}
