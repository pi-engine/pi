<?php
namespace Pi\Oauth\Provider\Result;

class Error extends Response
{
    protected $error;
    protected $errors = array();
    protected $errorType = 'error';

    public function __construct($error, $errorDescription = null, $errorUri = null, $statusCode = null)
    {
        if (is_array($error)) {
            $errorDescription = isset($error['error_description']) ? $error['error_description'] : null;
            $errorUri = isset($error['error_uri']) ? $error['error_uri'] : null;
            $statusCode = isset($error['status_code']) ? $error['status_code'] : null;
            $error = $error['error'];
        }
        $this->setError($error, $errorDescription, $errorUri, $statusCode);
    }

    public function setError($error, $errorDescription = null, $errorUri = null, $statusCode = null)
    {
        if (!empty($this->errors) && !isset($this->errors[$error])) {
            throw new \Exception('Invalid error type.');
        }
        $this->setErrorCode($error)
            ->setErrorDescription($errorDescription)
            ->setErrorUri($errorUri)
            ->errorType('error');
        $this->setStatusCode(null === $statusCode ? 400 : $statusCode);
        $this->addHeaderLine('Cache-Control', 'no-store');

        return $this;
    }

    public function setErrorCode($error)
    {
        if (!empty($this->errors) && !isset($this->errors[$error])) {
            throw new \Exception('Invalid error code.');
        }
        $this->error = $error;
        $this->setParam('error', $error);
        return $this;
    }

    public function setErrorDescription($errorDescription)
    {
        if (!$errorDescription && !empty($this->errors[$this->error])) {
            $errorDescription = $this->errors[$this->error];
        }
        if (null !== $errorDescription) {
            $this->setParam('error_description', $errorDescription);
        }
        return $this;
    }

    public function setErrorUri($errorUri)
    {
        if ( null !== $errorUri) {
            if ($errorUri && $errorUri[0] == '#') {
                // we are referencing an oauth bookmark (for brevity)
                $errorUri = 'http://tools.ietf.org/html/rfc6749' . $errorUri;
            }
            $this->setParam('error_uri', $errorUri);
        }
        return $this;
    }
}