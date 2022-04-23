<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Doctrine\ORM\QueryBuilder;
use Kitpages\DataGridBundle\BundleOrmTestCase;
use Kitpages\DataGridBundle\Grid\ItemListNormalizer\StandardNormalizer;
use Kitpages\DataGridBundle\Hydrators\DataGridHydrator;
use Kitpages\DataGridBundle\Paginator\PaginatorConfig;
use Kitpages\DataGridBundle\Paginator\PaginatorManager;
use Kitpages\DataGridBundle\TestEntities\Node;
use Kitpages\DataGridBundle\Tool\UrlTool;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group GridManagerTest
 */
class GridManagerTest extends BundleOrmTestCase
{
    private EventDispatcherInterface $dispatcher;
    private QueryBuilder $queryBuilder;
    private GridManager $gridManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = new EventDispatcher();

        $em = $this->getEntityManager();
        $repository = $em->getRepository(Node::class);
        $this->queryBuilder = $repository->createQueryBuilder('node');

        $this->gridManager = $this->getGridManager();
    }

    public function getGridManager(): GridManager
    {
        $parameters = [
            'default_twig' => 'toto.html.twig',
            'item_count_in_page' => 50,
            'visible_page_count_in_paginator' => 5,
        ];

        // normalizer
        $normalizer = new StandardNormalizer();

        return new GridManager(
            $this->dispatcher,
            new PaginatorManager($this->dispatcher, $parameters),
            $normalizer,
            DataGridHydrator::class
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function initGridConfig(): GridConfig
    {
        // configure paginator
        $paginatorConfig = new PaginatorConfig($this->queryBuilder, 'node.id');
        $paginatorConfig->setItemCountInPage(3);

        $gridConfig = new GridConfig($this->queryBuilder, 'node.id');
        $gridConfig->setPaginatorConfig($paginatorConfig);
        $gridConfig
            ->addField(new Field('node.id'))
            ->addField(new Field(
                'node.createdAt',
                [
                'sortable' => true,
                'formatValueCallback' => function ($value) {
                    return $value->format('Y/m/d');
                },
                ]
            ));
        $gridConfig->addField(new Field(
            'node.content',
            [
                'formatValueCallback' => function ($value, $row) {
                    return $value . ':' . $row['createdAt']->format('Y');
                },
            ]
        ));
        $gridConfig->addField(new Field('node.user', [
            'filterable' => true,
        ]));

        return $gridConfig;
    }

    public function testGridBasic(): void
    {
        // create Request mock (ok this is not a mock....)
        $_SERVER['REQUEST_URI'] = '/foo';
        $request = new Request();
        $gridManager = $this->gridManager;

        // create queryBuilder
        $this->queryBuilder->select('node');

        $gridConfig = new GridConfig($this->queryBuilder, 'node.id');
        $gridConfig->addField(new Field(
            'node.createdAt',
            [
                'sortable' => true,
                'formatValueCallback' => function ($value) {
                    return $value->format('Y/m/d');
                },
            ]
        ));
        $gridConfig->addField(new Field(
            'node.content',
            [
                'formatValueCallback' => function ($value, $row) {
                    return $value . ':' . $row['node.createdAt']->format('Y');
                },
            ]
        ));

        // get paginator
        $grid = $gridManager->getGrid($gridConfig, $request);
        $paginator = $grid->getPaginator();

        // tests paginator
        $this->assertEquals(11, $paginator->getTotalItemCount());

        // grid test
        $itemList = $grid->getItemList();
        $this->assertCount(11, $itemList);
        $this->assertEquals(1, $itemList[0]['node.id']);
        // simple callback
        $this->assertEquals('2010/04/24', $grid->displayGridValue($itemList[0], $gridConfig->getFieldByName('node.createdAt')));
        $this->assertEquals('foobar:2010', $grid->displayGridValue($itemList[0], $gridConfig->getFieldByName('node.content')));

        // test $grid given in parameter
        $myCustomGrid = new CustomGrid(
            new UrlTool(),
            $request->getUri(),
            $this->dispatcher,
            $gridConfig
        );
        $myCustomGrid->setMyCustomParamter('my parameter value');
        /** @var CustomGrid $grid */
        $grid = $gridManager->getGrid($gridConfig, $request, $myCustomGrid);
        $paginator = $myCustomGrid->getPaginator();

        // tests paginator
        $this->assertEquals(11, $paginator->getTotalItemCount());

        // grid test
        $itemList = $myCustomGrid->getItemList();
        $this->assertCount(11, $itemList);
        $this->assertEquals(1, $itemList[0]['node.id']);
        // custom grid test
        $this->assertEquals('my parameter value', $grid->getMyCustomParamter());
        // simple callback
        $this->assertEquals('2010/04/24', $myCustomGrid->displayGridValue($itemList[0], $gridConfig->getFieldByName('node.createdAt')));
        $this->assertEquals('foobar:2010', $myCustomGrid->displayGridValue($itemList[0], $gridConfig->getFieldByName('node.content')));
    }

    public function testGridRelation(): void
    {
        // create Request mock (ok this is not a mock....)
        $request = new Request();
        $request->query->set('kitdg_paginator_paginator_currentPage', 2);
        $gridManager = $this->gridManager;

        // create queryBuilder
        $this->queryBuilder->select('node, node.id*2 as doubleId');

        $gridConfig = $this->initGridConfig();
        $gridConfig->addField(new Field('doubleId'));

        // get paginator
        $grid = $gridManager->getGrid($gridConfig, $request);
        $paginator = $grid->getPaginator();

        // tests paginator
        $this->assertEquals(11, $paginator->getTotalItemCount());

        // grid test
        $itemList = $grid->getItemList();
        $this->assertCount(3, $itemList);
        $this->assertEquals('paginator', $paginator->getPaginatorConfig()->getName());
        $this->assertEquals(2, $paginator->getCurrentPage());
        $this->assertEquals(1, $paginator->getPreviousButtonPage());
        $this->assertEquals(3, $paginator->getNextButtonPage());
        $this->assertEquals(10, $itemList[1]['doubleId']);
        // simple callback
    }

    /*
     * Test added following this issue : https://github.com/kitpages/KitpagesDataGridBundle/issues/18
     * But I can't reproduce that bug...
     * TODO: go back here later and reproduce this issue...
     */

    /**
     * @group testDQL
     */
    public function testGridLeftJoin(): void
    {
        // create Request mock (ok this is not a mock....)
        $request = new Request();
        $request->query->set('kitdg_paginator_paginator_currentPage', 2);
        $gridManager = $this->gridManager;

        // create queryBuilder
        $this->queryBuilder->select('DISTINCT node.id as gouglou, node, count(sn.id) as intervals')
            ->leftJoin('node.subNodeList', 'sn')
            ->groupBy('node.id')
            ->orderBy('node.id', 'ASC');

        $gridConfig = $this->initGridConfig();
        $gridConfig->addField(new Field('doubleId'));

        // get paginator
        $grid = $gridManager->getGrid($gridConfig, $request);
        $paginator = $grid->getPaginator();

        // tests paginator
        $this->assertEquals(11, $paginator->getTotalItemCount());

        // grid test
        $itemList = $grid->getItemList();
        $this->assertCount(3, $itemList);
        $this->assertEquals('paginator', $paginator->getPaginatorConfig()->getName());
        $this->assertEquals(2, $paginator->getCurrentPage());
        $this->assertEquals(1, $paginator->getPreviousButtonPage());
        $this->assertEquals(3, $paginator->getNextButtonPage());
        // simple callback
    }

    public function testGridLeftJoinGroupBy(): void
    {
        // create Request mock (ok this is not a mock....)
        $request = new Request();
        $request->query->set('kitdg_paginator_paginator_currentPage', 1);
        $gridManager = $this->gridManager;

        // create queryBuilder
        $this->queryBuilder->select('node, assoc, count(sn.id) as intervals')
            ->leftJoin('node.assoc', 'assoc')
            ->leftJoin('node.subNodeList', 'sn')
            ->groupBy('node.id')
            ->where('node.id = 11')
            ->orderBy('node.id', 'ASC');

        $gridConfig = $this->initGridConfig();
        $gridConfig->addField(new Field('assoc.id'));

        // get paginator
        $grid = $gridManager->getGrid($gridConfig, $request);

        // grid test
        $itemList = $grid->getItemList();

        $this->assertCount(1, $itemList);

        $expected = [
            'node.content' => 'I like it!',
            'node.user' => 'toto',
            'node.parentId' => 0,
            'node.id' => 11,
            'assoc.id' => 1,
            'assoc.name' => 'test assoc',
            'intervals' => '0',
            'node.createdAt' => new \DateTime('2010-04-21 12:14:20'),
        ];

        $this->assertEquals($expected, $itemList[0]);
    }

    public function testGridLeftJoinWithoutGroupBy(): void
    {
        // create Request mock (ok this is not a mock....)
        $request = new Request();
        $gridManager = $this->gridManager;

        // create queryBuilder
        $em = $this->getEntityManager();
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('node, mn')
            ->from(Node::class, 'node')
            ->leftJoin('node.mainNode', 'mn')
            ->orderBy('node.id', 'ASC');

        $gridConfig = $this->initGridConfig();
        $nodeIdField = new Field('node.id');
        $gridConfig->addField($nodeIdField);
        $mainNodeIdField = new Field('mn.id');
        $gridConfig->addField($mainNodeIdField);

        // get paginator
        $gridConfig->setQueryBuilder($queryBuilder);
        $grid = $gridManager->getGrid($gridConfig, $request);
        $paginator = $grid->getPaginator();

        // tests paginator
        $this->assertEquals(11, $paginator->getTotalItemCount());

        // grid test
        $itemList = $grid->getItemList();
        $this->assertCount(3, $itemList);

        $cnt = 0;
        foreach ($itemList as $item) {
            $cnt++;
            $nodeId = $grid->displayGridValue($item, $nodeIdField);
            $this->assertEquals($cnt, $nodeId);

            $mainNodeId = $grid->displayGridValue($item, $mainNodeIdField);
            if ($cnt == 1) {
                // the first node should not avec a mainNodeId, see 3 first nodes of fixtures
                $this->assertNull($mainNodeId);
            } else {
                $this->assertEquals(1, $mainNodeId);
            }
        }

        $mainNodeIdField->setNullIfNotExists(true);
        $cnt = 0;
        foreach ($itemList as $item) {
            $cnt++;
            $nodeId = $grid->displayGridValue($item, $nodeIdField);
            $this->assertEquals($cnt, $nodeId);
            $mainNodeId = $grid->displayGridValue($item, $mainNodeIdField);
            if ($cnt == 1) {
                $this->assertTrue($mainNodeId === null);
            } else {
                $this->assertEquals(1, $mainNodeId);
            }
        }
    }

    public function testGridFilter(): void
    {
        // create Request mock (ok this is not a mock....)
        $request = new Request();
        $request->query->set('kitdg_grid_grid_filter', 'foouser');
        $request->query->set('kitdg_grid_grid_sort_field', 'node.createdAt');
        $request->query->set('kitdg_paginator_paginator_currentPage', 2);
        $gridManager = $this->gridManager;

        // create queryBuilder
        $this->queryBuilder->select('node');

        $gridConfig = $this->initGridConfig();

        // get paginator
        $gridConfig->setQueryBuilder($this->queryBuilder);
        $grid = $gridManager->getGrid($gridConfig, $request);
        $paginator = $grid->getPaginator();

        // tests paginator
        $this->assertEquals(2, $paginator->getTotalItemCount());

        // grid test
        $itemList = $grid->getItemList();
        $this->assertCount(2, $itemList);
        $this->assertEquals(8, $itemList[0]['node.id']);
        $this->assertEquals(1, $paginator->getCurrentPage());

        $request->query->set('kitdg_grid_grid_sort_field', 'node.user');
        $gridConfig->setQueryBuilder($this->queryBuilder);
        $grid = $gridManager->getGrid($gridConfig, $request);
        $itemList = $grid->getItemList();
        $this->assertEquals(6, $itemList[0]['node.id']);

        $request->query->set('kitdg_grid_grid_filter', 'foo');
        $gridConfig->setQueryBuilder($this->queryBuilder);
        $grid = $gridManager->getGrid($gridConfig, $request);
        $itemList = $grid->getItemList();
        $this->assertCount(3, $itemList);
    }

    public function testGridUtf8Filter(): void
    {
        // create Request mock (ok this is not a mock....)
        $request = new Request();
        $request->query->set('kitdg_grid_grid_filter', 'foouser');
        $request->query->set('kitdg_grid_grid_sort_field', 'node.createdAt');
        $request->query->set('kitdg_paginator_paginator_currentPage', 2);
        $gridManager = $this->gridManager;

        // create queryBuilder
        $this->queryBuilder->select('node');

        $gridConfig = $this->initGridConfig();

        // get paginator
        $grid = $gridManager->getGrid($gridConfig, $request);
        $paginator = $grid->getPaginator();

        // tests paginator
        $this->assertEquals(2, $paginator->getTotalItemCount());

        // grid test
        $itemList = $grid->getItemList();
        $this->assertCount(2, $itemList);
        $this->assertEquals(8, $itemList[0]['node.id']);
        $this->assertEquals(1, $paginator->getCurrentPage());

        $request->query->set('kitdg_grid_grid_filter', 'fös');
        $grid = $gridManager->getGrid($gridConfig, $request);
        $itemList = $grid->getItemList();
        $this->assertCount(1, $itemList);
    }

    public function testGridSelector(): void
    {
        // create Request mock (ok this is not a mock....)
        $request = new Request();
        $request->query->set('kitdg_grid_grid_selector_field', 'node.user');
        $request->query->set('kitdg_grid_grid_selector_value', 'foouser');
        $request->query->set('kitdg_grid_grid_sort_field', 'node.createdAt');
        $request->query->set('kitdg_paginator_paginator_currentPage', 2);
        $gridManager = $this->gridManager;

        // create queryBuilder
        $this->queryBuilder->select('node');

        $gridConfig = $this->initGridConfig();

        // get paginator
        $grid = $gridManager->getGrid($gridConfig, $request);
        $paginator = $grid->getPaginator();

        // tests paginator
        $this->assertEquals(2, $paginator->getTotalItemCount());

        // grid test
        $itemList = $grid->getItemList();
        $this->assertCount(2, $itemList);
        $this->assertEquals(8, $itemList[0]['node.id']);
        $this->assertEquals(1, $paginator->getCurrentPage());

        $request->query->set('kitdg_grid_grid_sort_field', 'node.user');
        $grid = $gridManager->getGrid($gridConfig, $request);
        $itemList = $grid->getItemList();
        $this->assertEquals(6, $itemList[0]['node.id']);

        $request->query->set('kitdg_grid_grid_selector_value', '5');
        $grid = $gridManager->getGrid($gridConfig, $request);
        $itemList = $grid->getItemList();
        $this->assertCount(0, $itemList);
    }

    public function testShouldConfigureDefaultPaginator(): void
    {
        $request = new Request();
        $configurePaginator = fn (PaginatorConfig $config) => $config->setItemCountInPage(100);

        $gridConfig = new GridConfig($this->queryBuilder, 'node.id');
        $gridConfig->setConfigurePaginator($configurePaginator);

        $this->assertNull($gridConfig->getPaginatorConfig());

        $grid = $this->gridManager->getGrid($gridConfig, $request);

        $this->assertNull($gridConfig->getPaginatorConfig());

        $paginator = $grid->getPaginator();

        $this->assertSame(100, $paginator->getPaginatorConfig()->getItemCountInPage());
    }
}
