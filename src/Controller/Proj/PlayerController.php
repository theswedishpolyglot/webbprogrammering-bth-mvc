<?php

namespace App\Controller\Proj;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Form\PlayerRegistrationType;

class PlayerController extends AbstractController
{
    #[Route('/proj/register', name: 'proj_register')]
    public function register(Request $request, SessionInterface $session): Response
    {
        $form = $this->createForm(PlayerRegistrationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $playerData = $form->getData();
            $session->set('playerName', $playerData['name']);
            $session->set('bankBalance', $playerData['bankBalance']);
            return $this->redirectToRoute('proj_home');
        }

        return $this->render('proj/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
