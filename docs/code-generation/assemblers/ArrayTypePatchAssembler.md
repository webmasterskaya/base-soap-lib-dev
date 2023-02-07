# ArrayTypePatchAssembler

`ArrayTypePatchAssembler` применяется для исправления поведения в классах, которые реализуют поведение с объектом, как с массиовм.
Идеально подходит для применения с ArrayOf* свойствами.
Генератор поддерживает настройки `ArrayTypePatchAssemblerOptions`. Экземпляр класса настроек необходимо передать в конструктор ассемблера,
в качестве первого аргумента.

## Доступные параметры `ArrayTypePatchAssemblerOptions`

- `getterPatch` - Применять патч к геттерам (`false` по умолчанию);
- `getterOptions` - Параметры, с которыми применяется `GetterAssembler` (`null` по умолчанию). Должен быть экземпляром `GetterAssemblerOptions`;
- `setterPatch` - Применять патч к сеттерам (`false` по умолчанию);
- `setterOptions` - Параметры, с которыми применяется `SetterAssembler` (`null` по умолчанию). Должен быть экземпляром `SetterAssemblerOptions`;
- `immutableSetterPatch` - Применять патч к immutable сеттерам (`false` по умолчанию);
- `immutableSetterOptions` - Параметры, с которыми применяется `ImmutableSetterAssembler` (`null` по умолчанию). Должен быть экземпляром `ImmutableSetterAssemblerOptions`;
- `fluentSetterPatch` - Применять патч к fluent сеттерам (`false` по умолчанию);
- `fluentSetterOptions` - Параметры, с которыми применяется `FluentSetterAssembler` (`null` по умолчанию). Должен быть экземпляром `FluentSetterAssemblerOptions`;
- `iteratorPatch` - Применять патч к методам, реализуемым ассемблером `IteratorAssembler` (`true` по умолчанию);
- `countablePatch` - Применять патч к методам, реализуемым ассемблером `CountableAssembler` (`true` по умолчанию);
- `arrayAccessPatch` - Применять патч к методам, реализуемым ассемблером `ArrayAccessAssembler` (`true` по умолчанию);

> **Notice**
> Обратите внимание!
> Патч самостоятельно не применяет [`IteratorAssembler`](IteratorAssembler.md), [`CountableAssembler`](CountableAssembler.md) и [`ArrayAccessAssembler`](ArrayAccessAssembler.md)

> **Notice**
> Обратите внимание!
> Если не используется ни один из сеттеров, то патч самостоятельно применит [`SetterAssembler`](SetterAssembler.md) и патч к нему.

## Пример запуска генератора:
```php

// Параметры генератора геттеров
$getterOptions = (new GetterAssemblerOptions())
    ->withDocBlocks(true)
    ->withReturnType(true);

// Параметры генератора immutable сеттеров
$fluentSetterOptions = (new FluentSetterAssemblerOptions())
    ->withDocBlocks(true)
    ->withReturnType(true)
    ->withTypeHints(true);

// Параметры генератора fluent сеттеров
$immutableSetterOptions = (new ImmutableSetterAssemblerOptions())
    ->withDocBlocks(true)
    ->withReturnTypes(true)
    ->withTypeHints(true);
    
return Config::create()
    //...
    // Все классы будут иметь геттеры
    ->addRule(
        new Rules\AssembleRule(
            new GetterAssembler($getterOptions)
        )
    )
    // Все классы будут иметь immutable сеттеры 
    ->addRule(
        new Rules\AssembleRule(
            new ImmutableSetterAssembler($immutableSetterOptions)
        )
    )
    // Все классы будут иметь fluent сеттеры 
    ->addRule(
        new Rules\AssembleRule(
            new FluentSetterAssembler($fluentSetterOptions)
        )
    )
    // Применим патч только к классам, которые должны вести себя, как массив 
    ->addRule(
        new Rules\TypenameMatchesRule(
            new Rules\MultiRule([
                new Rules\AssembleRule(
                    new IteratorAssembler()
                ),
                new Rules\AssembleRule(
                    new CountableAssembler()
                ),
                new Rules\AssembleRule(
                    new ArrayAccessAssembler()
                ),
                new Rules\AssembleRule(
                    new ArrayTypePatchAssembler(
                        (new ArrayTypePatchAssemblerOptions())
                            ->withGetterPatch()
                            ->withGetterOptions($getterOptions)
                            ->withFluentSetterPatch()
                            ->withFluentSetterOptions($fluentSetterOptions)
                            ->withImmutableSetterPatch()
                            ->withImmutableSetterOptions($immutableSetterOptions)
                    )
                ),
                // Обязательно применяем TraversableFixAssembler
                new Rules\AssembleRule(new TraversableFixAssembler()),
            ]),
            '/^ArrayOf(.*)$/'
        )
    );
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
class ArrayOfBooks implements IteratorAggregate, Countable, ArrayAccess
{

    /**
     * @var Book[]
     */
    private $Book = null;

    private function asArrayOfBook()
    {
        if (!is_array($this->Book)) {
        	/** @noinspection PhpInvalidInstanceofInspection */
        	if ($this->Book instanceof Book) {
        		$this->Book = [$this->Book];
        	} else {
        		$this->Book = [];
        	}
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset) : bool
    {
        $this->asArrayOfBook();

        return is_array($this->Book) && isset($this->Book[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        $this->asArrayOfBook();

        return (is_array($this->Book) && isset($this->Book[$offset]))
        	? $this->Book[$offset]
        	: null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->asArrayOfBook();

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
        $this->asArrayOfBook();

        if(is_array($this->Book)) {
        	unset($this->Book[$offset]);
        }
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
        $this->asArrayOfBook();

        return is_array($this->Book) ? count($this->Book) : 0;
    }

    /**
     * @return ArrayIterator|Book[]
     * @phpstan-return ArrayIterator<array-key, Book>
     * @psalm-return ArrayIterator<array-key, Book>
     */
    public function getIterator()
    {
        $this->asArrayOfBook();

        return new ArrayIterator(is_array($this->Book) ? $this->Book : []);
    }

    /**
     * @return Book[]
     */
    public function getBook() : array
    {
        $this->asArrayOfBook();

        return $this->Book;
    }

    /**
     * @return $this
     * @param
     * \RZ\Integrations\Exchange1C\Type\Book|\RZ\Integrations\Exchange1C\Type\Book[]
     * $Book
     */
    public function setBook($Book) : ArrayOfBooks
    {
        if (!is_array($Book)) {
        	$Book = [$Book];
        }

        foreach ($Book as $BookItem) {
        	if (!($BookItem instanceof Book)) {
        		throw new InvalidArgumentException(
        			sprintf(
        				'The Book property can only contain items of \RZ\Integrations\Exchange1C\Type\Book type , %s given',
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
     * @return ArrayOfBooks
     * @param
     * \RZ\Integrations\Exchange1C\Type\Book|\RZ\Integrations\Exchange1C\Type\Book[]
     * $Book
     */
    public function withBook($Book) : ArrayOfBooks
    {
        if (!is_array($Book)) {
        	$Book = [$Book];
        }

        foreach ($Book as $BookItem) {
        	if (!($BookItem instanceof Book)) {
        		throw new InvalidArgumentException(
        			sprintf(
        				'The Book property can only contain items of \RZ\Integrations\Exchange1C\Type\Book type , %s given',
        				is_object($BookItem)
        					? get_class($BookItem)
        					: sprintf('%1$s(%2$s)', gettype($BookItem), var_export($BookItem, true))
        			)
        		 );
        	}
        }

        $new = clone $this;
        $new->Book = $Book;

        return $new;
    }


}
```