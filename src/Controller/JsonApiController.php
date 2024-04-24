<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\DeckOfCards;
use Psr\Log\LoggerInterface;

class JsonApiController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/api/deck', name: 'api_deck', methods: ['GET'])]
    public function deck(SessionInterface $session): JsonResponse
    {
        $deck = $session->get('deck', new DeckOfCards());
        return $this->json([
            'deck' => $deck->getSortedCards(),
        ]);
    }

    #[Route('/api/deck/shuffle', name: 'api_shuffle', methods: ['POST', 'GET'])]
    public function shuffle(SessionInterface $session): JsonResponse
    {
        $deck = $session->get('deck', new DeckOfCards());
        $deck->resetDeck($this->logger);
        $deck->shuffle($this->logger);
        $session->set('deck', $deck);
        $session->set('drawn_cards', []);

        $shuffledDeck = $deck->getCards()->toArray();

        $shuffledDeckArray = array_map(function ($card) {
            return (string) $card;
        }, $shuffledDeck);

        return $this->json([
            'deck' => $shuffledDeckArray,
        ]);
    }

    #[Route('/api/deck/draw', name: 'api_draw', methods: ['POST', 'GET'])]
    public function draw(SessionInterface $session): JsonResponse
    {
        return $this->drawCards($session, 1);
    }

    #[Route('/api/deck/draw/{number}', name: 'api_draw_multiple', methods: ['POST', 'GET'])]
    public function drawMultiple(SessionInterface $session, Request $request, int $number = 1): JsonResponse
    {
        $number = $request->query->getInt('number', $number);
        return $this->drawCards($session, $number);
    }

    private function drawCards(SessionInterface $session, int $number): JsonResponse
    {
        $deck = $session->get('deck', new DeckOfCards());
        $cards = [];
        $drawnCards = $session->get('drawn_cards', []);
        for ($i = 0; $i < $number; $i++) {
            $card = $deck->drawCard($this->logger);
            if ($card) {
                $cards[] = $card;
                $drawnCards[] = (string) $card;
            } else {
                break;
            }
        }
        $session->set('deck', $deck);
        $session->set('drawn_cards', $drawnCards);

        return $this->json([
            'drawnCards' => array_map('strval', $cards),
            'remaining' => count($deck->getCards()),
        ]);
    }
}
