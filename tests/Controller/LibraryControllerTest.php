<?php

namespace App\Tests\Controller;

use App\Controller\LibraryController;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\BookRepository;
use ReflectionClass;

class LibraryControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/library');

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Library');
    }

    public function testAdd(): void
    {
        $client = static::createClient();
        $client->request('GET', '/library/add');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main h1', 'Create new Book');
    }
    public function testResetLibrary(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $client->request('GET', '/library/reset');
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html:contains("Book 1")', 'Book 1');
        $this->assertSelectorTextContains('html:contains("Book 2")', 'Book 2');
        $this->assertSelectorTextContains('html:contains("Book 3")', 'Book 3');
    }    

    public function testEditBook(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $crawler = $client->request('GET', '/library/1/edit');

        $form = $crawler->selectButton('Update')->form([
            'book[title]' => 'Updated Title',
            'book[isbn]' => '1234567890123',
            'book[author]' => 'Updated Author',
            'book[image]' => 'updated_image.png'
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/library/');

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('td', 'Updated Title');
    }     

    public function testDeleteBook(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $crawler = $client->request('GET', '/library/add');
        $form = $crawler->selectButton('Save')->form([
            'book[title]' => 'Test Book',
            'book[isbn]' => '1234567890123',
            'book[author]' => 'Test Author',
            'book[image]' => 'test_image.jpg',
        ]);
        $client->submit($form);

        $container = self::getContainer();
        $bookRepository = $container->get('doctrine')->getRepository(Book::class);
        $book = $bookRepository->findOneBy(['isbn' => '1234567890123']);
        $this->assertNotNull($book);

        $crawler = $client->request('GET', '/library/' . $book->getId());
        $form = $crawler->selectButton('Delete')->form();
        $client->submit($form);

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('td', 'Test Book');
    }

    public function testInitializeDefaultBooks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/library/');
    
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html', 'Library');
        $this->assertSelectorTextContains('html', 'Book 1');
    }

    public function testGetAllBooks(): void
    {
        $bookRepository = $this->createMock(BookRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $bookRepository->expects($this->exactly(2))
            ->method('findAll')
            ->willReturnOnConsecutiveCalls([], [
                (new Book())->setTitle('Book 1')->setIsbn('1234567890123')->setAuthor('Author 1')->setImage('book_1.png'),
                (new Book())->setTitle('Book 2')->setIsbn('1234567890124')->setAuthor('Author 2')->setImage('book_2.png'),
                (new Book())->setTitle('Book 3')->setIsbn('1234567890125')->setAuthor('Author 3')->setImage('book_3.png'),
            ]);

        $libraryController = new LibraryController($bookRepository, $entityManager);

        $entityManager->expects($this->exactly(3))->method('persist')->willReturnCallback(function (Book $book) {
            static $count = 0;
            $titles = ['Book 1', 'Book 2', 'Book 3'];
            $this->assertEquals($titles[$count], $book->getTitle());
            $count++;
        });

        $entityManager->expects($this->once())->method('flush');
        $books = $this->invokeMethod($libraryController, 'getAllBooks');

        $this->assertCount(3, $books);
        $this->assertEquals('Book 1', $books[0]->getTitle());
        $this->assertEquals('Book 2', $books[1]->getTitle());
        $this->assertEquals('Book 3', $books[2]->getTitle());
    }

    /**
     * Invoke a private or protected method on an object.
     *
     * @param object $object The object instance.
     * @param string $methodName The name of the method to invoke.
     * @param array<int, mixed> $parameters An array of parameters to pass to the method.
     * @return mixed The return value of the invoked method.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
