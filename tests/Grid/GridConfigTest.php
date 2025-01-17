<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Doctrine\ORM\QueryBuilder;
use Kitpages\DataGridBundle\Paginator\PaginatorConfig;
use PHPUnit\Framework\TestCase;

class GridConfigTest extends TestCase
{
    protected GridConfig $gridConfig;

    protected function setUp(): void
    {
        $this->gridConfig = new GridConfig(
            $this->createMock(QueryBuilder::class),
            'node.id',
        );

        parent::setUp();
    }

    public function testCanAddAndRetrieveSingleFieldLegacySyntax(): void
    {
        $fieldName = uniqid('', true);

        $this->gridConfig->addField(new Field($fieldName));

        $this->assertInstanceOf(Field::class, $this->gridConfig->getFieldByName($fieldName));
    }

    public function testCanAddAndRetrieveSingleFieldNewSyntax(): void
    {
        $fieldName = uniqid('', true);
        $this->gridConfig->addField($fieldName, [
            'label' => $fieldName,
        ]);

        $this->assertInstanceOf(Field::class, $this->gridConfig->getFieldByName($fieldName));
        $this->assertEquals($fieldName, $this->gridConfig->getFieldByName($fieldName)->getLabel());
    }

    public function testCanAddAndRetrieveSingleFieldNewSyntaxWithFieldOption(): void
    {
        $fieldName = uniqid('', true);
        $this->gridConfig->addField($fieldName, [
            FieldOption::Label->value => $fieldName,
        ]);

        $this->assertInstanceOf(Field::class, $this->gridConfig->getFieldByName($fieldName));
        $this->assertEquals($fieldName, $this->gridConfig->getFieldByName($fieldName)->getLabel());
    }

    public function testShouldNotAddFieldWithOptions(): void
    {
        $fieldName = uniqid('', true);
        $field = new Field($fieldName, ['label' => $fieldName]);

        $this->expectException(\InvalidArgumentException::class);

        $this->gridConfig->addField($field, [
            'label' => $fieldName,
        ]);
    }

    public function testTags(): void
    {
        $this->gridConfig->addField(
            new Field('f1', [], ['foo', 'bar']),
        );
        $this->gridConfig->addField(
            new Field('f2', [], ['bar']),
        );
        $this->gridConfig->addField(
            new Field('f3', [], ['foo', 'bar', 'biz']),
        );
        $this->assertCount(1, $this->gridConfig->getFieldListByTag('biz'));
        $this->assertCount(2, $this->gridConfig->getFieldListByTag('foo'));
        $this->assertCount(3, $this->gridConfig->getFieldListByTag('bar'));
        $this->assertCount(0, $this->gridConfig->getFieldListByTag('gloubi'));
        $field = $this->gridConfig->getFieldListByTag('biz')[0];
        $this->assertEquals('f3', $field->getFieldName());
    }

    public function testShouldConfigurePaginator(): void
    {
        $configurePaginator = fn (PaginatorConfig $config) => $config->setItemCountInPage(100);

        $this->gridConfig->setConfigurePaginator($configurePaginator);

        $this->assertNull($this->gridConfig->getPaginatorConfig());
        $this->assertSame($configurePaginator, $this->gridConfig->getConfigurePaginator());
    }
}
