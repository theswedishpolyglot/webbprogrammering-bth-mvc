<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'about')]
    public function index(): Response
    {
        return $this->render('about.html.twig', [
            'courseGitRepo' => 'https://github.com/dbwebb-se/mvc',
            'myGitRepo' => 'https://github.com/theswedishpolyglot/webbprogrammering-bth-mvc',
            'imagePath' => 'images/mvc-course.jpeg'
        ]);
    }
}
