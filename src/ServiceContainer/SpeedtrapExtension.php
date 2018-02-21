<?php
declare(strict_types=1);

namespace Brunty\Behat\SpeedtrapExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SpeedtrapExtension implements Extension
{
    const CONFIG_KEY = 'speedtraplogger';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey(): string
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
        /** @noinspection NullPointerExceptionInspection */
        $builder
            ->children()
                ->scalarNode('scenario_threshold')
                    ->defaultValue(2000)
                ->end()
                ->scalarNode('step_threshold')
                    ->defaultValue(0)
                ->end()
                ->scalarNode('report_length')
                    ->defaultValue(10)
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
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
     * @throws \InvalidArgumentException
     */
    private function getOutputTypeValidator(): callable
    {
        return function ($value) {
            $allowed = ['console', 'csv'];
            $invalid = array_diff($value, $allowed);

            if ( ! empty($invalid)) {
                $message = 'Invalid output types: %s. Allowed types: %s';
                throw new \InvalidArgumentException(sprintf($message, implode(',', $invalid), implode(',', $allowed)));
            }

            return $value;
        };
    }
}
