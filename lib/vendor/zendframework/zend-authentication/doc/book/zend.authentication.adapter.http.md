# HTTP Authentication Adapter

## Introduction

`Zend\Authentication\Adapter\Http` provides a mostly-compliant implementation of
[RFC-2617](http://tools.ietf.org/html/rfc2617),
[Basic](http://en.wikipedia.org/wiki/Basic_authentication_scheme) and
[Digest](http://en.wikipedia.org/wiki/Digest_access_authentication) *HTTP* Authentication. Digest
authentication is a method of *HTTP* authentication that improves upon Basic authentication by
providing a way to authenticate without having to transmit the password in clear text across the
network.

**Major Features:**

- Supports both Basic and Digest authentication.
- Issues challenges in all supported schemes, so client can respond with any scheme it supports.
- Supports proxy authentication.
- Includes support for authenticating against text files and provides an interface for
authenticating against other sources, such as databases.

There are a few notable features of *RFC-2617* that are not implemented yet:

- Nonce tracking, which would allow for "stale" support, and increased replay attack protection.
- Authentication with integrity checking, or "auth-int".
- Authentication-Info *HTTP* header.

## Design Overview

This adapter consists of two sub-components, the *HTTP* authentication class itself, and the
so-called "Resolvers." The *HTTP* authentication class encapsulates the logic for carrying out both
Basic and Digest authentication. It uses a Resolver to look up a client's identity in some data
store (text file by default), and retrieve the credentials from the data store. The "resolved"
credentials are then compared to the values submitted by the client to determine whether
authentication is successful.

## Configuration Options

The `Zend\Authentication\Adapter\Http` class requires a configuration array passed to its
constructor. There are several configuration options available, and some are required:

> ## Note
The current implementation of the `nonce_timeout` has some interesting side effects. This setting is
supposed to determine the valid lifetime of a given nonce, or effectively how long a client's
authentication information is accepted. Currently, if it's set to 3600 (for example), it will cause
the adapter to prompt the client for new credentials every hour, on the hour. This will be resolved
in a future release, once nonce tracking and stale support are implemented.

## Resolvers

The resolver's job is to take a username and realm, and return some kind of credential value. Basic
authentication expects to receive the Base64 encoded version of the user's password. Digest
authentication expects to receive a hash of the user's username, the realm, and their password (each
separated by colons). Currently, the only supported hash algorithm is *MD5*.

`Zend\Authentication\Adapter\Http` relies on objects implementing
`Zend\Authentication\Adapter\Http\ResolverInterface`. A text file resolver class is included with
this adapter, but any other kind of resolver can be created simply by implementing the resolver
interface.

### File Resolver

The file resolver is a very simple class. It has a single property specifying a filename, which can
also be passed to the constructor. Its `resolve()` method walks through the text file, searching for
a line with a matching username and realm. The text file format similar to Apache htpasswd files:

```text
<username>:<realm>:<credentials>\n
```

Each line consists of three fields - username, realm, and credentials - each separated by a colon.
The credentials field is opaque to the file resolver; it simply returns that value as-is to the
caller. Therefore, this same file format serves both Basic and Digest authentication. In Basic
authentication, the credentials field should be written in clear text. In Digest authentication, it
should be the *MD5* hash described above.

There are two equally easy ways to create a File resolver:

```php
<?php
use Zend\Authentication\Adapter\Http\FileResolver;

$path     = 'files/passwd.txt';
$resolver = new FileResolver($path);

```

or

```php
<?php
$path     = 'files/passwd.txt';
$resolver = new FileResolver();
$resolver->setFile($path);

```

If the given path is empty or not readable, an exception is thrown.

## Basic Usage

First, set up an array with the required configuration values:

```php
<?php
$config = array(
    'accept_schemes' => 'basic digest',
    'realm'          => 'My Web Site',
    'digest_domains' => '/members_only /my_account',
    'nonce_timeout'  => 3600,
);

```

This array will cause the adapter to accept either Basic or Digest authentication, and will require
authenticated access to all the areas of the site under `/members_only` and `/my_account`. The realm
value is usually displayed by the browser in the password dialog box. The `nonce_timeout`, of
course, behaves as described above.

Next, create the `Zend\Authentication\Adapter\Http` object:

```php
<?php
$adapter = new Zend\Authentication\Adapter\Http($config);

```

Since we're supporting both Basic and Digest authentication, we need two different resolver objects.
Note that this could just as easily be two different classes:

```php
<?php
use Zend\Authentication\Adapter\Http\FileResolver;

$basicResolver = new FileResolver();
$basicResolver->setFile('files/basicPasswd.txt');

$digestResolver = new FileResolver();
$digestResolver->setFile('files/digestPasswd.txt');

$adapter->setBasicResolver($basicResolver);
$adapter->setDigestResolver($digestResolver);

```

Finally, we perform the authentication. The adapter needs a reference to both the Request and
Response objects in order to do its job:

```php
<?php
assert($request instanceof Zend\Http\Request);
assert($response instanceof Zend\Http\Response);

$adapter->setRequest($request);
$adapter->setResponse($response);

$result = $adapter->authenticate();
if (!$result->isValid()) {
    // Bad username/password, or canceled password prompt
}

```
