# SetterAssembler

`SetterAssembler` добавит сеттер к сгенерированному классу.
Генератор поддерживает настройки `SetterAssemblerOptions`. Экземпляр класса настроек необходимо передать в конструктор ассемблера,
в качестве первого аргумента.

## Доступные параметры `SetterAssemblerOptions`
- `typeHints` - Указать типы аргументов (`false` по умолчанию)
- `docBlocks` - Добавить PHPdoc к методу (`true` по умолчанию)

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new SetterAssembler(
        (new SetterAssemblerOptions())
            ->withDocBlocks(false) // Не генерировать PHPdoc
            ->withTypeHints() // Указать типы аргументов
    )));
    //...
```

## Пример сгенерированного кода:
```php
class SomeClass{
    private $prop1;
    
    public function setProp1(string $prop1)
    {
        $this->prop1 = $prop1;
    }
}
```