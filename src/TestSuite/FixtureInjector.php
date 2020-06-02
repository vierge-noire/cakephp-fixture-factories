<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;

/**
 * This class has to be used along the fixture factories
 *
 * Class FixtureInjector
 * @package CakephpFixtureFactories\TestSuite
 */
class FixtureInjector extends \Cake\TestSuite\Fixture\FixtureInjector
{
    /**
     * @var FixtureManager
     */
    public $_fixtureManager;

    public function __construct(\Cake\TestSuite\Fixture\FixtureManager $manager)
    {
        $this->_fixtureManager = $manager;
    }


    /**
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $this->_fixtureManager->initDb();
    }

    /**
     * Cleanup before test starts
     * Truncates the tables that were used by the previous test before starting a new one
     * The truncation may be by-passed by setting in the test
     *
     * @param \PHPUnit\Framework\Test $test The test case
     * @return void
     */
    public function startTest(Test $test): void
    {
        if (!$this->skipTablesTruncation($test)) {
            $this->_fixtureManager->truncateDirtyTablesForAllTestConnections();
        }
        if (!empty($test->fixtures)) {
            parent::startTest($test);
        }
    }

    /**
     * Do not do anything here, startTest will do the database cleanup before running the next test
     *
     * @param \PHPUnit\Framework\Test $test The test case
     * @param float                   $time current time
     * @return void
     */
    public function endTest(Test $test, float $time): void
    {
        // noop, see method description
    }

    /**
     * The tables are not truncated at the end of the suite.
     * This way one can observe the content of the test DB
     * after a suite has been run.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        // noop, see method description
    }

    /**
     * If a test uses the SkipTablesTruncation trait, table truncation
     * does not occur between tests
     * @param Test $test
     * @return bool
     */
    public function skipTablesTruncation(Test $test): bool
    {
        return $test->skipTablesTruncation ?? false;
    }
}
