<?php

namespace CakephpFixtureFactories\TestSuite\Sniffer;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;


trait TableSnifferFinder
{
    /**
     * Get the driver of the given connection and
     * return the corresponding truncator
     * @param string $connectionName
     * @return BaseTableSniffer
     */
    public function getTableSniffer(): BaseTableSniffer
    {
        $connection = ConnectionManager::get($this->getInput()->getOption('connection'));
        $driver = get_class($connection->getDriver());

        try {
            $snifferName = Configure::readOrFail('TestFixtureTableSniffers.' . $driver);
        } catch (\RuntimeException $e) {
            throw new \PHPUnit\Framework\Exception("The driver $driver is not being supported");
        }
        /** @var BaseTableSniffer $truncator */
        return new $snifferName($connection);
    }
}