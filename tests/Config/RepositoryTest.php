<?php

namespace Illuminate\Tests\Config;

use PHPUnit\Framework\TestCase;
use Illuminate\Config\Repository;

class RepositoryTest extends TestCase
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $config;

    protected function setUp()
    {
        $this->repository = new Repository($this->config = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat',
            'null' => null,
            'associate' => [
                'x' => 'xxx',
                'y' => 'yyy',
            ],
            'array' => [
                'aaa',
                'zzz',
            ],
            'x' => [
                'z' => 'zoo',
            ],
            'callback_array' => function () {
                return [
                    'name' => 'John Doe',
                ];
            },
            'callback_instance' => function () {
                $std = new \stdClass();
                $std->name = 'Jane Doe';

                return $std;
            },
        ]);

        parent::setUp();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Repository::class, $this->repository);
    }

    public function testHasIsTrue()
    {
        $this->assertTrue($this->repository->has('foo'));
    }

    public function testHasIsFalse()
    {
        $this->assertFalse($this->repository->has('not-exist'));
    }

    public function testGet()
    {
        $this->assertSame('bar', $this->repository->get('foo'));
    }

    public function testCallbackArray()
    {
        $data = call_user_func(
            $this->repository->get('callback_array')
        );

        $this->assertEquals($data['name'], 'John Doe');
    }

    public function testCallbackInstance()
    {
        $data = call_user_func(
            $this->repository->get('callback_instance')
        );

        $this->assertEquals($data->name, 'Jane Doe');
    }

    public function testGetWithArrayOfKeys()
    {
        $this->assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
            'none' => null,
        ], $this->repository->get([
            'foo',
            'bar',
            'none',
        ]));

        $this->assertSame([
            'x.y' => 'default',
            'x.z' => 'zoo',
            'bar' => 'baz',
            'baz' => 'bat',
        ], $this->repository->get([
            'x.y' => 'default',
            'x.z' => 'default',
            'bar' => 'default',
            'baz',
        ]));
    }

    public function testGetMany()
    {
        $this->assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
            'none' => null,
        ], $this->repository->getMany([
            'foo',
            'bar',
            'none',
        ]));

        $this->assertSame([
            'x.y' => 'default',
            'x.z' => 'zoo',
            'bar' => 'baz',
            'baz' => 'bat',
        ], $this->repository->getMany([
            'x.y' => 'default',
            'x.z' => 'default',
            'bar' => 'default',
            'baz',
        ]));
    }

    public function testGetWithDefault()
    {
        $this->assertSame('default', $this->repository->get('not-exist', 'default'));
    }

    public function testSet()
    {
        $this->repository->set('key', 'value');
        $this->assertSame('value', $this->repository->get('key'));
    }

    public function testSetArray()
    {
        $this->repository->set([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $this->assertSame('value1', $this->repository->get('key1'));
        $this->assertSame('value2', $this->repository->get('key2'));
    }

    public function testPrepend()
    {
        $this->repository->prepend('array', 'xxx');
        $this->assertSame('xxx', $this->repository->get('array.0'));
    }

    public function testPush()
    {
        $this->repository->push('array', 'xxx');
        $this->assertSame('xxx', $this->repository->get('array.2'));
    }

    public function testAll()
    {
        $this->assertSame($this->config, $this->repository->all());
    }

    public function testOffsetUnset()
    {
        $this->assertArrayHasKey('associate', $this->repository->all());
        $this->assertSame($this->config['associate'], $this->repository->get('associate'));

        unset($this->repository['associate']);

        $this->assertArrayHasKey('associate', $this->repository->all());
        $this->assertNull($this->repository->get('associate'));
    }
}
