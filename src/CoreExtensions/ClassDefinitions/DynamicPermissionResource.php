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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions;

use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Helper\DataProviderResolver;
use FrontendPermissionToolkitBundle\Service;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\Extension\ColumnType;
use Pimcore\Model\DataObject\ClassDefinition\Data\Extension\QueryColumnType;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;
use Pimcore\Tool\Serialize;

class DynamicPermissionResource extends Data implements Data\ResourcePersistenceAwareInterface, Data\QueryResourcePersistenceAwareInterface
{
    use DataObject\Traits\SimpleComparisonTrait;
    use ColumnType;
    use QueryColumnType;

    /**
     * Static type of this element
     *
     * @var string
     */
    public $fieldtype = 'dynamicPermissionResource';

    /**
     * Type for the column to query
     *
     * @var string
     */
    protected $queryColumnType = 'longtext';

    /**
     * Type for the column
     *
     * @var string
     */
    protected $columnType = 'longtext';

    /**
     * Type for the generated phpdoc
     *
     * @var string
     */
    public $phpdocType = 'array';

    /**
     * @var string
     */
    public $dataProvider = '';

    /**
     * @var array
     */
    public $permissionResources = [];

    /**
     * @var array
     */
    public $permissionOptions = [];

    /**
     * @return string
     */
    public function getDataProvider(): string
    {
        return $this->dataProvider;
    }

    /**
     * @param string $dataProvider
     */
    public function setDataProvider(string $dataProvider): void
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return array
     */
    public function getPermissionResources(): array
    {
        return $this->permissionResources ?: [];
    }

    /**
     * @param array $permissionResources
     */
    public function setPermissionResources($permissionResources): void
    {
        $this->permissionResources = $permissionResources;
    }

    /**
     * @return array
     */
    public function getPermissionOptions(): array
    {
        return $this->permissionOptions ?: [];
    }

    /**
     * @param array $permissionOptions
     */
    public function setPermissionOptions($permissionOptions): void
    {
        $this->permissionOptions = $permissionOptions;
    }

    protected function cleanupAndCheckForEmpty($data)
    {
        $resources = $this->loadPermissionResourcesFromProvider();

        $data = $data ?? [];

        $cleanData = [];

        foreach ($resources as $resource) {
            $originalValue = $data[$resource['value']] ?? null;
            $cleanData[$resource['value']] = $originalValue ?: Service::INHERIT;
        }

        return $cleanData;
    }

    /**
     * Returns the data for the editmode
     *
     * @param mixed $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return mixed
     */
    public function getDataForEditmode($data, $object = null, $params = [])
    {
        return $data;
    }

    /**
     * Converts data from editmode to internal eg. Image-Id to Asset\Image object
     *
     * @param mixed $data
     * @param null|DataObject\AbstractObject $object
     * @param mixed $params
     *
     * @return mixed
     */
    public function getDataFromEditmode($data, $object = null, $params = [])
    {
        return $data;
    }

    /**
     * Returns the data which should be stored in the query columns
     *
     * @param mixed $data
     * @param null|Concrete $object
     * @param mixed $params
     *
     * @return mixed
     *
     * abstract public function getDataForQueryResource($data);
     */
    public function getDataForQueryResource($data, $object = null, $params = [])
    {
        $data = $this->cleanupAndCheckForEmpty($data);
        if (is_array($data)) {
            return http_build_query($data, '', ';') . ';';
        }

        return '';
    }

    /**
     * Returns the the data that should be stored in the resource
     *
     * @param mixed $data
     * @param null|Concrete $object
     * @param mixed $params
     *
     * @return mixed
     */
    public function getDataForResource($data, $object = null, $params = [])
    {
        return Serialize::serialize($this->cleanupAndCheckForEmpty($data));
    }

    /**
     * Convert the saved data in the resource to the internal eg. Image-Id to Asset\Image object, this is the inverted getDataForResource()
     *
     * @param mixed $data
     * @param null|Concrete $object
     * @param mixed $params
     *
     * @return mixed
     */
    public function getDataFromResource($data, $object = null, $params = [])
    {
        return $this->cleanupAndCheckForEmpty(Serialize::unserialize($data));
    }

    /**
     * Checks if data is valid for current data field
     *
     * @param mixed $data
     * @param bool $omitMandatoryCheck
     *
     * @throws \Exception
     */
    public function checkValidity($data, $omitMandatoryCheck = false, $params = [])
    {
        if (!$omitMandatoryCheck && $this->getMandatory() && empty($data)) {
            throw new ValidationException('Empty mandatory field [ '.$this->getName().' ]');
        }

        if (!empty($data) && !is_array($data)) {
            throw new ValidationException('Invalid table data');
        }
    }

    /**
     * converts object data to a simple string value or CSV Export
     *
     * @abstract
     *
     * @param DataObject\Concrete $object
     * @param array $params
     *
     * @return string
     */
    public function getForCsvExport($object, $params = [])
    {
        $data = $this->getDataFromObjectParam($object, $params);
        if (is_array($data)) {
            return base64_encode(Serialize::serialize($data));
        }

        return '';
    }

    /**
     * @param string $importValue
     * @param null|DataObject\Concrete $object
     * @param array $params
     *
     * @return array|null
     */
    public function getFromCsvImport($importValue, $object = null, $params = [])
    {
        $value = Serialize::unserialize(base64_decode($importValue));
        if (is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * @param array|null $oldValue
     * @param array|null $newValue
     *
     * @return bool
     */
    public function isEqual($oldValue, $newValue): bool
    {
        return $this->isEqualArray($oldValue, $newValue);
    }

    /** Generates a pretty version preview (similar to getVersionPreview) can be either html or
     * a image URL. See the https://github.com/pimcore/object-merger bundle documentation for details
     *
     * @param array|null $data
     * @param DataObject\Concrete|null $object
     * @param mixed $params
     *
     * @return array|string
     */
    public function getDiffVersionPreview($data, $object = null, $params = [])
    {
        if ($data) {
            $html = '<table>';

            foreach ($data as $key => $permission) {
                $html .= '<tr>';
                $html .= '<td>' . htmlentities($key) . '</td>';
                $html .= '<td>' . htmlentities($permission) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';

            $value = [];
            $value['html'] = $html;
            $value['type'] = 'html';

            return $value;
        } else {
            return '';
        }
    }

    /**
     * @see Data::getVersionPreview
     *
     * @param string $data
     * @param null|DataObject\Concrete $object
     * @param mixed $params
     *
     * @return string
     */
    public function getVersionPreview($data, $object = null, $params = [])
    {
        $versionPreview = $this->getDiffVersionPreview($data, $object, $params);
        if (is_array($versionPreview) && $versionPreview['html']) {
            return $versionPreview['html'];
        }

        return '';
    }

    protected function loadPermissionResourcesFromProvider(): array
    {
        $dataProvider = DataProviderResolver::resolveDataProvider($this->getDataProvider());

        $permissionResources = [];
        if ($dataProvider) {
            $context = [
                'fieldname' => $this->getName()
            ];
            $permissionResources = $dataProvider->getPermissionResources($context, $this);
        }

        return $permissionResources;
    }

    public function enrichFieldDefinition($context = [])
    {
        $this->setPermissionResources($this->loadPermissionResourcesFromProvider());

        $this->setPermissionOptions([
            ['key' => Service::INHERIT, 'value' => Service::INHERIT],
            ['key' => Service::ALLOW, 'value' => Service::ALLOW],
            ['key' => Service::DENY, 'value' => Service::DENY]
        ]);

        return $this;
    }

    public function enrichLayoutDefinition($object, $context = [])
    {
        $this->enrichFieldDefinition($context);
    }

    public function getParameterTypeDeclaration(): ?string
    {
        return '?array';
    }

    public function getReturnTypeDeclaration(): ?string
    {
        return '?array';
    }

    public function getPhpdocInputType(): ?string
    {
        return 'null|array';
    }

    public function getPhpdocReturnType(): ?string
    {
        return 'null|array';
    }
}
