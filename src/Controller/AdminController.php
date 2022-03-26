<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADMIN')]
#[Route('/administration')]
class AdminController extends AbstractController
{
    /**
     * @IsGranted ("ROLE_ADMIN") or ("ROLE_MOD")
     * @Route ("/manage_account/{page}", name="admin_manage_account")
     */
    public function account (Request $request,UserRepository $ur, $page = 1) {

        if($request->getMethod() == 'GET' && $request->get('nbPage') !== null){
            $pageSize = $request->get('nbPage');
        }else{
            $pageSize = '10';
        }


        $query = $ur->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->getQuery();

        $paginator = new  \Doctrine\ORM\Tools\Pagination\Paginator($query);

        $totalAccounts = count($paginator);

        $pagesCount= ceil($totalAccounts / $pageSize);

        $users = $paginator->getQuery()
            ->setFirstResult($pageSize * ($page - 1))
            ->setMaxResults($pageSize)
            ->getResult();

        return $this->render('admin/manage_account.html.twig', [
            'users' => $users,
            'pagesCount' => $pagesCount,
            'current' => $page
        ]);
    }

    /**
     * @Route("/manage_account/delete/{id}", name="admin_delete")
     */
    public function deleteAccount($id, UserRepository $ur, EntityManagerInterface $em ){
        $user = $ur->find(['id'=>$id]);
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('admin_manage_account');
    }

    /**
     * @IsGranted ("ROLE_ADMIN")
     * @Route ("/manage_account/upgrade/{id}" , name="admin_upgrade")
     */
    public function upgradeAccount($id, UserRepository $ur, EntityManagerInterface $em, Request $request){
        if($request->getMethod() == 'POST'){
            $user = $ur->find(['id'=> $id]);
            $rank = $request->get('roles');
            switch ($rank){
                case 'user' : $user->setRoles(['ROLE_USER']); break;
                case 'mod' : $user->setRoles(['ROLE_MOD']); break;
                case 'admin' : $user->setRoles(['ROLE_ADMIN']); break;
                default : $this->addFlash('error',"Une erreur s'est produite, merci de contacter un développeur pour résoudre ce problème");
            }
            $em->flush();
        }
        return $this->redirectToRoute('admin_manage_account');
    }

    /**
     * @Route ("/api/filter_accounts", name="filter_users")
     */
    public function filterUsers(UserRepository $ur, Request $request) {
        $users = $ur->getUsersBySearch($request->get('search'));

        return $this->render('admin/_users_tab.html.twig', [
            'users' => $users
        ]);
    }
    /**
     * @Route ("/amdin/reset/timer/{user}", name="admin_reset_timer")
     */
    public function resetTimer (User $user, EntityManagerInterface $em){
        $user->setLastAttempt(null);
        $em->flush();
        return $this->redirectToRoute('admin_manage_account');
    }

}
