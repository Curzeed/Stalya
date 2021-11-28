<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QCMController extends AbstractController
{
    #[Route('/qcm', name: 'index_qcm')]
    public function index(): Response
    {
        return $this->render('qcm/index.html.twig', [
            'controller_name' => 'QCMController',
        ]);
    }

    /**
     * @Route ("/main_qcm", name="main_qcm")
     */
    public function main(QuestionRepository $qr) : Response{
        $questions = $qr->findAll();

        return $this->render('qcm/main_qcm.html.twig');
    }
}
