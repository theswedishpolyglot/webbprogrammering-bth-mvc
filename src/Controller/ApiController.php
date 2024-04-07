<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'routes' => [
                'quote' => '/api/quote',
            ],
        ]);
    }

    #[Route('/api/quote', name: 'api_quote')]
    public function quote(): JsonResponse
    {
        $quotes = ["To be or not to be.", "And the lord said, may there be light.", "Do or do not, there is no try."];
        $quote = $quotes[array_rand($quotes)];

        return $this->json([
            'quote' => $quote,
            'date' => date('Y-m-d'),
            'timestamp' => time(),
        ]);
    }
}
