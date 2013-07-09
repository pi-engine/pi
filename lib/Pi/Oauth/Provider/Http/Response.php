<?php
namespace Pi\Oauth\Provider\Http;

use SimpleXMLElement;
use Zend\Http\PhpEnvironment\Response as HttpResponse;

class Response extends HttpResponse
{
    protected $format = 'json';
    protected $params = array();

    public function setParam($key, $value = null)
    {
        if (null !== $value) {
            $this->params[$key] = $value;
        } elseif (array_key_exist($key, $this->params)) {
            unset($this->params[$key]);
        }
        return $this;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function setFormat($format = 'json')
    {
        $this->format = $format;
        return $this;
    }

    protected function setHeader()
    {
        switch ($this->format) {
            case 'xml':
                $this->addHeaderLine('Content-Type', 'text/xml');
                break;
            case 'json':
            default:
                $this->addHeaderLine('Content-Type', 'application/json');
                break;
        }
        return $this;
    }


    public function setContent($params = array())
    {
        $params = $params ?: $this->params;
        $content = null;
        switch ($this->format) {
            case 'xml':
                // this only works for single-level arrays
                $xml = new SimpleXMLElement('<response/>');
                array_walk($params, array($xml, 'addChild'));
                $content = $xml->asXML();
            case 'json':
            default:
                $content = json_encode($params);
        }

        parent::setContent($content);
        return $this;
    }

    public function send($params = array())
    {
        $this->setHeader()->setContent($params);
        parent::send();
    }
}