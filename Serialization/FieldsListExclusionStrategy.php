<?php

namespace BiteCodes\RestApiGeneratorBundle\Serialization;

use Gorka\DotNotationAccess\DotNotationAccessArray;
use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

class FieldsListExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $parentFieldNames = [];

    public function __construct(array $fields)
    {
        $this->fields = $this->stringValuesToKeys($fields);
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $navigatorContext)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $navigatorContext)
    {
        if (empty($this->fields)) {
            return false;
        }

        $name = $property->serializedName ?: $property->name;

        $this->trackDepth($name, $navigatorContext->getDepth());

        return !in_array($name, $this->getFields());
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        $parentFields = $this->parentFieldNames;

        if (count($parentFields) == 1) {
            $fields = $this->fields;
        } else {
            $path = join('.', array_slice($parentFields, 0, count($parentFields) - 1));

            $fields = new DotNotationAccessArray($this->fields);
            $fields = $fields->get($path, []);
        }

        return $this->normalizeKeys($fields);
    }

    /**
     * @param $name
     * @param $depth
     */
    protected function trackDepth($name, $depth)
    {
        $this->parentFieldNames[$depth] = $name;
        $depths = array_reverse(array_keys($this->parentFieldNames));

        // Remove old field names
        if (max($depths) > $depth) {
            while (($key = array_shift($depths)) > $depth) {
                unset($this->parentFieldNames[$key]);
            }
        }
    }

    /**
     * @param $fields
     * @return array
     */
    protected function normalizeKeys($fields)
    {
        return array_map(function ($key, $value) {
            return is_array($value) ? $key : $key;
        }, array_keys($fields), $fields);
    }

    protected function stringValuesToKeys($fields)
    {
        return array_reduce(array_keys($fields), function ($acc, $key) use ($fields) {
            if (is_array($fields[$key])) {
                $acc[$key] = $this->stringValuesToKeys($fields[$key]);
            } else {
                $acc[$fields[$key]] = true;
            }
            return $acc;
        }, []);
    }
}
