
![Sandals Church](img/github_banner.jpg?raw=true)

# How To Use

```shell
composer require sandalschurch/ccb-interface
```

Provide your ccb credentials and base url to the constructor of the `CCBPublicAPI` class. After doing so you'll be free to use any of the methods from the class.
```php
$ccb = new CCBPublicAPI("YOUR CCB URL", "USERNAME", "PASS");

$individual = $ccb->getIndividual("Bryan Orozco", "7777777777", "bryan@email.com", 1);
...
```