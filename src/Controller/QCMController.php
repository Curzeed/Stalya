<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
     * @IsGranted("ROLE_USER")
     */
    public function main(QuestionRepository $qr, Request $request, EntityManagerInterface $em) : Response{
        $user = $this->getUser();
        $atmDate = new DateTime('now');
        if ($user->getLastAttempt() != null && $user->getLastAttempt()->modify('+24 hour') > $atmDate) {
            return $this->redirect('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        }

        $questions = $qr->findAll();
            if($request->getMethod() == 'POST'){
                $tableauDeQuestions = [];
                $user->setScore(0);
                $data = $request->request->all();
                $emptyresponses = 0;
                foreach ($questions as $question){
                    if ($question->countCorrect() <= 1 ) {
                        $answered = array_key_exists('response_radio_'.$question->getId(), $data);
                    } else {
                        $answered = false;
                        foreach ($question->getReponses() as $response) {
                            $answered = array_key_exists('question_'.$question->getId().'_response_'.$response->getId(), $data);
                            if ($answered) break;
                        }
                    }
                    if ($answered === false) {
                        $emptyresponses++;
                    }
                    $tableauDeQuestions[$question->getId()] = $question;
                }
                if ($emptyresponses > 0 && !array_key_exists('timeout',$data)) {
                    $this->addFlash('warning',"Vus n'avez pas répondu à toutes les questions");
                    return $this->render('qcm/main_qcm.html.twig', ['questions' => $tableauDeQuestions, 'answers' => $data]);
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
                                $user->setScore($user->getScore()+$current_question->getValue()/$current_question->countCorrect());
                            } else {
                                $user->setScore($user->getScore()-$current_question->getValue()/$current_question->countCorrect());
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
                        } else {
                            $user->setScore($user->getScore()-$current_question->getValue());
                        }
                    }
                }
                $date = new DateTime('now');
                $user->setLastAttempt($date);
                $em->persist($user);
                $em->flush();
                
                $this->addFlash('success',"Vous avez terminé le qcm ! ");
                return $this->redirectToRoute('main');
            }

        return $this->render('qcm/main_qcm.html.twig', ['questions' => $questions, 'answers' => []]);
    }
}
