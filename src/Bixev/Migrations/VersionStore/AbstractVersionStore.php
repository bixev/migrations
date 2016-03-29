<?php
namespace Bixev\Migrations\VersionStore;

abstract class AbstractVersionStore
{

    use \Bixev\LightLogger\LoggerTrait;

    /**
     * @var VersionStore\AbstractVersionStore
     */
    protected $_versionStore;

    /**
     * @param \Bixev\LightLogger\LoggerInterface|null $logger
     */
    public function __construct(\Bixev\LightLogger\LoggerInterface $logger = null)
    {
        $this->setLogger($logger);
    }

    /**
     * @return bool
     */
    abstract public function isInitialized();

    abstract public function initialize();

    abstract public function getCurrentVersion($namespace);

    public function updateVersion($namespace, $newVersion = null)
    {
        if ($newVersion === null) {
            $this->log('Autoincrement version');
            $newVersion = $this->getIncrementedVersion($namespace);
        }
        $this->log('Set version for ' . $namespace . ' : ' . $newVersion);
        $this->doUpdateVersion($namespace, $newVersion);
    }

    abstract protected function doUpdateVersion($namespace, $newVersion = null);

    protected function getIncrementedVersion($namespace)
    {
        $currentVersion = $this->getCurrentVersion($namespace);
        if (empty($currentVersion)) {
            $currentVersion = $this->getUnexistingVersion();
        }

        return $this->incrementedVersion($currentVersion);
    }

    abstract protected function getUnexistingVersion();

    public function getVersionSubPath($version)
    {
        return $version;
    }

}
