# ImmutableSetterAssembler

`ImmutableSetterAssembler` добавит сеттер к сгенерированному классу. Метод вернет новый экземпляр класса, для удобства реализации Immutable interface.
Генератор поддерживает настройки `ImmutableSetterAssemblerOptions`. Экземпляр класса настроек необходимо передать в конструктор ассемблера,
в качестве первого аргумента.

## Доступные параметры `ImmutableSetterAssemblerOptions`
- `typeHints` - Указать типы аргументов (`false` по умолчанию)
- `returnType` - Указывать тип возвращаемого значения (`false` по умолчанию)
- `docBlocks` - Добавить PHPdoc к методу (`true` по умолчанию)

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new ImmutableSetterAssembler(
        (new ImmutableSetterAssemblerOptions())
            ->withDocBlocks(false) // Не генерировать PHPdoc
            ->withTypeHints() // Указать типы аргументов
            ->withReturnType() // Указать тип возвращаемого значения
    )));
    //...
```

## Пример сгенерированного кода:
```php
class SomeClass{
    private $prop1;
    
    public function setProp1(string $prop1): SomeClass
    {
        $new = clone $this;
        $new->prop1 = $prop1;
        
        return $new;
    }
}
```