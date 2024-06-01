<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class JsonApiControllerTest extends WebTestCase
{
    public function testDeck(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/api/deck');

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('deck', $data);
    }

    public function testShuffle(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/api/deck/shuffle');

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('deck', $data);
    }

    public function testDraw(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/api/deck/draw');

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('drawnCards', $data);
        $this->assertArrayHasKey('remaining', $data);
    }

    public function testDrawMultiple(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/api/deck/draw/3');

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('drawnCards', $data);
        $this->assertArrayHasKey('remaining', $data);
        $this->assertCount(3, $data['drawnCards']);
    }

    public function testDrawCardsEmptyDeck(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('POST', '/api/deck/shuffle');
        for ($i = 0; $i < 52; $i++) {
            $client->request('POST', '/api/deck/draw');
        }

        $client->request('POST', '/api/deck/draw');
        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('drawnCards', $data);
        $this->assertArrayHasKey('remaining', $data);
        $this->assertEmpty($data['drawnCards']);
        $this->assertEquals(0, $data['remaining']);
    }

    public function testGameState(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/api/game');

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testGameStateExists(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/game/start');

        $client->request('GET', '/api/game');
        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('game', $data);
        $this->assertArrayHasKey('player', $data['game']);
        $this->assertArrayHasKey('bank', $data['game']);
        $this->assertArrayHasKey('deck', $data['game']);
    }

    public function testGetAllBooks(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/api/library/books');

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertIsArray($data);
    }

    public function testGetBookByIsbn(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $isbn = '1234567890123';
        $client->request('GET', '/api/library/book/' . $isbn);

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('isbn', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('image', $data);
    }

    public function testGetNonExistentBookByIsbn(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $isbn = 'nonexistentisbn';
        $client->request('GET', '/api/library/book/' . $isbn);

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertJson($responseContent);
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('error', $data);
    }
}
