# Генераторы (ассемблеры) кода

Ассемблер кода представляет собой прослойку над [laminas-code](https://github.com/laminas/laminas-code).

Как создавать свои собственные ассемблеры, читайте [тут](https://github.com/phpro/soap-client/blob/master/docs/code-generation/assemblers.md#creating-your-own-assembler).

# Список генераторов

- [ArrayAccessAssembler](#arrayaccessassembler)
- [IteratorAssembler](#iteratorassembler)
- [CountableAssembler](#countableassembler)
- [ArrayTypePatchAssembler](#arraytypepatchassembler)
- [ArrayPropertyPatchAssembler](#arraypropertypatchassembler)
- [ArrayPropertyAssembler](#arraypropertyassembler) `deprecated`

## ArrayAccessAssembler

`ArrayAccessAssembler` сгенерируеи класс SOAP-типа, который будет реализовать интерфейс `\ArrayAccess`.
Применяется к первому (подразумевается, что к единственному) свойству объекта, обеспечивая доступ к объекту, как к массиву.

## IteratorAssembler

IteratorAssembler doc



## CountableAssembler

CountableAssembler doc

## ArrayTypePatchAssembler

ArrayTypePatchAssembler doc

## ArrayPropertyPatchAssembler

ArrayPropertyPatchAssembler doc


## ArrayPropertyAssembler

> **Warning**
> Не используйте этот ассемблер! Мы отказываемся от его использования из-за отсутсвия гибкости и недостаточной функциональности.
> Вместо этого используйте различные комбинации из - [IteratorAssembler](#iteratorassembler), [ArrayAccessAssembler](#arrayaccessassembler), [CountableAssembler](#countableassembler), [ArrayTypePatchAssembler](#arraytypepatchassembler), [ArrayPropertyPatchAssembler](#arraypropertypatchassembler)
 
`ArrayPropertyAssembler` хорошо подходит для SOAP типов данных, которые содержат повторяющееся перечисление других типов.
Этот ассемблер даёт возможность работать с такими перечислениями, как с обычным php массивом.
Сгенерированный класс типа будет реализовать интерфейсы `\IteratorAggregate`, `\ArrayAccess`, `\Countable`

Пример вызова генератора:
```php
/**
* Для всех типов, название которых начинается с фразы `ArrayOf` применяем `ArrayPropertyAssembler`
 */
    //...
    ->addRule(
            new Rules\AssembleRule(new ArrayAccessAssembler()),
        );
    //...
```

Пример XSD схемы:
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

Пример сгенерированног кода:
```php
use IteratorAggregate;
use ArrayAccess;
use Countable;

class ArrayOfBooks implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var Type\Book[]
     */
    private $Book;
    
    /**
     * @return Type\Book[]
     */
    public function getBook() : array
    {
        $this->bookAsArray();

        return $this->Book;
    }
    
    /**
     * Set the Book property value.
     *
     * The method will set the Book property and will return a new instance with the
     * new value set. Used to create variations of the same base instance, without
     * modifying the original values.
     *
     * @param Type\Book|Type\Book[] $Book
     * @return ArrayOfBooks
     */
    public function withBook($Book) : Type\ArrayOfBooks
    {
        $new = clone $this;
        $new->setBook($Book);

        return $new;
    }

    /**
     * Set the Book property value.
     *
     * The method will set the Book property and will return the current instance to
     * enable chaining.
     *
     * @param Type\Book|Type\Book[] $Book
     * @return ArrayOfBooks
     */
    public function setBook($Book) : Type\ArrayOfBooks
    {
        if (!is_array($Book)) {
        	$Book = [$Book];
        }

        foreach ($Book as $BookItem) {
        	if (!($BookItem instanceof Type\Book)) {
        		throw new \InvalidArgumentException(
        			sprintf(
        				'The Book property can only contain items of Type\Book type , %s given',
        				is_object($BookItem)
        					? get_class($BookItem)
        					: sprintf('%1$s(%2$s)', gettype($BookItem), var_export($BookItem, true))
        			)
        		 );
        	}
        }

        $this->Book = $Book;

        return $this;
    }

    /**
     * Retrieve an external iterator
     *
     * @return \ArrayIterator An instance of an object implementing <b>Iterator</b>
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator() : \ArrayIterator
    {
        $this->bookAsArray();

        return new \ArrayIterator($this->Book);
    }

    /**
     * Whether a offset exists
     *
     * The return value will be casted to boolean if non-boolean was returned.
     *
     * @param mixed $offset An offset to check for.
     * @return bool true on success or false on failure.
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset) : bool
    {
        $this->bookAsArray();

        return is_array($this->Book) && isset($this->Book[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        $this->bookAsArray();

        return (is_array($this->Book) && isset($this->Book[$offset]))
        	? $this->Book[$offset]
        	: null;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return void
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Type\Book)) {
        	throw new \InvalidArgumentException(
        		sprintf(
        			'The Book property can only contain items of type Type\Book, %s given',
        			is_object($value)
        				? get_class($value)
        				: sprintf('%1$s(%2$s)', gettype($value), var_export($value, true))
        		)
        	);
        }

        $this->bookAsArray();

        if (is_null($offset)) {
        	$this->Book[] = $value;
        } else {
        	$this->Book[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     * @return void
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        $this->bookAsArray();

        unset($this->Book[$offset]);
    }

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
        $this->bookAsArray();

        return is_array($this->Book) ? count($this->Book) : 0;
    }

    private function bookAsArray()
    {
        if (!is_array($this->Book)) {
        	if ($this->Book instanceof Type\Book) {
        		$this->Book = [$this->Book];
        	} else {
        		$this->Book = [];
        	}
        }
    }
    
    
}
```

Пример запуска генератора:
```php
/**
* Для всех типов, название которых начинается с фразы `ArrayOf` применяем `ArrayPropertyAssembler`
 */
    //...
    ->addRule(
            new Rules\TypenameMatchesRule(
                new Rules\MultiRule([
                    new Rules\AssembleRule(new ArrayPropertyAssembler()),
                ]),
                '/^ArrayOf(.*)$/'
            )
        );
    //...
```