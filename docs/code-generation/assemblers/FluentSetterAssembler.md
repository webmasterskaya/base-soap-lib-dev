# FluentSetterAssembler

`FluentSetterAssembler` добавит сеттер к сгенерированному классу. Метод вернет текущий экземпляр класса, для удобства реализации Fluent interface.
Генератор поддерживает настройки `FluentSetterAssemblerOptions`. Экземпляр класса настроек необходимо передать в конструктор ассемблера,
в качестве первого аргумента.

## Доступные параметры `FluentSetterAssemblerOptions`
- `typeHints` - Указать типы аргументов (`false` по умолчанию)
- `returnType` - Указывать тип возвращаемого значения (`false` по умолчанию)
- `docBlocks` - Добавить PHPdoc к методу (`true` по умолчанию)

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new FluentSetterAssembler(
        (new FluentSetterAssemblerOptions())
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
        $this->prop1 = $prop1;
        return $this;
    }
}
```