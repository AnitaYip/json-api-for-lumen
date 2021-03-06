<?php

namespace Tests\RealPage\JsonApi\Lumen;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RealPage\JsonApi\Lumen\MediaTypeGuard;
use RealPage\JsonApi\Lumen\EnforceMediaType;

class EnforceMediaTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testHandlesInvalidExistingContentTypeRequests()
    {
        $guard   = $this->getMockBuilder(MediaTypeGuard::class)->disableOriginalConstructor()->setMethods([
            'getContentType',
            'validateExistingContentType',
        ])->getMock();
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $next    = function () {
            return 'hello';
        };

        $guard->expects($this->once())->method('getContentType')->willReturn('application/vnd.api+json');
        $guard->expects($this->once())->method('validateExistingContentType')->with($request)->willReturn(false);
        $middleware = new EnforceMediaType();
        $response   = $middleware->handle($request, $next, $guard);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getStatusCode(), 415);
    }

    public function testHandlesIncorrectHeadersForData()
    {
        $guard   = $this->getMockBuilder(MediaTypeGuard::class)->disableOriginalConstructor()->setMethods([
            'getContentType',
            'hasCorrectHeadersForData',
            'validateExistingContentType',
        ])->getMock();
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $next    = function () {
            return 'hello';
        };

        $guard->expects($this->once())->method('getContentType')->willReturn('application/vnd.api+json');
        $guard->expects($this->once())->method('validateExistingContentType')->with($request)->willReturn(true);
        $guard->expects($this->once())->method('hasCorrectHeadersForData')->with($request)->willReturn(false);
        $middleware = new EnforceMediaType();
        $response   = $middleware->handle($request, $next, $guard);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getStatusCode(), 415);
    }

    public function testHandlesIncorrectlySetAcceptHeader()
    {
        $guard   = $this->getMockBuilder(MediaTypeGuard::class)->disableOriginalConstructor()->setMethods([
            'getContentType',
            'hasCorrectHeadersForData',
            'hasCorrectlySetAcceptHeader',
            'validateExistingContentType',
        ])->getMock();
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $next    = function () {
            return 'hello';
        };

        $guard->expects($this->once())->method('getContentType')->willReturn('application/vnd.api+json');
        $guard->expects($this->once())->method('validateExistingContentType')->with($request)->willReturn(true);
        $guard->expects($this->once())->method('hasCorrectHeadersForData')->with($request)->willReturn(true);
        $guard->expects($this->once())->method('hasCorrectlySetAcceptHeader')->with($request)->willReturn(false);
        $middleware = new EnforceMediaType();
        $response   = $middleware->handle($request, $next, $guard);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getStatusCode(), 406);
    }

    public function testHandlesCorrectlyFormattedRequest()
    {
        $guard    = $this->getMockBuilder(MediaTypeGuard::class)->disableOriginalConstructor()->setMethods([
            'getContentType',
            'hasCorrectHeadersForData',
            'hasCorrectlySetAcceptHeader',
            'validateExistingContentType',
        ])->getMock();
        $request  = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods(['header'])->getMock();
        $next     = function () use ($response) {
            return $response;
        };

        $guard->expects($this->once())->method('getContentType')->willReturn('application/vnd.api+json');
        $guard->expects($this->once())->method('validateExistingContentType')->with($request)->willReturn(true);
        $guard->expects($this->once())->method('hasCorrectHeadersForData')->with($request)->willReturn(true);
        $guard->expects($this->once())->method('hasCorrectlySetAcceptHeader')->with($request)->willReturn(true);
        $response->expects($this->once())->method('header');

        $middleware         = new EnforceMediaType();
        $middlewareResponse = $middleware->handle($request, $next, $guard);

        $this->assertSame($response, $middlewareResponse);
    }
}
