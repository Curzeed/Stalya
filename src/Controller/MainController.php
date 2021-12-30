<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
    /**
     * @Route("/liste", name="session_list_users")
     */
    public function listSession(SessionRepository $sr){
        return $this->render('session/available_session_user.html.twig', ['sessions' => $sr->findAll()]);
    }
    /**
     * @Route ("/inscription/{id}", name="session_inscription")
     */
    public function signUpSession(Session $session, EntityManagerInterface $em) :  Response{
        $user = $this->getUser();
        $session->addUser($user);
        $user->setSession($session);
        $em->flush();
        return $this->redirectToRoute('session_list_users');
    }
    /**
     * @Route ("/revoke/{id}", name="session_revoke")
     */
    public function revokeSession(Session $session, EntityManagerInterface $em) : Response{
        $user = $this->getUser();

        $session->removeUser($user);
        $em->flush();
        return $this->redirectToRoute('session_list_users');
    }
}
