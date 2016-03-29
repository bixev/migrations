<?php
namespace Bixev\Migrations\VersionStore;

class MysqlVersionStore extends AbstractVersionStore
{

    /**
     * @var \PDO
     */
    protected $_db;

    protected $_tableName;

    public function setDb(\PDO $databaseConnector, $tableName)
    {
        $this->_db = $databaseConnector;
        $this->_tableName = $tableName;
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        if ($this->_db === null || $this->_tableName === null) {
            throw new Exception('Require databaseConnector/tableName');
        }
        $rows = $this->_db->query("SHOW TABLES LIKE '" . $this->_tableName . "'")->fetchAll();

        return count($rows) == 1;
    }

    public function initialize()
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . $this->backquote($this->_tableName) . " (
                  `id` INT(5) NOT NULL AUTO_INCREMENT,
                  `code_version` VARCHAR(20) NULL COMMENT 'program version',
                  `data_version` INT(5) NOT NULL COMMENT 'data version',
                  `date_in` DATETIME NOT NULL COMMENT 'date of update to this version',
                  `date_out` DATETIME DEFAULT NULL COMMENT 'date out of this version',
                  `name` VARCHAR( 50 ) NOT NULL DEFAULT '' COMMENT 'version namespace',
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='data updates history' ;";

        $this->_db->exec($sql);
    }

    protected function backquote($str)
    {
        return str_replace("'", '`', $this->_db->quote($str));
    }

    public function getCurrentVersion($namespace)
    {
        $sql = "SELECT code_version, data_version 
                FROM " . $this->backquote($this->_tableName) . "
                WHERE 
                    name = " . $this->_db->quote($namespace) . "
                    AND date_out IS NULL";
        $row = $this->_db->query($sql)->fetch();
        if ($row === false) {
            return null;
        }

        return $row['code_version'] . '/' . $row['data_version'];
    }

    protected function doUpdateVersion($namespace, $newVersion = null)
    {
        if (strpos($newVersion, '/') !== false) {
            $version = explode('/', $newVersion);
            $codeVersion = $version[0];
            $dataVersion = $version[1];
        } else {
            $codeVersion = null;
            $dataVersion = $newVersion;
        }

        $oDateNow = new \DateTime('NOW', new \DateTimeZone('Europe/Paris'));
        $dateNow = $oDateNow->format('Y-m-d H:i:s');

        $sql = "UPDATE " . $this->_tableName . "
                SET date_out = " . $this->_db->quote($dateNow) . "
                WHERE 
                    name = " . $this->_db->quote($namespace) . "
                    AND date_out IS NULL";
        $this->_db->exec($sql);

        $sql = "INSERT INTO " . $this->_tableName . "(`name`,`date_in`,`code_version`,`data_version`)
                VALUES (" . $this->_db->quote($namespace) . ",
                        " . $this->_db->quote($dateNow) . ",
                        " . $this->_db->quote($codeVersion) . ",
                        " . $this->_db->quote($dataVersion) . ")";
        $this->_db->exec($sql);
    }

    protected function incrementedVersion($version)
    {
        if (strpos($version, '/') !== false) {
            $version = explode('/', $version);
            $codeVersion = $version[0];
            $dataVersion = $version[1];
        } else {
            $codeVersion = null;
            $dataVersion = $version;
        }
        $dataVersion++;

        return $codeVersion . '/' . $dataVersion;
    }

    protected function getUnexistingVersion()
    {
        return '0/0';
    }

}
