<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testConstructor(): void
    {
        $field = new Field('id');
        $this->assertEquals($field->getFieldName(), 'id');

        $field = new Field('phone', [
            'label' => 'Phone',
            'sortable' => true,
            'filterable' => true,
            'visible' => false,
            'formatValueCallback' => function ($value) {
                return mb_strtoupper($value);
            },
            'autoEscape' => true,
            'translatable' => true,
            'category' => 'my.category',
            'nullIfNotExists' => true,
        ]);
        $this->assertEquals('Phone', $field->getLabel());
        $this->assertTrue($field->getSortable());
        $this->assertTrue($field->getFilterable());
        $this->assertFalse($field->getVisible());
        $this->assertNotNull($field->getFormatValueCallback());
        $this->assertTrue($field->getFilterable());
        $this->assertEquals('my.category', $field->getCategory());
        $this->assertTrue($field->getNullIfNotExists());

        $field = new Field('test');
        $this->assertFalse($field->getNullIfNotExists());
    }

    public function testWrongParameterConstructor(): void
    {
        try {
            $field = new Field('phone', [
                'foo' => 'bar',
            ]);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testTagSystem(): void
    {
        $field = new Field(
            'phone',
            [
                'label' => 'Phone',
                'sortable' => true,
                'filterable' => true,
                'visible' => false,
                'formatValueCallback' => function ($value) {
                    return mb_strtoupper($value);
                },
                'autoEscape' => true,
                'translatable' => true,
                'category' => 'my.category',
                'nullIfNotExists' => true,
            ],
            [
                'foo',
                'bar',
            ]
        );
        $this->assertTrue($field->hasTag('foo'));
        $this->assertFalse($field->hasTag('tutu'));
        $this->assertEquals(['foo', 'bar'], $field->getTagList());
    }
}
