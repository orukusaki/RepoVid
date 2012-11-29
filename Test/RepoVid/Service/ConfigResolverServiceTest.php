<?php
/**
 * ConfigResolverServiceTest
 *
 * @package RepoVid
 * @author  Peter Smith <peter@orukusaki.co.uk>
 * @link    github.com/orukusaki/RepoVid
 */
namespace Test\RepoVid\Service;

use RepoVid\Service\ConfigResolverService;
/**
 * Config Resolver Service Test
 *
 * @package RepoVid
 * @author  Peter Smith <peter@orukusaki.co.uk>
 * @link    github.com/orukusaki/RepoVid
 */
class ConfigResolverServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Simple Config Substitution Happens
     *
     * @param string $input    Input
     * @param string $expected Expected
     *
     * @dataProvider provideSimpleConfigData
     * @covers RepoVid\Service\ConfigResolverService
     *
     * @return void
     */
    public function testSimpleConfigSubstitutionHappens($input, $expected)
    {
        $inputConfig = json_decode($input);
        $this->assertNotNull($inputConfig);
        $expectedConfig = json_decode($expected);
        $this->assertNotNull($expectedConfig);

        $resolver = new ConfigResolverService;

        $this->assertEquals($expectedConfig, $resolver->resolve($inputConfig));
    }

    /**
     * Provider for testSimpleConfigSubstitutionHappens
     *
     * @return array
     */
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

    /**
     * Test Nested Config Substitution Happens
     *
     * @param string $input    Input
     * @param string $expected Expected
     *
     * @dataProvider provideNestedConfigData
     * @covers RepoVid\Service\ConfigResolverService
     *
     * @return void
     */
    public function testNestedConfigSubstitutionHappens($input, $expected)
    {
        $inputConfig = json_decode($input);
        $this->assertNotNull($inputConfig);
        $expectedConfig = json_decode($expected);
        $this->assertNotNull($expectedConfig);

        $resolver = new ConfigResolverService;

        $this->assertEquals($expectedConfig, $resolver->resolve($inputConfig));
    }

    /**
     * Provider for testSimpleConfigSubstitutionHappens
     *
     * @return array
     */
    public function provideNestedConfigData()
    {
        return array(
            array(
                '{
                    "key1": [
                        "value3",
                        "value4"
                    ],
                    "key2": "value2-{key1/1}",
                    "key3": "{key5}"
                }',
                '{
                    "key1": [
                        "value3",
                        "value4"
                    ],
                    "key2": "value2-value4",
                    "key3": "{key5}"
                }',
            ),
            array(
                '{
                    "key1": {
                        "key3": "value3",
                        "key4": "value4"
                    },
                    "key2": "value2/{key1/key3}"
                }',
                '{
                    "key1": {
                        "key3": "value3",
                        "key4": "value4"
                    },
                    "key2": "value2/value3"
                }',
            ),
        );
    }

    /**
     * Test Recursive Config Substitution Happens
     *
     * @param string $input    Input
     * @param string $expected Expected
     *
     * @dataProvider provideRecursiveConfigData
     * @covers RepoVid\Service\ConfigResolverService
     *
     * @return void
     */
    public function testRecursiveConfigSubstitutionHappens($input, $expected)
    {
        $inputConfig = json_decode($input);
        $this->assertNotNull($inputConfig);
        $expectedConfig = json_decode($expected);
        $this->assertNotNull($expectedConfig);

        $resolver = new ConfigResolverService;

        $this->assertEquals($expectedConfig, $resolver->resolve($inputConfig));
    }

    /**
     * Provider for testSimpleConfigSubstitutionHappens
     *
     * @return array
     */
    public function provideRecursiveConfigData()
    {
        return array(
            array(
                '{
                    "key1": [
                        "value3",
                        "value4-{key5}"
                    ],
                    "key2": "value2-{key1/1}",
                    "key5": "value5"
                }',
                '{
                    "key1": [
                        "value3",
                        "value4-value5"
                    ],
                    "key2": "value2-value4-value5",
                    "key5": "value5"
                }',
            ),
            array(
                '{
                    "key1": [
                        "value3",
                        "value4-{key3}"
                    ],
                    "key2": "value2-{key3}",
                    "key3": "{key1/0}"
                }',
                '{
                    "key1": [
                        "value3",
                        "value4-value3"
                    ],
                    "key2": "value2-value3",
                    "key3": "value3"
                }',
            ),
        );
    }

    /**
     * Test Circular Config Substitution Happens
     *
     * @param string $input Input
     *
     * @dataProvider provideCircularConfigData
     * @expectedException RuntimeException
     * @covers RepoVid\Service\ConfigResolverService
     *
     * @return void
     */
    public function testCircularConfigSubstitutionHappens($input)
    {
        $inputConfig = json_decode($input);
        $this->assertNotNull($inputConfig);

        $resolver = new ConfigResolverService;

        $resolver->resolve($inputConfig);
    }

    /**
     * Provider for testSimpleConfigSubstitutionHappens
     *
     * @return array
     */
    public function provideCircularConfigData()
    {
        return array(
            array(
                '{
                    "key1": "{key2}",
                    "key2": "{key1}"
                }',
            )
        );
    }
}