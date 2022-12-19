<?php

namespace App\Controller;

use App\Repository\AgenciaRepository;

use App\Entity\Agencia;
use App\Form\AgenciaFormType;
use App\Repository\GerenteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgenciaController extends AbstractController
{
    #[Route('/agencia', name: 'app_agencia')]
    public function index(AgenciaRepository $agenciaRepository, GerenteRepository $gerenteRepository, UserRepository $userRepository): Response
    {
        $agencias = $agenciaRepository->findAll();
        $gerentes = $gerenteRepository->findAll();
        

        //dd($agencias);


        return $this->render('agencia/index.html.twig', [
            'agencias' => $agencias,
            'page' => 'agencias',
            'gerentes' => $gerentes,
            #'users' => $users,
        ]);
    }
    
    #[Route('/agencia/criar', name: 'app_agencia_criar')]

    public function criarAgencia(Request $request, AgenciaRepository $agenciaRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        $form = $this->createForm(AgenciaFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $agencia = $form->getData();
            $entityManagerInterface->persist($agencia);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_agencia');
        }
    

        
        return $this->render('agencia/form.twig', [
            'agenciaForm' => $form->createView(),
            'page' => 'agencias',
            
        ]);
    }

}
