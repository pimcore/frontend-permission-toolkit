<?php

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

namespace FrontendPermissionToolkitBundle\CoreExtensions\Navigation;

use FrontendPermissionToolkitBundle\Service;
use Pimcore\Http\RequestHelper;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Document;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Builder extends \Pimcore\Navigation\Builder
{
    /**
     * @var Service
     */
    protected $service;

    /**
     * @var Concrete
     */
    protected $currentUser;

    public function __construct(RequestHelper $requestHelper, string $pageClass = null)
    {
        parent::__construct($requestHelper, $pageClass);
    }

    /**
     * @param Service $service
     */
    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @param TokenStorageInterface $securityTokenStorage
     */
    public function setCurrentUser(TokenStorageInterface $securityTokenStorage)
    {
        if ($securityToken = $securityTokenStorage->getToken()) {
            $user = $securityToken->getUser();
            if ($user instanceof Concrete) {
                $this->currentUser = $user;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getChildren(Document $parentDocument): array
    {
        $children = $parentDocument->getChildren();

        $allowedChildren = [];

        foreach ($children as $child) {
            $permissionResource = $child->getProperty('permission_resource');

            if (empty($permissionResource) || $this->currentUser && $this->service->isAllowed($this->currentUser, $child->getProperty('permission_resource'))) {
                $allowedChildren[] = $child;
            }
        }

        return $allowedChildren;
    }
}
