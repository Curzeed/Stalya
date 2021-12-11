<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $passwordConfirm = $request->get('passwordconfirm');
            if ($passwordConfirm === $form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $userPasswordHasherInterface->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $token  = hash("sha256", $user->getEmail());
                $user->setTokenMail($token);
                $user->setIsVerified(false);
                $url = $this->generateUrl("validate_email",[
                    'token' => $token,
                    'email' => $user->getEmail()
                ], UrlGeneratorInterface::ABSOLUTE_URL);
                $email = (new Email())
                        ->from('test@test.test')
                        ->to($user->getEmail())
                        ->subject('Confirmation de compte ! ')
                        ->html("
                            <a  href='$url'> Validez votre mail ! </a>
                            <p> $url</p>
                            ");
                $entityManager = $this->getDoctrine()->getManager();
                $user->setRoles(["ROLE_USER"]);
                $user->setScore(0);
                $entityManager->persist($user);
                $entityManager->flush();
                try {
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    echo $e->getMessage();
                }
                $this->addFlash('success','Ajout avec succès, veuillez confirmer votre adresse e-mail.');
                return $this->redirectToRoute('app_login');
            }else{
                $this->addFlash('error',"Les mots de passe ne correspondent pas ! ");
                return $this->redirectToRoute('app_register');
            }
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route ("/validate/email/{token}/{email}", name="validate_email")
     */
    public function validateEmail($token, $email, UserRepository $ur, EntityManagerInterface $er){
        $user = $ur->findOneBy(['email' =>$email]);
        $user->setIsVerified(true);
        $er->persist($user);
        $er->flush();
        $this->addFlash('success',"Votre e-mail a bien été vérifié !");
        return $this->redirectToRoute('app_login');
    }
}