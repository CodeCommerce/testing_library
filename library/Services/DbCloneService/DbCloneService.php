<?php

include_once LIBRARY_PATH . '/DbHandler.php';

class DbCloneService implements ShopServiceInterface
{
    /**
     * @var DbHandler
     */
    private $dbHandler;

    /** @var ServiceConfig */
    private $serviceConfig;

    /** @var oxConfigFile */
    private $shopConfig;

    /**
     * Initiates service requirements.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $this->serviceConfig = $config;
        $shopPath = $config->getShopDirectory();
        include_once $shopPath . "core/oxconfigfile.php";
        $this->shopConfig = new oxConfigFile($shopPath . "config.inc.php");
        $this->dbHandler = new DbHandler($this->shopConfig);
        $this->dbHandler->setTemporaryFolder($this->serviceConfig->getTempDirectory());
    }

    /**
     * Initiates service.
     *
     * @param Request $request
     *
     * @return null
     */
    public function init($request)
    {
        if ($request->getParameter('createClone')) {
            $dumpPrefix = $request->getParameter('dump-prefix');
            $origDbName = $request->getParameter('originalDbName');
            $dbCloneName = $request->getParameter('dbCloneName');

            $this->dbHandler->setDbCloneName($dbCloneName);

            $this->dropDatabaseCloneIfExists($dbCloneName);
            $this->dbHandler->createDatabase($dbCloneName);

            $_ENV['DBCLONENAME'] = $dbCloneName;
            $this->shopConfig = new oxConfigFile($this->serviceConfig->getShopDirectory() . "config.inc.php");
            oxRegistry::set('oxConfigFile', $this->shopConfig);

            if ($request->getParameter('importOriginalData')) {
                $this->dbHandler->dumpDB($dumpPrefix);
                $this->dbHandler->import($this->dbHandler->getTemporaryFolder() . $dumpPrefix . '_' . $origDbName);
            }
        }

        if ($request->getParameter('dropCloneAfterTestSuite')) {
            register_shutdown_function(function($dbCloneName) {
                $this->dropDatabaseCloneIfExists($dbCloneName);
            }, $dbCloneName);
        }
    }

    /**
     * @param string $dbCloneName
     */
    public function dropDatabaseCloneIfExists($dbCloneName) {

        if ($this->dbHandler->databaseExists($dbCloneName)) {
            $this->dbHandler->dropDatabase();
        }
    }
}