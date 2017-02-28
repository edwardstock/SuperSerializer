<?php
namespace edwardstock\superserializer;

use SuperClosure\Serializer as CSerializer;

/**
 * super_serializer. 2017
 * @author Eduard Maximovich <edward.vstock@gmail.com>
 *
 */
class Serializer
{
    /**
     * @param mixed $any
     *
     * @return string
     */
    public static function serialize($any)
    {
        if (!is_object($any) && !is_array($any)) {
            return \serialize($any);
        }

        $hasClosures = false;
        $serialized  = null;

        // try-catch faster than check all values via reflection
        try {
            $serialized = \serialize($any);
        } catch (\Throwable $e) {
            $hasClosures = true;
        }

        if (!$hasClosures) {
            return $serialized;
        }

        if (is_object($any)) {
            $s = static::serializeObjectClosures($any);

            return \serialize($s);
        } else if (is_array($any)) {
            $s = static::serializeArrayClosures($any);

            return \serialize($s);
        } else if ($any instanceof \Closure) {
            return static::getClosureSerializer()->serialize($any);
        } else if (is_callable($any)) {
            return static::getClosureSerializer()->serialize(
                static::callableToClosure($any)
            );
        } else {
            return $serialized;
        }
    }

    /**
     * @param string $serialized
     *
     * @return mixed
     */
    public static function unserialize($serialized)
    {
        if (strpos($serialized, 'SerializableClosure') === false) {
            return \unserialize($serialized);
        }

        $unserialized = \unserialize($serialized);

        if (is_array($unserialized)) {
            return static::unserializeArrayClosures($unserialized);
        } else if (is_object($unserialized)) {
            return static::unserializeObjectClosures($unserialized);
        }

        return $unserialized;
    }

    /**
     * @param callable $callback
     * @param array    ...$args
     *
     * @return \Closure
     */
    public static function callableToClosure(callable $callback, ...$args)
    {
        if ($callback instanceof \Closure) {
            return $callback;
        }

        $objectOrClass = $callback[0];
        $methodName    = $callback[1];

        if (is_object($objectOrClass)) {
            return function () use ($objectOrClass, $methodName, &$args) {
                return $objectOrClass->{$methodName}(...$args);
            };
        }

        return function () use ($objectOrClass, $methodName, &$args) {
            return $objectOrClass::{$methodName}(...$args);
        };
    }

    private static function serializeArrayClosures(array $any)
    {
        $copy = $any;

        foreach ($copy AS $key => $value) {
            if ($value instanceof \Closure) {
                $copy[$key] = self::getClosureSerializer()->serialize($value);
            } else if (is_array($value)) {
                $copy[$key] = self::serializeArrayClosures($value);
            } else if (is_object($value)) {
                $copy[$key] = self::serializeObjectClosures($value);
            }
        }

        return $copy;
    }

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    private static function serializeObjectClosures($object)
    {
        $newObject = clone $object;
        $refObject = new \ReflectionObject($newObject);
        foreach ($refObject->getProperties() AS $property) {
            $property->setAccessible(true);

            $value = $property->getValue($newObject);

            if ($value instanceof \Closure) {
                $property->setValue(
                    $newObject,
                    self::getClosureSerializer()->serialize($value)
                );
            } else if (is_object($value)) {
                $s = self::serializeObjectClosures($value);
                $property->setValue(
                    $newObject,
                    $s
                );
            } else if (is_array($value)) {
                $s = self::serializeArrayClosures($value);
                $property->setValue(
                    $newObject,
                    $s
                );
            } else {
                $property->setValue($newObject, $value);
            }
        }

        return $newObject;
    }

    /**
     * @param array $unserialized
     *
     * @return array
     */
    private static function unserializeArrayClosures(array $unserialized)
    {
        foreach ($unserialized AS $key => $value) {
            if (is_string($value) && strpos($value, 'SerializableClosure') !== false) {
                $unserialized[$key] = self::getClosureSerializer()->unserialize($value);
            } else if (is_array($value)) {
                $unserialized[$key] = self::unserializeArrayClosures($value);
            } else if (is_object($value)) {
                $unserialized[$key] = self::unserializeObjectClosures($value);
            }
        }

        return $unserialized;
    }

    /**
     * @param $unserialized
     *
     * @return mixed
     */
    private static function unserializeObjectClosures($unserialized)
    {
        $refObject = new \ReflectionObject($unserialized);

        foreach ($refObject->getProperties() AS $property) {
            $property->setAccessible(true);

            $val = $property->getValue($unserialized);
            if (is_string($val) && strpos($val, 'SerializableClosure') !== false) {
                $property->setValue(
                    $unserialized,
                    self::getClosureSerializer()->unserialize($val)
                );
            } else if (is_object($val)) {
                $s = self::unserializeObjectClosures($val);
                $property->setValue($unserialized, $s);
            } else if (is_array($val)) {
                $s = self::unserializeArrayClosures($val);
                $property->setValue($unserialized, $s);
            } else {
                $property->setValue($unserialized, $val);
            }

        }

        return $unserialized;
    }

    /**
     * @return CSerializer
     */
    private static function getClosureSerializer()
    {
        return new CSerializer();
    }


}