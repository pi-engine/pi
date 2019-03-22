<?php

/**
 * ArangoDB PHP client: Base URLs
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Some basic URLs
 *
 * @package ArangoDBClient
 * @since   0.2
 */
abstract class Urls
{
    /**
     * URL base part for document-related CRUD operations REST calls
     */
    const URL_DOCUMENT = '/_api/document';

    /**
     * URL base part for edge-related CRUD operations REST calls
     */
    const URL_EDGE = '/_api/document';

    /**
     * URL base part for all retrieving connected edges
     */
    const URL_EDGES = '/_api/edges';

    /**
     * URL base part for all graph-related REST calls
     */
    const URL_GRAPH = '/_api/gharial';
    
    /**
     * URL base part for all view-related REST calls
     */
    const URL_VIEW = '/_api/view';

    /**
     * URL part vertex-related graph REST calls
     */
    const URLPART_VERTEX = 'vertex';

    /**
     * URL part for edge-related graph REST calls
     */
    const URLPART_EDGE = 'edge';

    /**
     * URL base part for all collection-related REST calls
     */
    const URL_COLLECTION = '/_api/collection';

    /**
     * URL base part for all index-related REST calls
     */
    const URL_INDEX = '/_api/index';

    /**
     * base URL part for cursor related operations
     */
    const URL_CURSOR = '/_api/cursor';

    /**
     * URL for export related operations
     */
    const URL_EXPORT = '/_api/export';

    /**
     * URL for AQL explain-related operations
     */
    const URL_EXPLAIN = '/_api/explain';

    /**
     * URL for AQL query validation-related operations
     */
    const URL_QUERY = '/_api/query';

    /**
     * URL for select-by-example
     */
    const URL_EXAMPLE = '/_api/simple/by-example';

    /**
     * URL for first-example
     */
    const URL_FIRST_EXAMPLE = '/_api/simple/first-example';

    /**
     * URL for any
     */
    const URL_ANY = '/_api/simple/any';

    /**
     * URL for fulltext
     */
    const URL_FULLTEXT = '/_api/simple/fulltext';

    /**
     * URL remove-by-example
     */
    const URL_REMOVE_BY_EXAMPLE = '/_api/simple/remove-by-example';

    /**
     * URL for remove-by-keys
     */
    const URL_REMOVE_BY_KEYS = '/_api/simple/remove-by-keys';

    /**
     * URL for update-by-example
     */
    const URL_UPDATE_BY_EXAMPLE = '/_api/simple/update-by-example';

    /**
     * URL for replace-by-example
     */
    const URL_REPLACE_BY_EXAMPLE = '/_api/simple/replace-by-example';

    /**
     * URL for lookup-by-keys
     */
    const URL_LOOKUP_BY_KEYS = '/_api/simple/lookup-by-keys';

    /**
     * URL for select-range
     */
    const URL_RANGE = '/_api/simple/range';

    /**
     * URL for select-all
     */
    const URL_ALL = '/_api/simple/all';

    /**
     * URL for select-all-keys
     */
    const URL_ALL_KEYS = '/_api/simple/all-keys';

    /**
     * URL for select-range
     */
    const URL_NEAR = '/_api/simple/near';

    /**
     * URL for select-range
     */
    const URL_WITHIN = '/_api/simple/within';

    /**
     * URL for document import
     */
    const URL_IMPORT = '/_api/import';

    /**
     * URL for batch processing
     */
    const URL_BATCH = '/_api/batch';

    /**
     * URL for transactions
     */
    const URL_TRANSACTION = '/_api/transaction';
    
    /**
     * URL for storage engine
     */
    const URL_ENGINE = '/_api/engine';

    /**
     * URL for admin version
     */
    const URL_ADMIN_VERSION = '/_api/version';

    /**
     * URL for server role
     */
    const URL_ADMIN_SERVER_ROLE = '/_admin/server/role';

    /**
     * URL for admin time
     */
    const URL_ADMIN_TIME = '/_admin/time';

    /**
     * URL for admin log
     */
    const URL_ADMIN_LOG = '/_admin/log';

    /**
     * base URL part for admin routing reload
     */
    const URL_ADMIN_ROUTING_RELOAD = '/_admin/routing/reload';

    /**
     * base URL part for admin statistics
     */
    const URL_ADMIN_STATISTICS = '/_admin/statistics';

    /**
     * base URL part for admin statistics-description
     */
    const URL_ADMIN_STATISTICS_DESCRIPTION = '/_admin/statistics-description';

    /**
     * base URL part for AQL user functions statistics
     */
    const URL_AQL_USER_FUNCTION = '/_api/aqlfunction';

    /**
     * base URL part for user management
     */
    const URL_USER = '/_api/user';

    /**
     * base URL part for user management
     */
    const URL_TRAVERSAL = '/_api/traversal';

    /**
     * base URL part for endpoint management
     */
    const URL_ENDPOINT = '/_api/endpoint';

    /**
     * base URL part for database management
     */
    const URL_DATABASE = '/_api/database';

    /**
     * URL for AQL query result cache
     */
    const URL_QUERY_CACHE = '/_api/query-cache';

    /**
     * URL for file uploads
     */
    const URL_UPLOAD = '/_api/upload';

    /**
     * URL for foxx-app installations
     */
    const URL_FOXX_INSTALL = '/_admin/foxx/install';

    /**
     * URL for foxx-app deinstallation
     */
    const URL_FOXX_UNINSTALL = '/_admin/foxx/uninstall';
}

class_alias(Urls::class, '\triagens\ArangoDb\Urls');
