<?php
namespace Tests\Optional;

use Akamon\MockeryCallableMock\MockeryCallableMock;
use Mockery as m;
use Optional\Optional;

/**
 * Class OptionalTest
 *
 * @package Tests\Optional
 */
class OptionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * @test
     * @expectedException \Optional\NullPointerException
     */
    public function testOfThrowNullPointerException()
    {
        Optional::of(null);
    }

    /**
     * @test
     * @expectedException \Optional\NoSuchElementException
     */
    public function testGetThrowNoSuchElementException()
    {
        Optional::blank()->get();
    }

    /**
     * @test
     */
    public function testIsPresent()
    {
        $this->assertFalse(Optional::blank()->isPresent());
        $this->assertTrue(Optional::of('foobar')->isPresent());
        $this->assertFalse(Optional::ofNullable(null)->isPresent());
        $this->assertTrue(Optional::ofNullable('foobar')->isPresent());
    }

    /**
     * @test
     */
    public function testifPresent()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar');
        Optional::of('foobar')->ifPresent($callableMock);

        $callableMock2 = new MockeryCallableMock();
        $callableMock2->shouldBeCalled()->never();
        Optional::blank()->ifPresent($callableMock2);
    }

    /**
     * @test
     */
    public function testFilterOnBlankReturnBlank()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->never();
        $this->assertFalse(Optional::blank()->filter($callableMock)->isPresent());
    }

    /**
     * @test
     */
    public function testFilterReturnTrue()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturn(true);
        $this->assertTrue(Optional::of('foobar')->filter($callableMock)->isPresent());
    }

    /**
     * @test
     */
    public function testFilterReturnFalse()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturn(false);
        $this->assertFalse(Optional::of('foobar')->filter($callableMock)->isPresent());
    }

    /**
     * @test
     */
    public function testMapOnBlankReturnBlank()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->never();
        $this->assertFalse(Optional::blank()->map($callableMock)->isPresent());
    }

    /**
     * @test
     */
    public function testMapOnOptionalReturnOptional()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturn('gru');
        $optional = Optional::of('foobar')->map($callableMock);
        $this->assertTrue($optional->isPresent());
        $this->assertEquals('gru', $optional->get());
    }

    /**
     * @test
     */
    public function testMapOptionalReturnBlank()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturn(null);
        $this->assertFalse(Optional::of('foobar')->map($callableMock)->isPresent());
    }

    /**
     * @test
     */
    public function testMapReturnOptionalOptional()
    {
        $callableMock4 = new MockeryCallableMock();
        $callableMock4->shouldBeCalled()->once()->with('foobar')->andReturn(Optional::of('gru'));
        $optional = Optional::of('foobar')->map($callableMock4);
        $this->assertTrue($optional->isPresent());
        $this->assertInstanceOf(Optional::class, $optional->get());
        $this->assertEquals('gru', $optional->get()->get());
    }

    /**
     * @test
     */
    public function testFlatMapOnBlankReturnBlank()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->never();
        $this->assertFalse(Optional::blank()->flatMap($callableMock)->isPresent());
    }

    /**
     * @test
     */
    public function testFlatMapReturnOptional()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturn('gru');
        $optional = Optional::of('foobar')->flatMap($callableMock);
        $this->assertTrue($optional->isPresent());
        $this->assertEquals('gru', $optional->get());
    }

    /**
     * @test
     */
    public function testFlatMapFlattenOptionalOptional()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturn(Optional::of('gru'));
        $optional = Optional::of('foobar')->flatMap($callableMock);
        $this->assertTrue($optional->isPresent());
        $this->assertEquals('gru', $optional->get());
    }

    /**
     * @test
     * @expectedException \Optional\NullPointerException
     */
    public function testFlatMapThrowNullPointerExceptionIfCallableReturnNull()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturnNull();
        Optional::of('foobar')->flatMap($callableMock);
    }

    /**
     * @test
     * @expectedException \Optional\NullPointerException
     */
    public function testFlatMapThrowNullPointerExceptionIfCallableReturnOptionalBlank()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->with('foobar')->andReturn(Optional::blank());
        Optional::of('foobar')->flatMap($callableMock);
    }

    /**
     * @test
     */
    public function testOrElse()
    {
        $this->assertEquals('foobar', Optional::of('foobar')->orElse('gru'));
        $this->assertEquals('gru', Optional::blank()->orElse('gru'));
    }

    /**
     * @test
     */
    public function testOrElseGet()
    {
        $callableMock = new MockeryCallableMock();
        $callableMock->shouldBeCalled()->once()->andReturn('gru');

        $this->assertEquals('foobar', Optional::of('foobar')->orElseGet($callableMock));
        $this->assertEquals('gru', Optional::blank()->orElseGet($callableMock));
    }

    /**
     * @test
     */
    public function testOrElseThrowDoesntTrowException()
    {
        $this->assertEquals('foobar', Optional::of('foobar')->orElseThrow(new \Exception()));
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function testOrElseThrowThrowException()
    {
        Optional::blank()->orElseThrow(new \Exception());
    }
}
