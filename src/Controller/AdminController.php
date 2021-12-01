<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[IsGranted('ROLE_ADMIN')]
#[Route('/administration', name: 'admin_prefix')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin')]
    public function index(ChartBuilderInterface $chartBuilder, UserRepository $ur): Response
    {
        $user = $ur->findAll();
        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => ['Score > 10', 'Score < 10'],
            'datasets' => [
                'label' => 'Test',
                'data' => count($user, COUNT_NORMAL),
                'backgroundColor' => 'rgb(255, 99, 132)',
                'borderColor' => 'rgb(255, 99, 132)',
            ]
        ]);
        return $this->render('admin/index.html.twig', ['chart' => $chart]);
    }
}
