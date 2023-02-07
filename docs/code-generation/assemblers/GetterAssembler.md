# GetterAssembler

`GetterAssembler` добавит геттер к сгенерированному классу.
Генератор поддерживает настройки `GetterAssemblerOptions`.
Экземпляр класса настроек необходимо передать в конструктор ассемблера, в качестве первого аргумента.

## Доступные параметры `GetterAssemblerOptions`
- `boolGetters` - использовать префикс функции «is» вместо «get» для типов `bool` (`false` по умолчанию)
- `returnType` - Указывать тип возвращаемого значения (`false` по умолчанию)
- `docBlocks` - Добавить PHPdoc к методу (`true` по умолчанию)

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new GetterAssembler(
        (new GetterAssemblerOptions())
            ->withDocBlocks(false) // Не генерировать PHPdoc
            ->withBoolGetters() // использовать префикс функции «is» вместо «get» для типов `bool`
            ->withReturnType() // Указать тип возвращаемого значения
    )));
    //...
```

## Пример сгенерированного кода:
```php
class SomeClass{
    
    /** 
     * @var string 
     */
    private $prop1;
    
    /** 
     * @var bool 
     */
    private $prop2;
    
    public function getProp1(): string
    {
        return $this->prop1;
    }
    
    public function isProp2(): bool
    {
        return $this->prop2;
    }
}
```
