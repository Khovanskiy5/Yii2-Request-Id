# Yii2 Request ID

![Yii2](https://img.shields.io/badge/Yii2-%5E2.0-brightgreen.svg)
![PHP](https://img.shields.io/badge/PHP-%5E7.4-blue.svg)
![License](https://img.shields.io/badge/license-BSD%203--Clause-yellow.svg)

**Yii2 Request ID** — это удобный компонент для фреймворка Yii2, который автоматически генерирует и обрабатывает уникальные идентификаторы запросов (Request ID). Эти идентификаторы помогают отслеживать и логировать запросы как в веб-приложениях, так и в консольных командах, обеспечивая лучшую трассировку и отладку.

Содержание
----------

1. [Особенности](#особенности)
2. [Установка](#установка)
3. [Настройка](#настройка)
4. [Использование](#использование)
5. [Примеры](#примеры)
6. [Пользовательские генераторы Request ID](#пользовательские-генераторы-request-id)
7. [Требования](#требования)
8. [Лицензия](#лицензия)
9. [Авторы](#авторы)
10. [Поддержка](#поддержка)

Особенности
-----------

- **Автоматическая генерация** уникального идентификатора для каждого HTTP-запроса и консольной команды.
- **Передача идентификатора** через заголовок `X-Request-ID` в HTTP-ответах.
- **Доступ к идентификатору** через сервис `RequestIdService` в любом месте приложения.
- **Поддержка консольных команд** с выводом идентификатора в консоль и логах.
- **Возможность расширения** и использования собственных генераторов идентификаторов.

 Установка
---------

### Требования

- **PHP:** >= 7.4
- **Yii2:** >= 2.0.45

### Шаг 1: Установка через Composer

Для установки пакета выполните следующую команду в корневой директории вашего проекта Yii2 Advanced:

```bash
composer require khovanskiy/yii2-request-id
```
### Установка локального пакета (опционально)

Если вы разрабатываете пакет локально и хотите подключить его без публикации, выполните следующие шаги:

1. **Добавьте репозиторий в `composer.json` основного проекта:**

```json
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://asset-packagist.org"
    },
    {
      "type": "path",
      "url": "../yii2-request-id",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

**Примечание:** Убедитесь, что путь `"../yii2-request-id"` указывает на директорию вашего локального пакета.

2. **Установите пакет с указанием ветки разработки:**

```bash
composer require khovanskiy/yii2-request-id:dev-main
```

**Важно:** Если в вашем пакете используется нестабильная версия (`dev-main`), убедитесь, что в `composer.json` основного проекта установлено `"prefer-stable": true`.

Настройка
---------

После установки необходимо настроить компонент в вашем приложении Yii2.

### Шаг 1: Конфигурация Yii2

Откройте конфигурационный файл вашего приложения (`common/config/main.php`, `backend/config/main.php` или `frontend/config/main.php` в зависимости от структуры вашего проекта) и добавьте следующие настройки:

```php 
<?php

use khovanskiy\yii2requestid\NginxRequestIdGenerator;
use khovanskiy\yii2requestid\RequestIdGenerator;
use khovanskiy\yii2requestid\RequestIdBootstrap;
use khovanskiy\yii2requestid\RequestIdLogFormatter;

return [
    // Другие настройки...
    'container' => [
        'singletons' => [
            RequestIdGenerator::class => NginxRequestIdGenerator::class,
        ],
    ],
    // Другие настройки...
];
```

**Пояснения:**

- **bootstrap:** Добавляет класс `RequestIdBootstrap` в процесс загрузки приложения, что обеспечивает инициализацию компонента.
- **container.singletons:** Определяет реализацию интерфейса `RequestIdGenerator`. В данном случае используется стандартный генератор `NginxRequestIdGenerator`, который генерирует 32-символьные шестнадцатеричные строки.

### Шаг 2: Настройка `composer.json` (для локальной установки)

Если вы используете локальную установку пакета, убедитесь, что в `composer.json` вашего основного проекта прописаны правильные настройки автозагрузки и репозитория:
```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        },
        {
            "type": "path",
            "url": "../yii2-request-id",
            "options": {
                "symlink": true,
                "replace": {
                    "khovanskiy/yii2-request-id": "1.0.0"
                }
            }
        }
    ],
    "require": {
        // Другие зависимости...
        "khovanskiy/yii2-request-id": "1.0.0"
    },
    "config": {
        "prefer-stable": true,
        "allow-plugins": {
            "yiisoft/yii2-composer": true
        },
        "process-timeout": 1800,
        "fxp-asset": {
            "enabled": false
        }
    }
}
```
**Примечание:** Если вы изменили `minimum-stability`, убедитесь, что она соответствует вашим требованиям.

Использование
-------------

После настройки компонент автоматически начнёт генерировать Request ID для каждого запроса и консольной команды. Вы можете получить доступ к текущему Request ID через сервис `RequestIdService`.

### Доступ к Request ID
```php
<?php

use khovanskiy\yii2requestid\RequestIdService;

// Получение сервиса через DI-контейнер
$requestIdService = Yii::$app->get(RequestIdService::class);

// Устанавливает или возвращает текущий Request ID.
// Если ID ещё не задан, будет сгенерирован новый.
// При необходимости можно передать собственный Request ID.
$requestIdService->setRequestId(); 
$requestIdService->setRequestId('Test_request_id'); 
$currentRequestId = $requestIdService->getRequestId();

echo "Текущий Request ID: " . $currentRequestId;
```

### Получение Request ID в консольных командах

При выполнении консольных команд Request ID будет автоматически сгенерирован и выведен в консоль, а также записан в логи.

Примеры
-------

### Веб-приложение

При каждом HTTP-запросе:

1. Компонент проверяет наличие заголовка `X-Request-ID`.
2. Если заголовок отсутствует, генерируется новый Request ID.
3. Request ID сохраняется в `Yii::$app->params['request_id']`.
4. В ответ добавляется заголовок `X-Request-ID` с текущим Request ID.
5. В логах записываются входящий запрос и его Request ID.

### Консольная команда

При запуске команды:

1. Генерируется новый Request ID.
2. Request ID выводится в консоль и сохраняется в `Yii::$app->params['request_id']`.
3. В логах фиксируется начало и завершение выполнения команды с соответствующим Request ID.

Пользовательские генераторы Request ID
-------------------------------------

Вы можете использовать собственные генераторы Request ID, реализовав интерфейс `RequestIdGenerator`.

### Шаг 1: Создайте класс генератора

```php
<?php

namespace app\components\RequestId;

use khovanskiy\yii2requestid\RequestIdGenerator;

class CustomRequestIdGenerator implements RequestIdGenerator
{
    public function generateRequestId(): string
    {
        // Ваш собственный алгоритм генерации Request ID
        return uniqid('req_', true);
    }
}
```

### Шаг 2: Зарегистрируйте генератор в контейнере

В конфигурационном файле (`main.php`) замените стандартный генератор на ваш собственный:

```php
<?php
use khovanskiy\yii2requestid\RequestIdGenerator;
use app\components\RequestId\CustomRequestIdGenerator;
return [
    // Другие настройки...
    'container' => [
        'singletons' => [
            RequestIdGenerator::class => CustomRequestIdGenerator::class,
        ],
    ],
    // Другие настройки...
];
```

Требования
----------

- **PHP:** >= 7.4
- **Yii2:** >= 2.0.45

Лицензия
--------

Этот проект распространяется под лицензией BSD-3-Clause.

Авторы
------

- **Khovanskiy** — [khovanskiy](https://github.com/khovanskiy5)

Поддержка
---------

Если у вас возникли вопросы или вы хотите предложить улучшения, пожалуйста, создайте issue на GitHub: https://github.com/khovanskiy5/Yii2-Request-Id/issues

---

**Спасибо за использование Yii2 Request ID!** Надеемся, что этот компонент поможет вам в улучшении трассировки и логирования ваших приложений на Yii2.
