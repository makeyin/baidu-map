<?php

namespace HerCat\BaiduMap\Tests\Kernel\Http;

use HerCat\BaiduMap\Kernel\Http\Response;
use HerCat\BaiduMap\Kernel\Support\Collection;
use HerCat\BaiduMap\Tests\TestCase;

class ResponseTest extends TestCase
{
    public function testBasicFeatures()
    {
        $response = new Response(200, [], '{"foo":"bar"}');

        $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $response);
        $this->assertSame('{"foo":"bar"}', (string) $response);
        $this->assertSame('{"foo":"bar"}', $response->getBodyContents());
        $this->assertSame('{"foo":"bar"}', $response->toJson());
        $this->assertSame(['foo' => 'bar'], $response->toArray());
        $this->assertSame('bar', $response->toObject()->foo);
        $this->assertInstanceOf(Collection::class, $response->toCollection());
        $this->assertSame(['foo' => 'bar'], $response->toCollection()->all());
    }

    public function testXMLContents()
    {
        $response = new Response(200, ['Content-Type' => 'application/xml'], '<xml><foo>bar</foo><bar>foo</bar></xml>');
        $this->assertSame(['foo' => 'bar', 'bar' => 'foo'], $response->toArray());

        $response = new Response(200, ['Content-Type' => 'text/xml'], '<xml><foo>bar</foo><bar>foo</bar></xml>');
        $this->assertSame(['foo' => 'bar', 'bar' => 'foo'], $response->toArray());

        $response = new Response(200, ['Content-Type' => 'text/html'], '<xml><foo>bar</foo><bar>foo</bar></xml>');
        $this->assertSame(['foo' => 'bar', 'bar' => 'foo'], $response->toArray());

        $response = new Response(200, ['Content-Type' => 'application/xml'], '<xml><foo>bar</foo><bar>foo</bar></xml>');
        $obj = $response->toObject();
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertSame('bar', $obj->foo);
        $this->assertSame('foo', $obj->bar);

        $response = new Response(200, ['Content-Type' => 'application/xml'], '<xml><foo>bar</foo><bar>foo</bar></xml>');
        $collection = $response->toCollection();
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame(['foo' => 'bar', 'bar' => 'foo'], $collection->all());
    }

    public function testInvalidContents()
    {
        $response = new Response(200, [], 'not json string');

        $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $response);
        $this->assertSame([], $response->toArray());

        // #1291
        $json = "{\"name\":\"mock-\x09name\"}";

        \json_decode($json, true);
        $this->assertSame(\JSON_ERROR_CTRL_CHAR, json_last_error());

        $response = new Response(200, ['Content-Type' => 'application/json'], $json);

        $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $response);
        $this->assertSame(['name' => 'mock-name'], $response->toArray());
    }
}
