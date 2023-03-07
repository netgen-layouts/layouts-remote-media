<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;
use InvalidArgumentException;

use function file_get_contents;

final class NetgenLayoutsRemoteMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new DelegatingLoader(
            new LoaderResolver(
                [
                    new GlobFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                ],
            ),
        );

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['provider'])) {
            throw new InvalidArgumentException('The "provider" option must be set');
        }

        $container->setParameter(
            'netgen_layouts.remote_media.cache.adapter_service_name',
            $config['cache']['adapter'],
        );

        $container->setParameter(
            'netgen_layouts.remote_media.cache.provider',
            $config['cache']['provider'],
        );

        $loader->load('default_settings.yaml');
        $loader->load('services/**/*.yaml', 'glob');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $prependConfigs = [
            'item_types.yaml' => 'netgen_content_browser',
            'block_definitions.yaml' => 'netgen_layouts',
            'value_types.yaml' => 'netgen_layouts',
            'netgen_layouts.yaml' => 'netgen_layouts',
            'view/block_view.yaml' => 'netgen_layouts',
            'view/item_view.yaml' => 'netgen_layouts',
            'image.yaml' => 'netgen_remote_media',
        ];

        foreach ($prependConfigs as $configFile => $prependConfig) {
            $configFile = __DIR__ . '/../Resources/config/' . $configFile;
            $config = Yaml::parse((string) file_get_contents($configFile));
            $container->prependExtensionConfig($prependConfig, $config);
            $container->addResource(new FileResource($configFile));
        }
    }
}
