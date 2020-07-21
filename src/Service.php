<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace FrontendPermissionToolkitBundle;

use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionManyToMany;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionManyToOneRelation;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionResource;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks;

class Service {

    const DENY = "deny";
    const ALLOW = "allow";
    const INHERIT = "inherit";

    private $permissionCache = array();

    /**
     * returns array of all permission resources of given object
     * automatically merges all permission resources of objects related to the given object with 'Permission Objects' or 'Permission Href'
     *
     * merging:
     * - when permission is set to allow / deny directly in object, this is always used
     * - otherwise optimistic merging is used -> once one permission is allowed, it stays that way
     *
     *
     * @param Concrete $object
     * @return array
     */
    public function getPermissions(Concrete $object): array {

        if(isset($this->permissionCache[$object->getId()])) {
            return $this->permissionCache[$object->getId()];
        }


        $permissions = [];

        $class = $object->getClass();
        $fieldDefinitions = $class->getFieldDefinitions();

        $permissionObjects = [];

        // get permission resources directly in given object
        foreach($fieldDefinitions as $fd) {
            if($fd instanceof PermissionManyToManyRelation) {
                $permissionObjects = array_merge($permissionObjects, $object->{'get' . $fd->getName()}());
            }
            if($fd instanceof PermissionManyToOneRelation) {
                $href = $object->{'get' . $fd->getName()}();
                if($href) {
                    $permissionObjects[] = $href;
                }
            }

            if($fd instanceof PermissionResource) {
                $permissions[$fd->getName()] = $object->{'get' . $fd->getName()}();
            }

            if($fd instanceof Objectbricks) {
                $bricks = $object->{'get' . $fd->getName()}();
                foreach($bricks->getBrickGetters() as $getter) {
                    $brick = $bricks->$getter();

                    if($brick) {
                        $brickFieldDefinitions = $brick->getDefinition()->getFieldDefinitions();
                        foreach($brickFieldDefinitions as $bfd) {
                            if($bfd instanceof PermissionManyToManyRelation) {
                                $permissionObjects = array_merge($permissionObjects, $brick->{'get' . $bfd->getName()}());
                            }
                            if($bfd instanceof PermissionManyToOneRelation) {
                                $href = $object->{'get' . $bfd->getName()}();
                                if($href) {
                                    $permissionObjects[] = $href;
                                }
                            }
                            if($bfd instanceof PermissionResource) {
                                $permissions[$bfd->getName()] = $brick->{'get' . $bfd->getName()}();
                            }
                        }
                    }
                }
            }
        }

        // get permission resources from linked objects and merge them with permissions of given object
        // - when permission is set to allow / deny directly in object, this is always used
        // - otherwise optimistic merging is used -> once one permission is allowed, it stays that way
        $mergedPermissions = $permissions;
        foreach($permissionObjects as $permissionObject) {
            $objectPermissions = self::getPermissions($permissionObject);

            foreach($objectPermissions as $key => $value) {
                if(($permissions[$key] == self::INHERIT || !array_key_exists($key, $permissions)) && $mergedPermissions[$key] != self::ALLOW) {
                    $mergedPermissions[$key] = $value;
                }
            }
        }

        $this->permissionCache[$object->getId()] = $mergedPermissions;
        return $mergedPermissions;
    }

    /**
     * checks if given object is allowed for given resource
     *
     * @param Concrete $object
     * @param string $resource
     * @return bool
     */
    public function isAllowed(Concrete $object, $resource): bool {
        $permissions = $this->getPermissions($object);
        return $permissions[$resource] == self::ALLOW;
    }

}
