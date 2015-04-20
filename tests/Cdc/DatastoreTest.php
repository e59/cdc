<?php

class Cdc_DatastoreTest extends DBTestCase
{

    protected $definitionStructure;
    protected $pdo;
    protected $definition;

    protected function setUp()
    {
        $this->pdo = $this->getConnection()->getConnection();

        $this->definitionStructure = array(
            'test' => array(
                'type'                    => Cdc_Definition::TYPE_RELATION,
                'statement_type'          => Cdc_Definition::STATEMENT_SELECT,
                Cdc_Definition::OPERATION => array(
                    'read' => array(),
                    // overriding the above value for statement_type
                    // again I emphasize, this index can be any string,
                    // it's up to you to make an operation useful
                    'operation for insert' => array(
                        'statement_type' => Cdc_Definition::STATEMENT_INSERT,
                    ),
                    'update'         => array(
                        'statement_type' => Cdc_Definition::STATEMENT_UPDATE,
                    ),
                    'delete'         => array(
                        'statement_type'                => Cdc_Definition::STATEMENT_DELETE,
                    ),
                ),
                // In attachments, you can use the column names of the parent
                // query by using the column name surrounded by # as
                // placeholder. These will be used as an array so you should
                // always use an in() operator in the where clause, for placeholders.
                // This is because the hydration proccess is in the application
                // level, so we have to fetch all attachments first then
                // distribute them between the result rows.
                Cdc_Definition::TYPE_ATTACHMENT => array(
                    // this is a n-n relationship. It works exactly the same as a 1-n
                    'category' => array(
                        'query_params' => array(
                            'cols' => array('id', 'name', 'test_id'),
                            'from' => array('category'),
                            'join' => array('test_category' => array('inner' => array('category.id = test_category.category_id'))),
                            // 'limit' => array('#page#', '#limit#'),
                            'order' => array('name asc'),
                            'where' => array('test_category.test_id in #id#'),
                        ),
                        'parent_key'                    => 'id',
                        'attachment_key'                => 'test_id',
                        Cdc_Definition::TYPE_ATTACHMENT => array(
                            // this is an 1-n relationship and also an attachment inside another attachment
                            // only one query fetches all the data that is distributed inside the result set
                            // doctrine calls this process "hydration", so I used the same term.
                            'subcategory' => array(
                                'query_params' => array(
                                    'cols' => array('subcategory.id', 'subcategory.name', 'category_id'),
                                    'from' => array('subcategory'),
                                    'join' => array('category' => array('inner' => array('category.id = subcategory.category_id'))),
                                    // 'limit' => array('#page#', '#limit#'),
                                    'order' => array('subcategory.name asc'),
                                    'where' => array('subcategory.category_id in #id#'),
                                ),
                                'parent_key'     => 'id',
                                'attachment_key' => 'category_id',
                            ),
                        ),
                    ),
                ),
            ),
            'id'             => array(
                'type'                    => Cdc_Definition::TYPE_COLUMN,
                Cdc_Definition::OPERATION => array(
                    'read' => array(),
                ),
            ),
            'email' => array(
                'type'                    => Cdc_Definition::TYPE_COLUMN,
                Cdc_Definition::OPERATION => array(
                    'read' => array(),
                ),
            ),
            'body' => array(
                'type'                    => Cdc_Definition::TYPE_COLUMN,
                Cdc_Definition::OPERATION => array(
                    'read' => array(
                        'hide' => true,
                    ),
                ),
            ),
        );
        $this->definition = new Cdc_Datastore($this->pdo, $this->definitionStructure);


        $this->pdo->exec('insert into test (email, body) values (\'teste@teste.com\', \'1abcdefghi\')');
        $this->pdo->exec('insert into test (email, body) values (\'teste2@teste.com\', \'2abcderefrrfefrefghi\')');
        $this->pdo->exec('insert into test (email, body) values (\'teste3@teste.com\', \'3abffwefrcdefghi\')');

        $this->pdo->exec('insert into category (name) values (\'category 1\')');
        $this->pdo->exec('insert into category (name) values (\'category 2\')');
        $this->pdo->exec('insert into category (name) values (\'category 3\')');
        $this->pdo->exec('insert into category (name) values (\'category 4\')');

        $this->pdo->exec('insert into test_category (test_id, category_id) values (1, 1)');
        $this->pdo->exec('insert into test_category (test_id, category_id) values (1, 2)');
        $this->pdo->exec('insert into test_category (test_id, category_id) values (1, 3)');
        $this->pdo->exec('insert into test_category (test_id, category_id) values (1, 4)');
        $this->pdo->exec('insert into test_category (test_id, category_id) values (2, 1)');
        $this->pdo->exec('insert into test_category (test_id, category_id) values (2, 4)');
        $this->pdo->exec('insert into test_category (test_id, category_id) values (3, 3)');

        $this->pdo->exec('insert into subcategory (category_id, name) values (1, \'subcategory 1 of 1\')');
        $this->pdo->exec('insert into subcategory (category_id, name) values (1, \'subcategory 2 of 1\')');
        $this->pdo->exec('insert into subcategory (category_id, name) values (1, \'subcategory 3 of 1\')');
        $this->pdo->exec('insert into subcategory (category_id, name) values (2, \'subcategory 4 of 2\')');
        $this->pdo->exec('insert into subcategory (category_id, name) values (2, \'subcategory 5 of 2\')');
        $this->pdo->exec('insert into subcategory (category_id, name) values (3, \'subcategory 6 of 3\')');
        $this->pdo->exec('insert into subcategory (category_id, name) values (3, \'subcategory 7 of 3\')');
        $this->pdo->exec('insert into subcategory (category_id, name) values (3, \'subcategory 8 of 3\')');
    }

    public function testAttachments()
    {

        $s = $this->definition;

        // $query is a plain Cdc_Sql_Select and can be used already after the
        // next line. It should be customized for things like limit and where,
        // if applicable.
        $query = $s->createQuery('read');

        $this->assertInstanceOf('Cdc_Sql_Select', $query);

        $this->assertEquals(array('id', 'email', 'body'), $query->cols); // just to make sure
        // Running this method will run the query and hydrate the results with
        // all the attachments described in the definition structure.
        $result = $s->hydrateResultOf($query);

        // $result is the hydrated result set.
        // Hydration is very nice, it returns a count on each query,
        // and fetches the minimum possible data when adding attachments.
        // Most of the processing is done by PHP.
        // In queries with many attachments, you should avoid queries that will return big result sets.
        // Try to keep below 500 lines of data or so adding all parent rows and attachments.
        // The memory usage and iterations grow very quickly.

        $this->assertNotEmpty($result);

        $first = reset($result);

        $this->assertArrayHasKey('category', $first);

        $this->assertNotEmpty($first['category']);

        $first_category = reset($first['category']);

        $this->assertArrayHasKey('subcategory', $first_category);
    }

    public function testInsert()
    {
        $s = $this->definition;

        $data = array('email' => 'a@bc.com', 'body'  => 'test body');

        $query = $s->createQuery('operation for insert', $data);

        $this->assertInstanceOf('Cdc_Sql_Insert', $query);

        $query->stmt();

        $this->assertEquals($data, $this->pdo->query('select email, body from test where email = \'a@bc.com\'')->fetch());
    }

    public function testUpdate()
    {
        $s = $this->definition;

        $cols = array('email' => 'a@bc.com', 'body'  => 'test body updated');
        $where  = array('id =' => 1);

        $query = $s->createQuery('update', compact('cols', 'where'));

        $this->assertInstanceOf('Cdc_Sql_Update', $query);

        $query->stmt();

        $this->assertEquals($cols, $this->pdo->query('select email, body from test where id = 1')->fetch());
    }

    public function testDelete()
    {

        $s = $this->definition;

        $where = array('id =' => 1);

        $query = $s->createQuery('delete', $where);

        $this->assertInstanceOf('Cdc_Sql_Delete', $query);

        $query->stmt();

        $this->assertEmpty($this->pdo->query('select email, body from test where id = 1')->fetch());

        $this->assertNotEmpty($this->pdo->query('select email, body from test where id = 2')->fetch());
    }

}
