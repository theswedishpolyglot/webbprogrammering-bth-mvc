<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CardControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/card');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Card Game Dashboard');
    }

    public function testDeck(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/card/deck');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Current Deck');
    }

    public function testShuffle(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/card/deck/shuffle');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Shuffled Deck');
    }

    public function testDrawCard(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/card/deck/draw');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Card Drawn');
    }

    public function testDrawAllCards(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/card/deck/draw/multiple?number=52');

        $client->request('POST', '/card/deck/draw');

        // echo $client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main p', 'No card drawn or no cards left in the deck.');
    }

    public function testDrawMoreThanAvailableCards(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/card/deck/draw/multiple?number=52');

        $client->request('GET', '/card/deck/draw/multiple?number=5');

        // echo $client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main p', 'Cards remaining in the deck: 0');
    }

    public function testDrawMultipleCards(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/card/deck/draw/multiple?number=3');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Multiple Cards Drawn');
        $this->assertSelectorExists('.card', '3');
    }

    public function testSessionData(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/card/session');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Session Contents');
    }

    public function testDeleteSession(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/card/session/delete');

        $this->assertResponseRedirects('/card');
    }    
}