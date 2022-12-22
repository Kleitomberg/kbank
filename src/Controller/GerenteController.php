<?php

namespace App\Controller;

use App\Entity\Gerente;
use App\Entity\User;
use App\Repository\AgenciaRepository;
use App\Repository\ContaRepository;
use App\Repository\GerenteRepository;
use App\Repository\TransacaoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GerenteController extends AbstractController
{
    #[Route('/gerente/{gerente}', name: 'app_gerente')]
    public function index(AgenciaRepository $agenciaRepository, $gerente, GerenteRepository $gerenteRepository, UserRepository $userRepository, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository): Response
    {   

       

        $user = $userRepository->find($gerente);
        $gerente = $gerenteRepository->findOneBy(['user' => $user]);
        $agencia = $agenciaRepository->findOneBy(['gerente' => $gerente->getId()]);
        
        $contas = $contaRepository->findBy(['agencia' => $agencia->getId()]);
        $contas_ativas = $contaRepository->findBy(['agencia' => $agencia->getId(), 'active' => "1"]);

        $contas_inativas = $contaRepository->findBy(['agencia' => $agencia->getId(), 'active' => "0"]);

        
        $usuario = $this->getUser();

        if ($usuario->getUserIdentifier() != $gerente->getUser()->getEmail()) {
            $this->addFlash('error', 'Você não tem permissão para acessar essa página!');
            return $this->redirectToRoute('app_index');
        }
        

        
        
        $clientes = [];
        
        foreach ($contas_ativas as $conta) {
            $cliente = $conta->getUsuario();
            $cliente->getEmail();

            $clientes[] = $cliente;
        }

        #transações da agencia
        
        $transacoes_a = $transacaoRepository->findBy(['destinatario' => $contas_ativas]);
        $transacoes_b = $transacaoRepository->findBy(['remetente' => $contas_ativas]);

        $transacoes = array_merge($transacoes_a, $transacoes_b);
        

        
        


        return $this->render('gerente/index.html.twig', [
            'controller_name' => 'GerenteController',
            'agencia' => $agencia,
            'contas' => $contas,  
            'contas_ativas' => count($contas_ativas),
            'contas_inativas' => count($contas_inativas),
            'clientes' => $clientes,
            'transacoes' => $transacoes,

        ]);
    }
    #aprovarConta
    #[Route('/gerente/aprovarConta/{conta}', name: 'app_conta_aprovar')]
    public function aprovarConta(Request $request, $conta, UserRepository $userRepository, ContaRepository $contaRepository, EntityManagerInterface $entityManager): Response
    {
        $conta = $contaRepository->find($conta);
        $conta->setActive(true);
        $user = $conta->getUsuario();
        $user->setRoles(['ROLE_CLIENTE']);
        $user->setIsVerified(true);

        $entityManager->persist($user);


        $entityManager->persist($conta);
        $entityManager->flush();
        
        $gerente = $this->getUser();
        $gerente->getUserIdentifier();

        $gerente = $userRepository->findOneBy(['email' => $gerente->getUserIdentifier()]);

        $this->addFlash('success', 'Conta aprovada com sucesso!');
        return $this->redirectToRoute('app_gerente', ['gerente' => $gerente->getId()]);


    }
}
