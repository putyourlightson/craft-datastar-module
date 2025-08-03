<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * Whether to register the Datastar script on the frontend.
     */
    public bool $registerScript = true;

    /**
     * The name of the signals variable that will be injected into Datastar templates.
     */
    public string $signalsVariableName = 'signals';

    /**
     * The event options to override the Datastar defaults. Null values will be ignored.
     */
    public array $defaultEventOptions = [
        'retryDuration' => null,
    ];

    /**
     * The element options to override the Datastar defaults. Null values will be ignored.
     */
    public array $defaultElementOptions = [
        'useViewTransition' => null,
    ];

    /**
     * The signal options to override the Datastar defaults. Null values will be ignored.
     */
    public array $defaultSignalOptions = [
        'onlyIfMissing' => null,
    ];

    /**
     * The execute script options to override the Datastar defaults. Null values will be ignored.
     */
    public array $defaultExecuteScriptOptions = [
        'autoRemove' => null,
        'attributes' => null,
    ];
}
