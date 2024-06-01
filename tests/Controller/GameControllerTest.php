<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
class GameControllerTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'This is the card game 21.');
    }

    public function testDoc(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/doc');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Game 21 - Documentation');
    }

    public function testDrawRedirectsToResultIfPlayerExceeds21(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/start');
        $client->followRedirect();

        for ($i = 0; $i < 10; $i++) {
            $client->request('POST', '/game/draw');
            $client->followRedirect();
        }

        $crawler = $client->getCrawler();
        $mainText = $crawler->filter('main')->text();

        $this->assertStringContainsString('Bank wins!', $mainText);
    }
    
    public function testPlayRedirectsToStartIfNoGameInSession(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/play');

        $this->assertResponseRedirects('/game/start');
    }

    public function testDrawRedirectsToStartIfNoGameInSession(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/game/draw');

        $this->assertResponseRedirects('/game/start');
    }

    public function testStayRedirectsToStartIfNoGameInSession(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/game/stay');

        $this->assertResponseRedirects('/game/start');
    }

    public function testResultRedirectsToStartIfNoGameInSession(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/result');

        $this->assertResponseRedirects('/game/start');
    }

    public function testStartGame(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/start');

        $this->assertResponseRedirects('/game/play');
    }

    public function testPlayGame(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client->request('GET', '/game/start');

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Play Game');
    }
    

    public function testDrawCardInGame(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/start');
        $client->followRedirect();
        $client->request('POST', '/game/draw');
        $this->assertResponseRedirects('/game/play', 302);
    }    

    public function testStayInGame(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client->request('GET', '/game/start');
        $client->followRedirect();

        $client->request('POST', '/game/stay');

        $this->assertResponseRedirects('/game/result', 302);
    }

    public function testGameResult(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/start');
        $client->followRedirect();
        $client->request('POST', '/game/stay');
        $client->followRedirect();
        $client->request('GET', '/game/result');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Game Result');
    }    
}
