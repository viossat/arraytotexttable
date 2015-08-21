# ArrayToTextTable

## Installation

```
composer require mathieuviossat/arraytotexttable
```

```json
{
    "require": {
        "mathieuviossat/arraytotexttable": "~1.0.0"
    }
}
```

## Example

```php
use MathieuViossat\Util\ArrayToTextTable;

$data = [
    [
        'firstname' => 'Mollie',
        'surname' => 'Alvarez',
        'email' => 'molliealvarez@example.com',
    ],
    [
        'firstname' => 'Dianna',
        'surname' => 'Mcbride',
        'age' => 43,
        'email' => 'diannamcbride@example.com',
    ],
    [
        'firstname' => 'Elvira',
        'surname' => 'Mueller',
        'age' => 50,
        'email' => 'elviramueller@example.com',
    ],
    [
        'firstname' => 'Corine',
        'surname' => 'Morton',
        'age' => 35,
    ],
    [
        'firstname' => 'James',
        'surname' => 'Allison',
    ],
    [
        'firstname' => 'Bowen',
        'surname' => 'Kelley',
        'age' => 50,
        'email' => 'bowenkelley@example.com',
    ],
];

$renderer = new ArrayToTextTable($data);
echo $renderer->getTable();
```

## Result

```
┌───────────┬─────────┬───────────────────────────┬─────┐
│ FIRSTNAME │ SURNAME │           EMAIL           │ AGE │
├───────────┼─────────┼───────────────────────────┼─────┤
│ Mollie    │ Alvarez │ molliealvarez@example.com │     │
│ Dianna    │ Mcbride │ diannamcbride@example.com │ 43  │
│ Elvira    │ Mueller │ elviramueller@example.com │ 50  │
│ Corine    │ Morton  │                           │ 35  │
│ James     │ Allison │                           │     │
│ Bowen     │ Kelley  │ bowenkelley@example.com   │ 50  │
└───────────┴─────────┴───────────────────────────┴─────┘
```

## Options

```php
$renderer->setData($newData);
// Accepts array of arrays and array of objects.
// Can also be called through getTable():
$renderer = new ArrayToTextTable();
echo $renderer->getTable($data);

$renderer->setDecorator(new \Zend\Text\Table\Decorator\Ascii());
// Unicode (default), Ascii, Blank

$renderer->setIndentation("\t");
// default: *empty string*

$renderer->setDisplayKeys(false);
// true, false, 'auto' (default, doesn't display keys if all of them are integers)

$renderer->setUpperKeys(false);
// default: true

$renderer->setKeysAlignment(ArrayToTextTable::AlignCenter);
// AlignLeft, AlignCenter (default), AlignRight

$renderer->setValuesAlignment(ArrayToTextTable::AlignLeft);
// AlignLeft (default), AlignCenter, AlignRight

$formatter = function(&$value, $key, $renderer) {
    if ($value === true)
        $value = 'TRUE';
    else if ($value === false)
        $value = 'FALSE';
    else if ($value === '')
        $value = 'EMPTY';
    else if ($value === null)
        $value = 'NULL';
};
$renderer->setFormatter($formatter);
// default: null
```

## License

This library is published under [The MIT License](http://opensource.org/licenses/MIT).
