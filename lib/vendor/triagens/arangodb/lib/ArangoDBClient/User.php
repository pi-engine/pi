<?php

/**
 * ArangoDB PHP client: single user document
 *
 * @package   ArangoDBClient
 * @author    Frank Mayer
 * @since     1.2
 */

namespace ArangoDBClient;

/**
 * Value object representing a single User document
 *
 * @property string     user
 * @property mixed|null passwd
 * @property mixed|null active
 * @property array|null extra
 * @package   ArangoDBClient
 * @since     1.2
 */
class User extends Document
{

}

class_alias(User::class, '\triagens\ArangoDb\User');
