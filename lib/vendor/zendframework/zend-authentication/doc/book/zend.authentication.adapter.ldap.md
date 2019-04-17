# LDAP Authentication

## Introduction

`Zend\Authentication\Adapter\Ldap` supports web application authentication with *LDAP* services. Its
features include username and domain name canonicalization, multi-domain authentication, and
failover capabilities. It has been tested to work with [Microsoft Active
Directory](http://www.microsoft.com/windowsserver2003/technologies/directory/activedirectory/) and
[OpenLDAP](http://www.openldap.org/), but it should also work with other *LDAP* service providers.

This documentation includes a guide on using `Zend\Authentication\Adapter\Ldap`, an exploration of
its *API*, an outline of the various available options, diagnostic information for troubleshooting
authentication problems, and example options for both Active Directory and OpenLDAP servers.

## Usage

To incorporate `Zend\Authentication\Adapter\Ldap` authentication into your application quickly, even
if you're not using `Zend\Mvc`, the meat of your code should look something like the following:

```php
<?php
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\Ldap as AuthAdapter;
use Zend\Config\Reader\Ini as ConfigReader;
use Zend\Config\Config;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream as LogWriter;
use Zend\Log\Filter\Priority as LogFilter;

$username = $this->getRequest()->getPost('username');
$password = $this->getRequest()->getPost('password');


$auth = new AuthenticationService();

$configReader = new ConfigReader();
$configData = $configReader->fromFile('./ldap-config.ini');
$config = new Config($configData, true);

$log_path = $config->production->ldap->log_path;
$options = $config->production->ldap->toArray();
unset($options['log_path']);

$adapter = new AuthAdapter($options,
                           $username,
                           $password);

$result = $auth->authenticate($adapter);

if ($log_path) {
    $messages = $result->getMessages();

    $logger = new Logger;
    $writer = new LogWriter($log_path);

    $logger->addWriter($writer);

    $filter = new LogFilter(Logger::DEBUG);
    $writer->addFilter($filter);

    foreach ($messages as $i => $message) {
        if ($i-- > 1) { // $messages[2] and up are log messages
            $message = str_replace("\n", "\n  ", $message);
            $logger->debug("Ldap: $i: $message");
        }
    }
}

```

Of course, the logging code is optional, but it is highly recommended that you use a logger.
`Zend\Authentication\Adapter\Ldap` will record just about every bit of information anyone could want
in `$messages` (more below), which is a nice feature in itself for something that has a history of
being notoriously difficult to debug.

The `Zend\Config\Reader\Ini` code is used above to load the adapter options. It is also optional. A
regular array would work equally well. The following is an example `ldap-config.ini` file that has
options for two separate servers. With multiple sets of server options the adapter will try each, in
order, until the credentials are successfully authenticated. The names of the servers (e.g.,
'server1' and 'server2') are largely arbitrary. For details regarding the options array, see the
**Server Options** section below. Note that `Zend\Config\Reader\Ini` requires that any values with
"equals" characters (**=**) will need to be quoted (like the DNs shown below).

```ini
[production]

ldap.log_path = /tmp/ldap.log

; Typical options for OpenLDAP
ldap.server1.host = s0.foo.net
ldap.server1.accountDomainName = foo.net
ldap.server1.accountDomainNameShort = FOO
ldap.server1.accountCanonicalForm = 3
ldap.server1.username = "CN=user1,DC=foo,DC=net"
ldap.server1.password = pass1
ldap.server1.baseDn = "OU=Sales,DC=foo,DC=net"
ldap.server1.bindRequiresDn = true

; Typical options for Active Directory
ldap.server2.host = dc1.w.net
ldap.server2.useStartTls = true
ldap.server2.accountDomainName = w.net
ldap.server2.accountDomainNameShort = W
ldap.server2.accountCanonicalForm = 3
ldap.server2.baseDn = "CN=Users,DC=w,DC=net"
```

The above configuration will instruct `Zend\Authentication\Adapter\Ldap` to attempt to authenticate
users with the OpenLDAP server `s0.foo.net` first. If the authentication fails for any reason, the
AD server `dc1.w.net` will be tried.

With servers in different domains, this configuration illustrates multi-domain authentication. You
can also have multiple servers in the same domain to provide redundancy.

Note that in this case, even though OpenLDAP has no need for the short NetBIOS style domain name
used by Windows, we provide it here for name canonicalization purposes (described in the **Username
Canonicalization** section below).

## The API

The `Zend\Authentication\Adapter\Ldap` constructor accepts three parameters.

The `$options` parameter is required and must be an array containing one or more sets of options.
Note that it is **an array of arrays** of [Zend\\Ldap\\Ldap](zend.ldap.introduction) options. Even
if you will be using only one *LDAP* server, the options must still be within another array.

Below is [print\_r()](http://php.net/print_r) output of an example options parameter containing two
sets of server options for *LDAP* servers `s0.foo.net` and `dc1.w.net` (the same options as the
above *INI* representation):

```console
Array
(
    [server2] => Array
        (
            [host] => dc1.w.net
            [useStartTls] => 1
            [accountDomainName] => w.net
            [accountDomainNameShort] => W
            [accountCanonicalForm] => 3
            [baseDn] => CN=Users,DC=w,DC=net
        )

    [server1] => Array
        (
            [host] => s0.foo.net
            [accountDomainName] => foo.net
            [accountDomainNameShort] => FOO
            [accountCanonicalForm] => 3
            [username] => CN=user1,DC=foo,DC=net
            [password] => pass1
            [baseDn] => OU=Sales,DC=foo,DC=net
            [bindRequiresDn] => 1
        )

)
```

The information provided in each set of options above is different mainly because AD does not
require a username be in DN form when binding (see the `bindRequiresDn` option in the **Server
Options** section below), which means we can omit a number of options associated with retrieving the
DN for a username being authenticated.

> ## Note
#### What is a Distinguished Name?
A DN or "distinguished name" is a string that represents the path to an object within the *LDAP*
directory. Each comma-separated component is an attribute and value representing a node. The
components are evaluated in reverse. For example, the user account **CN=Bob
Carter,CN=Users,DC=w,DC=net** is located directly within the **CN=Users,DC=w,DC=net container**.
This structure is best explored with an *LDAP* browser like the *ADSI* Edit *MMC* snap-in for Active
Directory or phpLDAPadmin.

The names of servers (e.g. 'server1' and 'server2' shown above) are largely arbitrary, but for the
sake of using `Zend\Config\Reader\Ini`, the identifiers should be present (as opposed to being
numeric indexes) and should not contain any special characters used by the associated file formats
(e.g. the '**.**'*INI* property separator, '**&**' for *XML* entity references, etc).

With multiple sets of server options, the adapter can authenticate users in multiple domains and
provide failover so that if one server is not available, another will be queried.

> ## Note
#### The Gory Details: What Happens in the Authenticate Method?
When the `authenticate()` method is called, the adapter iterates over each set of server options,
sets them on the internal `Zend\Ldap\Ldap` instance, and calls the `Zend\Ldap\Ldap::bind()` method
with the username and password being authenticated. The `Zend\Ldap\Ldap` class checks to see if the
username is qualified with a domain (e.g., has a domain component like `alice@foo.net` or
`FOO\alice`). If a domain is present, but does not match either of the server's domain names
(`foo.net` or *FOO*), a special exception is thrown and caught by `Zend\Authentication\Adapter\Ldap`
that causes that server to be ignored and the next set of server options is selected. If a domain
**does** match, or if the user did not supply a qualified username, `Zend\Ldap\Ldap` proceeds to try
to bind with the supplied credentials. if the bind is not successful, `Zend\Ldap\Ldap` throws a
`Zend\Ldap\Exception\LdapException` which is caught by `Zend\Authentication\Adapter\Ldap` and the
next set of server options is tried. If the bind is successful, the iteration stops, and the
adapter's `authenticate()` method returns a successful result. If all server options have been tried
without success, the authentication fails, and `authenticate()` returns a failure result with error
messages from the last iteration.

The username and password parameters of the `Zend\Authentication\Adapter\Ldap` constructor represent
the credentials being authenticated (i.e., the credentials supplied by the user through your *HTML*
login form). Alternatively, they may also be set with the `setUsername()` and `setPassword()`
methods.

## Server Options

Each set of server options **in the context of Zend\\Authentication\\Adapter\\Ldap** consists of the
following options, which are passed, largely unmodified, to `Zend\Ldap\Ldap::setOptions()`:

> ## Note
If you enable **useStartTls = TRUE** or **useSsl = TRUE** you may find that the *LDAP* client
generates an error claiming that it cannot validate the server's certificate. Assuming the *PHP*
*LDAP* extension is ultimately linked to the OpenLDAP client libraries, to resolve this issue you
can set "`TLS_REQCERT never`" in the OpenLDAP client `ldap.conf` (and restart the web server) to
indicate to the OpenLDAP client library that you trust the server. Alternatively, if you are
concerned that the server could be spoofed, you can export the *LDAP* server's root certificate and
put it on the web server so that the OpenLDAP client can validate the server's identity.

## Collecting Debugging Messages

`Zend\Authentication\Adapter\Ldap` collects debugging information within its `authenticate()`
method. This information is stored in the `Zend\Authentication\Result` object as messages. The array
returned by `Zend\Authentication\Result::getMessages()` is described as follows

In practice, index 0 should be displayed to the user (e.g., using the FlashMessenger helper), index
1 should be logged and, if debugging information is being collected, indexes 2 and higher could be
logged as well (although the final message always includes the string from index 1).

## Common Options for Specific Servers

### Options for Active Directory

For *ADS*, the following options are noteworthy:

> ## Note
Technically there should be no danger of accidental cross-domain authentication with the current
`Zend\Authentication\Adapter\Ldap` implementation, since server domains are explicitly checked, but
this may not be true of a future implementation that discovers the domain at runtime, or if an
alternative adapter is used (e.g., Kerberos). In general, account name ambiguity is known to be the
source of security issues, so always try to use qualified account names.

### Options for OpenLDAP

For OpenLDAP or a generic *LDAP* server using a typical posixAccount style schema, the following
options are noteworthy:

