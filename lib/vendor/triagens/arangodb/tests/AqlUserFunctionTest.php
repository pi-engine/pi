<?php
/**
 * ArangoDB PHP client testsuite
 * File: AqlUserFunction.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class AqlUserFunctionTest
 * Basic Tests for the Graph API implementation
 *
 * @property Connection        $connection
 *
 * @property CollectionHandler collectionHandler
 *
 * @package ArangoDBClient
 */
class AqlUserFunctionTest extends
    \PHPUnit_Framework_TestCase
{
    protected static $testsTimestamp;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        static::$testsTimestamp = str_replace('.', '', (string) microtime(true));
    }

    public function setUp()
    {
        $this->connection = getConnection();

        $this->cleanup();
    }


    /**
     * Clean up existing functions in the test namespace
     */
    private function cleanup()
    {
        $userFunction = new AqlUserFunction($this->connection);
        $list         = $this->filter($userFunction->getRegisteredUserFunctions());

        foreach ($list as $func) {
            $userFunction->unregister($func['name']);
        }
    }

    /**
     * Filters a list of functions. only functions in namespace "phpTestFunctions::'.  static::$testsTimestamp.'::" will be returned
     *
     * @param $list
     *
     * @return array
     */
    private function filter($list)
    {
        $result = [];
        foreach ($list as $value) {
            if (strpos(@$value['name'], 'phpTestFunctions') === 0) {
                $result[] = $value;
            }
        }

        return $result;
    }


    /**
     * Test if AqlUserFunctions can be registered, listed and unregistered
     */
    public function testRegisterListAndUnRegisterAqlUserFunctionWithInitialConfig()
    {
        $name = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction';
        $code = 'function (celsius) { return celsius * 1.8 + 32; }';

        $array = [
            'name' => $name,
            'code' => $code
        ];

        $userFunction = new AqlUserFunction($this->connection, $array);

        $result = $userFunction->register();
        
        static::assertTrue($result['isNewlyCreated']);

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
        $list = $this->filter($userFunction->getRegisteredUserFunctions());

        static::assertCount(1, $list, 'List returned did not return expected 1 attribute (This might happen, if tests are run in parallel)');
        static::assertTrue(
            $list[0]['name'] === $name && $list[0]['code'] === $code,
            'did not return expected Function. Instead returned: ' . $list[0]['name'] . ' and ' . $list[0]['code']
        );

        $result = $userFunction->unregister();

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
    }

    /**
     * Test if AqlUserFunctions can be registered, listed and unregistered using the register() shortcut method
     */
    public function testRegisterListAndUnRegisterAqlUserFunctionUsingShortcut()
    {

        $name = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction';
        $code = 'function (celsius) { return celsius * 1.8 + 32; }';

        $userFunction = new AqlUserFunction($this->connection);

        $result = $userFunction->register($name, $code);
        static::assertTrue($result['isNewlyCreated']);

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
        $list = $this->filter($userFunction->getRegisteredUserFunctions());

        static::assertCount(1, $list, 'List returned did not return expected 1 attribute (This might happen, if tests are run in parallel)');
        static::assertTrue(
            $list[0]['name'] === $name && $list[0]['code'] === $code,
            'did not return expected Function. Instead returned: ' . $list[0]['name'] . ' and ' . $list[0]['code']
        );

        $result = $userFunction->unregister($name);

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
    }

    /**
     * Test if AqlUserFunctions can be registered, listed and unregistered with getters and setters
     */
    public function testRegisterListAndUnRegisterAqlUserFunctionWithGettersAndSetters()
    {
        $name = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction';
        $code = 'function (celsius) { return celsius * 1.8 + 32; }';

        $userFunction = new AqlUserFunction($this->connection);
        $userFunction->setName($name);
        $userFunction->setCode($code);

        static::assertTrue(isset($userFunction->name), 'Should return true, as the attribute was set, before.');

        // check if getters work fine
        static::assertEquals(
            $name, $userFunction->getName(), 'Did not return name, instead returned: ' . $userFunction->getName()
        );
        static::assertEquals(
            $code, $userFunction->getCode(), 'Did not return code, instead returned: ' . $userFunction->getCode()
        );


        // also check setters/getters if wrong/no attribute is given
        static::assertEquals(
            null, $userFunction->getFakeAttributeName, 'Getter with unknown attribute did not return null, instead returned: ' . $userFunction->getFakeAttributeName
        );

        static::assertEquals(
            null, $userFunction->setFakeAttributeName, 'Setter with unknown attribute did not return chainable object, instead returned..: ' . $userFunction->setFakeAttributeName
        );

        // Check setting/getting class properties via set/get methods
        static::assertSame(
            $userFunction, $userFunction->set('FakeAttributeName', 1), 'Set-method did not return chainable object'
        );
        static::assertSame(
            1, $userFunction->get('FakeAttributeName'), 'Get-method did not return previously set property'
        );

        // Check giving the set method a non-string key
        $caught = false;
        try {
            $userFunction->set(1, 1);
        } catch (ClientException $e) {
            $caught = true;
        }

        static::assertTrue($caught);


        $result = $userFunction->register();
        static::assertTrue($result['isNewlyCreated']);

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
        $list = $this->filter($userFunction->getRegisteredUserFunctions());
        static::assertCount(1, $list, 'List returned did not return expected 1 attribute');
        static::assertTrue(
            $list[0]['name'] === $name && $list[0]['code'] === $code,
            'did not return expected Function. Instead returned: ' . $list[0]['name'] . ' and ' . $list[0]['code']
        );

        $result = $userFunction->unregister();

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
    }


    /**
     * Test if AqlUserFunctions can be registered, listed and unregistered
     */
    public function testRegisterListAndUnRegisterAqlUserFunctionWithWithMagicSettersAndGetters()
    {

        $name = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction';
        $code = 'function (celsius) { return celsius * 1.8 + 32; }';


        $userFunction = new AqlUserFunction($this->connection);

        $userFunction->name = $name;
        $userFunction->code = $code;

        // check if getters work fine
        static::assertEquals(
            $name, $userFunction->name, 'Did not return name, instead returned: ' . $userFunction->name
        );
        static::assertEquals(
            $code, $userFunction->code, 'Did not return code, instead returned: ' . $userFunction->code
        );

        $result = $userFunction->register();

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
        $list = $this->filter($userFunction->getRegisteredUserFunctions());
        static::assertCount(1, $list, 'List returned did not return expected 1 attribute');
        static::assertTrue(
            $list[0]['name'] === $name && $list[0]['code'] === $code,
            'did not return expected Function. Instead returned: ' . $list[0]['name'] . ' and ' . $list[0]['code']
        );

        $result = $userFunction->unregister();

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );
    }


    /**
     * Test if AqlUserFunctions can be registered, listed and unregistered
     *
     */
    public function testReRegisterListAndUnRegisterAqlUserFunctionTwice()
    {

        $name = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction';
        $code = 'function (celsius) { return celsius * 1.8 + 32; }';


        $userFunction = new AqlUserFunction($this->connection);

        $userFunction->name = $name;
        $userFunction->code = $code;

        $result = $userFunction->register();
        static::assertTrue($result['isNewlyCreated']);

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );

        $result = $userFunction->register();
        static::assertFalse($result['isNewlyCreated']);

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );

        $list = $this->filter($userFunction->getRegisteredUserFunctions());
        static::assertCount(1, $list, 'List returned did not return expected 1 attribute');
        static::assertTrue(
            $list[0]['name'] === $name && $list[0]['code'] === $code,
            'did not return expected Function. Instead returned: ' . $list[0]['name'] . ' and ' . $list[0]['code']
        );

        $result = $userFunction->unregister();

        static::assertEquals(
            false, $result['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($result, 1)
        );

        $e = null;
        try {
            $userFunction->unregister();
        } catch (Exception $e) {
        }

        static::assertEquals(
            404, $e->getCode(), 'Did not return code 404, instead returned: ' . $e->getCode()
        );
    }

    /**
     * Test the namespace filter when getting the registered AQL functions.
     */
    public function testGetAQLFunctionsWithNamespaceFilter()
    {

        $name1 = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction';
        $name2 = 'phpTestFunctions1' . static::$testsTimestamp . '::myFunction';
        $code  = 'function (celsius) { return celsius * 1.8 + 32; }';

        //Setup
        $userFunction = new AqlUserFunction($this->connection);

        $userFunction->name = $name1;
        $userFunction->code = $code;

        $result = $userFunction->register();

        $userFunction = new AqlUserFunction($this->connection);

        $userFunction->name = $name2;
        $userFunction->code = $code;

        $result = $userFunction->register();

        $functions = $this->filter($userFunction->getRegisteredUserFunctions('phpTestFunctions' . static::$testsTimestamp));
        static::assertCount(1, $functions, 'phpTestFunctions namespace should contain 1 function.');

        $functions = $this->filter($userFunction->getRegisteredUserFunctions('phpTestFunctions1' . static::$testsTimestamp));
        static::assertCount(1, $functions, 'phpTestFunctions namespace should contain 1 function.');

        $userFunction->unregister($name1);
        $userFunction->unregister($name2);
    }

    /**
     * Un-register all AQL functions on a namespace.
     */
    public function testUnRegisterAQLFunctionsOnNamespace()
    {

        $name1 = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction1';
        $name2 = 'phpTestFunctions' . static::$testsTimestamp . '::myFunction2';
        $code  = 'function (celsius) { return celsius * 1.8 + 32; }';

        //Setup
        $userFunction = new AqlUserFunction($this->connection);

        $userFunction->name = $name1;
        $userFunction->code = $code;

        $result = $userFunction->register();

        $userFunction = new AqlUserFunction($this->connection);

        $userFunction->name = $name2;
        $userFunction->code = $code;

        $result = $userFunction->register();

        $functions = $this->filter($userFunction->getRegisteredUserFunctions('phpTestFunctions' . static::$testsTimestamp));
        static::assertCount(2, $functions, 'phpTestFunctions namespace should only contain 2 functions.');

        $userFunction->unregister('phpTestFunctions' . static::$testsTimestamp, true);

        $functions = $this->filter($userFunction->getRegisteredUserFunctions('phpTestFunctions' . static::$testsTimestamp));
        static::assertEmpty($functions, 'phpTestFunctions namespace should only contain no functions.');
    }

    public function tearDown()
    {
        $this->cleanup();
        unset($this->connection);
    }
}
