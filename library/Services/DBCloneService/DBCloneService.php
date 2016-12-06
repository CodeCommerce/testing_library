<?php

include_once LIBRARY_PATH .'/DbHandler.php';

/**
* Shop database cloner class for cloning/deleting the original database during testing
* Class DBCloneService
*/
class DBCloneService implements ShopServiceInterface
{
    private $dbHandler;

    /**
     * Initiates class dependencies.
     *
     * @param ServiceConfig $config
     */
    public function __construct($config)
    {
        $configFile = new oxConfigFile($config->getShopDirectory() . "config.inc.php");
        $this->dbHandler = new DbHandler($configFile);
        $this->dbHandler->setTemporaryFolder($config->getTempDirectory());
    }

    /**
     * Handles request parameters.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function init($request)
    {
        if ($request->getParameter('cloneDB')) {
            $this->dropDatabaseCloneIfExists();
            $dumpPrefix = $request->getParameter('dump-prefix');
            $this->dbHandler->dumpDB($dumpPrefix);
            $this->dbHandler->createDatabaseClone();
            $this->dbHandler->import($this->dbHandler->getTemporaryFolder() . $dumpPrefix . '_' . $this->dbHandler->getDbName(true));
        }

        if ($request->getParameter('dropDBClone')) {
            $this->dropDatabaseCloneIfExists();
        }
    }

    protected function dropDatabaseCloneIfExists()
    {
        if ($this->dbHandler->dbCloneExists()) {
            $this->dbHandler->dropDatabase();
        }
    }

    protected function getDbHandler()
    {
        return $this->dbHandler;
    }
}