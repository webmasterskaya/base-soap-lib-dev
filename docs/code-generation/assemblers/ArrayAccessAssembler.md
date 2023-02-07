# ArrayAccessAssembler

`ArrayAccessAssembler` сгенерирует класс SOAP-типа, который будет реализовать интерфейс `\ArrayAccess`.
Применяется к первому (подразумевается, что к единственному) свойству объекта, обеспечивая доступ к объекту, как к массиву.

> **Notice**
> Обратите внимание на метод `offsetSet` в сгенерированногом коде. При установке значения в объект, как в массив, будет проверен 
> не только тип переданных данных, но и тип данных уже хранящихся в объекте, и выброшено исключение `\InvalidArgumentException` или `\RuntimeException`

## Пример запуска генератора:
```php
    //...
    ->addRule(new Rules\AssembleRule(new ArrayAccessAssembler()));
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
class ArrayOfBooks implements ArrayAccess
{

    /**
     * @var Book
     */
    private $Book = null;

    /**
     * @inheritDoc
     */
    public function offsetExists($offset) : bool
    {
        return is_array($this->Book) && isset($this->Book[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return (is_array($this->Book) && isset($this->Book[$offset]))
        	? $this->Book[$offset]
        	: null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Book)) {
        	throw new InvalidArgumentException(
        		sprintf(
        			'The Book property can only contain items of type \RZ\Integrations\Exchange1C\Type\Book, %s given',
        			is_object($value)
        				? get_class($value)
        				: sprintf('%1$s(%2$s)', gettype($value), var_export($value, true))
        		)
        	);
        }

        if(!is_array($this->Book) && !is_null($this->Book)) {
        	/** @noinspection PhpParamsInspection */
        	throw new RuntimeException(
        		sprintf(
        			'The property ArrayOfBooks::$Book must be array, %s given',
        			is_object($this->Book)
        				? get_class($this->Book)
        				: sprintf('%1$s(%2$s)', gettype($this->Book), var_export($this->Book, true))
        		)
        	);
        }

        if (is_null($offset)) {
        	$this->Book[] = $value;
        } else {
        	$this->Book[$offset] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        if(is_array($this->Book)) {
        	unset($this->Book[$offset]);
        }
    }


}
```