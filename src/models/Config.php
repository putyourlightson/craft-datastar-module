<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\models;

use Craft;
use craft\base\Model;
use craft\helpers\Json;
use putyourlightson\datastar\Datastar;

class Config extends Model
{
    public ?int $siteId = null;
    public string $route = '';
    public array $params = [];

    /**
     * Creates a new instance from a hashed config string.
     */
    public static function fromHashed(string $config): ?static
    {
        $data = Craft::$app->getSecurity()->validateData($config);
        if ($data === false) {
            return null;
        }

        return new self(Json::decodeIfJson($data));
    }

    /**
     * Validates that none of the params are objects, recursively.
     *
     * @uses validateParams()
     */
    protected function defineRules(): array
    {
        return [
            [['siteId', 'route'], 'required'],
            [['siteId'], 'integer'],
            [['route'], 'string'],
            [['params'], 'validateParams'],
        ];
    }

    /**
     * Validates the params.
     */
    public function validateParams(): bool
    {
        return $this->validateParamsRecursively($this->params);
    }

    /**
     * Returns a hashed, JSON-encoded array of attributes.
     */
    public function getHashed(): string
    {
        $attributes = array_filter([
            'siteId' => $this->siteId,
            'route' => $this->route,
            'params' => $this->params,
        ]);
        $encoded = Json::encode($attributes);

        return Craft::$app->getSecurity()->hashData($encoded);
    }

    /**
     * Validates the params recursively.
     */
    private function validateParamsRecursively(array $params): bool
    {
        $signalsVariableName = Datastar::getInstance()->settings->signalsVariableName;

        foreach ($params as $key => $value) {
            if ($key === $signalsVariableName) {
                $this->addError('params', 'Param `' . $signalsVariableName . '` is reserved. Use a different name or modify the name of the signals variable using the `signalsVariableName` config setting.');
                return false;
            }
        }

        foreach ($params as $key => $value) {
            if (is_object($value)) {
                $this->addError('params', 'Param `' . $key . '` is an object, which is a forbidden param type in the context of a Datastar request.');
                return false;
            }

            if (is_array($value)) {
                return $this->validateParamsRecursively($value);
            }
        }

        return true;
    }
}
