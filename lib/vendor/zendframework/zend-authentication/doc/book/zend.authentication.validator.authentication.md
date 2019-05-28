# Authentication Validator

## Introduction

`Zend\Authentication\Validator\Authentication` provides the ability to utilize a validator for an
InputFilter in the instance of a Form or for single use where you simply want a true/false value and
being able to introspect the error.

The available configuration options include:

- **adapter**: This is an instance of `Zend\Authentication\Adapter`.
- **identity**: This is the identity or name of the identity in the passed in context.
- **credential**: This is the credential or the name of the credential in the passed in context.
- **service**: This is an instance of `Zend\Authentication\AuthenticationService`

## Basic Usage

```php
<?php
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Validator\Authentication as AuthenticationValidator;

$service   = new AuthenticationService();
$adapter   = new My\Authentication\Adapter();
$validator = new AuthenticationValidator(array(
    'service' => $service,
    'adapter' => $adapter,
));

$validator->setCredential('myCredentialContext');
$validator->isValid('myIdentity', array(
     'myCredentialContext' => 'myCredential',
));

```
