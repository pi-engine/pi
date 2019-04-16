<?php

/**
 * ArangoDB PHP client: query handling
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2015, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

class QueryHandler extends Handler
{
    /**
     * Clears the list of slow queries
     *
     * @throws Exception
     */
    public function clearSlow()
    {
        $url = UrlHelper::buildUrl(Urls::URL_QUERY, ['slow']);
        $this->getConnection()->delete($url);
    }

    /**
     * Returns the list of slow queries
     *
     * @throws Exception
     *
     * @return array
     */
    public function getSlow()
    {
        $url      = UrlHelper::buildUrl(Urls::URL_QUERY, ['slow']);
        $response = $this->getConnection()->get($url);

        return $response->getJson();
    }

    /**
     * Returns the list of currently executing queries
     *
     * @throws Exception
     *
     * @return array
     */
    public function getCurrent()
    {
        $url      = UrlHelper::buildUrl(Urls::URL_QUERY, ['current']);
        $response = $this->getConnection()->get($url);

        return $response->getJson();
    }

    /**
     * Kills a specific query
     *
     * This will send an HTTP DELETE command to the server to terminate the specified query
     *
     * @param string $id - query id
     *
     * @throws Exception
     *
     * @return bool
     */
    public function kill($id)
    {
        $url = UrlHelper::buildUrl(Urls::URL_QUERY, [$id]);
        $this->getConnection()->delete($url);

        return true;
    }

}

class_alias(QueryHandler::class, '\triagens\ArangoDb\QueryHandler');
