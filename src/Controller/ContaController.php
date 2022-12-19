<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContaController extends AbstractController
{
    #[Route('/conta', name: 'app_conta')]
    public function index(): Response
    {
        return $this->render('conta/index.html.twig', [
            'controller_name' => 'ContaController',
        ]);
    }
}
