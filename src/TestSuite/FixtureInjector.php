<?php

namespace TestFixtureFactories\TestSuite;

use Core\Test\TestSuite\FixtureManager;
use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use function class_uses;
use function in_array;

/**
 * This class has to be used along the fixture factories
 *
 * Class FixtureInjector
 * @package TestFixtureFactories\TestSuite
 */
class FixtureInjector extends BaseTestListener
{
    /**
     * @var FixtureManager
     */
    public $_fixtureManager;

    public function __construct(\Cake\TestSuite\Fixture\FixtureManager $manager)
    {
        if (isset($_SERVER['argv'])) {
            $manager->setDebug(in_array('--debug', $_SERVER['argv']));
        }
        $this->_fixtureManager = $manager;
    }


    /**
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite)
    {
        // noop
    }

    /**
     * Truncates the tables that were used by the previous test before starting a new one
     *
     * @param \PHPUnit\Framework\Test $test The test case
     * @return void
     */
    public function startTest(Test $test)
    {
        $this->_fixtureManager->startTest();
    }

    /**
     * Do not do anything here, startTest will do the cleanup before running the next test
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
     * Skip the Cake EndTestSuite because it drops fixture loaded tables
     * and we do not want any tables to be dropped
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        // noop, see method description
    }
}
