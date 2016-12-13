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
            // TODO: implement
            $this->dropDatabaseCloneIfExists();
            //$dumpPrefix = $request->getParameter('dump-prefix');
            //$this->dbHandler->dumpDB($dumpPrefix);
            //$this->dbHandler->createDatabase();
            //$this->dbHandler->import($this->dbHandler->getTemporaryFolder() . $dumpPrefix . '_' . $this->dbHandler->getDbName());
            echo 'DbCloneService creates the clone' . PHP_EOL;
        }

        if ($request->getParameter('dropCloneAfterTestSuite')) {
            // TODO: implement
            register_shutdown_function(function() {
                echo 'DbCloneService\'s shutdown function was called' . PHP_EOL;
                $this->dropDatabaseCloneIfExists();
            });
        }
    }

    /**
     *
     */
    public function dropDatabaseCloneIfExists() {
        // TODO: implement
        echo 'DbCloneService drops database if it exists' . PHP_EOL;
    }
}