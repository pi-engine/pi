<?php

/**
 * ArangoDB PHP client: view handler
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2019, ArangoDB GmbH, Cologne, Germany
 *
 * @since     3.4
 */

namespace ArangoDBClient;

/**
 * A handler that manages views.
 *
 * @package ArangoDBClient
 * @since   3.4
 */
class ViewHandler extends Handler
{
    /**
     * rename option
     */
    const OPTION_RENAME = 'rename';

    /**
     * Create a view
     *
     * This will create a view using the given view object and return an array of the created view object's attributes.<br><br>
     *
     * @throws Exception
     *
     * @param View $view - The view object which holds the information of the view to be created
     *
     * @return array
     * @since   3.4
     */
    public function create(View $view)
    {
        $params   = [
            View::ENTRY_NAME       => $view->getName(),
            View::ENTRY_TYPE       => $view->getType(),
        ];
        $url      = Urls::URL_VIEW;
        $response = $this->getConnection()->post($url, $this->json_encode_wrapper($params));
        $json     = $response->getJson();
        $view->setId($json[View::ENTRY_ID]);

        return $view->getAll();
    }

    /**
     * Get a view
     *
     * This will get a view.<br><br>
     *
     * @param String $view    - The name of the view
     *
     * @return View|false
     * @throws \ArangoDBClient\ClientException
     * @since   3.4
     */
    public function get($view)
    {
        $url = UrlHelper::buildUrl(Urls::URL_VIEW, [$view]);

        $response = $this->getConnection()->get($url);
        $data = $response->getJson();

        $result = new View($data[View::ENTRY_NAME], $data[View::ENTRY_TYPE]);
        $result->setId($data[View::ENTRY_ID]);

        return $result;
    }

    /**
     * Get a view's properties<br><br>
     *
     * @throws Exception
     *
     * @param mixed $view - view name as a string or instance of View
     *
     * @return array - Returns an array of attributes. Will throw if there is an error
     * @since 3.4
     */
    public function properties($view)
    {
        if ($view instanceof View) {
            $view = $view->getName();
        }

        $url = UrlHelper::buildUrl(Urls::URL_VIEW, [$view, 'properties']);
        $result = $this->getConnection()->get($url);

        return $result->getJson();
    }
    
    /**
     * Set a view's properties<br><br>
     *
     * @throws Exception
     *
     * @param mixed $view       - view name as a string or instance of View
     * @param array $properties - array with view properties
     *
     * @return array - Returns an array of attributes. Will throw if there is an error
     * @since 3.4
     */
    public function setProperties($view, array $properties)
    {
        if ($view instanceof View) {
            $view = $view->getName();
        }

        $url = UrlHelper::buildUrl(Urls::URL_VIEW, [$view, 'properties']);
        $response = $this->getConnection()->put($url, $this->json_encode_wrapper($properties));
        $json     = $response->getJson();

        return $json;
    }


    /**
     * Drop a view<br><br>
     *
     * @throws Exception
     *
     * @param mixed $view - view name as a string or instance of View
     *
     * @return bool - always true, will throw if there is an error
     * @since 3.4
     */
    public function drop($view) 
    {
        if ($view instanceof View) {
            $view = $view->getName();
        }

        $url = UrlHelper::buildUrl(Urls::URL_VIEW, [$view]);
        $this->getConnection()->delete($url);

        return true;
    }
    
    /**
     * Rename a view
     *
     * @throws Exception
     *
     * @param mixed $view - view name as a string or instance of View
     * @param string $name       - new name for collection
     *
     * @return bool - always true, will throw if there is an error
     */
    public function rename($view, $name)
    {
        if ($view instanceof View) {
            $view = $view->getName();
        }

        $params = [View::ENTRY_NAME => $name];
        $this->getConnection()->put(
            UrlHelper::buildUrl(Urls::URL_VIEW, [$view, self::OPTION_RENAME]),
            $this->json_encode_wrapper($params)
        );

        return true;
    }
}

class_alias(ViewHandler::class, '\triagens\ArangoDb\ViewHandler');
