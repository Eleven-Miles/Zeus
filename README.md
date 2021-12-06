# Zeus

Contains any useful components

To use this within your application, create your own Kernel class extending the one in this package, then
in your `_construct` function add `parent::__construct();` this will then register all the base classes found
in Zeus.

The Zeus Kernel class constructor accepts an array of configuration options for the various modules that the base class sets up and manages, called in the `registerClasses` method. This allows for individual module configuration, as well as a top level option to disable all custom actions/filters called within each module.

The purpose of this additional level of configuration is to allow project specific customisation and reduce conflicts where existing methods may override new actions/filters when called in a custom theme.

Below is the full list of available configuration options (available within the `Kernel` class file ):

```PHP
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
```

To use a customised configuration in your theme's extended Kernel, implement as below:

```PHP
use NanoSoup\Zeus\Kernel as KernelBase;

class Kernel extends KernelBase
{
    public function __construct()
    {
        $kernelModuleConfig = [
            'acf' => [
                'addSettingsPage' => false,
                // Add as many of the configuration options above as required
                // ensuring you place options under the correct array key module name
            ]
        ];
        parent::__construct($kernelModuleConfig);

        $this->registerClasses();
    }
}
```
