# IteratorAssembler

`IteratorAssembler` сгенерирует класс SOAP-типа, который будет реализовать интерфейс `\IteratorAggregate`.
Применяется к первому (подразумевается, что к единственному) свойству объекта для создания внешнего итератора.

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new IteratorAssembler()));
    //...
```

## Пример XSD схемы:
```xml
<!-- -->
<xs:complexType name="ArrayOfBooks">
    <xs:sequence>
        <xs:element name="Book"
                    type="tns:Book"
                    nillable="true"
                    minOccurs="0"
                    maxOccurs="unbounded"/>
    </xs:sequence>
</xs:complexType>
<!-- -->
```

## Пример сгенерированного кода:
```php
class ArrayOfBooks implements IteratorAggregate
{

    /**
     * @var Book
     */
    private $Book = null;

    /**
     * @return ArrayIterator|Book[]
     * @phpstan-return ArrayIterator<array-key, Book>
     * @psalm-return ArrayIterator<array-key, Book>
     */
    public function getIterator()
    {
        return new ArrayIterator(is_array($this->Book) ? $this->Book : []);
    }


}
```