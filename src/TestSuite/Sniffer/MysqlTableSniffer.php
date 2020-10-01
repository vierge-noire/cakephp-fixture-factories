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


use Cake\Database\Connection;

class MysqlTableSniffer extends BaseTableSniffer
{
    /**
     * @inheritDoc
     * @return array
     */
    public function getDirtyTables(): array
    {
        return $this->fetchQuery("
            SELECT table_name
            FROM INFORMATION_SCHEMA.TABLES
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND table_name NOT LIKE '%phinxlog'
                AND AUTO_INCREMENT > 1;
        ");
    }

    /**
     * @inheritDoc
     * @return bool|void
     * @throws \Exception
     */
    public function truncateDirtyTables()
    {
        $tables = $this->getDirtyTables();
        if (empty($tables)) {
            return;
        }
        $this->getConnection()->disableConstraints(function (Connection $connection) use ($tables) {
            $connection->transactional(function(Connection $connection) use ($tables) {
                $connection->execute(
                    $this->implodeSpecial(
                        "TRUNCATE TABLE `",
                        $tables,
                        "`;"
                    )
                );
            });
        });
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getAllTables(): array
    {
        $databaseName = $this->getConnection()->config()['database'];

        return $this->fetchQuery("
            SELECT table_name
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '$databaseName';
        ");
    }

    /**
     * @inheritDoc
     * @return array|void
     * @throws \Exception
     */
    public function dropAllTables()
    {
        $tables = $this->getAllTables();

        $this->getConnection()->disableConstraints(function (Connection $connection) use ($tables) {
            $connection->transactional(function(Connection $connection) use ($tables) {
                $connection->execute(
                    $this->implodeSpecial(
                        'DROP TABLE IF EXISTS `',
                        $tables,
                        '`;'
                    )
                );
            });
        });
    }
}