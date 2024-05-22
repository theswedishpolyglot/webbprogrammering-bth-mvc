<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\DeckOfCards;
use Psr\Log\LoggerInterface;

class CardController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function ensureDeckExists(SessionInterface $session): DeckOfCards
    {
        $deck = $session->get('deck');
        if (!$deck) {
            $deck = new DeckOfCards();
            $deck->shuffle($this->logger);
            $session->set('deck', $deck);
            $this->logger->info('New deck initialized and shuffled');
        }
        $session->save();
        return $deck;
    }

    #[Route('/card', name: 'card_home')]
    public function index(): Response
    {
        return $this->render('card/index.html.twig');
    }

    #[Route('/card/deck', name: 'card_deck', methods: ['GET'])]
    public function deck(SessionInterface $session): Response
    {
        $deck = $session->get('deck', new DeckOfCards());

        $sortedCards = $deck->getSortedCards();

        return $this->render('card/deck.html.twig', [
            'deck' => $sortedCards,
        ]);
    }


    #[Route('/card/deck/shuffle', name: 'card_shuffle', methods: ['POST', 'GET'])]
    public function shuffle(SessionInterface $session): Response
    {
        $deck = $this->ensureDeckExists($session);
        $deck->resetDeck();
        $deck->shuffle($this->logger);
        $session->set('drawn_cards', []);
        $this->logger->info('Deck has been shuffled and the session cleared');
        $session->set('deck', $deck);
        $deckArray = $deck->getCards()->toArray();

        return $this->render('card/shuffle.html.twig', [
            'deck' => $deckArray
        ]);
    }

    #[Route('/card/deck/draw', name: 'card_draw', methods: ['POST', 'GET'])]
    public function draw(SessionInterface $session, LoggerInterface $logger): Response
    {
        $deck = $this->ensureDeckExists($session);
        $this->logger->info('Attempting to draw a card. Current deck:', ['deck' => $deck->detailedString()]);

        $card = $deck->drawCard($logger);

        if (!$card) {
            $this->logger->info('Failed to draw a card. No more cards or error.');
            $this->addFlash('error', 'No more cards to draw.');
            return $this->render('card/draw.html.twig', [
                'card' => null,
                'remaining' => count($deck->getCards()),
                'drawnCards' => $session->get('drawn_cards', [])
            ]);
        }

        $this->logger->info('Card drawn:', ['card' => $card->__toString()]);

        $session->set('deck', $deck);
        $session->save();

        $drawnCards = $session->get('drawn_cards', []);
        $drawnCards[] = (string) $card;
        $session->set('drawn_cards', $drawnCards);

        return $this->render('card/draw.html.twig', [
            'card' => (string) $card,
            'remaining' => count($deck->getCards()),
            'drawnCards' => $drawnCards
        ]);
    }

    #[Route('/card/deck/draw/multiple', name: 'card_draw_multiple', methods: ['GET'])]
    public function drawMultiple(SessionInterface $session, Request $request): Response
    {
        $number = $request->query->getInt('number', 1);

        $this->logger->info("Attempting to draw {$number} cards.");

        $deck = $this->ensureDeckExists($session);
        $cards = [];
        for ($i = 0; $i < $number; $i++) {
            $card = $deck->drawCard($this->logger);
            if (!$card) {
                $this->logger->info('No more cards available to draw.');
                break;
            }
            $cards[] = $card;
        }

        $session->set('deck', $deck);
        $drawnCards = $session->get('drawn_cards', []);
        $drawnCards = array_merge($drawnCards, array_map('strval', $cards));
        $session->set('drawn_cards', $drawnCards);

        return $this->render('card/draw_multiple.html.twig', [
            'cards' => array_map('strval', $cards),
            'remaining' => count($deck->getCards()),
            'drawnCards' => $drawnCards
        ]);
    }


    #[Route("/card/session", name: "card_session")]
    public function session(SessionInterface $session): Response
    {
        return $this->render('card/session.html.twig', [
            'sessionData' => $session->all()
        ]);
    }

    #[Route("/card/session/delete", name: "card_session_delete", methods: ["POST", "GET"])]
    public function deleteSession(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('success', 'Session data cleared successfully.');
        return $this->redirectToRoute('card_home');
    }
}
