<?php

namespace Bluora\LaravelModelDynamicFilter;

abstract class SettingsAbstract
{
    protected $settings = [];

    /**
     * Set variables by array.
     *
     * @param array $options
     *
     * @return App\Settings\SettingsAbstract
     */
    public function setArray($options)
    {
        foreach ($options as $name => $value) {
            $this->set($name, $value);
        }

        return $this;
    }

    /**
     * Set variables by array.
     *
     * @param array $options
     *
     * @return App\Settings\SettingsAbstract
     */
    public function set($name, $value)
    {
        array_set($this->settings, $name, $value);

        return $this;
    }

    /**
     * Get variables by array.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function get($name, $default_value = null)
    {
        return array_get($this->settings, $name, $default_value);
    }

    /**
     * Has value.
     *
     * @param array $options
     *
     * @return bool
     */
    public function has($name)
    {
        return array_has($this->settings, $name);
    }
}
