<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LuckyControllerTest extends WebTestCase
{
    public function testNumber(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/lucky');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Your lucky number is');
    }
}
