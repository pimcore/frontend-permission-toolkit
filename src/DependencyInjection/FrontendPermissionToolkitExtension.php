<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace FrontendPermissionToolkitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class FrontendPermissionToolkitExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');
        $loader->load('generic-data-index.yaml');

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['PimcoreStudioBackendBundle'])) {
            $loader->load('studio_backend.yaml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        if ($container->hasExtension('pimcore_studio_backend')) {
            $loader->load('pimcore/studio_backend.yaml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../src/Resources/config')
        );

        if ($container->hasExtension('pimcore_studio_ui')) {
            $loader->load('studio_ui.yaml');
        }
    }
}
