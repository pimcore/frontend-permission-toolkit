# FrontendPermissionToolkit 

Adds some helpers to define permissions for users in websites based on pimcore objects.
So user permissions for complex systems can be defined directly in pimcore objects.  

A scenario to setup a role based permission system: 
- user represented as objects with a number of permission rights (= Permission Resources)
- each user has relations to user groups (also pimcore objects) with also a number of permission rights (= Permission Resources)

![sample](readme/img/sample.png)

### Development Instance
> http://objecttools.plugins.elements.pm/admin


### functionalities
- additional datatypes for pimcore objects
  - Permission Resource:
     - represents one specific user right (e.g. login) 
     - can have values ```allow``` ```deny``` ```inherit``` 
  - Permission Objects: Wrapper for default datatype objects for recursive permission calculation. 
  - Permission Href: Wrapper for default datatype href for recursive permission calculation.

- service for checking user rights based on a pimcore object and a permission resource as service class ```FrontendPermissionToolkit_Service``` with two methods:
  - ```FrontendPermissionToolkit_Service::getPermissions```: 
     - returns an array of all permissions for the given object, automatically merges all permission resources of objects related to the given object with 'Permission Objects' or 'Permission Href'.
     - merging: When permission is set to allow / deny directly in object, this is always used. Otherwise optimistic merging is used -> once one permission is allowed, it stays that way.
  - ```FrontendPermissionToolkit_Service::isAllowed```: checks if given object is allowed for given resource


### used by projects for example
- Eberspaecher (http://eberspaecher.dev.elements.pm)


### integration with pimcore navigation
1) add following class to your website: 
```php
<?php

include_once("Pimcore/View/Helper/PimcoreNavigation.php");

class Website_View_Helper_Navigation_Controller extends \Pimcore\View\Helper\PimcoreNavigationController
{
    /**
     * @var Object_Portaluser
     */
    protected $currentUser = null;

    public function __construct($currentUser) {
        $this->currentUser = $currentUser;
    }

    protected function getChilds($parentDocument) {
        $children = $parentDocument->getChilds();

        $allowedChildren = array();

        foreach($children as $child) {
            $permissionResource = $child->getProperty("permission_resource");

            if(empty($permissionResource) || FrontendPermissionToolkit_Service::isAllowed($this->currentUser, $child->getProperty("permission_resource"))) {
                $allowedChildren[] = $child;
            }
        }

        return $allowedChildren;
    }

}
?>
```

2) Add following line to your website controller in init-method after call of parent::init()
```php
<?php
Pimcore_View_Helper_PimcoreNavigation::$_controller = new Website_View_Helper_Navigation_Controller($user);
```

.