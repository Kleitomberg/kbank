<?php

namespace App\Controller;

use App\Entity\Agencia;
use App\Entity\Conta;
use App\Entity\Gerente;
use App\Entity\User;
use App\Form\AgenciaFormType;
use App\Repository\AgenciaRepository;
use App\Repository\ContaRepository;
use App\Repository\GerenteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepository, AgenciaRepository $agenciaRepository, GerenteRepository $gerenteRepository, ContaRepository $contaRepository ): Response
    {
        $users = $userRepository->findAll();
        $agencias = $agenciaRepository->findAll();
        $gerentes = $gerenteRepository->findAll();
        $contas = $contaRepository->findAll();
        


        return $this->render('admin/index.html.twig', [
            'users' => count($users),
            'agencias' => count($agencias),
            'gerentes' => count($gerentes),
            'contas' => count($contas),
            'page' => 'admin',
        ]);
    }
    #GERENTES
    #[Route('/admin/gerentes', name: 'app_admin_gerentes')]
    public function GetestesAdmin(GerenteRepository $gerenteRepository): Response
    {
        $gerentes = $gerenteRepository->findAll();
        return $this->render('admin/gerentes.twig', [
            'page' => 'gerentes',
            'gerentes' => $gerentes,
        ]);
    }

    #[Route('/admin/gerentes/create', name: 'app_admin_gerentes_create')]
    public function CreateUserGerenteAdmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setNome($request->request->get('nome'));
            $user->setCpf($request->request->get('cpf'));
            $user->setCelular($request->request->get('celular'));
            $user->setIsVerified(true);

            $user->setRoles(['ROLE_GERENTE']);
            $user->setPassword($userPasswordHasher->hashPassword($user, $request->request->get('password')));
            
            $gerente = new Gerente();
            $gerente->setUser($user);   
            $gerente->setNome($request->request->get('nome'));         
            

            $entityManager->persist($gerente);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_gerentes');
        }
        return $this->render('admin/gerentes_create.twig', [
            'page' => 'gerentes',
        ]);
    }

    #[Route('/admin/gerentes/{id}/edit', name: 'app_admin_gerentes_edit')]
    public function EditUserGerenteAdmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Gerente $gerente): Response
    {
        if ($request->isMethod('POST')) {
            $user = $gerente->getUser();
            $user->setEmail($request->request->get('email'));
            $user->setNome($request->request->get('nome'));
            $user->setCpf($request->request->get('cpf'));
            $user->setCelular($request->request->get('celular'));
            $senha = $request->request->get('password');
            $user->setPassword(
            $userPasswordHasher->hashPassword($user,$senha));
            
            $gerente->setNome($request->request->get('nome'));

            $entityManager->flush();
            return $this->redirectToRoute('app_admin_gerentes');
        }
        return $this->render('admin/gerentes_edit.twig', [
            'page' => 'gerentes',
            'gerente' => $gerente,
        ]);
    }

    #[Route('/admin/gerentes/{id}/delete', name: 'app_admin_gerentes_delete')]
    public function DeleteUserGerenteAdmin(EntityManagerInterface $entityManager, Gerente $gerente): Response
    {
        $entityManager->remove($gerente);
        $entityManager->flush();
        return $this->redirectToRoute('app_admin_gerentes');
    }


    #AGENCIA
    #[Route('/admin/agencias', name: 'app_admin_agencias')]
    public function GetAgenciasAdmin(AgenciaRepository $agenciaRepository, GerenteRepository $gerenteRepository): Response
    {
        $agencias = $agenciaRepository->findAll();
        $gerentes = $gerenteRepository->findAll();

        return $this->render('admin/agencias.twig', [
            'page' => 'agencias',
            'agencias' => $agencias,
            'gerentes' => $gerentes,
        ]);
    }

    #[Route('/admin/agencias/create', name: 'app_admin_agencias_create')]
    public function CreateAgenciaAdmin(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $form = $this->createForm(AgenciaFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $agencia = $form->getData();
            $entityManagerInterface->persist($agencia);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_agencia');
        }
    

        
        return $this->render('admin/agencia_create.twig', [
            'agenciaForm' => $form->createView(),
            'page' => 'agencias',
            
        ]);
    }


    #CONTA
    #[Route('/admin/contas', name: 'app_admin_contas')]
    public function GetContasAdmin(ContaRepository $contaRepository): Response
    {
        $contas = $contaRepository->findAll();
        return $this->render('admin/contas.twig', [
            'page' => 'contas',
            'contas' => $contas,
        ]);
    }

    #[Route('/admin/contas/create', name: 'app_admin_contas_create')]
    public function CreateContaAdmin(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $form = $this->createForm(ContaFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $conta = $form->getData();
            $entityManagerInterface->persist($conta);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_conta');
        }
    

        
        return $this->render('admin/conta_create.twig', [
            'contaForm' => $form->createView(),
            'page' => 'contas',
            
        ]);
    }

    #[Route('/admin/contas/{id}/edit', name: 'app_admin_contas_edit')]
    public function EditContaAdmin(Request $request, EntityManagerInterface $entityManager, Conta $conta): Response
    {
        if ($request->isMethod('POST')) {
            $conta->setNumero($request->request->get('numero'));
            $conta->setSaldo($request->request->get('saldo'));
            

            $entityManager->flush();
            return $this->redirectToRoute('app_admin_contas');
        }
        return $this->render('admin/contas_edit.twig', [
            'page' => 'contas',
            'conta' => $conta,
        ]);
    }
    
}
