<?php
namespace Bixev\Migrations;

class API
{

    use \Bixev\LightLogger\LoggerTrait;

    /**
     * @var VersionStore\AbstractVersionStore
     */
    protected $_versionStore;

    protected $_updaters = [];

    /**
     * @param VersionStore\AbstractVersionStore $versionStore
     * @param \Bixev\LightLogger\LoggerInterface|null $logger
     * @throws \Exception
     */
    public function __construct(VersionStore\AbstractVersionStore $versionStore, \Bixev\LightLogger\LoggerInterface $logger = null)
    {
        $this->_versionStore = $versionStore;
        $this->setLogger($logger);
    }

    public function update($namespace, $directory)
    {

        if (!$this->_versionStore->isInitialized()) {
            $this->_versionStore->initialize();
        }

        $processedVersions = [];

        $loop = true;
        while ($loop) {
            $currentVersion = $this->_versionStore->getCurrentVersion($namespace);
            if (array_search($currentVersion, $processedVersions) !== false) {
                throw new Exception('Version already processed : ' . $currentVersion);
            }
            $this->log('Checking updates for "' . $namespace . '" at version ' . $currentVersion);
            if ($this->updateExists($namespace, $directory, $currentVersion)) {
                $this->log('Update found');
                $this->doUpdate($namespace, $directory, $currentVersion);
                $processedVersions[] = $currentVersion;
            } else {
                $this->log('No update found');
                $loop = false;
            }
        }
    }

    public function setUpdater($extension, Updater\AbstractUpdater $updater)
    {
        $this->_updaters[$extension] = $updater;
    }

    protected function updateExists($namespace, $directory, $currentVersion)
    {
        $path = $this->getUpdateDirectory($directory, $currentVersion);

        return is_dir($path);
    }

    protected function getUpdateDirectory($directory, $version)
    {
        $subpath = $this->_versionStore->getVersionSubPath($version);
        $path = $directory . DIRECTORY_SEPARATOR . $subpath;

        return $path;
    }

    protected function doUpdate($namespace, $directory, $currentVersion)
    {
        $directoryPath = $this->getUpdateDirectory($directory, $currentVersion);
        $pathList = scandir($directoryPath);
        $newVersion = null;
        foreach ($pathList as $path) {
            $path_parts = pathinfo($path);
            $fullPath = $directoryPath . DIRECTORY_SEPARATOR . $path_parts['basename'];
            $fileName = $path_parts['basename'];
            $extension = isset($path_parts['extension']) ? $path_parts['extension'] : null;
            if ($extension !== null && isset($this->_updaters[$extension])) {
                try {
                    $updater = $this->_updaters[$extension];
                    /* @var $updater \Bixev\Migrations\Updater\AbstractUpdater */
                    $this->log("Processing update file " . $fileName);
                    $updater->update($fullPath);
                    $newVersion1 = $updater->getVersion();
                    if ($newVersion1 !== null) {
                        $newVersion = $newVersion1;
                    }
                } catch (\Exception $e) {
                    throw new \Exception("Exception occured while updating " . $fileName . " : (" . $e->getCode() . ") " . $e->getMessage());
                }
            }
        }

        $this->_versionStore->updateVersion($namespace, $newVersion);
    }

}
