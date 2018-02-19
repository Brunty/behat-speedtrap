<?php

namespace Brunty\Behat\SpeedtrapExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SpeedtrapExtension implements Extension
{
    const CONFIG_KEY = 'speedtraplogger';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return self::CONFIG_KEY;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // nothing to do here
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        // nothing to do here
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('threshold')
                    ->defaultValue(2000)
                ->end()
                ->scalarNode('report_length')
                    ->defaultValue(10)
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.xml');

        $extensionConfig = new Config($container, $config);
        $container->set('brunty.speedtrap_extension.config', $extensionConfig);
    }

    /**
     * @return \Closure
     */
    private function getOutputTypeValidator()
    {
        return function ($value) {
            $allowed = ['console', 'csv'];
            $invalid = array_diff($value, $allowed);

            if ( ! empty($invalid)) {
                $message = 'Invalid output types: %s. Allowed types: %s';
                throw new \InvalidArgumentException(sprintf($message, join(',', $invalid), join(',', $allowed)));
            }

            return $value;
        };
    }
}
