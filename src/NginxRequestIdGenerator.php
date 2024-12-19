<?php

namespace khovanskiy\yii2requestid;

use yii\base\Component;

class NginxRequestIdGenerator extends Component implements RequestIdGenerator
{
    public function generateRequestId(): string
    {
        $randomBytes = random_bytes(16);
        return bin2hex($randomBytes);
    }
}