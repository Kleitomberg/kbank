<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GerenteController extends AbstractController
{
    #[Route('/gerente', name: 'app_gerente')]
    public function index(): Response
    {
        return $this->render('gerente/index.html.twig', [
            'controller_name' => 'GerenteController',
        ]);
    }
}
