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
use Symfony\Component\Yaml\Yaml;

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
        // usage.* telemetry provider; the core extension point is guaranteed by the pimcore/pimcore constraint.
        // The walk recognises permission fields by type name, taken from this bundle's own data-type
        // registration so that nothing has to be kept in step by hand.
        $loader->load('telemetry.yaml');
        $container->setParameter(
            'frontend_permission_toolkit.permission_field_types',
            array_keys(
                Yaml::parseFile(__DIR__ . '/../Resources/config/pimcore/config.yml')['pimcore']['objects']['class_definitions']['data']['map']
            )
        );

        $loader->load('studio_backend.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('pimcore/studio_backend.yaml');
        $loader->load('studio_ui.yaml');
    }
}
