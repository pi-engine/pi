<?php

namespace ArangoDBClient;

require __DIR__ . '/init.php';


abstract class AbstractEntity extends Document implements \JsonSerializable
{
    /**
     * Collection name.
     *
     * @var string
     */
    protected $_collectionName;

    /**
     * Constructor.
     *
     * {@inheritdoc}
     *
     * @param array $options - optional, initial $options for document
     *
     * @throws \Exception
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);

        if (empty($this->_collectionName)) {
            throw new \Exception('No collection name provided!!!', 666);
        }
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->_collectionName;
    }

    /**
     * Sets internal key (eg. when using in forms).
     *
     * @param string $key
     */
    public function setInternalKey($key)
    {
        parent::setInternalKey($key);
        if (empty($this->_id)) {
            $this->_id = $this->_collectionName . '/' . $key;
        }
    }

    /**
     * Called when entity is created
     */
    public function onCreate()
    {

    }

    /**
     * Called when entity is saved
     */
    public function onUpdate()
    {

    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *        which is a value of any type other than a resource.
     * @since 3.2
     */
    public function jsonSerialize()
    {
        return $this->getAll();
    }
}

abstract class AbstractCollection extends CollectionHandler
{

    /**
     * @var string collection name
     */
    protected $_collectionName;
    /**
     * @var DocumentHandler
     */
    protected $_documentHandler;

    /**
     * AbstractCollection constructor.
     *
     * {@inheritdoc}
     *
     * @param Connection $connection
     *
     * @throws \Exception
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        if (empty($this->_collectionName)) {
            throw new \Exception('No collection name provided!!!', 666);
        }

        $this->_documentHandler = new DocumentHandler($connection);
        $this->_documentHandler->setDocumentClass($this->_documentClass);
    }

    /**
     * @return string
     */
    public function getCollectionNameString()
    {
        return $this->_collectionName;
    }

    /**
     * Get document(s) by specifying an example
     *
     * This will throw if the list cannot be fetched from the server
     *
     *
     * @throws Exception
     *
     * @param mixed      $document     - the example document as a Document object or an array
     * @param bool|array $options      - optional, prior to v1.0.0 this was a boolean value for sanitize, since v1.0.0 it's an array of options.
     *                                 <p>Options are :<br>
     *                                 <li>'_sanitize'         - True to remove _id and _rev attributes from result documents. Defaults to false.</li>
     *                                 <li>'sanitize'          - Deprecated, please use '_sanitize'.</li>
     *                                 <li>'_hiddenAttributes' - Set an array of hidden attributes for created documents.
     *                                 <li>'hiddenAttributes'  - Deprecated, please use '_hiddenAttributes'.</li>
     *                                 <p>
     *                                 This is actually the same as setting hidden attributes using setHiddenAttributes() on a document. <br>
     *                                 The difference is, that if you're returning a resultset of documents, the getAll() is already called <br>
     *                                 and the hidden attributes would not be applied to the attributes.<br>
     *                                 </p>
     *                                 </li>
     *                                 <li>'batchSize' - can optionally be used to tell the server to limit the number of results to be transferred in one batch</li>
     *                                 <li>'skip'      - Optional, The number of documents to skip in the query.</li>
     *                                 <li>'limit'     - Optional, The maximal amount of documents to return. 'skip' is applied before the limit restriction.</li>
     *                                 </p>
     *
     * @return cursor - Returns a cursor containing the result
     */
    public function findByExample($document, $options = [])
    {
        return parent::byExample($this->_collectionName, $document, $options);
    }

    /**
     * Find all documents for given keys
     *
     * @param array $ids - array of document keys
     *
     * @return array of matching entities
     */
    public function findByIds($ids)
    {
        return $this->lookupByKeys($this->_collectionName, $ids);
    }

    /**
     * Find by Example.
     *
     * @param array $example
     *
     * @return AbstractEntity|bool
     */
    public function findOneByExample($example)
    {
        $cursor = $this->byExample($this->_collectionName, $example);
        if ($cursor->getCount() > 0) {
            /* @var $document AbstractEntity */
            $document = $cursor->getAll()[0];
            $document->setIsNew(false);

            return $document;
        }

        return false;
    }

    /**
     * Gets one document by given ID
     *
     * @param string|int $id
     *
     * @return AbstractEntity|null
     * @throws ServerException
     */
    public function findOneById($id)
    {
        try {
            return $this->_documentHandler->getById($this->_collectionName, $id);
        } catch (ServerException $e) {
            if ($e->getServerMessage() === 'document not found') {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Gets internal collection name
     *
     * @return string
     */
    public function getInternalCollectionName()
    {
        return $this->_collectionName;
    }


    /**
     * Store a document to a collection
     *
     * {@inheritDoc}
     *
     * @param AbstractEntity $document
     *
     * @return mixed
     */
    public function store($document)
    {
        if (is_null($document->get('_dateCreated'))) {
            $document->set('_dateCreated', date('Y-m-d H:i:s'));
        }
        $document->set('_dateUpdated', date('Y-m-d H:i:s'));

        if ($document->getIsNew()) {
            if (method_exists($document, 'onCreate')) {
                $document->onCreate();
            }

            return $this->_documentHandler->save($this->_collectionName, $document);
        } else {
            if (method_exists($document, 'onUpdate')) {
                $document->onUpdate();
            }

            return $this->_documentHandler->replace($document);
        }
    }

    /**
     * Removes specified document from collection
     *
     * @param AbstractEntity $document
     * @param                $options
     *
     * @return array - an array containing an attribute 'removed' with the number of documents that were deleted, an an array 'ignored' with the number of not removed keys/documents
     */
    public function removeDocument(AbstractEntity $document, $options = [])
    {
        return $this->removeByKeys($this->_collectionName, [$document->getInternalKey()], $options);
    }
}


class User extends AbstractEntity
{
    /**
     * Collection name.
     *
     * @var string
     */
    protected $_collectionName = 'users';

    public function setName($value)
    {
        $this->set('name', trim($value));
    }

    public function setAge($value)
    {
        $this->set('age', (int) $value);
    }

    public function onCreate()
    {
        parent::onCreate();

        $this->set('_dateCreated', date('Y-m-d H:i:s'));
    }

    public function onUpdate()
    {
        parent::onUpdate();
        $this->set('_dateUpdated', date('Y-m-d H:i:s'));
    }
}


class Users extends AbstractCollection
{

    protected $_documentClass = '\ArangoDBClient\User';
    protected $_collectionName = 'users';

    public function getByAge($value)
    {
        return $this->findByExample(['age' => $value])->getAll();
    }
}


try {
    $connection      = new Connection($connectionOptions);
    $usersCollection = new Users($connection);

    // set up a document collection "users"
    $collection = new Collection('users');
    try {
        $usersCollection->create($collection);
    } catch (\Exception $e) {
        // collection may already exist - ignore this error for now
    }

    // create a new document
    $user1 = new User();
    $user1->setName('  John  ');
    $user1->setAge(19);
    $usersCollection->store($user1);
    var_dump($user1);

    $user2 = new User();
    $user2->setName('Marry');
    $user2->setAge(19);
    $usersCollection->store($user2);
    var_dump(json_encode($user2));

    // get document by example
    $cursor = $usersCollection->findOneByExample(['age' => 19, 'name' => 'John']);
    var_dump($cursor);

    // get cursor by example
    $cursor = $usersCollection->findByExample(['age' => 19]);
    var_dump($cursor->getAll());

    $array = $usersCollection->getByAge(19);
    var_dump($array);

} catch (ConnectException $e) {
    print $e . PHP_EOL;
} catch (ServerException $e) {
    print $e . PHP_EOL;
} catch (ClientException $e) {
    print $e . PHP_EOL;
}

