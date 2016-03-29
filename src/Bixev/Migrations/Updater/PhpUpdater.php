<?php
namespace Bixev\Migrations\Updater;

class PhpUpdater extends AbstractUpdater
{
    protected function doUpdate($path)
    {
        require $path;
    }

    public function setVersion($codeVersion, $dbVersion)
    {
        $this->updateVersion($codeVersion . '/' . $dbVersion);
    }

}
