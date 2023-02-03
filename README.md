# Набор инструментов для генерации кода PHP-SOAP приложения

Инструмент содержит набор классов для настройки генерации кода [`phpro/soap-client`](https://github.com/phpro/soap-client).

> **Note**
> Для обеспечения совместимасти с PHP >= 7.1 используйте `phpro/soap-client:~v1`
 
## Набор классов:

- [Правила генерации `Rules`](/docs/code-generation/RULES.md)
- [Генераторы кода `Assemblers`](/docs/code-generation/ASSEMBLERS.md)

## Установка

Через composer:
```shell
composer require webmasterskaya/base-soap-lib-dev
```

В проекте должен присутсвовать `phpro/soap-client`. Если его нет, то установите его из composer
```shell
composer require phpro/soap-client
```

## Настройка и запуск

Все настройки, инструкции и доступные команды описаны [тут](https://github.com/phpro/soap-client#getting-your-soap-integration-up-and-running).