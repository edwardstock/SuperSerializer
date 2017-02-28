# SuperSerializer
Can serialize everything, even object with properties contains Closures (big thx to SuperClosure project) and callables

[![Build Status](https://travis-ci.org/edwardstock/SuperSerializer.svg?branch=master)](https://travis-ci.org/edwardstock/SuperSerializer)

## Usage

```php
<?php
use edwardstock\superserializer\Serializer;

$o       = new \stdClass();
$o->func = function () {};

$o2       = new \stdClass();
$o2->prop = [
    'k' => function () {
        },
];

$values = [
    'integer'     => 1,
    'float'       => 111.111,
    'object'      => new \stdClass(),
    'array'       => ['k' => 'v'],
    'array_with_closure'  => [
        'k' => function () {
        },
    ],
    'object_with_closure' => $o,
    'bool_true'           => true,
    'bool_false'          => false,
    'null'        => null,
];

$ser1 = Serializer::serialize($o);
$ser2 = Serializer::serialize($o2);
$ser3 = Serializer::serialize($values);

// $o1
$unser1 = Serializer::unserialize($ser1);

// $o2
$unser2 = Serializer::unserialize($ser2);

// $values
$unser3 = Serializer::unserialize($ser3);

```