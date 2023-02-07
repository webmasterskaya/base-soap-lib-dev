# ClassMapAssembler

`ClassMapAssembler` используется во время выполнения команды `generate:classmap`. 
По умолчанию активируется `Phpro\SoapClient\CodeGenerator\Assembler\ClassMapAssembler`.
Если вам нужен код, совместимый, с `webmasterskaya/base-soap-lib`, необходимо заменить стандартный ассемблер в конфигурационном файле.
Для корректной работы ассемблера в конфигурационном файле обязательно должны быть заданы параметры `setClassMapName`, `setClassMapNamespace`, `setClassMapDestination`. 


## Пример запуска генератора:
```php
    //...
    ->setClassMapName('MyClassMapCollection')
    ->setClassMapNamespace('Vendor\Package')
    ->setClassMapDestination('./src')
    /**
    * Необходимо "обнулить" стандартный RulesSet.
    * Будьте осторожны! 
    * Это отключит стандартное поведение для `ClassMapAssembler`, и `PropertyAssembler` 
    */
    ->setRuleSet(new RuleSet([])) 
    ->addRule(
        new Rules\AssembleRule(
            new Assembler\ClassMapAssembler()
        )
    )
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

## Пример сгенерированног кода:
```php
namespace Vendor\Package;

use Vendor\Package\Type;
use Soap\ExtSoapEngine\Configuration\ClassMap\ClassMapCollection;
use Soap\ExtSoapEngine\Configuration\ClassMap\ClassMap;
use Webmasterskaya\Soap\Base\Soap\ExtSoap\Configuration\ClientClassMapCollectionInterface;

class MyClassMapCollection implements ClientClassMapCollectionInterface
{

    public function __invoke() : \Soap\ExtSoapEngine\Configuration\ClassMap\ClassMapCollection
    {
        return new ClassMapCollection(
            new ClassMap('Book', Type\Book::class),
            new ClassMap('ArrayOfBooks', Type\ArrayOfBooks::class),
        );
    }


}
```