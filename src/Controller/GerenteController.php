<?php

namespace App\Controller;

use App\Entity\Gerente;
use App\Entity\Transacao;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        $user->setRoles(['ROLE_CLIENT']);
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

    #editar agencia
    #[Route('/gerente/editarAgencia/{agencia}', name: 'app_agencia_editar')]
    public function editarAgencia(Request $request, UserRepository $userRepository, $agencia, AgenciaRepository $agenciaRepository, EntityManagerInterface $entityManager): Response
    {
        



       if ($request->isMethod('POST')){
        $agencia = $agenciaRepository->find($agencia);
        
        $agencia->setCodigo($request->request->get('codigo'));
        $agencia->setBairro($request->request->get('bairro'));
        $agencia->setCep($request->request->get('cep'));
        $agencia->setCidade($request->request->get('cidade'));
        $agencia->setEstado($request->request->get('estado'));
        $agencia->setNumero($request->request->get('numero'));
        $agencia->setRua($request->request->get('rua'));
        $agencia->setTelefone($request->request->get('telefone'));
       

        $entityManager->persist($agencia);
        $entityManager->flush();

        $gerente = $this->getUser();
        $gerente->getUserIdentifier();

        $gerente = $userRepository->findOneBy(['email' => $gerente->getUserIdentifier()]);

        $this->addFlash('success', 'Agência editada com sucesso!');
        return $this->redirectToRoute('app_gerente', ['gerente' => $gerente->getId()]);
       }
       return $this->render('gerente/form_agencia.twig', [
        'controller_name' => 'GerenteController',
        'agencia' => $agenciaRepository->find($agencia),
    ]);


        
    }
    #editar cliente
    #[Route('/gerente/editarCliente/{cliente}', name: 'app_cliente_editar')]
    public function editarCliente(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, UserRepository $userRepository, $cliente, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($cliente);
        
        if ($request->isMethod('POST')){
            $user->setNome($request->request->get('nome'));
            $user->setCpf($request->request->get('cpf'));
            $user->setEmail($request->request->get('email'));
            $user->setCelular($request->request->get('celular'));
            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $user,
                    $request->request->get('password')
                )

                );

           
            $user->setIsVerified(true);
            
            $entityManager->persist($user);
            $entityManager->flush();
    
            $gerente = $this->getUser();
            $gerente->getUserIdentifier();
    
            $gerente = $userRepository->findOneBy(['email' => $gerente->getUserIdentifier()]);
    
            $this->addFlash('success', 'Cliente editado com sucesso!');
            return $this->redirectToRoute('app_gerente', ['gerente' => $gerente->getId()]);
           }

           return $this->render('gerente/form_cliente.twig', [
            'controller_name' => 'GerenteController',
            'cliente' => $user,
        ]);

        

    }

    #acessar conta
    #[Route('/gerente/{gerente}/acessarConta/{conta}', name: 'app_conta_acessar_gerente')]
    public function acessarConta(Request $request, $conta, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository): Response
    {
        $conta = $contaRepository->find($conta);
        $transacoes = $transacaoRepository->findBy(['destinatario' => $conta]);
        $transacoes_b = $transacaoRepository->findBy(['remetente' => $conta]);

        $transacoes = array_merge($transacoes, $transacoes_b);

        return $this->render('gerente/conta.html.twig', [
            'controller_name' => 'GerenteController',
            'conta' => $conta,
            'transacoes' => $transacoes,
        ]);
    }

    #transações

    #sacar
    #[Route('/gerente/{gerente}/acessarConta/{conta}/sacar', name: 'app_conta_sacar_gerente')]
    public function sacar(Request $request, $conta, $gerente,UserRepository $userRepository, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository, EntityManagerInterface $entityManager): Response
    {   
        $user = $this->getUser();
        $user->getUserIdentifier();
        $gerente = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        
        $conta = $contaRepository->find($conta);
        $transacoes = $transacaoRepository->findBy(['destinatario' => $conta]);
        $transacoes_b = $transacaoRepository->findBy(['remetente' => $conta]);

        $transacoes = array_merge($transacoes, $transacoes_b);

        if ($request->isMethod('POST')){
            $valor = $request->request->get('valor');
            $conta->setSaldo($conta->getSaldo() - $valor);
            $transacao = new Transacao();
            $transacao->setValor($valor);
            $transacao->setDestinatario($conta);
            $transacao->setDescricao('Saque');
            $transacao->setData(new \DateTime());
            $entityManager->persist($transacao);
            $entityManager->persist($conta);
            $entityManager->flush();
            $this->addFlash('success', 'Saque realizado com sucesso!');
            return $this->redirectToRoute('app_conta_acessar_gerente', ['gerente'=> $gerente->getId() ,'conta' => $conta->getId()]);
        }

        return $this->render('gerente/conta.html.twig', [
            'controller_name' => 'GerenteController',
            'conta' => $conta,
            'transacoes' => $transacoes,
        ]);
    }

    #depositar

    #[Route('/gerente/{gerente}/acessarConta/{conta}/depositar', name: 'app_conta_depositar_gerente')]
    public function depositar(Request $request, $conta, $gerente,UserRepository $userRepository, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository, EntityManagerInterface $entityManager): Response
    {   
        $user = $this->getUser();
        $user->getUserIdentifier();
        $gerente = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        
        $conta = $contaRepository->find($conta);
        $transacoes = $transacaoRepository->findBy(['destinatario' => $conta]);
        $transacoes_b = $transacaoRepository->findBy(['remetente' => $conta]);

        $transacoes = array_merge($transacoes, $transacoes_b);

        if ($request->isMethod('POST')){
            $valor = $request->request->get('valor');
            $conta->setSaldo($conta->getSaldo() + $valor);
            $transacao = new Transacao();
            $transacao->setValor($valor);
            $transacao->setDestinatario($conta);
            $transacao->setDescricao('Depósito');
            $transacao->setData(new \DateTime());
            $entityManager->persist($transacao);
            $entityManager->persist($conta);
            $entityManager->flush();
            $this->addFlash('success', 'Depósito realizado com sucesso!');
            return $this->redirectToRoute('app_conta_acessar_gerente', ['gerente'=> $gerente->getId() ,'conta' => $conta->getId()]);
        }

        return $this->render('gerente/conta.html.twig', [
            'controller_name' => 'GerenteController',
            'conta' => $conta,
            'transacoes' => $transacoes,
        ]);
    }

    #transferir

    #[Route('/gerente/{gerente}/acessarConta/{conta}/transferir', name: 'app_conta_transferir_gerente')]
    public function transferir(Request $request, $conta, $gerente,UserRepository $userRepository, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository, EntityManagerInterface $entityManager): Response
    {   
        $user = $this->getUser();
        $user->getUserIdentifier();
        $gerente = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        
        $conta = $contaRepository->find($conta);

        
        $transacoes = $transacaoRepository->findBy(['destinatario' => $conta]);
        $transacoes_b = $transacaoRepository->findBy(['remetente' => $conta]);

        $transacoes = array_merge($transacoes, $transacoes_b);

        #Fazer um codigo que: imprime numeros de 500 a -100 multiplos de 3 e 5.
        for ($i = 500; $i >= -100; $i--) {
            if ($i % 3 == 0 && $i % 5 == 0) {
                echo $i . "<br>";
            }
        }

        if ($request->isMethod('POST')){
            $valor = $request->request->get('valor');
            $transacao = new Transacao();
            $transacao->setValor($valor);
            $transacao->setRemetente($conta);
            $conta_destino = $contaRepository->findOneBy(['numero' => $request->request->get('conta')]);
            $transacao->setDestinatario($conta_destino);
            $conta->transferir($valor,$conta_destino);
            $transacao->setDescricao('Transferência');
            $transacao->setData(new \DateTime());
            $entityManager->persist($transacao);
            $entityManager->persist($conta);
            $entityManager->flush();
            $this->addFlash('success', 'Transferência realizada com sucesso!');
            return $this->redirectToRoute('app_conta_acessar_gerente', ['gerente'=> $gerente->getId() ,'conta' => $conta->getId()]);
        }

        return $this->render('gerente/conta.html.twig', [
            'controller_name' => 'GerenteController',
            'conta' => $conta,
            'transacoes' => $transacoes,
        ]);
    }

}
