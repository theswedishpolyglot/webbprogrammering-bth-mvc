<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'API Overview');
    }

    public function testApiQuote(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        
        $client->request('GET', '/api/quote');
        $this->assertResponseIsSuccessful();
    }
}
