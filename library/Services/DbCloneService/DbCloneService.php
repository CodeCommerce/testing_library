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

            $this->dropDatabaseCloneIfExists($dbCloneName);
            $this->dbHandler->createDatabase($dbCloneName);

            if ($request->getParameter('importOriginalData')) {
                $this->dbHandler->dumpDB($dumpPrefix);
                $this->dbHandler->import($this->dbHandler->getTemporaryFolder() . $dumpPrefix . '_' . $origDbName);
            }

            echo 'DbCloneService creates the clone' . PHP_EOL;
        }

        if ($request->getParameter('dropCloneAfterTestSuite')) {
            // TODO: implement
            register_shutdown_function(function($dbCloneName) {
                echo 'DbCloneService\'s shutdown function was called' . PHP_EOL;
                $this->dropDatabaseCloneIfExists($dbCloneName);
            }, $dbCloneName);
        }
    }

    /**
     * @param string $dbCloneName
     */
    public function dropDatabaseCloneIfExists($dbCloneName) {

        if ($this->dbHandler->databaseExists($dbCloneName)) {
            $this->dbHandler->dropDatabase($dbCloneName);
        }

        echo 'DbCloneService drops database if it exists' . PHP_EOL;
    }
}