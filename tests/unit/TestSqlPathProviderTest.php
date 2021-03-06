<?php
/**
 * This file is part of OXID eSales Testing Library.
 *
 * OXID eSales Testing Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Testing Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Testing Library. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 */

use OxidEsales\EshopCommunity\Core\Edition\EditionSelector;
use OxidEsales\TestingLibrary\TestSqlPathProvider;

class TestSqlPathProviderTest extends PHPUnit_Framework_TestCase
{
    public function providerChecksForCorrectPath()
    {
        return [
            [
                '/var/www/oxideshop/tests/Acceptance/Admin',
                EditionSelector::ENTERPRISE,
                '/var/www/oxideshop/source/Edition/Enterprise/Tests/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/oxideshop/source/Edition/Enterprise/Tests/Acceptance/Admin',
                EditionSelector::ENTERPRISE,
                '/var/www/oxideshop/source/Edition/Enterprise/Tests/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/oxideshop/tests/Acceptance/Admin',
                EditionSelector::COMMUNITY,
                '/var/www/oxideshop/tests/Acceptance/Admin/testSql'
            ],
            [
                '/var/www/oxideshop/tests/Acceptance/Admin',
                EditionSelector::PROFESSIONAL,
                '/var/www/oxideshop/tests/Acceptance/Admin/testSql'
            ],
        ];
    }

    /**
     * @param string $testSuitePath
     * @param string $edition
     * @param string $resultPath
     *
     * @dataProvider providerChecksForCorrectPath
     */
    public function testChecksForCorrectPath($testSuitePath, $edition, $resultPath)
    {
        $shopPath = '/var/www/oxideshop/source';
        $editionSelector = new EditionSelector($edition);
        $testDataPathProvider = new TestSqlPathProvider($editionSelector, $shopPath);

        $this->assertSame($resultPath, $testDataPathProvider->getDataPathBySuitePath($testSuitePath));
    }
}
