<?php


namespace TestFixtureFactories\TestSuite;


use Cake\Console\Shell;
use Cake\Console\ShellDispatcher;
use Cake\TestSuite\Fixture\FixtureInjector as BaseTestListener;
use Core\Test\TestSuite\FixtureManager;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Test;

class FixtureInjector extends BaseTestListener
{
    /**
     * @var FixtureManager
     */
    public $_fixtureManager;
    /**
     * Adds fixtures to a test case when it starts.
     *
     * @param \PHPUnit\Framework\Test $test The test case
     * @return void
     */
    public function startTest(Test $test)
    {
        if ($this->isNewSchool($test)) {
            $this->_fixtureManager->startTest();
        } else {
            parent::startTest($test);
        }
    }

    /**
     * Unloads fixtures from the test case.
     *
     * @param \PHPUnit\Framework\Test $test The test case
     * @param float $time current time
     * @return void
     */
    public function endTest(Test $test, $time)
    {
        if (!$this->isNewSchool($test)) {
            parent::endTest($test, $time);
        }
    }

    private function isNewSchool(Test $test)
    {
        return isset(class_uses($test)[NewtonFixtureManager::class]);
    }
}
