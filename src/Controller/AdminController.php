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
}
