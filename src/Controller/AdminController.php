<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[IsGranted('ROLE_ADMIN')]
#[Route('/administration')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin')]
    public function index(ChartBuilderInterface $chartBuilder, UserRepository $ur): Response
    {
        $labels = [];
        $datasets = [];
        $users = $ur->findAll();

        foreach ($users as $user) {
            $labels[] = $user->getUserIdentifier();
            $datasets[] = $user->getScore();
            $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
            $chart->setData([
                'labels' => $labels,
                'datasets' => [
                    'label' => 'Test',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $datasets,
                ]
            ]);
            $chart->setOptions([/* ... */]);
        }
        return $this->render('admin/index.html.twig', ['chart' => $chart]);
    }

    /**
     * @Route ("/manage_account", name="main_admin")
     */
    public function account (UserRepository $ur) {
        $users  = $ur->findAll();

        return $this->render('admin/manage_account.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/manage_account/{id}", name="admin_delete")
     */
    public function deleteAccount($id, UserRepository $ur, EntityManagerInterface $em ){
        $user = $ur->find(['id'=>$id]);
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('main_admin');
    }

    /**
     * @Route ("/manage_account/{id}" , name="admin_updrade")
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
}
