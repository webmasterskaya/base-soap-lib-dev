# ArrayPropertyPatchAssembler

`ArrayPropertyPatchAssembler` применяется для исправления поведения сеттеров в классах, когда нужно добавить возможность
работать со свойством, которое является сложным типом, как с массивом.
Идеально подходит для применения с ArrayOf* свойствами.
Ассемблеру необходимо передавать обязательный аргумент `metadata`, для того, чтобы он мог определить связанный со свойством тип данных.

> **Warning**
> Целевой класс (класс типа свойства) обязательно должен реализовать сеттер для свойства.

## Пример запуска генератора:
```php
    //...
    // Добавляем immutable сеттеры
    ->addRule(
        new Rules\AssembleRule(
            new Assembler\ImmutableSetterAssembler(
                (new Assembler\ImmutableSetterAssemblerOptions())
                    ->withDocBlocks()
                    ->withReturnTypes()
                    ->withTypeHints()
            )
        )
    )
    // Патчим сеттеры свойств, тип которых начинается с фразы `ArrayOf`
    ->addRule(
            new PropertyTypeMatchesRule(
                new Rules\AssembleRule(
                    new Assembler\ArrayPropertyPatchAssembler($engine->getMetadata())
                ),
                '/^ArrayOf(.*)$/'
            )
        );
    //...
```

```xml
<!-- -->
<xs:schema>
    <xs:complexType name="ArrayOfBooks">
        <xs:sequence>
            <xs:element name="Book"
                        type="tns:Book"
                        nillable="true"
                        minOccurs="0"
                        maxOccurs="unbounded"/>
        </xs:sequence>
    </xs:complexType>
    <xs:complexType name="Bookshelf">
        <xs:sequence>
            <xs:element name="Name"
                        type="xs:string"
                        nillable="true"/>
            <xs:element name="Books"
                        type="tns:ArrayOfBooks"
                        nillable="true"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>
<!-- -->
```

## Пример сгенерированного кода:
```php
class Bookshelf
{
    /**
     * @var string
     */
    private $Name = null;

    /**
     * @var Type\ArrayOfBooks
     */
    private $Books = null;

    /**
     * @param string $FullName
     * @return $this
     */
    public function setName(string $Name) : Bookshelf
    {
        $this->Name = $Name;
        return $this;
    }
    
    /**
     * @param Type\ArrayOfBooks|Type\Book[] $Books
     * @return $this
     */
    public function setBooks($Books) : Bookshelf
    {
        if (!($Accounts instanceof Type\ArrayOfBooks)) {
        	$arrayOfBooks = new Type\ArrayOfBooks();
        	$arrayOfBooks->setBook($Books);
        	$Books = $arrayOfBooks;
        }

        $this->Books = $Books;
        return $this;
    }
}
```

Без применеия патча, вышеприведенный код выглядел бы следующим образом (сравните метод `setBooks`):
```php
class Bookshelf
{
    /**
     * @var string
     */
    private $Name = null;

    /**
     * @var Type\ArrayOfBooks
     */
    private $Books = null;

    /**
     * @param string $FullName
     * @return $this
     */
    public function setName(string $Name) : Bookshelf
    {
        $this->Name = $Name;
        return $this;
    }
    
    /**
     * @param Type\ArrayOfBooks $Books
     * @return $this
     */
    public function setBooks(Type\ArrayOfBooks $Books) : Bookshelf
    {
        $this->Books = $Books;
        return $this;
    }
}
```