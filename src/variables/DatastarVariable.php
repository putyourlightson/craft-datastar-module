<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\variables;

use craft\helpers\Json;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\helpers\ActionHelper;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Twig\Error\SyntaxError;
use yii\web\Response;

class DatastarVariable
{
    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('get', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('post', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('put', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('patch', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('delete', $template, $variables, $options);
    }

    /**
     * Returns signals from an array of provided values.
     */
    public function getSignals(array $values): string
    {
        $this->validateSignalValues($values);

        return Json::encode($values);
    }

    /**
     * Returns JSON encoded signals from the public properties of the class and the provided values.
     */
    public function getSignalsFromClass(string $class, array $values = []): string
    {
        $classValues = $this->getClassPropertyValues($class);

        foreach ($values as $key => $value) {
            $classValues[$key] = $value;
        }

        return $this->getSignals($classValues);
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->sse->runAction($route, $params);
    }

    private function validateSignalValues(array $values): void
    {
        foreach ($values as $value) {
            if (is_object($value)) {
                throw new SyntaxError('Signals cannot contain objects.');
            }

            if (is_array($value)) {
                $this->validateSignalValues($value);
            }
        }
    }

    private function getClassPropertyValues(string $class): array
    {
        if (!class_exists($class)) {
            throw new SyntaxError('Class `' . $class . '` could not be found. Ensure that the class exists and is autoloaded.');
        }

        $reflection = new ReflectionClass($class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $defaultValues = $reflection->getDefaultProperties();

        $values = [];
        foreach ($properties as $property) {
            $defaultValue = $defaultValues[$property->name] ?? '';
            $values[$property->name] = $this->getPropertyValue($property, $defaultValue);
        }

        return $values;
    }

    private function getPropertyValue(ReflectionProperty $property, mixed $defaultValue): mixed
    {
        $type = $property->getType();
        if (!($type instanceof ReflectionNamedType)) {
            return $defaultValue;
        }

        if ($type->isBuiltin()) {
            return $defaultValue;
        }

        $class = $type->getName();

        return $this->getClassPropertyValues($class);
    }
}
