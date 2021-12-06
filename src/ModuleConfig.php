<?php

namespace NanoSoup\Zeus;

/**
 * Class ModuleConfig
 */
class ModuleConfig
{
    /**
     * The constructed config array options
     *
     * @var array
     */
    private $config = [];

    /**
     * @param array $moduleConfig - An array of configuration options
     */
    public function __construct(array $moduleConfig)
    {
        $this->config = $moduleConfig;
    }

    /**
     * Returns an option value if it exists else returns null
     *
     * @param string $optionName - The name of the config option to check within the config array
     *
     * @return mixed
     */
    public function getOption(string $optionName)
    {
        if (in_array($optionName, array_keys($this->config))) {
            return $this->config[$optionName];
        }

        return null;
    }
}
