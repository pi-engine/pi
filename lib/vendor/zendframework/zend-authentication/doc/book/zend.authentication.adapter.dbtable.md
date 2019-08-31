# Database Table Authentication

> ## Note
`Zend\Authentication\Adapter\DbTable` has been deprecated, as its responsibilities have been
splitted off into `Zend\Authentication\Adapter\DbTable\CallbackCheck` and
`Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter`. Use
`Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter` instead of
`Zend\Authentication\Adapter\DbTable`.

## Introduction

`Zend\Authentication\Adapter\DbTable` provides the ability to authenticate against credentials
stored in a database table. Because `Zend\Authentication\Adapter\DbTable` requires an instance of
`Zend\Db\Adapter\Adapter` to be passed to its constructor, each instance is bound to a particular
database connection. Other configuration options may be set through the constructor and through
instance methods, one for each option.

The available configuration options include:

- **tableName**: This is the name of the database table that contains the authentication
credentials, and against which the database authentication query is performed.
- **identityColumn**: This is the name of the database table column used to represent the identity.
The identity column must contain unique values, such as a username or e-mail address.
- **credentialColumn**: This is the name of the database table column used to represent the
credential. Under a simple identity and password authentication scheme, the credential value
corresponds to the password. See also the `credentialTreatment` option.
- **credentialTreatment**: In many cases, passwords and other sensitive data are encrypted, hashed,
encoded, obscured, salted or otherwise treated through some function or algorithm. By specifying a
parameterized treatment string with this method, such as '`MD5(?)`' or '`PASSWORD(?)`', a developer
may apply such arbitrary *SQL* upon input credential data. Since these functions are specific to the
underlying *RDBMS*, check the database manual for the availability of such functions for your
database system.

## Basic Usage

As explained in the introduction, the `Zend\Authentication\Adapter\DbTable` constructor requires an
instance of `Zend\Db\Adapter\Adapter` that serves as the database connection to which the
authentication adapter instance is bound. First, the database connection should be created.

The following code creates an adapter for an in-memory database, creates a simple table schema, and
inserts a row against which we can perform an authentication query later. This example requires the
*PDO* SQLite extension to be available:

```php
<?php
use Zend\Db\Adapter\Adapter as DbAdapter;

// Create a SQLite database connection
$dbAdapter = new DbAdapter(array(
                'driver' => 'Pdo_Sqlite',
                'database' => 'path/to/sqlite.db'
            ));

// Build a simple table creation query
$sqlCreate = 'CREATE TABLE [users] ('
           . '[id] INTEGER  NOT NULL PRIMARY KEY, '
           . '[username] VARCHAR(50) UNIQUE NOT NULL, '
           . '[password] VARCHAR(32) NULL, '
           . '[real_name] VARCHAR(150) NULL)';

// Create the authentication credentials table
$dbAdapter->query($sqlCreate);

// Build a query to insert a row for which authentication may succeed
$sqlInsert = "INSERT INTO users (username, password, real_name) "
           . "VALUES ('my_username', 'my_password', 'My Real Name')";

// Insert the data
$dbAdapter->query($sqlInsert);

```

With the database connection and table data available, an instance of
`Zend\Authentication\Adapter\DbTable` may be created. Configuration option values may be passed to
the constructor or deferred as parameters to setter methods after instantiation:

```php
<?php
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

// Configure the instance with constructor parameters...
$authAdapter = new AuthAdapter($dbAdapter,
                               'users',
                               'username',
                               'password'
                               );

// ...or configure the instance with setter methods
$authAdapter = new AuthAdapter($dbAdapter);

$authAdapter
    ->setTableName('users')
    ->setIdentityColumn('username')
    ->setCredentialColumn('password')
;

```

At this point, the authentication adapter instance is ready to accept authentication queries. In
order to formulate an authentication query, the input credential values are passed to the adapter
prior to calling the `authenticate()` method:

```php
<?php
// Set the input credential values (e.g., from a login form)
$authAdapter
    ->setIdentity('my_username')
    ->setCredential('my_password')
;

// Perform the authentication query, saving the result

```

In addition to the availability of the `getIdentity()` method upon the authentication result object,
`Zend\Authentication\Adapter\DbTable` also supports retrieving the table row upon authentication
success:

```php
<?php
// Print the identity
echo $result->getIdentity() . "\n\n";

// Print the result row
print_r($authAdapter->getResultRowObject());

/* Output:
my_username

Array
(
    [id] => 1
    [username] => my_username
    [password] => my_password
    [real_name] => My Real Name
)
*/

```

Since the table row contains the credential value, it is important to secure the values against
unintended access.

When retrieving the result object, we can either specify what columns to return, or what columns to
omit:

```php
<?php
$columnsToReturn = array(
    'id', 'username', 'real_name'
);
print_r($authAdapter->getResultRowObject($columnsToReturn));

/* Output:

Array
(
   [id] => 1
   [username] => my_username
   [real_name] => My Real Name
)
*/

$columnsToOmit = array('password');
print_r($authAdapter->getResultRowObject(null, $columnsToOmit);

/* Output:

Array
(
   [id] => 1
   [username] => my_username
   [real_name] => My Real Name
)
*/
```

## Advanced Usage: Persisting a DbTable Result Object

By default, `Zend\Authentication\Adapter\DbTable` returns the identity supplied back to the auth
object upon successful authentication. Another use case scenario, where developers want to store to
the persistent storage mechanism of `Zend\Authentication` an identity object containing other useful
information, is solved by using the `getResultRowObject()` method to return a **stdClass** object.
The following code snippet illustrates its use:

```php
<?php
// authenticate with Zend\Authentication\Adapter\DbTable
$result = $this->_auth->authenticate($adapter);

if ($result->isValid()) {
    // store the identity as an object where only the username and
    // real_name have been returned
    $storage = $this->_auth->getStorage();
    $storage->write($adapter->getResultRowObject(array(
        'username',
        'real_name',
    )));

    // store the identity as an object where the password column has
    // been omitted
    $storage->write($adapter->getResultRowObject(
        null,
        'password'
    ));

    /* ... */
} else {

    /* ... */
}

```

### Advanced Usage By Example

While the primary purpose of the `Zend\Authentication` component (and consequently
`Zend\Authentication\Adapter\DbTable`) is primarily **authentication** and not **authorization**,
there are a few instances and problems that toe the line between which domain they fit within.
Depending on how you've decided to explain your problem, it sometimes makes sense to solve what
could look like an authorization problem within the authentication adapter.

With that disclaimer out of the way, `Zend\Authentication\Adapter\DbTable` has some built in
mechanisms that can be leveraged for additional checks at authentication time to solve some common
user problems.

```php
<?php
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

// The status field value of an account is not equal to "compromised"
$adapter = new AuthAdapter($db,
                           'users',
                           'username',
                           'password',
                           'MD5(?) AND status != "compromised"'
                           );

// The active field value of an account is equal to "TRUE"
$adapter = new AuthAdapter($db,
                           'users',
                           'username',
                           'password',
                           'MD5(?) AND active = "TRUE"'
                           );

```

Another scenario can be the implementation of a salting mechanism. Salting is a term referring to a
technique which can highly improve your application's security. It's based on the idea that
concatenating a random string to every password makes it impossible to accomplish a successful brute
force attack on the database using pre-computed hash values from a dictionary.

Therefore, we need to modify our table to store our salt string:

```php
<?php
$sqlAlter = "ALTER TABLE [users] "
          . "ADD COLUMN [password_salt] "
          . "AFTER [password]";

```

Here's a simple way to generate a salt string for every user at registration:

```php
<?php
$dynamicSalt = '';
for ($i = 0; $i < 50; $i++) {
    $dynamicSalt .= chr(rand(33, 126));
}

```

And now let's build the adapter:

```php
<?php
$adapter = new AuthAdapter($db,
                           'users',
                           'username',
                           'password',
                           "MD5(CONCAT('staticSalt', ?, password_salt))"
                          );

```

> ## Note
You can improve security even more by using a static salt value hard coded into your application. In
the case that your database is compromised (e. g. by an *SQL* injection attack) but your web server
is intact your data is still unusable for the attacker.

Another alternative is to use the `getDbSelect()` method of the
`Zend\Authentication\Adapter\DbTable` after the adapter has been constructed. This method will
return the `Zend\Db\Sql\Select` object instance it will use to complete the `authenticate()`
routine. It is important to note that this method will always return the same object regardless if
`authenticate()` has been called or not. This object **will not** have any of the identity or
credential information in it as those values are placed into the select object at `authenticate()`
time.

An example of a situation where one might want to use the `getDbSelect()` method would check the
status of a user, in other words to see if that user's account is enabled.

```php
<?php
// Continuing with the example from above
$adapter = new AuthAdapter($db,
                           'users',
                           'username',
                           'password',
                           'MD5(?)'
                           );

// get select object (by reference)
$select = $adapter->getDbSelect();
$select->where('active = "TRUE"');

// authenticate, this ensures that users.active = TRUE
$adapter->authenticate();

```
