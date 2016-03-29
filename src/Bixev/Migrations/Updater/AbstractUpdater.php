<?php
namespace Bixev\Migrations\Updater;

abstract class AbstractUpdater
{

    protected $_newVersion;

    public function update($path)
    {
        $this->_newVersion = null;
        $this->doUpdate($path);
    }

    abstract protected function doUpdate($path);

    public function updateVersion($version)
    {
        $this->_newVersion = $version;
    }

    public function getVersion()
    {
        return $this->_newVersion;
    }

}
