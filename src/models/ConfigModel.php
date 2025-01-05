<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\models;

use Craft;
use craft\base\Model;
use craft\helpers\Json;
use putyourlightson\datastar\Datastar;

class ConfigModel extends Model
{
    public ?int $siteId = null;
    public string $template = '';
    public array $variables = [];

    /**
     * Creates a new instance from a hashed config string.
     */
    public static function fromHashed(string $config): ?self
    {
        $data = Craft::$app->getSecurity()->validateData($config);
        if ($data === false) {
            return null;
        }

        return new self(Json::decodeIfJson($data));
    }

    /**
     * Validates that none of the variables are objects, recursively.
     *
     * @uses validateVariables()
     */
    protected function defineRules(): array
    {
        return [
            [['siteId', 'template'], 'required'],
            [['siteId'], 'integer'],
            [['template'], 'string'],
            [['variables'], 'validateVariables'],
        ];
    }

    /**
     * Validates the variables.
     */
    public function validateVariables(): bool
    {
        return $this->validateVariablesRecursively($this->variables);
    }

    /**
     * Returns a hashed, JSON-encoded array of attributes.
     */
    public function getHashed(): string
    {
        $attributes = array_filter([
            'siteId' => $this->siteId,
            'template' => $this->template,
            'variables' => $this->variables,
        ]);
        $encoded = Json::encode($attributes);

        return Craft::$app->getSecurity()->hashData($encoded);
    }

    /**
     * Validates the variables recursively.
     */
    private function validateVariablesRecursively(array $variables): bool
    {
        $signalsVariableName = Datastar::getInstance()->settings->signalsVariableName;

        foreach ($variables as $key => $value) {
            if ($key === $signalsVariableName) {
                $this->addError('variables', 'Variable `' . $signalsVariableName . '` is reserved. Use a different name or modify the name of the signals variable using the `signalsVariableName` config setting.');
                return false;
            }

            if (is_object($value)) {
                $this->addError('variables', 'Variable `' . $key . '` is an object, which is a forbidden variable type in the context of a Datastar request.');
                return false;
            }

            if (is_array($value)) {
                return $this->validateVariablesRecursively($value);
            }
        }

        return true;
    }
}
