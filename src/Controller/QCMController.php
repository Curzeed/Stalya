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

            if($request->getMethod() == 'POST'){
                $user = $this->getUser();
                $tableauDeQuestions = [];
                $data = $request->request->all();
                foreach ($questions as $question){

                    $tableauDeQuestions[$question->getId()] = $question;
                }

                foreach ($data as $reponse  => $value){
                    // Verif checkbox
                    if(str_starts_with($reponse,"question_")){
                        $tabReponses = [];
                        $checkboxRes = explode('_', $reponse);

                        $questionId = $checkboxRes[1];
                        $reponseId = $checkboxRes[3];
                        $current_question = $tableauDeQuestions[$questionId];

                        // Array indexed by ID
                        foreach ($current_question->getReponses() as $rep){
                            $tabReponses[$rep->getId()] = $rep;
                        }
                        if(in_array($reponseId,$tabReponses)){
                            if($tabReponses[$reponseId]->getIsCorrect() == true){
                                $user->setScore($user->getScore()+$current_question->getValue());
                                $em->persist($user);
                                $em->flush();
                            }
                        }
                    }
                    // Verif radio
                    if(str_starts_with($reponse,"response_radio_")){
                        $tabReponses = [];
                        $radioRes = explode('_',$reponse);
                        $questionId = $radioRes[2];
                        $reponseId = $value;
                        $current_question = $tableauDeQuestions[$questionId];

                        // Array indexed by ID
                        foreach ($current_question->getReponses() as $rep){
                            $tabReponses[$rep->getId()] = $rep;
                        }
                        if($tabReponses[$reponseId]->getIsCorrect() == true){
                            $user->setScore($user->getScore()+$current_question->getValue());
                            $em->persist($user);
                            $em->flush();
                        }
                    }
                }

                $this->addFlash('success',"Vous avez terminé le qcm ! ");
                $this->render('main/index.html.twig');
            }

        return $this->render('qcm/main_qcm.html.twig', ['questions' => $questions]);
    }
}
