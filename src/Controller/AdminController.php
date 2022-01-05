<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Knp\Component\Pager\PaginatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/administration')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin')]
    public function index(ChartBuilderInterface $chartBuilder, UserRepository $ur): Response{

        return $this->render('admin/index.html.twig');
    }

    /**
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
        return $this->redirectToRoute('main_admin');
    }

    /**
     * @Route ("/manage_account/upgrade/{id}" , name="admin_upgrade")
     */
    public function upgradeAccount($id, UserRepository $ur, EntityManagerInterface $em){
        $user = $ur->find(['id'=>$id]);
        if(in_array("ROLE_ADMIN",$user->getRoles())){
            $user->setRoles(["ROLE_ADMIN"]);
        }else{
            $user->setRoles(["ROLE_USER"]);
        }
        $em->flush();
        return $this->redirectToRoute('main_admin');
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
}
