<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions;

use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use putyourlightson\datastar\models\ConfigModel;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Twig\Error\SyntaxError;

class DatastarFunctions
{
    public const ALLOWED_METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * Returns the Datastar URL endpoint, wrapped in an action function.
     */
    public static function datastar(string $template, array $variables = [], string $method = 'get'): string
    {
        $method = self::getValidMethod($method);
        $url = self::datastarUrl($template, $variables, $method);

        return "$$method('$url')";
    }

    /**
     * Returns the Datastar URL endpoint.
     */
    public static function datastarUrl(string $template, array $variables = [], string $method = 'get'): string
    {
        $config = new ConfigModel([
            'siteId' => Craft::$app->getSites()->getCurrentSite()->id,
            'template' => $template,
            'variables' => $variables,
            'method' => self::getValidMethod($method),
        ]);

        if (!$config->validate()) {
            throw new SyntaxError(implode(' ', $config->getFirstErrors()));
        }

        return UrlHelper::actionUrl('datastar-module', [
            'config' => $config->getHashed(),
        ]);
    }

    /**
     * Returns an array of signal values.
     */
    public static function datastarSignals(array $values): string
    {
        self::validateSignals($values);

        return Json::encode($values);
    }

    /**
     * Returns a class’s public properties as signals.
     */
    public static function datastarSignalsFromClass(string $class, array $values = []): string
    {
        $classValues = self::getClassPropertyValues($class);

        foreach ($values as $key => $value) {
            $classValues[$key] = $value;
        }

        return self::datastarSignals($classValues);
    }

    private static function validateSignals(array $values): void
    {
        foreach ($values as $value) {
            if (is_object($value)) {
                throw new SyntaxError('Signal values cannot contain objects.');
            }

            if (is_array($value)) {
                self::validateSignals($value);
            }
        }
    }

    private static function getValidMethod(string $method): string
    {
        $method = strtolower($method);
        if (!in_array($method, static::ALLOWED_METHODS)) {
            throw new SyntaxError('Method must be one of ' . implode(', ', self::ALLOWED_METHODS));
        }

        return $method;
    }

    private static function getClassPropertyValues(string $class): array
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
            $values[$property->name] = self::getPropertyValue($property, $defaultValue);
        }

        return $values;
    }

    private static function getPropertyValue(ReflectionProperty $property, mixed $defaultValue): mixed
    {
        $type = $property->getType();
        if (!($type instanceof ReflectionNamedType)) {
            return $defaultValue;
        }

        if ($type->isBuiltin()) {
            return $defaultValue;
        }

        $class = $type->getName();

        return self::getClassPropertyValues($class);
    }
}
