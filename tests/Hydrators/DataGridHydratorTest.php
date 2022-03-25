<?php declare(strict_types=1);

namespace Doctrine\Tests\ORM\Hydration;

use Doctrine\DBAL\Result;
use Doctrine\ORM\Query\ResultSetMapping;
use Kitpages\DataGridBundle\BundleOrmTestCase;
use Kitpages\DataGridBundle\Hydrators\DataGridHydrator;

class DataGridHydratorTest extends BundleOrmTestCase
{
    /**
     * Select u.id, u.name from CmsUser u
     */
    public function testNewHydrationSimpleEntityQuery(): void
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Kitpages\DataGridBundle\TestEntities\Node', 'node');
        $rsm->addEntityResult('Kitpages\DataGridBundle\TestEntities\NodeAssoc', 'assoc');
        $rsm->addFieldResult('node', 'id1', 'id');
        $rsm->addFieldResult('node', 'user2', 'user');
        $rsm->addFieldResult('node', 'content3', 'content');
        $rsm->addFieldResult('node', 'parent_id4', 'parentId');
        $rsm->addFieldResult('node', 'created_at5', 'createdAt');
        $rsm->addFieldResult('assoc', 'id6', 'id');
        $rsm->addFieldResult('assoc', 'name7', 'name');
        $rsm->addScalarResult('intervals8', 'intervals');

        // Faked result set
        $resultSet = [
            [
                'id1' => 11,
                'user2' => 'toto',
                'content3' => 'I like it!',
                'parent_id4' => 0,
                'created_at5' => new \DateTime('2010-04-21 12:14:20'),
                'id6' => 1,
                'name7' => 'tutu',
                'intervals8' => 10,
            ],
        ];

        $stmt = $this->createMock(Result::class);
        $times = 0;
        $stmt->expects($this->exactly(count($resultSet) + 1))
            ->method('fetchAssociative')
            ->willReturnCallback(function () use (&$times, $resultSet) {
                if ($data = $resultSet[$times++] ?? null) {
                    return $data;
                }

                return null;
            });

        $hydrator = new DataGridHydrator($this->getEntityManager());

        $result = $hydrator->hydrateAll($stmt, $rsm);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        $this->assertEquals(11, $result[0]['node.id']);
        $this->assertEquals('toto', $result[0]['node.user']);
        $this->assertEquals('I like it!', $result[0]['node.content']);
        $this->assertEquals(0, $result[0]['node.parentId']);
        $this->assertEquals(new \DateTime('2010-04-21 12:14:20'), $result[0]['node.createdAt']);
        $this->assertEquals(1, $result[0]['assoc.id']);
        $this->assertEquals('tutu', $result[0]['assoc.name']);
        $this->assertEquals(10, $result[0]['intervals']);
    }
}
