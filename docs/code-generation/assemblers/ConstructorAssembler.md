# ConstructorAssembler

`ConstructorAssembler` добавляет конструктор, со всеми свойствами класса, в сгенерированный класс.
Генератор поддерживает настройки `ConstructorAssemblerOptions`. Экземпляр класса настроек необходимо передать в конструктор ассемблера, 
в качестве первого аргумента.

## Доступные параметры `ConstructorAssemblerOptions`
- `typeHints` - Указать типы аргументов (`false` по умолчанию)
- `docBlocks` - Добавить PHPdoc к методу (`true` по умолчанию)
- `nullableParams` - Установить `null`, как значение по умолчанию для всех аргументов метода (`false` по умолчанию)

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new ConstructorAssembler(
        (new ConstructorAssemblerOptions())
            ->withDocBlocks(false) // Не генерировать PHPdoc
            ->withTypeHints() // Указать типы аргументов
            ->withNullableParams() // Разрешить `null` для аргументов
    )));
    //...
```

## Пример сгенерированного кода:
```php
    //...
    public function __construct(?string $prop1 = null, ?int $prop2 = null)
    {
        $this->prop1 = $prop1;
        $this->prop2 = $prop2;
    }
    //...
```