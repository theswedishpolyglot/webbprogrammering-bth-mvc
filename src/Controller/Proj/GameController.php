<?php

namespace App\Controller\Proj;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Proj\DeckOfCards;
use App\Entity\Proj\Player;
use App\Entity\Proj\Bank;
use App\Entity\Proj\Game;
use App\Form\BetType;
use Psr\Log\LoggerInterface;

class GameController extends AbstractController
{
    #[Route('/proj/game', name: 'proj_game_home')]
    public function index(SessionInterface $session, LoggerInterface $logger): Response
    {
        $playerName = $session->get('playerName');
        $bankBalance = $session->get('bankBalance');

        if (!$playerName || !$bankBalance) {
            return $this->redirectToRoute('proj_register');
        }

        $form = $this->createForm(BetType::class);
        $logger->info('Game home accessed.', [
            'playerName' => $playerName,
            'bankBalance' => $bankBalance
        ]);

        return $this->render('proj/game/index.html.twig', [
            'playerName' => $playerName,
            'bankBalance' => $bankBalance,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/proj/game/start', name: 'proj_game_start', methods: ['POST'])]
    public function start(SessionInterface $session, LoggerInterface $logger, Request $request): Response
    {
        $form = $this->createForm(BetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $numberOfHands = $data['hands'];
            $betAmount = $data['bet'];
            $bankBalance = $session->get('bankBalance');

            if ($betAmount > $bankBalance) {
                $this->addFlash('error', 'Bet amount exceeds bank balance.');
                return $this->redirectToRoute('proj_game_home');
            }

            $deck = new DeckOfCards();
            $deck->shuffle($logger);

            $player = new Player();
            $bank = new Bank();

            $game = new Game($player, $bank, $deck, $logger, $numberOfHands, $betAmount);
            $session->set('game', $game->toArray());

            $logger->info('Game started.', [
                'numberOfHands' => $numberOfHands,
                'betAmount' => $betAmount,
                'bankBalance' => $bankBalance
            ]);

            return $this->redirectToRoute('proj_game_play');
        }

        return $this->redirectToRoute('proj_game_home');
    }

    #[Route('/proj/game/play', name: 'proj_game_play')]
    public function play(SessionInterface $session, LoggerInterface $logger): Response
    {
        $gameData = $session->get('game');
        if (!$gameData) {
            $logger->info('No game data found in session, redirecting to start.');
            return $this->redirectToRoute('proj_game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);

        $handsData = [];
        foreach ($game->getPlayerHands() as $hand) {
            if ($hand) {
                $handsData[] = [
                    'value' => $hand->getValue(),
                    'stayed' => $hand->isStayed(),
                    'busted' => $hand->isBusted()
                ];
            }
        }

        if ($game->allPlayersDone()) {
            $logger->info('All players are done, redirecting to result.');
            return $this->redirectToRoute('proj_game_result');
        }

        $logger->info('Game play accessed.', [
            'hands' => $handsData,
            'bankHand' => $game->getBank()->getHand()
        ]);

        return $this->render('proj/game/play.html.twig', [
            'hands' => $game->getPlayerHands(),
            'bankHand' => $game->getBank()->getHand(),
            'betAmount' => $game->getBetAmount()
        ]);
    }

    #[Route('/proj/game/draw', name: 'proj_game_draw', methods: ['POST'])]
    public function draw(SessionInterface $session, LoggerInterface $logger, Request $request): Response
    {
        $handIndex = $request->request->getInt('handIndex', 0);
        $gameData = $session->get('game');
        if (!$gameData) {
            $logger->info('No game data found in session, redirecting to start.');
            return $this->redirectToRoute('proj_game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);

        $hand = $game->getPlayerHands()[$handIndex] ?? null;
        if ($hand) {
            $game->playerDrawCard($handIndex);
            $logger->info('Player drew a card', [
                'handIndex' => $handIndex,
                'handValue' => $hand->getValue()
            ]);

            if ($hand->getValue() > 21) {
                $hand->setBusted(true);
                $this->addFlash('error', 'Hand is busted.');
                $logger->info('Player hand busted', ['handIndex' => $handIndex]);
            }
        }

        $session->set('game', $game->toArray());

        if ($game->allPlayersDone()) {
            $logger->info('All players are done. Bank is playing now.');
            $game->bankPlay();
            $session->set('game', $game->toArray());
            return $this->redirectToRoute('proj_game_result');
        }

        return $this->redirectToRoute('proj_game_play');
    }

    #[Route('/proj/game/stay', name: 'proj_game_stay', methods: ['POST'])]
    public function stay(SessionInterface $session, LoggerInterface $logger, Request $request): Response
    {
        $handIndex = $request->request->getInt('handIndex', 0);
        $gameData = $session->get('game');
        if (!$gameData) {
            $logger->info('No game data found in session, redirecting to start.');
            return $this->redirectToRoute('proj_game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);

        $hand = $game->getPlayerHands()[$handIndex] ?? null;
        if ($hand) {
            $hand->setStayed(true);
            $logger->info("Player hand $handIndex stayed", [
                'handIndex' => $handIndex,
                'hands' => $game->getPlayerHands()
            ]);
            $session->set('game', $game->toArray());
        }

        if ($game->allPlayersDone()) {
            $logger->info('All players are done. Bank is playing now.');
            $game->bankPlay();
            $session->set('game', $game->toArray());
            return $this->redirectToRoute('proj_game_result');
        }

        return $this->redirectToRoute('proj_game_play');
    }

    #[Route('/proj/game/result', name: 'proj_game_result')]
    public function result(SessionInterface $session, LoggerInterface $logger): Response
    {
        $gameData = $session->get('game');
        if (!$gameData) {
            $logger->info('No game data found in session, redirecting to start.');
            return $this->redirectToRoute('proj_game_start');
        }

        $deck = new DeckOfCards();
        $player = new Player();
        $bank = new Bank();
        $game = new Game($player, $bank, $deck, $logger);
        $game->fromArray($gameData, $logger);

        $playerName = $session->get('playerName');
        $bankBalance = $session->get('bankBalance');
        $results = $game->getResult();
        $totalWinnings = 0;
        $bankFinalScore = $game->getBank()->getHand()->getValue();

        $winnings = [];
        $betAmount = $game->getBetAmount();
        foreach ($results as $index => $result) {
            if ($result === 'Won') {
                $winnings[$index] = $betAmount;
                $totalWinnings += $betAmount;
            } elseif ($result === 'Busted' || $result === 'Lost') {
                $winnings[$index] = -$betAmount;
                $totalWinnings -= $betAmount;
            } else {
                $winnings[$index] = 0;
            }
        }

        $bankBalance += $totalWinnings;
        $session->set('bankBalance', $bankBalance);

        $logger->info('Game result calculated.', [
            'playerName' => $playerName,
            'hands' => $game->getPlayerHands(),
            'bankHand' => $game->getBank()->getHand(),
            'results' => $results,
            'bankBalance' => $bankBalance,
            'totalWinnings' => $totalWinnings,
            'bankFinalScore' => $bankFinalScore
        ]);

        return $this->render('proj/game/result.html.twig', [
            'playerName' => $playerName,
            'hands' => $game->getPlayerHands(),
            'bankHand' => $game->getBank()->getHand(),
            'results' => $results,
            'bankBalance' => $bankBalance,
            'totalWinnings' => $totalWinnings,
            'bankFinalScore' => $bankFinalScore,
            'winnings' => $winnings
        ]);
    }
}
