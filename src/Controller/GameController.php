<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Player;
use App\Entity\Bank;
use App\Entity\DeckOfCards;
use App\Entity\Game;
use Psr\Log\LoggerInterface;

class GameController extends AbstractController
{
    #[Route('/game', name: 'game_home')]
    public function index(): Response
    {
        return $this->render('game/index.html.twig');
    }

    #[Route('/game/doc', name: 'game_doc')]
    public function doc(): Response
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route('/game/start', name: 'game_start')]
    public function start(SessionInterface $session, LoggerInterface $logger): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle($logger);

        $player = new Player();
        $bank = new Bank();

        $game = new Game($player, $bank, $deck, $logger);

        $session->set('game', $game->toArray());

        return $this->redirectToRoute('game_play');
    }

    #[Route('/game/play', name: 'game_play')]
    public function play(SessionInterface $session, LoggerInterface $logger): Response
    {
        $gameData = $session->get('game');
        if (!$gameData) {
            return $this->redirectToRoute('game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);

        return $this->render('game/play.html.twig', [
            'playerHand' => $game->getPlayer()->getHand(),
            'bankHand' => $game->getBank()->getHand()
        ]);
    }

    #[Route('/game/draw', name: 'game_draw', methods: ['POST'])]
    public function draw(SessionInterface $session, LoggerInterface $logger): Response
    {
        $gameData = $session->get('game');
        if (!$gameData) {
            return $this->redirectToRoute('game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);
        $game->playerDrawCard();

        $session->set('game', $game->toArray());

        if ($game->getPlayer()->getHand()->getValue() > 21) {
            return $this->redirectToRoute('game_result');
        }

        return $this->redirectToRoute('game_play');
    }

    #[Route('/game/stay', name: 'game_stay', methods: ['POST'])]
    public function stay(SessionInterface $session, LoggerInterface $logger): Response
    {
        $gameData = $session->get('game');
        if (!$gameData) {
            return $this->redirectToRoute('game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);
        $game->bankPlay();

        $session->set('game', $game->toArray());

        return $this->redirectToRoute('game_result');
    }

    #[Route('/game/result', name: 'game_result')]
    public function result(SessionInterface $session, LoggerInterface $logger): Response
    {
        $gameData = $session->get('game');
        if (!$gameData) {
            return $this->redirectToRoute('game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);

        return $this->render('game/result.html.twig', [
            'playerHand' => $game->getPlayer()->getHand(),
            'bankHand' => $game->getBank()->getHand(),
            'result' => $game->getResult()
        ]);
    }
}
