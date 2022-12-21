<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Conta;
use App\Form\ContaFormType;
use App\Form\RegistrationFormType;
use App\Repository\ContaRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ContaRepository $contaRepository): Response
    {
        $user_loged = $this->getUser();
        

       if ($user_loged){
        $us = $userRepository->findOneBy(['email' => $user_loged->getUserIdentifier()]);
        return $this->redirectToRoute('app_conta_criar', ['user' =>$us->getId() ]);

        
       }

        $user = new User();
        
        $form = $this->createForm(
            RegistrationFormType::class, $user, 
            
        );

        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {

            // encode the plain password

            #if exist user
            
        

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_CLIENT']);

            $cpf = $form->get('cpf')->getData();

            $existUser = $userRepository->findOneBy(['cpf' => $cpf]);
            
            if ($existUser){
                $user = $existUser;
                $contaExistente = $contaRepository->findOneBy(['usuario' => $existUser->getId(),'active' => true]);
                
            }else{
                $contaExistente = false;
            }


            

            if ($contaExistente){
                return $this->redirectToRoute('app_conta_criar', ['user' => $existUser->getId()]);
            }
           
            $entityManager->persist($user);
            

            $agencia = $form->get('conta')->getData();
           # dd($agencia);
            $conta = new Conta();
            if ($existUser){
                $conta->setUsuario($existUser);
            }else{
                $conta->setUsuario($user);
            }
            
            $conta->setSaldo(0);
            $conta->setNumero(rand(100000, 999999));
            $conta->setAgencia($agencia->getAgencia());
            $conta->setTipo($agencia->getTipo());

          

            
            $conta->setActive(false);
            
            

            $entityManager->persist($conta);
           

            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('ozkberguinho@gmail.com', 'Kbank agencias'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email
                  

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
