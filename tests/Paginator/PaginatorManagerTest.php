<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Paginator;

use Kitpages\DataGridBundle\BundleOrmTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PaginatorManagerTest extends BundleOrmTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function getPaginatorManager(): PaginatorManager
    {
        // create EventDispatcher mock
        $service = new EventDispatcher();
        $parameters = [
            'default_twig' => 'toto.html.twig',
            'item_count_per_page' => 50,
            'visible_page_count_in_paginator' => 5,
        ];
        // create paginatorManager
        $paginatorManager = new PaginatorManager($service, $parameters);

        return $paginatorManager;
    }

    public function testPaginator(): void
    {
        // create queryBuilder
        $em = $this->getEntityManager();
        $repository = $em->getRepository('Kitpages\DataGridBundle\TestEntities\Node');
        $queryBuilder = $repository->createQueryBuilder('node');
        $queryBuilder->select('node');

        // create Request mock (ok this is not a mock....)
        $request = new \Symfony\Component\HttpFoundation\Request();
        $_SERVER['REQUEST_URI'] = '/foo';
        $paginatorManager = $this->getPaginatorManager();

        // configure paginator
        $paginatorConfig = new PaginatorConfig();
        $paginatorConfig->setCountFieldName('node.id');
        $paginatorConfig->setItemCountInPage(3);

        // get paginator
        $paginatorConfig->setQueryBuilder($queryBuilder);
        $paginator = $paginatorManager->getPaginator($paginatorConfig, $request);

        // tests
        $this->assertEquals(11, $paginator->getTotalItemCount());

        $this->assertEquals(4, $paginator->getTotalPageCount());

        $this->assertEquals([1, 2, 3, 4], $paginator->getPageRange());

        $this->assertEquals(1, $paginator->getCurrentPage());
        $this->assertEquals(2, $paginator->getNextButtonPage());
    }

    public function testPaginatorGroupBy(): void
    {
        // create queryBuilder
        $em = $this->getEntityManager();
        $repository = $em->getRepository('Kitpages\DataGridBundle\TestEntities\Node');
        $queryBuilder = $repository->createQueryBuilder('node');
        $queryBuilder->select('node.user, count(node.id) as cnt');
        $queryBuilder->groupBy('node.user');

        // create EventDispatcher mock
        $service = new EventDispatcher();
        // create Request mock (ok this is not a mock....)
        $request = new \Symfony\Component\HttpFoundation\Request();
        $_SERVER['REQUEST_URI'] = '/foo';

        // create gridManager instance
        $paginatorManager = $this->getPaginatorManager();

        // configure paginator
        $paginatorConfig = new PaginatorConfig();
        $paginatorConfig->setCountFieldName('node.user');
        $paginatorConfig->setItemCountInPage(3);

        // get paginator
        $paginatorConfig->setQueryBuilder($queryBuilder);
        $paginator = $paginatorManager->getPaginator($paginatorConfig, $request);

        // tests
        $this->assertEquals(6, $paginator->getTotalItemCount());

        $this->assertEquals(2, $paginator->getTotalPageCount());

        $this->assertEquals([1, 2], $paginator->getPageRange());

        $this->assertEquals(1, $paginator->getCurrentPage());
        $this->assertEquals(2, $paginator->getNextButtonPage());
    }
}
