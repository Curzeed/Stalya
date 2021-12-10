<?php

namespace App\Controller;

use App\Form\QCMType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function main(QuestionRepository $qr, Request $request, EntityManagerInterface $em) : Response{
        $questions = $qr->findAll();
        $form = $this->createForm(QCMType::class,$questions);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em->persist();
            $em->flush();

        }
        return $this->render('qcm/main_qcm.html.twig', ['questions' => $questions]);
    }
}
