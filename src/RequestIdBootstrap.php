<?php

namespace khovanskiy\yii2requestid;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\web\Application as WebApplication;
use yii\console\Application as ConsoleApplication;

class RequestIdBootstrap implements BootstrapInterface
{
    private RequestIdService $requestIdService;
    private RequestIdGenerator $requestIdGenerator;
    private string $headerName;

    /**
     * Конструктор класса.
     *
     * @param RequestIdService $requestIdService Сервис для управления Request ID.
     * @param RequestIdGenerator $requestIdGenerator Генератор Request ID.
     * @param string $headerName Название заголовка для Request ID.
     */
    public function __construct(
        RequestIdService $requestIdService,
        RequestIdGenerator $requestIdGenerator,
        string $headerName = 'X-Request-ID'
    ) {
        $this->requestIdService = $requestIdService;
        $this->requestIdGenerator = $requestIdGenerator;
        $this->headerName = $headerName;
    }

    /**
     * Метод инициализации Bootstrap.
     *
     * @param Application $app Приложение Yii.
     */
    public function bootstrap($app): void
    {
        if ($app instanceof WebApplication) {
            $this->bootstrapWebApplication($app);
        } elseif ($app instanceof ConsoleApplication) {
            $this->bootstrapConsoleApplication($app);
        }
    }

    /**
     * Инициализация для веб-приложения.
     *
     * @param WebApplication $app Веб-приложение Yii.
     */
    private function bootstrapWebApplication(WebApplication $app): void
    {
        // Перед обработкой запроса
        Event::on(WebApplication::class, Application::EVENT_BEFORE_REQUEST, function () {
            $request = Yii::$app->request;
            $requestId = $request->getHeaders()->get($this->headerName)
                ?? $this->requestIdGenerator->generateRequestId();
            $this->requestIdService->setRequestId($requestId);
            Yii::debug("Incoming request with ID: {$requestId}", __METHOD__);
        });

        // После обработки запроса
        Event::on(WebApplication::class, Application::EVENT_AFTER_REQUEST, function () {
            $response = Yii::$app->response;
            $response->getHeaders()->set($this->headerName, $this->requestIdService->getRequestId());
        });
    }

    /**
     * Инициализация для консольного приложения.
     *
     * @param ConsoleApplication $app Консольное приложение Yii.
     */
    private function bootstrapConsoleApplication(ConsoleApplication $app): void
    {
        // Перед выполнением действия
        Event::on(ConsoleApplication::class, ConsoleApplication::EVENT_BEFORE_ACTION, function ($event) {
            $requestId = $this->requestIdGenerator->generateRequestId();
            $this->requestIdService->setRequestId($requestId);
            $commandName = $this->getCommandName($event->action->uniqueId);

            Yii::info("Executing command: {$commandName} with Request ID: {$requestId}", __METHOD__);
            Yii::debug("Start command '{$commandName}' with ID: {$requestId}", __METHOD__);
        });

        // После выполнения действия
        Event::on(ConsoleApplication::class, ConsoleApplication::EVENT_AFTER_ACTION, function ($event) {
            $commandName = $this->getCommandName($event->action->uniqueId);
            $requestId = $this->requestIdService->getRequestId();

            Yii::debug("Finish command '{$commandName}' with ID: {$requestId}", __METHOD__);
        });
    }

    /**
     * Получение имени команды из уникального идентификатора действия.
     *
     * @param string $uniqueId Уникальный идентификатор действия.
     * @return string Имя команды.
     */
    private function getCommandName(string $uniqueId): string
    {
        $baseCommand = explode('/', $uniqueId)[0] ?? '';
        return $baseCommand ? 'php yii ' . $baseCommand : 'php yii';
    }
}