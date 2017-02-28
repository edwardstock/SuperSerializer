<?php
namespace edwardstock\superializer\tests\unit\helpers;

use edwardstock\superserializer\Serializer;
use edwardstock\superserializer\tests\TestCase;


/**
 * forker. 2016
 * @author Eduard Maximovich <edward.vstock@gmail.com>
 *
 */
class SerializerTest extends TestCase
{

    public function testSerialize()
    {
        $o       = new \stdClass();
        $o->func = function () {
        };

        $o2       = new \stdClass();
        $o2->prop = [
            'k' => function () {
            },
        ];

        $values = [
            'integer'             => 1,
            'float'               => 111.111,
            'object'              => new \stdClass(),
            'array'               => ['k' => 'v'],
            'array_with_closure'  => [
                'k' => function () {
                },
            ],
            'object_with_closure' => $o,
            'bool_true'           => true,
            'bool_false'          => false,
            'null'                => null,
        ];

        foreach ($values AS $name => $value) {
            $s = Serializer::serialize($value);
            self::assertTrue(is_string($s));
        }
    }

    public function testUnserialize()
    {
        $o       = new \stdClass();
        $o->func = function () {
        };

        $o2       = new \stdClass();
        $o2->prop = [
            'k' => function () {
            },
        ];

        $o->sub = $o2;

        $values = [
            'integer'             => 1,
            'float'               => 111.111,
            'object'              => new \stdClass(),
            'array'               => ['k' => 'v'],
            'array_with_closure'  => [
                'k' => function () {
                },
            ],
            'object_with_closure' => $o,
            'bool_true'           => true,
            'bool_false'          => false,
            'null'                => null,
        ];

        foreach ($values AS $name => $value) {
            if (is_object($value)) {
                $clone = $value;
                $s     = Serializer::serialize($clone);
                $this->assertTrue(is_string($s));

                $uns = Serializer::unserialize($s);
                $this->assertEquals($value, $uns);
            } else {
                $s = Serializer::serialize($value);
                $this->assertTrue(is_string($s));

                $uns = Serializer::unserialize($s);
                $this->assertEquals($value, $uns);
            }

        }

    }
}