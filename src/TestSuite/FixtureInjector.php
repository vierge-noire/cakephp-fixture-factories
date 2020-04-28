<?php

namespace CakephpFixtureFactories\TestSuite;

use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use TestListenerDefaultImplementation;

/**
 * This class has to be used along the fixture factories
 *
 * Class FixtureInjector
 * @package CakephpFixtureFactories\TestSuite
 */
class FixtureInjector extends BaseTestListener
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
    public function startTestSuite(TestSuite $suite)
    {
        $this->_fixtureManager->initDb();
    }

    /**
     * Cleanup before test starts
     * Truncates the tables that were used by the previous test before starting a new one
     *
     * @param \PHPUnit\Framework\Test $test The test case
     * @return void
     */
    public function startTest(Test $test)
    {
        $this->_fixtureManager->truncateDirtyTablesForAllConnections();
    }

    /**
     * Do not do anything here, startTest will do the database cleanup before running the next test
     *
     * @param \PHPUnit\Framework\Test $test The test case
     * @param float                   $time current time
     * @return void
     */
    public function endTest(Test $test, $time)
    {
        // noop, see method description
    }

    /**
     * Truncate all dirty tables at the end of the test suite to leave a clean database
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        $this->_fixtureManager->truncateDirtyTablesForAllConnections();
    }
}
