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
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

class DbHandler
{
    /** @var string Folder to store database dumps. */
    private $temporaryFolder = '';

    /** @var oxConfigFile */
    private $configFile;

    /** @var mysqli Database connection. */
    private $dbConnection;

    /** @var  string */
    private $dbCloneName;

    /**
     * Initiates class dependencies.
     *
     * @param oxConfigFile $configFile
     * @param string $dbCloneName
     */
    public function __construct($configFile, $dbCloneName = null)
    {
        $this->configFile = $configFile;
        $this->dbConnection = mysqli_connect($this->getDbHost(), $this->getDbUser(), $this->getDbPassword());
        $this->dbCloneName = $dbCloneName;
    }

    /**
     * Set temporary folder
     *
     * @param string $sTemporaryFolder folder path
     */
    public function setTemporaryFolder($sTemporaryFolder)
    {
        $this->temporaryFolder = $sTemporaryFolder;
    }

    /**
     * @param string $dbCloneName
     */
    public function setDbCloneName($dbCloneName)
    {
        $this->dbCloneName = $dbCloneName;
    }

    /**
     * Return temporary folder path
     *
     * @return string
     */
    public function getTemporaryFolder()
    {
        return $this->temporaryFolder;
    }

    /**
     * Drops the database
     * @param string|null $dbCloneName
     */
    public function dropDatabase($dbCloneName = null)
    {
        $dbName = $dbCloneName? $dbCloneName : $this->getDbName();
        echo 'drop database ' . $dbName . PHP_EOL;
        $this->query('drop database `' . $dbName . '`');
    }

    /**
     * Creates the database
     *
     * @param string|null $dbCloneName
     */
    public function createDatabase($dbCloneName = null)
    {
        $dbName = $dbCloneName? $dbCloneName : $this->getDbName();
        echo 'create database ' . $dbName . PHP_EOL;
        $this->query('create database `' . $dbName . '` collate ' . $this->getCharsetMode() . '_general_ci');
    }

    /**
     * Creates a dump of the current database, and store in temporary folder.
     * The dump includes the data and sql insert statements.
     *
     * @param string $dumpFilePrefix dump file name prefix.
     */
    public function dumpDB($dumpFilePrefix = null)
    {
        $fileName = $this->getDumpFileName($dumpFilePrefix);
        $this->executeCommand($this->getExportCommand($fileName));
    }

    /**
     * Restore db from existing dump
     *
     * @param string $dumpFilePrefix dump file name prefix.
     */
    public function restoreDB($dumpFilePrefix = null)
    {
        $this->import($this->getDumpFileName($dumpFilePrefix));
    }

    /**
     * Execute sql statements from sql file
     *
     * @param string $sqlFile     SQL File name to import.
     * @param string $charsetMode Charset of imported file. Will use shop charset mode if not set.
     */
    public function import($sqlFile, $charsetMode = null)
    {
        if (file_exists($sqlFile)) {
            $charsetMode = $charsetMode ? $charsetMode : $this->getCharsetMode();
            $this->executeCommand($this->getImportCommand($sqlFile, $charsetMode));
        } else {
            throw new Exception("File '$sqlFile' was not found.");
        }
    }

    /**
     * Executes query on database.
     *
     * @param string $sql Sql query to execute.
     *
     * @return resource
     */
    public function query($sql)
    {
        mysqli_select_db($this->getDbConnection(), $this->getDbName());
        return mysqli_query($this->getDbConnection(), $sql);
    }

    /**
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        return mysqli_real_escape_string($this->getDbConnection(), $value);
    }

    /**
     * Returns charset mode
     *
     * @return string
     */
    public function getCharsetMode()
    {
        return $this->configFile->iUtfMode ? 'utf8' : 'latin1';
    }

    /**
     * @param boolean $useOriginal  force to return the original dbName
     *
     * @return string
     */
    public function getDbName($useOriginal = false)
    {
        if (!$useOriginal && $this->dbCloneName && $this->databaseExists($this->dbCloneName)) {
            return $this->dbCloneName;
        }

        return $this->configFile->dbName;
    }

    /**
     * @return string
     */
    public function getDbUser()
    {
        return $this->configFile->dbUser;
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        return $this->configFile->dbPwd;
    }

    /**
     * @return string
     */
    public function getDbHost()
    {
        return $this->configFile->dbHost;
    }

    /**
     * Returns database resource
     *
     * @return mysqli
     */
    public function getDbConnection()
    {
        return $this->dbConnection;
    }

    /**
     * @param string $dbName
     * @return boolean
     */
    public function databaseExists($dbName)
    {
        $result = $this->dbConnection->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'' . $dbName . '\'');
        $exists = $result->num_rows === 1;
        $result->close();
        return $exists;
    }

    /**
     * Returns CLI import command, execute sql from given file
     *
     * @param string $fileName    SQL File name to import.
     * @param string $charsetMode Charset of imported file.
     *
     * @return string
     */
    protected function getImportCommand($fileName, $charsetMode)
    {
        $dbName = $this->getDbName();

        $command = 'mysql -h' . escapeshellarg($this->getDbHost());
        $command .= ' -u' . escapeshellarg($this->getDbUser());
        if ($password = $this->getDbPassword()) {
            $command .= ' -p' . escapeshellarg($password);
        }
        $command .= ' --default-character-set=' . $charsetMode;
        $command .= ' ' .escapeshellarg();
        $command .= ' < ' . escapeshellarg($fileName) . ' 2>&1';

        echo 'import ' . $fileName . ' into database ' . $dbName . PHP_EOL;

        return $command;
    }

    /**
     * Returns CLI command for db export to given file name
     *
     * @param string $fileName file name
     *
     * @return string
     */
    protected function getExportCommand($fileName)
    {
        $dbName = $this->getDbName(true);

        $command = 'mysqldump -h' . escapeshellarg($this->getDbHost());
        $command .= ' -u' . escapeshellarg($this->getDbUser());
        if ($password = $this->getDbPassword()) {
            $command .= ' -p' . escapeshellarg($password);
        }
        $command .= ' --add-drop-table ' . escapeshellarg($dbName);
        $command .= ' > ' . escapeshellarg($fileName);

        echo 'export database ' . $dbName . ' as ' . $fileName . PHP_EOL;

        return $command;
    }

    /**
     * Execute shell command
     *
     * @param $command
     *
     * @throws Exception
     */
    protected function executeCommand($command)
    {
        exec($command, $output, $resultCode);

        if ($resultCode > 0) {
            sleep(1);
            exec($command, $output, $resultCode);

            if ($resultCode > 0) {
                $output = implode("\n", $output);
                throw new Exception("Failed to execute command: '$command' with output: '$output' ");
            }
        }
    }

    /**
     * Create dump file name
     *
     * @param string $dumpFilePrefix - dump file prefix
     *
     * @return string
     */
    protected function getDumpFileName($dumpFilePrefix = null)
    {
        if (empty($dumpFilePrefix)) {
            $dumpFilePrefix = 'tmp_db_dump';
        }

        $fileName = $this->getTemporaryFolder() . '/' . $dumpFilePrefix . '_' . $this->getDbName(true);

        return $fileName;
    }
}
