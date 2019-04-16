<?php

/**
 * ArangoDB PHP client: foxx upload
 *
 * @package   ArangoDBClient
 * @author    Tom Regner <thomas.regner@fb-research.de>
 * @copyright Copyright 2016, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * A class for uploading Foxx application zips to a database
 *
 * @package   ArangoDBClient
 * @since     3.1
 */
class FoxxHandler extends Handler
{
    /**
     * Upload and install a foxx app.
     *
     * @throws ClientException
     *
     * @param string $localZip   - the path to the local foxx-app zip-archive to upload/install
     * @param string $mountPoint - the mount-point for the app, must begin with a '/'
     * @param array  $options    - for future usage
     *
     * @return array - the server response
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function installFoxxZip($localZip, $mountPoint, array $options = [])
    {
        if (!file_exists($localZip)) {
            throw new ClientException("Foxx-Zip {$localZip} does not exist (or file is unreadable).");
        }

        try {
            $post     = file_get_contents($localZip);
            $response = $this->getConnection()->post(Urls::URL_UPLOAD, $post);

            if ($response->getHttpCode() < 400) {
                $response = $this->getConnection()->put(Urls::URL_FOXX_INSTALL, json_encode(['appInfo' => $response->getJson()['filename'], 'mount' => $mountPoint]));
                if ($response->getHttpCode() < 400) {
                    return $response->getJson();
                }

                throw new ClientException('Foxx-Zip install failed');
            }

            throw new ClientException('Foxx-Zip upload failed');
        } catch (ServerException $e) {
            throw new ClientException($e->getMessage());
        }
    }

    /**
     * Remove a foxx-app.
     *
     * @throws ClientException
     *
     * @param string $mountPoint - the mount-point for the app, must begin with a '/'
     * @param array  $options    - for future usage
     *
     * @return array - the server response
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeFoxxApp($mountPoint, array $options = [])
    {
        try {
            $response = $this->getConnection()->put(Urls::URL_FOXX_UNINSTALL, json_encode(['mount' => $mountPoint]));
            if ($response->getHttpCode() < 400) {
                return $response->getJson();
            }

            throw new ClientException(sprintf('Foxx uninstall failed (Code: %d)', $response->getHttpCode()));
        } catch (ServerException $e) {
            if ($e->getMessage() === 'Service not found') {
                throw new ClientException(sprintf('Mount point %s not present.', $mountPoint));
            }
            throw new ClientException($e->getMessage());
        }
    }
}

class_alias(FoxxHandler::class, '\triagens\ArangoDb\FoxxHandler');
