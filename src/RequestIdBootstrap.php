<?php

namespace khovanskiy\yii2requestid;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\Module;
use yii\web\Application as WebApplication;
use yii\console\Application as ConsoleApplication;

class RequestIdBootstrap implements BootstrapInterface
{
    private RequestIdService $requestIdService;
    private RequestIdGenerator $requestIdGenerator;

    public function __construct(RequestIdService $requestIdService, RequestIdGenerator $requestIdGenerator)
    {
        $this->requestIdService = $requestIdService;
        $this->requestIdGenerator = $requestIdGenerator;
    }

    public function bootstrap($app)
    {
        if ($app instanceof WebApplication) {
            Event::on(WebApplication::class, Application::EVENT_BEFORE_REQUEST, function() {
                $request = Yii::$app->request;
                $requestId = $request->headers->get('X-Request-ID', $this->requestIdGenerator->generateRequestId());
                $this->requestIdService->setRequestId($requestId);
                Yii::debug("Incoming request with ID: {$this->requestIdService->getRequestId()}", __METHOD__);
            });

            Event::on(WebApplication::class, Application::EVENT_AFTER_REQUEST, function() {
                $response = Yii::$app->response;
                $response->headers->set('X-Request-ID', $this->requestIdService->getRequestId());
            });
        }

        if ($app instanceof ConsoleApplication) {
            Event::on(ConsoleApplication::class, Module::EVENT_BEFORE_ACTION, function($event) use ($app) {
                $requestId = $this->requestIdGenerator->generateRequestId();
                $this->requestIdService->setRequestId($requestId);
                $uniqueIdParts = explode('/', $event->action->uniqueId);
                $baseCommand = $uniqueIdParts[0] ?? '';

                $commandName = $baseCommand ? 'php yii ' . $baseCommand : 'php yii';
                echo "Executing command: " . $commandName . PHP_EOL;
                echo "Request ID: " . $this->requestIdService->getRequestId() . PHP_EOL;
                Yii::debug("Start command '{$commandName}' with ID: {$this->requestIdService->getRequestId()}", __METHOD__);
            });

            Event::on(ConsoleApplication::class, Module::EVENT_AFTER_ACTION, function($event) {
                $uniqueIdParts = explode('/', $event->action->uniqueId);
                $baseCommand = $uniqueIdParts[0] ?? '';
                $commandName = $baseCommand ? 'php yii ' . $baseCommand : 'php yii';

                Yii::debug("Finish command '{$commandName}' with ID: {$this->requestIdService->getRequestId()}", __METHOD__);
            });
        }
    }
}