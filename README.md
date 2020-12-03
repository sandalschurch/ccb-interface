
# CCBInterface [![Actions Status](https://github.com/sandalschurch/CCBInterface/workflows/ccb%20test/badge.svg)](https://github.com/sandalschurch/CCBInterface/actions)

![Sandals Church](img/github_banner.jpg?raw=true)

# Why
This repository was created to be used as a git submodule in any other repositories where we need to connect with the CCB third party platform.

# Key Concept
We are using what is called a [strategy design patter](https://sourcemaking.com/design_patterns/strategy). The overarching idea is that we have a base class `CCBInterface` that all of our child classes will inherit from. `CCBInterface` has a number of [abstract](https://www.php.net/manual/en/language.oop5.abstract.php) methods that force each child to define their own implementation of the method. The function signature and paramaters will always be the same but the implementation will be diffent form class to class. 

# How To Use

```shell
git submodule add https://github.com/sandalschurch/CCBInterface.git
```
Adding the submodule will give you access to a `/CCBInterface` directory. All of the different class implementations are found inside `/CCBInterface/src`.

Now depending on what kind of implementation you want to use, you can call it like so
```php
$ccb = new CCBPublicAPI();

$individual = $ccb->getIndividual("Bryan Orozco", "7777777777", "bryan@email.com", 1);
...
```
Now if you ever wanted to change the implementation all you have to do is update the $ccb variable.
> Main takeway from the strategy pattern.
```php
$ccb = new CCBPrivateAPI();

$individual = $ccb->getIndividual("Bryan Orozco", "7777777777", "bryan@email.com", 1);
...
```
