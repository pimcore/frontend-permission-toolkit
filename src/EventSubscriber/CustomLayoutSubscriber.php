<?php
declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace FrontendPermissionToolkitBundle\EventSubscriber;

use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\DynamicPermissionResource;
use Pimcore\Event\DataObjectCustomLayoutEvents;
use Pimcore\Event\Model\DataObject\CustomLayoutEvent;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
final class CustomLayoutSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectCustomLayoutEvents::PRE_ADD => 'onUpdate',
            DataObjectCustomLayoutEvents::PRE_UPDATE => 'onUpdate',
        ];
    }

    public function onUpdate(CustomLayoutEvent $event): void
    {
        $customLayout = $event->getCustomLayout();
        $this->resetPermissionResources($customLayout->getLayoutDefinitions());
    }

    private function resetPermissionResources(ClassDefinition\Data | ClassDefinition\Layout | null $layout): void
    {
        if ($layout === null) {
            return;
        }

        if ($layout instanceof DynamicPermissionResource) {
            $layout->setPermissionResources([]);
        }
        if (method_exists($layout, 'getChildren')) {
            foreach ($layout->getChildren() ?? [] as $child) {
                $this->resetPermissionResources($child);
            }
        }
    }
}
