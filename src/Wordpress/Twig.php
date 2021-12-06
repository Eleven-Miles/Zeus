<?php

namespace NanoSoup\Zeus\Wordpress;

use HelloNico\Twig\DumpExtension;
use Timber\Timber;
use Twig\Environment;
use Twig\TwigFunction;
use NanoSoup\Zeus\ModuleConfig;

class Twig
{
    /**
     * Twig constructor.
     */
    public function __construct($moduleConfig)
    {
        $config = new ModuleConfig($moduleConfig);

        if ($config->getOption('disabled')) {
            return;
        }

        add_action('init', [$this, 'additionalTwigFileLoaderPaths']);
        add_filter('timber/twig', [$this, 'registerTwigFunctions']);
        add_filter('timber_context', [$this, 'addToContext']);
    }

    /**
     * Registers additional paths in Timber
     */
    public function additionalTwigFileLoaderPaths()
    {
        Timber::$locations = [
            get_template_directory() . '/public/dist/',
        ];
    }

    /**
     * @param Environment $twig
     * @return Environment
     */
    public function registerTwigFunctions(Environment $twig)
    {
        $twig->addFunction(new TwigFunction('make_relative_url', [$this, 'makeRelativeURL']));

        if (class_exists('DumpExtension')) {
            $twig->addExtension(new DumpExtension());
        }

        return $twig;
    }

    /**
     * This adds stuff to the global context - note this is run on EVERY page
     * so only put in what is really needed AND can't be done via the controller
     *
     * @param $data
     * @return mixed
     */
    public function addToContext($data)
    {
        return $data;
    }
}
