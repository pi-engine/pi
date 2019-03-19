<?php

/**
 * ArangoDB PHP client: user document handler
 *
 * @package   ArangoDBClient
 * @author    Frank Mayer
 * @since     1.2
 */

namespace ArangoDBClient;

/**
 * A handler that manages users
 * .
 * A user-document handler that fetches vertices from the server and
 * persists them on the server. It does so by issuing the
 * appropriate HTTP requests to the server.
 *
 * @package   ArangoDBClient
 * @since     1.2
 */
class UserHandler extends Handler
{

    /**
     * save a user to the user-collection
     *
     * This will save the user to the users collection. It will additionally grant the user permissions
     * for the current database
     *
     * This will throw if the user cannot be saved
     *
     * @throws Exception
     *
     * @param string $username - The name of the user as a string. This is mandatory.
     * @param mixed  $passwd   - The user password as a string. If no password is specified, the empty string will be used.
     * @param mixed  $active   - an optional flag that specifies whether the user is active. If not specified, this will default to true.
     * @param array  $extra    - an optional array with arbitrary extra data about the user.
     *
     * @return boolean - true, if user could be saved
     * @since 1.2
     */
    public function addUser($username, $passwd = null, $active = null, $extra = null)
    {
        $userDocument         = new User();
        $userDocument->user   = $username;
        $userDocument->passwd = $passwd;
        $userDocument->active = $active;
        $userDocument->extra  = $extra;
        $data                 = $userDocument->getAll();

        $this->getConnection()->post(Urls::URL_USER, $this->json_encode_wrapper($data));

        try {
            // additionally set user permissions in the current databases
            $this->grantPermissions($username, $this->getConnection()->getDatabase());
        } catch (\Exception $e) {
        }

        return true;
    }

    /**
     * Replace an existing user, identified by its username
     *
     * This will replace the user-document on the server
     *
     * This will throw if the document cannot be replaced
     *
     * @throws Exception
     *
     * @param string $username - The name of the user as a string, who's user-data is going to be replaced. This is mandatory.
     * @param mixed  $passwd   - The user password as a string. If no password is specified, the empty string will be used.
     * @param mixed  $active   - an optional flag that specifies whether the user is active. If not specified, this will default to true.
     * @param array  $extra    - an optional array with arbitrary extra data about the user.
     *
     * @return bool - always true, will throw if there is an error
     */
    public function replaceUser($username, $passwd = null, $active = null, $extra = null)
    {
        $userDocument         = new User();
        $userDocument->passwd = $passwd;
        $userDocument->active = $active;
        $userDocument->extra  = $extra;
        $data                 = $userDocument->getAll();
        $url                  = UrlHelper::buildUrl(Urls::URL_USER, [$username]);
        $this->getConnection()->put($url, $this->json_encode_wrapper($data));

        return true;
    }


    /**
     * Update an existing user, identified by the username
     *
     * This will update the user-document on the server
     *
     * This will throw if the document cannot be updated
     *
     * @throws Exception
     *
     * @param string $username - The name of the user as a string, who's user-data is going to be updated. This is mandatory.
     * @param mixed  $passwd   - The user password as a string. If no password is specified, the empty string will be used.
     * @param mixed  $active   - an optional flag that specifies whether the user is active. If not specified, this will default to true.
     * @param array  $extra    - an optional array with arbitrary extra data about the user.
     *
     * @return bool - always true, will throw if there is an error
     */
    public function updateUser($username, $passwd = null, $active = null, $extra = null)
    {
        $userDocument         = new User();
        $userDocument->active = $active;
        if (null !== $passwd) {
            $userDocument->passwd = $passwd;
        }
        if (null !== $active) {
            $userDocument->active = $active;
        }
        if (null !== $extra) {
            $userDocument->extra = $extra;
        }

        $url = UrlHelper::buildUrl(Urls::URL_USER, [$username]);
        $this->getConnection()->patch($url, $this->json_encode_wrapper($userDocument->getAll()));

        return true;
    }


    /**
     * Get a single user-document, identified by the username
     *
     * This will throw if the document cannot be fetched from the server
     *
     * @throws Exception
     *
     * @param string $username - username as a string
     *
     * @return User - the user-document fetched from the server
     */
    public function get($username)
    {
        $url      = UrlHelper::buildUrl(Urls::URL_USER, [$username]);
        $response = $this->getConnection()->get($url);

        $data = $response->getJson();

        $options = ['_isNew' => false];

        $_documentClass = $this->_documentClass;
        return $_documentClass::createFromArray($data, $options);
    }


    /**
     * Remove a user, identified by the username
     *
     * @throws Exception
     *
     * @param string $username - username as a string, of the user that is to be deleted
     *
     * @return bool - always true, will throw if there is an error
     */
    public function removeUser($username)
    {
        // This preserves compatibility for the old policy parameter.
        $params = [];

        $url = UrlHelper::buildUrl(Urls::URL_USER, [$username]);
        $url = UrlHelper::appendParamsUrl($url, $params);
        $this->getConnection()->delete($url);

        return true;
    }


    /**
     * Grant R/W permissions to a user, for a specific database
     *
     * @throws Exception
     *
     * @param string $username     - username as a string
     * @param string $databaseName - name of database as a string
     *
     * @return bool - always true, will throw if there is an error
     *
     * @deprecated use UserHandler::grantDatabasePermissions instead
     */
    public function grantPermissions($username, $databaseName)
    {
        return $this->grantDatabasePermissions($username, $databaseName);
    }


    /**
     * Grant R/W permissions to a user, for a specific database
     *
     *
     * @param string $username     - username as a string
     * @param string $databaseName - name of database as a string
     * @param string $permissions  - permissions string `rw` for read-write (default), `ro` for read-only
     *
     * @return bool
     */
    public function grantDatabasePermissions($username, $databaseName, $permissions = 'rw')
    {
        $data = [
            'grant' => $permissions
        ];

        $url = UrlHelper::buildUrl(Urls::URL_USER, [$username, 'database', $databaseName]);
        $this->getConnection()->put($url, $this->json_encode_wrapper($data));

        return true;
    }


    /**
     * Revoke R/W permissions for a user, for a specific database
     *
     * @throws Exception
     *
     * @param string $username     - username as a string
     * @param string $databaseName - name of database as a string
     *
     * @return bool - always true, will throw if there is an error
     *
     * @deprecated use UserHandler::revokeDatabasePermissions instead
     */
    public function revokePermissions($username, $databaseName)
    {
        return $this->revokeDatabasePermissions($username, $databaseName);
    }


    /**
     * Revoke R/W permissions for a user, for a specific database
     *
     * @throws Exception
     *
     * @param string $username     - username as a string
     * @param string $databaseName - name of database as a string
     *
     * @return bool - always true, will throw if there is an error
     */
    public function revokeDatabasePermissions($username, $databaseName)
    {
        $data = [
            'grant' => 'none'
        ];

        $url = UrlHelper::buildUrl(Urls::URL_USER, [$username, 'database', $databaseName]);
        $this->getConnection()->put($url, $this->json_encode_wrapper($data));

        return true;
    }


    /**
     * Grant R/W permissions to a user, for a specific collection
     *
     *
     * @param string $username       - username as a string
     * @param string $databaseName   - name of database as a string
     * @param string $collectionName - name of collection as a string
     * @param string $permissions    - permissions string `rw` for read-write (default), `ro` for read-only
     *
     * @return bool
     */
    public function grantCollectionPermissions($username, $databaseName, $collectionName, $permissions = 'rw')
    {
        $data = [
            'grant' => $permissions
        ];

        $url = UrlHelper::buildUrl(Urls::URL_USER, [$username, 'database', $databaseName, $collectionName]);
        $this->getConnection()->put($url, $this->json_encode_wrapper($data));

        return true;
    }


    /**
     * Revoke R/W permissions for a user, for a specific database
     *
     * @throws Exception
     *
     * @param string $username       - username as a string
     * @param string $databaseName   - name of database as a string
     * @param string $collectionName - name of collection as a string
     *
     * @return bool - always true, will throw if there is an error
     */
    public function revokeCollectionPermissions($username, $databaseName, $collectionName)
    {
        $data = [
            'grant' => 'none'
        ];

        $url = UrlHelper::buildUrl(Urls::URL_USER, [$username, 'database', $databaseName, $collectionName]);
        $this->getConnection()->put($url, $this->json_encode_wrapper($data));

        return true;
    }


    /**
     * Gets the list of databases a user has access to
     *
     * @throws Exception
     *
     * @param string $username - username as a string
     *
     * @return array of database names for the databases the user has access to
     */
    public function getDatabases($username)
    {
        $url      = UrlHelper::buildUrl(Urls::URL_USER, [$username, 'database']);
        $response = $this->getConnection()->get($url);

        $data = $response->getJson();

        return $data['result'];
    }


    /**
     * Gets the list of collections a user has access to
     *
     * @param string $username     - username as a string
     * @param string $databaseName - name of the database
     *
     * @return string
     */
    public function getDatabasePermissionLevel($username, $databaseName)
    {
        $url      = UrlHelper::buildUrl(Urls::URL_USER, [$username, 'database', $databaseName]);
        $response = $this->getConnection()->get($url);

        $data = $response->getJson();

        return $data['result'];
    }


    /**
     * Gets the list of collections a user has access to
     *
     * @param string $username       - username as a string
     * @param string $databaseName   - name of the database
     * @param string $collectionName - name of the collection
     *
     * @return string
     */
    public function getCollectionPermissionLevel($username, $databaseName, $collectionName)
    {
        $url      = UrlHelper::buildUrl(Urls::URL_USER, [$username, 'database', $databaseName, $collectionName]);
        $response = $this->getConnection()->get($url);

        $data = $response->getJson();

        return $data['result'];
    }

}

class_alias(UserHandler::class, '\triagens\ArangoDb\UserHandler');
