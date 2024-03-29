<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
