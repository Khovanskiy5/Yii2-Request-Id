<?php


namespace khovanskiy\yii2requestid;

use Yii;
use Exception;

class RequestIdService
{
    protected RequestIdGenerator $requestIdGenerator;

    public function __construct(RequestIdGenerator $requestIdGenerator)
    {
        $this->requestIdGenerator = $requestIdGenerator;
    }

    public function setRequestId(?string $requestId = null): void
    {
        Yii::$app->params['request_id'] = $requestId ?? $this->requestIdGenerator->generateRequestId();
    }

    public function getRequestId(): string
    {
        if (!isset(Yii::$app->params['request_id']) || !is_string(Yii::$app->params['request_id'])) {
            $this->setRequestId();
        }

        return Yii::$app->params['request_id'];
    }
}