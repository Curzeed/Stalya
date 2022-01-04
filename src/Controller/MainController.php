<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @Route("/liste", name="session_list")
     */
    public function listSession(SessionRepository $sr){
        if(!$this->getUser()->canSignupToSession()) {
            $this->addFlash('warning', 'Cassez vous');
            return $this->redirectToRoute('main');
        }
        return $this->render('session/available_session_user.html.twig', ['sessions' => $sr->findAll()]);
    }
    /**
     * @Route ("/inscription/{id}", name="session_inscription")
     */
    public function signUpSession(Session $session, EntityManagerInterface $em) :  Response{
        $user = $this->getUser();
        if(!$user->canSignupToSession()) {
            $this->addFlash('warning', 'Cassez vous');
            return $this->redirectToRoute('session_list');
        }
        $session->addUser($user);
        $user->setSession($session);
        $em->flush();
        return $this->redirectToRoute('session_list');
    }
    /**
     * @Route ("/liste/{id}/users", name="session_list_users")
     */
    public function listSessionUsers(Session $session){
        return $this->render('session/available_list_session_users.html.twig', ['users_session'=> $session->getUser()]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route ("/history/{id}",name="user_history")
     */
    public function historyUser(User $user){
        $histories = $user->getHistories();

        $tab = [];
        $questions = [];


        foreach ($histories as $history) {
            if ($history->getResponse()->getQuestion()->countCorrect() > 1) {
                $tab[$history->getResponse()->getQuestion()->getId()][$history->getResponse()->getId()] = $history;
            } else {
                $tab[$history->getResponse()->getQuestion()->getId()] = $history;
            }
            $questions[$history->getResponse()->getQuestion()->getId()] = $history->getResponse()->getQuestion();
        }

        return $this->render('session/user_history.html.twig', [
            'histories' => $tab,
            'questions' => $questions
        ]);
    }

    /**
     * @Route ("/revoke/{id}", name="session_revoke")
     */
    public function revokeSession(Session $session, EntityManagerInterface $em) : Response{
        $user = $this->getUser();

        $session->removeUser($user);
        $em->flush();
        return $this->redirectToRoute('session_list');
    }
}
