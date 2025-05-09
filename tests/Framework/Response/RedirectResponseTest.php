<?php

namespace Tests\Framework\Response;

use PgFramework\Response\ResponseRedirect;
use PHPUnit\Framework\TestCase;

class RedirectResponseTest extends TestCase
{
    public function testStatus()
    {
        $response = new ResponseRedirect('/demo');
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testHeader()
    {
        $response = new ResponseRedirect('/demo');
        $this->assertEquals(['/demo'], $response->getHeader('Location'));
    }
}
