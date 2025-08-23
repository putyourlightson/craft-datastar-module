<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\dumpers;

use putyourlightson\datastar\Datastar;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class SseDumper extends HtmlDumper
{
    public function dump(Data $data, $output = null, array $extraDisplayOptions = []): ?string
    {
        $result = parent::dump($data, true, $extraDisplayOptions);

        Datastar::getInstance()->sse->dump($result);

        return $result;
    }
}
