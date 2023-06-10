## VSHF PHP Config Manager

VSHF PHP Config Manager is a settings/configuration manager for PHP applications. It provides functionality to handle settings and resources with their properties.

## Usage

To use the VSHF Config Manager, you need to instantiate the `Config` class:

```php
$settings = new \VSHF\Config\Config();
```

You can also initialize it with settings:

```php
$settings = new \VSHF\Config\Config([
   'setting1' =>'value1',
   'setting2' =>'value2'
]);
```

### Contexts
The default setting context is the _app_ context. When initializing with settings, they will be added to this context. 

You can hydrate different contexts later on:

```php
$settings->hydrate(
    [
        'settingA' =>'valueA',
        'settingB' =>'valueB'
    ],
    'myContext'
);
```

This will create an internal settings tree with the following structure:

```
[
    'app' => [
        'setting1' =>'value1',
        'setting2' =>'value2'
    ],
    'myContext' =>[
        'settingA' =>'valueA',
        'settingB' =>'valueB'
    ]
]
```

### Resources and properties

In the case of having a collection of resources and their properties, where the properties can be considered as _settings_ for each resource, you can use the Config Manager to handle them. 

For example, if you have a collection of _services_ with the _isPriced_ property, you can observe the value of this property for different services.

```
[
    'service_1' => [
        'isPriced' => FALSE,
        ...
    ],
    'service_2' => [
        'isPriced' => TRUE,
        ...
    ],
    ...
]
```

To hydrate with a resource collection:

```php
foreach ($collection as $itemId => $item) {
    $settings->hydrateResource(
        [
             'isPriced' => TRUE,
             // other properties
        ],
        'services', // Context
        $itemId // Resource ID
    );
}
```

## Observers

Each setting must have its corresponding _Observer_, which implements the `ObserverInterface`. An observer can handle one or more settings.

To register an observer:

```php

// In the main context:
$settings->registerObserver('settingId', MyObserver::class);


// In a custom context
$settings->registerObserver('settingId', MyObserver::class, 'myContext');

// Observing multiple settings with a single observer:
$settings->registerObserver('settingA', MyObserver::class);
$settings->registerObserver('settingB', MyObserver::class);
```

### Resource property observers

Similarly, each resource property must have its _PropertyObserver_, which implements the `PropertyObserverInterface`. An observer can handle one or more properties.

To register a PropertyObserver:

```php

$settings->registerObserver('propertyId', MyPropertyObserver::class, 'services');

// Observing multiple properties with a single observer:
$settings->registerObserver('propertyA', MyPropertyObserver::class, 'services');
$settings->registerObserver('propertyB', MyPropertyObserver::class, 'services');
```

## Get and save

To retrieve a setting:

```php
// From the main context:
$settings->get('settingA');

// From a custom context:
$settings->get('settingA', 'myContext');
```

To save a setting:

```php
// In the main context:
$settings->save('settingA', 'newValue');

// In a custom context:
$settings->save('settingA', 'newValue', 'myContext');
```

### Resource properties

To retrieve a property of a given resource:

```php
$settings->getProperty('propertyA', 'services', 'resourceId');
```

To save a property of a given resource:

```php
$settings->saveProperty('propertyA', 'services', 'resourceId');
```

To retrieve a given resource with all its properties:

```php
$settings->getResourceProperties('services', 'resourceId');
```

## Setting dependencies

A setting or a resource property can depend on one or more other settings, even from different contexts. 

To set dependencies, the `dependencies()` method of the observer must return a _Dependency_ object.

```php
// Inside settingA's observer class
public static function dependencies(){
    $dependency = new \VSHF\Config\Dependency();
    $dependency
        ->on('settingB')
        ->beingEqualTo('certainValue')
        ;
    return $dependency;
}
```

In this example, if _settingB_ is equal to _certainValue_, then _settingA_ is properly returned. Otherwise, NULL is returned. 

It's important to note that NULL should not be a default/proper setting value.

### Complex dependencies

Dependencies can be complex and involve logical operators such as _AND_ and _OR_. You can construct complex dependencies using the _Dependency_ object.

```php
$dependency = new \VSHF\Config\Dependency();

// AND
$dependency
    ->on('settingB')
    ->beingEqualTo('certainValue')
    ->and('settingC')
    ->beingTruthy()
    ;
    
// OR
$dependency
    ->on('settingB')
    ->beingEqualTo('certainValue')
    ->or('settingC')
    ->beingTruthy()
    ;

//INVALID, triggers an error
$dependency
    ->on('settingB')
    ->beingEqualTo('certainValue')
    ->and('settingC')
    ->beingTruthy()
    ->or('settingD')
    ->beingFalsy()
    ;

/*
 * This is equal to:
 *      settingB === certainValue && (
 *          settingC || !settingD
 *      )
 */
$dependency
    ->on('settingB')
    ->beingEqualTo('certainValue')
    ->andGroup()
    ->on('settingC')
    ->beingTruthy()
    ->or('settingD')
    ->beingFalsy()
    ->endGroup()
    ;

/*
 * This is equal to:
 *      settingB === certainValue || (
 *          settingC && !settingD
 *      )
 */
$dependency
    ->on('settingB')
    ->beingEqualTo('certainValue')
    ->orGroup()
    ->on('settingC')
    ->beingTruthy()
    ->and('settingD')
    ->beingFalsy()
    ->endGroup()
    ;
```

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/MIT)