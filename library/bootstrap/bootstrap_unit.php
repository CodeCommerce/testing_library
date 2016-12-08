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
 * @copyright (C) OXID eSales AG 2003-2014
 */

class UnitBootstrap extends Bootstrap
{
    /** @var int Whether to add demo data. */
    protected $addDemoData = 0;

    /**
     * Initiates shop before testing.
     */
    public function init()
    {
        parent::init();

        $config = $this->getTestConfig();

        if ($config->shouldUseDatabaseClone()) {
            $this->registerDbCloneSetup($config->shouldDeleteDatabaseCloneAfterTestsSuite());
            // TODO: how to make sure that the tests actually use the clone?
        }

        $dbRestoreClass = $config->getDatabaseRestorationClass();
        if (file_exists(TEST_LIBRARY_PATH .'dbRestore/'.$dbRestoreClass . ".php")) {
            $restoreDbPath = TEST_LIBRARY_PATH .'dbRestore/'. $dbRestoreClass . ".php";
        } else {
            $restoreDbPath = TEST_LIBRARY_PATH .'dbRestore/dbRestore.php';
        }
        include_once $restoreDbPath;

        $currentTestSuite = $config->getCurrentTestSuite();
        if (file_exists($currentTestSuite .'/additional.inc.php')) {
            include_once $currentTestSuite .'/additional.inc.php';
        }

        require_once TEST_LIBRARY_PATH .'/oxUnitTestCase.php';
    }

    /**
     * Clones the original database into a new database by creating, a dump, creating a new database, but
     * dropping an existing clone at the beginning
     *
     * @param boolean   $deleteDbCloneAfterSuite
     */
    protected function registerDbCloneSetup($deleteDbCloneAfterSuite)
    {
        $serviceCaller = new oxServiceCaller();
        $serviceCaller->setParameter('cloneDB', true);
        $serviceCaller->setParameter('dump-prefix', 'orig_db_dump');
        $serviceCaller->callService('DBCloneService', 1);

        if ($deleteDbCloneAfterSuite) {
            register_shutdown_function(function () {
                $serviceCaller = new oxServiceCaller();
                $serviceCaller->setParameter('dropDBClone', true);
                $serviceCaller->setParameter('dump-prefix', 'orig_db_dump');
                $serviceCaller->callService('DBCloneService', 1);
            });
        }
    }
}

/**
 * @deprecated Use oxTestConfig::getCurrentTestSuite() or oxTestConfig::getTempDirectory().
 *
 * @return string
 */
function getTestsBasePath()
{
    $testsPath = '';
    if (defined('CURRENT_TEST_SUITE')) {
        $testsPath = CURRENT_TEST_SUITE;
    }
    return $testsPath;
}

/**
 * Returns framework base path.
 * Overwrites original method so that it would be possible to mock shop directory during testing.
 *
 * @return string
 */
function getShopBasePath()
{
    $shopDirectory = OX_BASE_PATH;
    if (class_exists('oxConfig', false)) {
        $config = oxRegistry::getConfig();
        if (!empty($config->sConfigKey)) {
            $configShopDir = $config->getConfigParam('sShopDir');
            $shopDirectory = $configShopDir ? $configShopDir : $shopDirectory;
        }
    }

    return rtrim($shopDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
}

