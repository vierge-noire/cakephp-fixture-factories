<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Database\Exception;
use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Hash;

abstract class BaseTableSniffer
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Find all tables where an insert happened
     * This also includes empty tables, where a delete
     * was performed after an insert
     * @return array
     */
    abstract public function getDirtyTables(): array;

    /**
     * Truncate all the tables provided
     * @param array $tables
     * @return bool
     */
    abstract public function truncateDirtyTables();

    /**
     * List all tables
     * @return array
     */
    abstract public function getAllTables(): array;

    /**
     * Drop tables passed as a parameter
     * @param array $tables
     * @return array
     */
    abstract public function dropAllTables();

    /**
     * BaseTableTruncator constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection(ConnectionInterface $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * In case where the query fails because the database queried does
     * not exist, an exception is thrown.
     * @param string $query
     * @return array
     */
    protected function executeQuery(string $query): array
    {
        try {
            $tables = $this->getConnection()->execute($query)->fetchAll();
        } catch (\Exception $e) {
            $name = $this->getConnection()->configName();
            $db = $this->getConnection()->config()['database'];
            var_dump($e->getMessage());
            throw new Exception("Error in the connection '$name'. Is the database '$db' created and accessible?");
        }

        return Hash::extract($tables, '{n}.0');
    }
}