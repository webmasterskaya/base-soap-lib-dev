# CountableAssembler

`CountableAssembler` сгенерирует класс SOAP-типа, который будет реализовать интерфейс `\Countable`.
Применяется к первому (подразумевается, что к единственному) свойству объекта, обеспечивая возможность использовать функцию count() с объектом.

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new CountableAssembler()));
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
use Countable;

class ArrayOfBooks implements Countable
{

    /**
     * @var Book
     */
    private $Book = null;

    /**
     * Count elements of an object
     *
     * The return value is cast to an integer.
     *
     * @return int
     * @link https://php.net/manual/en/countable.count.php
     */
    public function count() : int
    {
        return is_array($this->Book) ? count($this->Book) : 0;
    }


}
```