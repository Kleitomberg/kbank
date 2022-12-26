<?php

namespace App\Controller;

use App\Entity\Transacao;
use App\Entity\User;
use App\Repository\ContaRepository;
use App\Repository\TransacaoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClienteController extends AbstractController
{
    #[Route('/cliente/{id}', name: 'app_cliente')]
    public function index(User $user, ContaRepository $contaRepository): Response
    {   
        $usuario = $this->getUser();

        if ($usuario->getUserIdentifier() != $user->getEmail()) {
            $this->addFlash('error', 'Você não tem permissão para acessar essa página!');
            return $this->redirectToRoute('app_index');
        }
        $contas = $contaRepository->findBy(['usuario' => $user->getId(), 'active' => true]);
        
        return $this->render('cliente/index.html.twig', [
            'controller_name' => 'ClienteController',
            'contas' => $contas,
        ]);
    }

    #[Route('/cliente/{id}/conta/{conta}', name: 'app_cliente_conta')]
    public function conta(User $user, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository, $conta): Response
    {

        $conta = $contaRepository->findOneBy(['usuario' => $user->getId(), 'active' => true, 'id' => $conta]);

        $transacoes_receive = $transacaoRepository->findBy(['destinatario' => $conta->getId()]);
        $transacoes_send = $transacaoRepository->findBy(['remetente' => $conta->getId()]);
        $transacoes = array_merge($transacoes_receive, $transacoes_send);
        return $this->render('cliente/conta.html.twig', [
            'controller_name' => 'ClienteController',
            'conta' => $conta,
            'transacoes' => $transacoes,
        ]);
    }

    #[Route('/cliente/{id}/conta/{conta}/depositar', name: 'app_cliente_conta_depositar')]
    public function depositar(Request $request, User $user, ContaRepository $contaRepository, $conta, EntityManagerInterface $entityManager): Response
    {
        $conta = $contaRepository->findOneBy(['usuario' => $user->getId(), 'active' => true, 'id' => $conta]);

        if (!$conta) {
            $this->addFlash('error', 'Conta não encontrada!');
            return $this->redirectToRoute('app_index');
        }
        #post 
        if ($request->isMethod('POST')) {
            $valor = $request->request->get('valor');
            if ($valor <= 0) {
                $this->addFlash('error', 'Valor inválido!');
                
            }
            else{
                $conta->creditar($valor);

            }
            $entityManager->persist($conta);
            $transacao = new Transacao();
            $transacao->setDescricao('Depósito');
            $transacao->setValor($valor);
            $transacao->setDestinatario($conta);
            $entityManager->persist($transacao);
            $entityManager->flush();
            $this->addFlash('success', 'Deposito realizado com sucesso!');
            return $this->redirectToRoute('app_cliente_conta', ['id' => $user->getId(), 'conta' => $conta->getId()]);
        }

        return $this->redirect($request->headers->get('referer'));

    }
    
    #transferecia
    #[Route('/cliente/{id}/conta/{conta}/transferir', name: 'app_cliente_conta_transferir')]
    public function transferir(Request $request, $conta, User $user, ContaRepository $contaRepository, EntityManagerInterface $entityManager):Response
    {

        $minhaconta = $contaRepository->findOneBy(['usuario' => $user->getId(), 'active' => true, 'id' => $conta]);

        if ($request->isMethod('POST')) {
            
            $contaDestino = $contaRepository->findOneBy(['numero' => $request->request->get('conta')]);
            $valor = $request->request->get('valor');

            if (!$contaDestino) {
                $this->addFlash('error', 'Conta não encontrada!');
            }
            else if ($valor <= 0) {
                $this->addFlash('error', 'Valor inválido!');
            }
            else if ($minhaconta->getSaldo() < $valor) {
                $this->addFlash('error', 'Saldo insuficiente!');
            }
            else{
                
                $minhaconta->transferir($valor, $contaDestino);
                
                $entityManager->persist($minhaconta);
                $entityManager->persist($contaDestino);
                $transacao = new Transacao();
                $transacao->setDescricao('Transferência');
                $transacao->setValor($valor);
                $transacao->setDestinatario($contaDestino);
                $transacao->setRemetente($minhaconta);
                $entityManager->persist($transacao);

                $entityManager->flush();
                $this->addFlash('success', 'Transferência realizada com sucesso!');
                return $this->redirectToRoute('app_cliente_conta', ['id' => $user->getId(), 'conta' => $minhaconta->getId()]);
        }

        return $this->redirect($request->headers->get('referer'));

    }
}
#app_cliente_conta_sacar
    #[Route('/cliente/{id}/conta/{conta}/sacar', name: 'app_cliente_conta_sacar')]
    public function sacar(Request $request, $conta, User $user, ContaRepository $contaRepository, EntityManagerInterface $entityManager):Response
    {

        $minhaconta = $contaRepository->findOneBy(['usuario' => $user->getId(), 'active' => true, 'id' => $conta]);

        if ($request->isMethod('POST')) {
            
            $valor = $request->request->get('valor');

            if ($valor <= 0) {
                $this->addFlash('error', 'Valor inválido!');
            }
            else if ($minhaconta->getSaldo() < $valor) {
                $this->addFlash('error', 'Saldo insuficiente!');
            }
            else{

                $minhaconta->debitar($valor);
                
                $entityManager->persist($minhaconta);

                $transacao = new Transacao();
                $transacao->setDescricao('Saque');
                $transacao->setValor($valor);
                $transacao->setDestinatario($minhaconta);
                $entityManager->persist($transacao);
                $entityManager->flush();
                $this->addFlash('success', 'Saque realizado com sucesso!');
                return $this->redirectToRoute('app_cliente_conta', ['id' => $user->getId(), 'conta' => $minhaconta->getId()]);

                
            }
            return $this->redirect($request->headers->get('referer'));
        }
    }       
    
    #app_cliente_conta_encerrar
    #[Route('/cliente/{id}/conta/{conta}/encerrar', name: 'app_cliente_conta_encerrar')]
    public function encerrar(Request $request, $conta, User $user, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository, EntityManagerInterface $entityManager):Response
    {

        $minhaconta = $contaRepository->findOneBy(['usuario' => $user->getId(), 'active' => true, 'id' => $conta]);

            
            $minhaconta->setActive(false);

           
            
            $saldo = $minhaconta->getSaldo();
            if ($saldo > 0) {
                $this->addFlash('error', 'Não é possível encerrar uma conta com saldo positivo!');
                return $this->redirectToRoute('app_cliente_conta', ['id' => $user->getId(), 'conta' => $minhaconta->getId()]);
            }
                
           
            $transaocoes = $transacaoRepository->findBy(['remetente' => $minhaconta->getId()]);
            foreach ($transaocoes as $transacao) {
                $transacao->setRemetente(null);
                $entityManager->persist($transacao);
            }
            $contaRepository->remove($minhaconta);

            
            $entityManager->flush();
            $this->addFlash('success', 'Conta encerrada com sucesso!');
            return $this->redirectToRoute('app_cliente', ['id' => $user->getId()]);
    
    }
    
}