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

namespace FrontendPermissionToolkitBundle\Tests\Unit\Telemetry;

use Codeception\Test\Unit;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\DynamicPermissionResource;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionManyToManyRelation;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionResource;
use FrontendPermissionToolkitBundle\Telemetry\ClassDefinitionPermissionFields;
use FrontendPermissionToolkitBundle\Telemetry\FieldTypeScanner;
use Pimcore\Model\DataObject\ClassDefinition\Data\Block;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;
use Pimcore\Model\Exception\NotFoundException;

/**
 * The recursion over a field-definition tree, built from real definition objects with enrichment
 * suppressed - which is why this runs without a kernel. Three answers: found, not found, or unknown when a
 * container could not be read and nothing was found elsewhere.
 */
class FieldTypeScannerTest extends Unit
{
    private const TYPES = ClassDefinitionPermissionFields::FIELD_TYPES;

    public function testFindsAPermissionFieldAtTheTopLevel(): void
    {
        $definitions = [$this->input('title'), $this->permissionResource()];

        $this->assertTrue($this->scanner()->containsAnyType($definitions, self::TYPES));
    }

    /**
     * Any of the toolkit's types is a hit, not only the first one on the list.
     */
    public function testAnyOfTheToolkitTypesIsAHit(): void
    {
        $relation = new PermissionManyToManyRelation();
        $relation->setName('groups');
        $dynamic = new DynamicPermissionResource();
        $dynamic->setName('resources');

        $this->assertTrue($this->scanner()->containsAnyType([$relation], self::TYPES));
        $this->assertTrue($this->scanner()->containsAnyType([$dynamic], self::TYPES));
    }

    /**
     * A permission field inside localized fields inside a block: the walk must descend through every container.
     */
    public function testFindsAPermissionFieldNestedInContainers(): void
    {
        $localized = new Localizedfields();
        $localized->setName('localizedfields');
        $localized->setChildren([$this->input('title'), $this->permissionResource()]);
        $block = new Block();
        $block->setName('content');
        $block->setChildren([$localized]);

        $this->assertTrue($this->scanner()->containsAnyType([$this->input('sku'), $block], self::TYPES));
    }

    public function testAModelWithoutAPermissionFieldIsNotAHit(): void
    {
        $localized = new Localizedfields();
        $localized->setName('localizedfields');
        $localized->setChildren([$this->input('title')]);

        $this->assertFalse($this->scanner()->containsAnyType([$this->input('sku'), $localized], self::TYPES));
    }

    public function testAnEmptyModelIsNotAHit(): void
    {
        $this->assertFalse($this->scanner()->containsAnyType([], self::TYPES));
    }

    /**
     * Exact type match: a similarly named type must never be mistaken for one of the toolkit's.
     */
    public function testTheTypesAreMatchedExactly(): void
    {
        $this->assertFalse($this->scanner()->containsAnyType([$this->permissionResource()], ['permissionResources']));
        $this->assertFalse($this->scanner()->containsAnyType([$this->input('title')], self::TYPES));
    }

    /**
     * A failing container never hides its siblings: the permission field after it is still found.
     */
    public function testAPermissionFieldAfterAnUnreadableSiblingContainerIsStillFound(): void
    {
        $definitions = [$this->unreadableContainer(), $this->input('sku'), $this->permissionResource()];

        $this->assertTrue($this->scanner()->containsAnyType($definitions, self::TYPES));
    }

    /**
     * Nothing found and a container unreadable: unknown, not "no permission field".
     */
    public function testAnUnreadableContainerWithNoMatchElsewhereIsUnknown(): void
    {
        $definitions = [$this->input('sku'), $this->unreadableContainer(), $this->input('title')];

        $this->assertNull($this->scanner()->containsAnyType($definitions, self::TYPES));
    }

    /**
     * An entry that is not a field definition at all is a corrupted tree, not an empty one: unknown unless a
     * readable sibling holds a permission field.
     */
    public function testACorruptedEntryIsUnknownUnlessASiblingHoldsAPermissionField(): void
    {
        $scanner = $this->scanner();

        $this->assertNull($scanner->containsAnyType([$this->input('sku'), 'not a definition'], self::TYPES));
        $this->assertTrue($scanner->containsAnyType(['not a definition', $this->permissionResource()], self::TYPES));
    }

    private function unreadableContainer(): Localizedfields
    {
        $container = $this->createMock(Localizedfields::class);
        $container->method('getFieldType')->willReturn('localizedfields');
        $container->method('getFieldDefinitions')
            ->willThrowException(new NotFoundException('nested definition missing'));

        return $container;
    }

    private function scanner(): FieldTypeScanner
    {
        return new FieldTypeScanner();
    }

    private function permissionResource(): PermissionResource
    {
        $field = new PermissionResource();
        $field->setName('permissions');

        return $field;
    }

    private function input(string $name): Input
    {
        $input = new Input();
        $input->setName($name);

        return $input;
    }
}
