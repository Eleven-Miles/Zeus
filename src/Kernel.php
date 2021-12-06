<?php

namespace NanoSoup\Zeus;

use NanoSoup\Zeus\Wordpress\ACF;
use NanoSoup\Zeus\Wordpress\Dashboard;
use NanoSoup\Zeus\Wordpress\Duplicate;
use NanoSoup\Zeus\GravityForms\GravityForms;
use NanoSoup\Zeus\Wordpress\Manifest;
use NanoSoup\Zeus\Wordpress\OptimiseWP;
use NanoSoup\Zeus\Wordpress\TinyMCE;
use NanoSoup\Zeus\Wordpress\Twig;
use NanoSoup\Zeus\Wordpress\Yoast;

/**
 * Class Kernel
 */
class Kernel
{
    /**
     * An array of all the available override config values for the currently
     * register Zeus modules below
     *
     * @var array
     */
    private $moduleConfigs = [
        'gravityforms' => [
            'disabled' => false,
            'enableStyling' => true,
            'pushToGTM' => true,
            'populateCustomFields' => true,
        ],
        'acf' => [
            'disabled' => false,
            'hideAdmin' => true,
            'settingsPage' => true,
            'allowedBlocks' => true
        ],
        'dashboard' => [
            'disabled' => false,
            'disableWidgets' => true,
            'disableComments' => true,
            'removeAdminColumns' => true,
        ],
        'duplicate' => [
            'disabled' => false,
        ],
        'manifest' => [
            'disabled' => false,
        ],
        'optimsewp' => [
            'disabled' => false,
            'disableJquery' => true,
            'disableScripts' => true,
            'disableEmojis' => true,
            'disableHeadLinks' => true,
            'disableRestAPI' => true,
            'disableAdminBar' => true,
        ],
        'tinymce' => [
            'disabled' => false,
        ],
        'twig' => [
            'disabled' => false,
        ],
        'yoast' => [
            'disabled' => false,
            'disableAdminFilters' => true,
            'disableOptimisations' => true,
        ]
    ];
    /**
     * Kernel constructor.
     */
    public function __construct($moduleConfigs = [])
    {
        $this->setModuleConfigs($moduleConfigs);
        $this->registerClasses();
    }

    /**
     * @return array
     */
    private function registerClasses()
    {
        return [
            new GravityForms($this->getModuleConfig('gravityforms')),
            new ACF($this->getModuleConfig('acf')),
            new Dashboard($this->getModuleConfig('dashboard')),
            new Duplicate($this->getModuleConfig('duplicate')),
            new Manifest($this->getModuleConfig('manifest')),
            new OptimiseWP($this->getModuleConfig('optimisewp')),
            new TinyMCE($this->getModuleConfig('tinymce')),
            new Twig($this->getModuleConfig('twig')),
            new Yoast($this->getModuleConfig('yoast'))
        ];
    }

    /**
     * Returns an array of module specific configuration values, allowing for specific functionality to be
     * enabled/disabled.
     *
     * @param string $moduleName - The array key string to match against a given module config
     *
     * @return array
     */
    private function getModuleConfig(string $moduleName): array
    {
        if (in_array($moduleName, array_keys($this->moduleConfigs))) {
            return $this->moduleConfigs[$moduleName];
        }

        return [];
    }

    /**
     * Replaces the default configs array with any overrides passed in via the constructor
     *
     * @param array $moduleConfigs - An array of config override values
     *
     * @return void
     */
    private function setModuleConfigs(array $moduleConfigs): void
    {
        $mergedModuleConfigs = array_replace_recursive($this->moduleConfigs, $moduleConfigs);

        $this->moduleConfigs = $mergedModuleConfigs;
    }
}
