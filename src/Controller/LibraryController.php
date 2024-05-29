<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/library')]
class LibraryController extends AbstractController
{
    public function __construct(
        private BookRepository $bookRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'library_home', methods: ['GET'])]
    public function index(): Response
    {
        $books = $this->getAllBooks();

        return $this->render('library/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/add', name: 'library_add', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveBook($book);

            return $this->redirectToRoute('library_home');
        }

        return $this->render('library/add.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset', name: 'library_reset', methods: ['GET'])]
    public function reset(): Response
    {
        $this->resetLibrary();

        return $this->redirectToRoute('library_home');
    }

    #[Route('/{id}', name: 'library_details', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('library/details.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'library_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveBook($book);

            return $this->redirectToRoute('library_home');
        }

        return $this->render('library/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'library_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book): Response
    {
        $token = (string) $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $token)) {
            $this->removeBook($book);
        }

        return $this->redirectToRoute('library_home');
    }

    /**
     * @return Book[]
     */
    private function getAllBooks(): array
    {
        $books = $this->bookRepository->findAll();

        if (empty($books)) {
            $books = $this->initializeDefaultBooks();
        }

        return $books;
    }

    /**
     * @return Book[]
     */
    private function initializeDefaultBooks(): array
    {
        $defaultBooks = [
            ['title' => 'Book 1', 'isbn' => '1234567890123', 'author' => 'Author 1', 'image' => 'book_1.png'],
            ['title' => 'Book 2', 'isbn' => '1234567890124', 'author' => 'Author 2', 'image' => 'book_2.png'],
            ['title' => 'Book 3', 'isbn' => '1234567890125', 'author' => 'Author 3', 'image' => 'book_3.png'],
        ];

        foreach ($defaultBooks as $data) {
            $book = new Book();
            $book->setTitle($data['title'])
                ->setIsbn($data['isbn'])
                ->setAuthor($data['author'])
                ->setImage($data['image']);
            $this->entityManager->persist($book);
        }

        $this->entityManager->flush();

        return $this->bookRepository->findAll();
    }

    private function saveBook(Book $book): void
    {
        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }

    private function removeBook(Book $book): void
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }

    private function resetLibrary(): void
    {
        $books = $this->bookRepository->findAll();
        foreach ($books as $book) {
            $this->removeBook($book);
        }

        $this->resetAutoIncrement();
        $this->initializeDefaultBooks();
    }

    private function resetAutoIncrement(): void
    {
        $connection = $this->entityManager->getConnection();
        $stmt = $connection->prepare("UPDATE sqlite_sequence SET seq = 0 WHERE name = 'book'");
        $stmt->executeStatement();
    }
}
