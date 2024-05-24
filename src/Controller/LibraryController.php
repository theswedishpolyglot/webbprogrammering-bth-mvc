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
    #[Route('/', name: 'library_home', methods: ['GET'])]
    public function index(BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        $books = $bookRepository->findAll();

        if (empty($books)) {
            $defaultBooks = [
                ['title' => 'Book 1', 'isbn' => '1234567890123', 'author' => 'Author 1', 'image' => 'path/to/image1.jpg'],
                ['title' => 'Book 2', 'isbn' => '1234567890124', 'author' => 'Author 2', 'image' => 'path/to/image2.jpg'],
                ['title' => 'Book 3', 'isbn' => '1234567890125', 'author' => 'Author 3', 'image' => 'path/to/image3.jpg'],
            ];

            foreach ($defaultBooks as $data) {
                $book = new Book();
                $book->setTitle($data['title']);
                $book->setIsbn($data['isbn']);
                $book->setAuthor($data['author']);
                $book->setImage($data['image']);
                $entityManager->persist($book);
            }

            $entityManager->flush();
            $books = $bookRepository->findAll();
        }

        return $this->render('library/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/add', name: 'library_add', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('library_home');
        }

        return $this->render('library/add.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset', name: 'library_reset', methods: ['GET'])]
    public function reset(EntityManagerInterface $entityManager, BookRepository $bookRepository): Response
    {
        $this->resetLibrary($entityManager, $bookRepository);

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
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('library_home');
        }

        return $this->render('library/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'library_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $token = (string) $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $token)) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('library_home');
    }

    private function resetLibrary(EntityManagerInterface $entityManager, BookRepository $bookRepository): void
    {
        $books = $bookRepository->findAll();
        foreach ($books as $book) {
            $entityManager->remove($book);
        }

        $defaultBooks = [
            ['title' => 'Book 1', 'isbn' => '1234567890123', 'author' => 'Author 1', 'image' => 'path/to/image1.jpg'],
            ['title' => 'Book 2', 'isbn' => '1234567890124', 'author' => 'Author 2', 'image' => 'path/to/image2.jpg'],
            ['title' => 'Book 3', 'isbn' => '1234567890125', 'author' => 'Author 3', 'image' => 'path/to/image3.jpg'],
        ];

        foreach ($defaultBooks as $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setIsbn($data['isbn']);
            $book->setAuthor($data['author']);
            $book->setImage($data['image']);
            $entityManager->persist($book);
        }

        $entityManager->flush();
    }
}
