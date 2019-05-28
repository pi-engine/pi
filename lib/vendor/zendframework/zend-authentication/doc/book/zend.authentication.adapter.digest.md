# Digest Authentication

## Introduction

[Digest authentication](http://en.wikipedia.org/wiki/Digest_access_authentication) is a method of
*HTTP* authentication that improves upon [Basic
authentication](http://en.wikipedia.org/wiki/Basic_authentication_scheme) by providing a way to
authenticate without having to transmit the password in clear text across the network.

This adapter allows authentication against text files containing lines having the basic elements of
Digest authentication:

- username, such as "**joe.user**"
- realm, such as "**Administrative Area**"
- *MD5* hash of the username, realm, and password, separated by colons

The above elements are separated by colons, as in the following example (in which the password is
"**somePassword**"):

```text
someUser:Some Realm:fde17b91c3a510ecbaf7dbd37f59d4f8
```

## Specifics

The digest authentication adapter, `Zend\Authentication\Adapter\Digest`, requires several input
parameters:

- filename - Filename against which authentication queries are performed
- realm - Digest authentication realm
- username - Digest authentication user
- password - Password for the user of the realm

These parameters must be set prior to calling `authenticate()`.

## Identity

The digest authentication adapter returns a `Zend\Authentication\Result` object, which has been
populated with the identity as an array having keys of **realm** and **username**. The respective
array values associated with these keys correspond to the values set before `authenticate()` is
called.

```php
<?php
use Zend\Authentication\Adapter\Digest as AuthAdapter;

$adapter = new AuthAdapter($filename,
                           $realm,
                           $username,
                           $password);

$result = $adapter->authenticate();

$identity = $result->getIdentity();

print_r($identity);

/*
Array
(
    [realm] => Some Realm
    [username] => someUser
)
*/

```
