<?php
/*
 * Copyright (c) 2015 Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Retrofit\Test\Unit\Event;

use Exception;
use Tebru\Retrofit\Adapter\Http\Response;
use Tebru\Retrofit\Event\ApiExceptionEvent;
use Tebru\Retrofit\Test\MockeryTestCase;

/**
 * Class ApiExceptionEventTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ApiExceptionEventTest extends MockeryTestCase
{
    public function testGetters()
    {
        $event = new ApiExceptionEvent(new Exception());
        $this->assertInstanceOf(Exception::class, $event->getException());
    }
}
