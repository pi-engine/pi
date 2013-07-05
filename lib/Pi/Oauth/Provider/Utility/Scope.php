<?php
namespace Pi\Oauth\Provider\Utility;

class Scope
{
    protected $scope = array();

    public function __construct($scope)
    {
        if ($scope) {
            if (is_array($scope)) {
                $scope = implode(' ', array_values($scope));
            }
            $this->scope = $this->canonize($scope);
        }
    }

    protected function validate($scope)
    {
        /**
         * @see http://tools.ietf.org/html/rfc6749#section-3.3
         *  scope       = scope-token *( SP scope-token )
         *  scope-token = 1*( %x21 / %x23-5B / %x5D-7E )
         */
        $scopeToken = '(?:\x21|[\x23-\x5B]|[\x5D-\x7E])+';
        $scopePattern = '/^' . $scopeToken . '(?:\x20' . $scopeToken . ')*$/';
        $result = preg_match($scopePattern, $scope);

        return (bool) $result;
    }

    protected function canonize($scope)
    {
        // Transform to string
        if (is_array($scope)) {
            $scope = implode(' ', array_values($scope));
        }
        if (!$this->validate($scope)) {
            throw new \Exception('invalid scope');
        }
        // Transform to array
        $scope = explode(' ', $scope);
        // Remove empty and duplicates
        $scope = array_unique(array_filter($scope));
        // Sort
        sort($scope, SORT_STRING);

        return $scope;
    }

    public function getScope($asArray = false)
    {
        return $asArray ? $this->scope : implode(' ', $this->scope);
    }

    /**
     * This object scope needs to contain all the scopes from the provided
     * scope object.
     */
    public function hasScope(Scope $scope)
    {
        $s = $scope->getScope(true);
        $arrayDiff = array_diff($s, $this->scope);
        return $arrayDiff ? false : true;
    }

    /**
     * This object scope needs to be a subset of the provided scope object.
     */
    public function isSubsetOf(Scope $scope)
    {
        $s = $scope->getScope(true);
        $arrayDiff = array_diff($this->scope, $s);
        return $arrayDiff ? false : true;
    }

    public function mergeWith(Scope $scope)
    {
        $this->scope = $this->canonize(array_merge($this->scope, $scope->getScope(true)));
        return $this;
    }
}