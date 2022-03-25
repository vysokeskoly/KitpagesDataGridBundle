<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class GridTest extends TestCase
{
    private Grid $grid;
    private \DateTime $now;
    private array $row;
    /** @var Field|MockObject */
    private $mockField;

    protected function setUp(): void
    {
        $dispatcher = new EventDispatcher();

        $this->grid = new Grid();
        $this->grid->setDispatcher($dispatcher);
        $this->now = new \DateTime();
        $this->row = [
            'node.id' => 12,
            'company.name' => 'Test Company',
            'node.html' => '<a>',
            'node.createdAt' => $this->now,
        ];
        $this->mockField = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructor(): void
    {
        $grid = new Grid();
        $this->assertTrue($grid instanceof Grid);
    }

    public function testDisplayGridValue(): void
    {
        $this->mockField->expects($this->any())
            ->method('getAutoEscape')
            ->willReturn(true);

        $this->mockField->expects($this->any())
            ->method('getFieldName')
            ->will($this->onConsecutiveCalls('company.name', 'node.createdAt', 'node.id', 'node.html'));

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('Test Company', $displayValue);

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals($this->now->format('Y-m-d H:i:s'), $displayValue);

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals(12, $displayValue);

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('&lt;a&gt;', $displayValue);
    }

    public function testDisplayGridValueAutoEscapeFalse(): void
    {
        $this->mockField->expects($this->any())
            ->method('getAutoEscape')
            ->willReturn(false);

        $this->mockField->expects($this->any())
            ->method('getFieldName')
            ->will($this->onConsecutiveCalls('node.html'));

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('<a>', $displayValue);
    }

    public function testDisplayGridValueCallbackSimple(): void
    {
        $this->mockField->expects($this->any())
            ->method('getAutoEscape')
            ->willReturn(false);
        $this->mockField->expects($this->any())
            ->method('getFormatValueCallback')
            ->willReturn(function ($value) {
                return mb_strtoupper($value);
            });

        $this->mockField->expects($this->any())
            ->method('getFieldName')
            ->will($this->onConsecutiveCalls('company.name', 'node.html'));

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('TEST COMPANY', $displayValue);

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('<A>', $displayValue);
    }

    public function testDisplayGridValueCallbackExtended(): void
    {
        $this->mockField->expects($this->any())
            ->method('getAutoEscape')
            ->willReturn(true);
        $this->mockField->expects($this->any())
            ->method('getFormatValueCallback')
            ->willReturn(function ($value, $row) {
                return mb_strtoupper($value) . ';' . $row['node.id'];
            });

        $this->mockField->expects($this->any())
            ->method('getFieldName')
            ->will($this->onConsecutiveCalls('company.name', 'node.html'));

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('TEST COMPANY;12', $displayValue);
        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('&lt;A&gt;;12', $displayValue);
    }

    public function testDisplayGridValueConvertionEvent(): void
    {
        $this->mockField->expects($this->any())
            ->method('getAutoEscape')
            ->willReturn(true);

        $this->mockField->expects($this->any())
            ->method('getFieldName')
            ->willReturn('company.name');

        $subscriber = new ConversionSubscriber();
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($subscriber);
        $this->grid->setDispatcher($dispatcher);

        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('12;Test Company', $displayValue);

        $subscriber->setIsDefaultPrevented(true);
        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('company.name;preventDefault;Test Company', $displayValue);

        $subscriber->setAfterActivated(true);
        $displayValue = $this->grid->displayGridValue($this->row, $this->mockField);
        $this->assertEquals('after;company.name;preventDefault;Test Company', $displayValue);
    }
}
