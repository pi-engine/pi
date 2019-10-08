<?php
/**
 * ArangoDB PHP client testsuite
 * File: ExportTest.php
 *
 * @package ArangoDBClient
 * @author  Jan Steemann
 */

namespace ArangoDBClient;

/**
 * @property Connection        $connection
 * @property Collection        $collection
 * @property CollectionHandler $collectionHandler
 * @property DocumentHandler   $documentHandler
 *
 * @package ArangoDBClient
 */
class ExportTest extends
    \PHPUnit_Framework_TestCase
{
    protected static $testsTimestamp;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        static::$testsTimestamp = str_replace('.', '_', (string) microtime(true));
    }


    public function setUp()
    {
        $this->connection        = getConnection();
        $this->collectionHandler = new CollectionHandler($this->connection);

        // clean up first
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        $this->collection = new Collection();
        $this->collection->setName('ArangoDB_PHP_TestSuite_TestCollection' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->collection);

        $this->documentHandler = new DocumentHandler($this->connection);

        $adminHandler       = new AdminHandler($this->connection);
        $version            = preg_replace('/-[a-z0-9]+$/', '', $adminHandler->getServerVersion());
        $this->hasExportApi = (version_compare($version, '2.6.0') >= 0);
        if (isCluster($this->connection)) {
            $this->hasExportApi = false;
        }
    }

    /**
     * Test export empty collection
     */
    public function testExportEmpty()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
            return;
        }
        $connection = $this->connection;

        $export = new Export($connection, $this->collection, []);
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNull($cursor->getId());

        // we're not expecting any results 
        static::assertEquals(0, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export some documents
     */
    public function testExportDocuments()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        $connection = $this->connection;
        for ($i = 0; $i < 100; ++$i) {
            $this->documentHandler->save($this->collection, ['value' => $i]);
        }

        $export = new Export($connection, $this->collection, []);
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNull($cursor->getId());

        static::assertEquals(100, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = $cursor->getNextBatch();
        static::assertCount(100, $all);

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export some documents w/ multiple fetches
     */
    public function testExportDocumentsTwoFetches()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        $connection = $this->connection;
        $statement  = new Statement(
            $connection, [
                'query' => "FOR i IN 1..1001 INSERT { _key: CONCAT('test', i), value: i } IN " . $this->collection->getName()
            ]
        );
        $statement->execute();

        $export = new Export($connection, $this->collection, []);
        $cursor = $export->execute();

        static::assertNotNull($cursor->getId());
        static::assertEquals(1, $cursor->getFetches());

        static::assertEquals(1001, $cursor->getCount());

        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertEquals(2, $cursor->getFetches());
        static::assertCount(1001, $all);

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export some documents w/ multiple fetches
     */
    public function testExportDocumentsMultipleFetches()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        $connection = $this->connection;
        $statement  = new Statement(
            $connection, [
                'query' => "FOR i IN 1..5000 INSERT { _key: CONCAT('test', i), value: i } IN " . $this->collection->getName()
            ]
        );
        $statement->execute();

        $export = new Export($connection, $this->collection, []);
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(5000, $cursor->getCount());
        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertEquals(5, $cursor->getFetches());
        static::assertCount(5000, $all);

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export some documents
     */
    public function testExportDocumentsWithSmallBatchSize()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        $connection = $this->connection;
        $statement  = new Statement(
            $connection, [
                'query' => "FOR i IN 1..5000 INSERT { _key: CONCAT('test', i), value: i } IN " . $this->collection->getName()
            ]
        );
        $statement->execute();

        $export = new Export($connection, $this->collection, ['batchSize' => 100]);
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(5000, $cursor->getCount());
        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertEquals(50, $cursor->getFetches());
        static::assertCount(5000, $all);

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export as Document object
     */
    public function testExportDocumentObjects()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        for ($i = 0; $i < 100; ++$i) {
            $this->documentHandler->save($this->collection, ['value' => $i]);
        }

        $export = new Export($this->connection, $this->collection, ['_flat' => false]);
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNull($cursor->getId());

        static::assertEquals(100, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = $cursor->getNextBatch();
        static::assertCount(100, $all);

        foreach ($all as $doc) {
            static::assertInstanceOf(Document::class, $doc);
        }

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export as Edge object
     */
    public function testExportEdgeObjects()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestEdge' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
        }

        $edgeCollection = new Collection();
        $edgeCollection->setName('ArangoDB_PHP_TestSuite_TestEdge' . '_' . static::$testsTimestamp);
        $edgeCollection->setType(Collection::TYPE_EDGE);
        $this->collectionHandler->create($edgeCollection);

        $edgeHandler = new EdgeHandler($this->connection);

        $vertexCollection = $this->collection->getName();

        for ($i = 0; $i < 100; ++$i) {
            $edgeHandler->saveEdge($edgeCollection, $vertexCollection . '/1', $vertexCollection . '/2', ['value' => $i]);
        }

        $export = new Export($this->connection, $edgeCollection, ['_flat' => false]);
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNull($cursor->getId());

        static::assertEquals(100, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = $cursor->getNextBatch();
        static::assertCount(100, $all);

        foreach ($all as $doc) {
            static::assertInstanceOf(Document::class, $doc);
            static::assertInstanceOf(Edge::class, $doc);
        }

        static::assertFalse($cursor->getNextBatch());

        $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestEdge' . '_' . static::$testsTimestamp);
    }

    /**
     * Test export as flat array
     */
    public function testExportFlat()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        for ($i = 0; $i < 200; ++$i) {
            $this->documentHandler->save($this->collection, ['value' => $i]);
        }

        $export = new Export($this->connection, $this->collection, ['batchSize' => 50, '_flat' => true]);
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(200, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertCount(200, $all);

        foreach ($all as $doc) {
            static::assertNotInstanceOf(Document::class, $doc);
            static::assertTrue(is_array($doc));
        }

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export with limit
     */
    public function testExportLimit()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        for ($i = 0; $i < 200; ++$i) {
            $this->documentHandler->save($this->collection, ['value' => $i]);
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'batchSize' => 50,
                '_flat'     => true,
                'limit'     => 107
            ]
        );
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(107, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertCount(107, $all);

        foreach ($all as $doc) {
            static::assertNotInstanceOf(Document::class, $doc);
            static::assertTrue(is_array($doc));
        }

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export with include restriction
     */
    public function testExportRestrictInclude()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        for ($i = 0; $i < 200; ++$i) {
            $this->documentHandler->save($this->collection, ['value1' => $i, 'value2' => 'test' . $i]);
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'batchSize' => 50,
                '_flat'     => true,
                'restrict'  => ['type' => 'include', 'fields' => ['_key', 'value2']]
            ]
        );
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(200, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertCount(200, $all);

        foreach ($all as $doc) {
            static::assertTrue(is_array($doc));
            static::assertCount(2, $doc);
            static::assertFalse(isset($doc['_id']));
            static::assertTrue(isset($doc['_key']));
            static::assertFalse(isset($doc['_rev']));
            static::assertFalse(isset($doc['value1']));
            static::assertTrue(isset($doc['value2']));
        }

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export with include restriction
     */
    public function testExportRestrictIncludeNonExisting()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        for ($i = 0; $i < 200; ++$i) {
            $this->documentHandler->save($this->collection, ['value1' => $i, 'value2' => 'test' . $i]);
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'batchSize' => 50,
                '_flat'     => true,
                'restrict'  => ['type' => 'include', 'fields' => ['foobar', 'baz']]
            ]
        );
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(200, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertCount(200, $all);

        foreach ($all as $doc) {
            static::assertTrue(is_array($doc));
            static::assertEquals([], $doc);
        }

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export with exclude restriction
     */
    public function testExportRestrictExclude()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        for ($i = 0; $i < 200; ++$i) {
            $this->documentHandler->save($this->collection, ['value1' => $i, 'value2' => 'test' . $i]);
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'batchSize' => 50,
                '_flat'     => true,
                'restrict'  => ['type' => 'exclude', 'fields' => ['_key', 'value2']]
            ]
        );
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(200, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertCount(200, $all);

        foreach ($all as $doc) {
            static::assertTrue(is_array($doc));
            static::assertCount(3, $doc);
            static::assertFalse(isset($doc['_key']));
            static::assertTrue(isset($doc['_rev']));
            static::assertTrue(isset($doc['_id']));
            static::assertTrue(isset($doc['value1']));
            static::assertFalse(isset($doc['value2']));
        }

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export with non-existing fields restriction
     */
    public function testExportRestrictExcludeNonExisting()
    {
        if (!$this->hasExportApi) {
            $this->markTestSkipped("test is only meaningful with export API being present");
        }
        for ($i = 0; $i < 200; ++$i) {
            $this->documentHandler->save($this->collection, ['value1' => $i, 'value2' => 'test' . $i]);
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'batchSize' => 50,
                '_flat'     => true,
                'restrict'  => ['type' => 'include', 'fields' => ['_id', 'foobar', 'baz']]
            ]
        );
        $cursor = $export->execute();

        static::assertEquals(1, $cursor->getFetches());
        static::assertNotNull($cursor->getId());

        static::assertEquals(200, $cursor->getCount());
        static::assertEquals(1, $cursor->getFetches());

        $all = [];
        while ($more = $cursor->getNextBatch()) {
            $all = array_merge($all, $more);
        }
        static::assertCount(200, $all);

        foreach ($all as $doc) {
            static::assertTrue(is_array($doc));
            static::assertCount(1, $doc);
            static::assertTrue(isset($doc['_id']));
            static::assertFalse(isset($doc['foobar']));
        }

        static::assertFalse($cursor->getNextBatch());
    }

    /**
     * Test export with invalid restriction definition
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testExportRestrictInvalidType()
    {
        if (!$this->hasExportApi) {
            throw new ClientException('Invalid restrictions type definition');
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'restrict' => ['type' => 'foo', 'fields' => ['_key']]
            ]
        );
        $export->execute();
    }

    /**
     * Test export with invalid restriction definition
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testExportRestrictMissingType()
    {
        if (!$this->hasExportApi) {
            throw new ClientException('Invalid restrictions type definition');
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'restrict' => ['fields' => ['_key']]
            ]
        );
        $export->execute();
    }

    /**
     * Test export with invalid restriction definition
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testExportRestrictInvalidFields()
    {
        if (!$this->hasExportApi) {
            throw new ClientException('Invalid restrictions fields definition');
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'restrict' => ['type' => 'include', 'fields' => 'foo']
            ]
        );
        $export->execute();
    }

    /**
     * Test export with invalid restriction definition
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testExportRestrictMissingFields()
    {
        if (!$this->hasExportApi) {
            throw new ClientException('Invalid restrictions fields definition');
        }

        $export = new Export(
            $this->connection, $this->collection, [
                'restrict' => ['type' => 'include']
            ]
        );
        $export->execute();
    }

    public function tearDown()
    {
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        unset($this->documentHandler, $this->collectionHandler, $this->collection, $this->connection);
    }

}
