<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\variables;

use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Request;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\ConfigModel;
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
        return $this->getAction('get', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('post', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('put', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('patch', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('delete', $template, $variables, $options);
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

    /**
     * Returns a Datastar action.
     */
    private function getAction(string $method, string $template, array $variables = [], array $options = []): string
    {
        $url = $this->getUrl($template, $variables);
        $args = ["'$url'"];

        if ($method !== 'get') {
            $headers = $options['headers'] ?? [];
            $headers[Request::CSRF_HEADER] = Craft::$app->getRequest()->getCsrfToken();
            $options['headers'] = $headers;
        }

        if (!empty($options)) {
            $args[] = Json::encode($options);
        }

        $args = implode(', ', $args);

        return "@$method($args)";
    }

    /**
     * Returns a Datastar URL endpoint.
     */
    private function getUrl(string $template, array $variables = []): string
    {
        $config = new ConfigModel([
            'siteId' => Craft::$app->getSites()->getCurrentSite()->id,
            'template' => $template,
            'variables' => $variables,
        ]);

        if (!$config->validate()) {
            throw new SyntaxError(implode(' ', $config->getFirstErrors()));
        }

        return UrlHelper::actionUrl('datastar-module', [
            'config' => $config->getHashed(),
        ]);
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
