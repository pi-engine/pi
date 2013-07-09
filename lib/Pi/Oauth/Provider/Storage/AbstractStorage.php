<?php
namespace Pi\Oauth\Provider\Storage;

use Pi\Oauth\Service;

abstract class AbstractStorage implements ModelInterface
{
    protected $config = array();
    protected $model;

    public function __construct(array $config = array())
    {
        $this->setConfig($config);
    }

    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        return $this;
    }

    public function setConfig(array $config)
    {
        if (isset($config['model']) && $config['model'] instanceof ModelInterface) {
            $this->model = $config['model'];
            unset($config['model']);
        }

        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function add($params)
    {
        if ($this instanceof CodeInterface) {
            if (!isset($params['expires'])) {
                $params['expires'] = time() + $this->config['expires_in'];
            }
            if (!isset($params['resource_owner'])) {
                $params['resource_owner'] = Service::resourceOwner();
            }
        }

        $result = $this->model->add($params);
        return $result;
    }

    public function get($id)
    {
        $params = $this->model->get($id);
        if ($params && $this instanceof CodeInterface) {
            if (!empty($params['expires']) && $params['expires'] < time()) {
                $params = false;
            }
        }

        return $params;
    }

    public function update($id, $params)
    {
        if ($this instanceof CodeInterface && !isset($params['expires'])) {
            $params['expires'] = time() + $this->config['expires_in'];
        }

        $result = $this->model->update($id, $params);
        return $result;
    }

    public function delete($id)
    {
        $result = $this->model->delete($id);
        return $result;
    }

    public function expire($expires = null)
    {
        if (!$this instanceof CodeInterface) {
            return true;
        }
        $expires = $expires ?: time();
        return $this->model->expire($expires);
    }

    /**
     * Generates an unique code.
     *
     * @return string
     *
     * @ingroup oauth2_section_4
     */
    protected function generateCode($len = 40)
    {
        if (file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 100) . uniqid(mt_rand(), true);
        } else {
            $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        }
        return substr(hash('sha512', $randomData), 0, $len);
    }
}