# TraversableFixAssembler

Если класс одновременно реализует интерфейсы `\IteratorAggregate`, `\ArrayAccess` и `\Countable`, 
`laminas-code` добавит в список реализуемых интерфейсов `\Traversable`, что приведёт к Fatal error, при запуске кода.
`TraversableFixAssembler` исправляет эту ошибку и удаляет `\Traversable` из `implements` класса.

## Пример запуска генератора:
```php
    //...
    ->addRule(
        new Rules\MultiRule([
            // Класс одновременно реализует интерфейсы `\IteratorAggregate`, `\ArrayAccess` и `\Countable`
            new Rules\AssembleRule(new Assembler\IteratorAssembler()),
            new Rules\AssembleRule(new Assembler\ArrayAccessAssembler()),
            new Rules\AssembleRule(new Assembler\CountableAssembler()),
            // После применения интерфейсов, удаляем `\Traversable`
            new Rules\AssembleRule(new Assembler\TraversableFixAssembler()),
        ])
    )
    //...
```