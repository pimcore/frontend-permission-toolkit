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

namespace FrontendPermissionToolkitBundle\Tests\Unit\Telemetry;

use Codeception\Test\Unit;
use FrontendPermissionToolkitBundle\Telemetry\FrontendPermissionToolkitUsageProvider;
use FrontendPermissionToolkitBundle\Telemetry\PermissionFieldsInterface;

class FrontendPermissionToolkitUsageProviderTest extends Unit
{
    public function testReportsUnderTheExpectedKey(): void
    {
        $this->assertSame('frontend_permission_toolkit', $this->provider(true)->getBundleKey());
    }

    public function testAConfiguredPermissionFieldIsUsed(): void
    {
        $this->assertTrue($this->provider(true)->isUsed());
    }

    /**
     * The bundle ships no permission field, so a model without one is a real "installed and not set up".
     */
    public function testNoPermissionFieldIsNotUsedRatherThanUnknown(): void
    {
        $this->assertFalse($this->provider(false)->isUsed());
    }

    /**
     * A data model that could not be read means unknown; `false` there would invent an adoption gap.
     */
    public function testAnUnreadableDataModelIsPassedThroughAsUnknown(): void
    {
        $this->assertNull($this->provider(null)->isUsed());
    }

    private function provider(?bool $exist): FrontendPermissionToolkitUsageProvider
    {
        $fields = $this->createMock(PermissionFieldsInterface::class);
        $fields->method('exist')->willReturn($exist);

        return new FrontendPermissionToolkitUsageProvider($fields);
    }
}
