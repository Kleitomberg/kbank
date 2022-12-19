<?php

namespace App\Controller;

use App\Entity\Transacao;
use App\Repository\AgenciaRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContaRepository;
use App\Repository\TransacaoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #deposito

    #[Route('/conta/deposito', name: 'app_conta_deposito')]
    public function deposito(Request $request, ContaRepository $contaRepository, TransacaoRepository $transacaoRepository, AgenciaRepository $agenciaRepository,  EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $numero = $request->request->get('conta');
            $valor = $request->request->get('valor');
            $agencia = $request->request->get('agencia');

            if (!$numero) {
                $this->addFlash('error', 'Conta não encontrada!');
                return $this->redirectToRoute('app_index');
            }
            if (!$valor) {
                $this->addFlash('error', 'Iforme o valor!');
                return $this->redirectToRoute('app_index');
            }

            $agenciaa = $agenciaRepository->findOneBy(array('codigo' => $agencia));
            if (!$agenciaa) {
                $this->addFlash('error', 'Agencia não encontrada!');
                return $this->redirectToRoute('app_index');
            }
            

           
            $conta = $contaRepository->findOneBy(array('numero' => $numero, 'agencia' => $agenciaa));

            if (!$conta) {
                $this->addFlash('error', 'Conta não encontrada!');
                return $this->redirectToRoute('app_index');
            }
            
           
            $conta->setSaldo($conta->getSaldo() + $request->request->get('valor'));

           
            $entityManager->persist($conta);
            $entityManager->flush();
            $transacao = new Transacao();
            $transacao->setDestinatario($conta);
            $transacao->setValor($request->request->get('valor'));
            $transacao->setDescricao('deposito');
            $entityManager->persist($transacao);
            $entityManager->flush();
            $this->addFlash('success', 'Deposito realizado com sucesso!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('conta/deposito.html.twig', [
            'controller_name' => 'ContaController',
        ]);
    }

}

