<?php

class FrontendPermissionToolkit_Service {

    const DENY = "deny";
    const ALLOW = "allow";
    const INHERIT = "inherit";

    private static $permissionCache = array();

    /**
     * returns array of all permission resources of given object
     * automatically merges all permission resources of objects related to the given object with 'Permission Objects' or 'Permission Href'
     *
     * merging:
     * - when permission is set to allow / deny directly in object, this is always used
     * - otherwise optimistic merging is used -> once one permission is allowed, it stays that way
     *
     *
     * @param Object_Concrete $object
     * @return array
     */
    public static function getPermissions(Object_Concrete $object) {

        if(self::$permissionCache[$object->getId()]) {
            return self::$permissionCache[$object->getId()];
        }


        $permissions = [];

        $class = $object->getClass();
        $fieldDefinitions = $class->getFieldDefinitions();

        $permissionObjects = [];

        // get permission resources directly in given object
        foreach($fieldDefinitions as $fd) {
            if($fd instanceof Object_Class_Data_PermissionObjects) {
                $permissionObjects = array_merge($permissionObjects, $object->{'get' . $fd->getName()}());
            }
            if($fd instanceof Object_Class_Data_PermissionHref) {
                $href = $object->{'get' . $fd->getName()}();
                if($href) {
                    $permissionObjects[] = $href;
                }
            }

            if($fd instanceof Object_Class_Data_PermissionResource) {
                $permissions[$fd->getName()] = $object->{'get' . $fd->getName()}();
            }

            if($fd instanceof Object_Class_Data_Objectbricks) {
                $bricks = $object->{'get' . $fd->getName()}();
                foreach($bricks->getBrickGetters() as $getter) {
                    $brick = $bricks->$getter();

                    if($brick) {
                        $brickFieldDefinitions = $brick->getDefinition()->getFieldDefinitions();
                        foreach($brickFieldDefinitions as $bfd) {
                            if($bfd instanceof Object_Class_Data_PermissionObjects) {
                                $permissionObjects = array_merge($permissionObjects, $brick->{'get' . $bfd->getName()}());
                            }
                            if($bfd instanceof Object_Class_Data_PermissionHref) {
                                $href = $object->{'get' . $bfd->getName()}();
                                if($href) {
                                    $permissionObjects[] = $href;
                                }
                            }
                            if($bfd instanceof Object_Class_Data_PermissionResource) {
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

        self::$permissionCache[$object->getId()] = $mergedPermissions;
        return $mergedPermissions;
    }

    /**
     * checks if given object is allowed for given resource
     *
     * @param $object
     * @param $resource
     * @return bool
     */
    public static function isAllowed($object, $resource) {
        $permissions = self::getPermissions($object);
        return $permissions[$resource] == self::ALLOW;
    }

}