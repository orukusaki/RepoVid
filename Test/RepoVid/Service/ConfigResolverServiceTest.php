<?php

namespace Test\RepoVid\Service;

use RepoVid\Service\ConfigResolverService;

class ConfigResolverServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideSimpleConfigData
     * @covers RepoVid\Service\ConfigResolverService
     */
    public function testSimpleConfigSubstitutionHappens($input, $expected)
    {
        $inputConfig = json_decode($input);
        $expectedConfig = json_decode($expected);

        $resolver = new ConfigResolverService;

        $this->assertEquals($expectedConfig, $resolver->resolve($inputConfig));
    }

    public function provideSimpleConfigData()
    {
        return array(
            array(
                '{
                    "key1": "value1",
                    "key2": "value2{key1}"
                }',
                '{
                    "key1": "value1",
                    "key2": "value2value1"
                }',
            ),
            array(
                '{
                    "key1": "value1",
                    "key2": "value2/{key1}/{key3}",
                    "key3": "value3"
                }',
                '{
                    "key1": "value1",
                    "key2": "value2/value1/value3",
                    "key3": "value3"
                }',
            ),
        );
    }
}