<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\View\Helper;

use Cake\View\Helper;

class FactoryBakeHelper extends Helper
{
    /**
     * @param array $defaultData
     * @return string
     */
    public function defaultData(array $defaultData): string
    {
        $rows = [];
        foreach ($defaultData as $key => $value) {
            $rows[] = "\t\t\t\t" . '\'' . $key . '\' => ' . $value . ',';
        }

        $string = implode(PHP_EOL, $rows);

        $default = <<<TXT
                // set the model's default values
                // For example:
                // 'name' => \$faker->lastName()
TXT;

        return ltrim($string ?: $default);
    }
}
