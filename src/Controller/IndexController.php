<?php

namespace App\Controller;

use App\Repository\AgenciaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(AgenciaRepository $agenciaRepository, UserRepository $userRepository): Response
    {
       $agencias = $agenciaRepository->findAll();

     
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'page'=>'home',
            'agencias' => $agencias,
        ]);
    }
}
