<?php

namespace App\Controller;

use App\Entity\History;
use App\Repository\HistoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\ReponsesRepository;
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
     * @Route ("/main_qcm", name="qcm_main")
     * @IsGranted("ROLE_USER")
     */
    public function main(QuestionRepository $qr, Request $request, EntityManagerInterface $em, ReponsesRepository $rr, HistoryRepository $hr) : Response{
        $user = $this->getUser();
        $atmDate = new DateTime('now');
        $questions = $qr->findAll();

        if ($user->canParticipate() || $user->getnbTry() >= 3) {
            $user->setnbTry(0);
            $user->setLastAttempt($atmDate);
            $em->flush();
            return $this->redirect('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        }
        $user->setnbTry($user->getnbTry()+1);
        $em->flush();
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
                    return $this->render('qcm/main_qcm.html.twig', ['questions' => $tableauDeQuestions, 'answers' => $data, 'timer' => $request->get('timer')]);
                }
                $hr->deleteByUser($this->getUser());
                foreach ($data as $reponse  => $value){
                    // Verif checkbox
                    if(str_starts_with($reponse,"question_")){
                        $history = new History();
                        $history->setUser($this->getUser());
                        $tabReponses = [];
                        $checkboxRes = explode('_', $reponse);

                        $questionId = $checkboxRes[1];
                        $reponseId = $checkboxRes[3];
                        $current_question = $tableauDeQuestions[$questionId];

                        $response = $rr->find($reponseId);
                        $history->setResponse($response);

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
                        $em->persist($history);
                    }
                    // Verif radio
                    if(str_starts_with($reponse,"response_radio_")){
                        $history = new History();
                        $history->setUser($this->getUser());
                        $tabReponses = [];
                        $radioRes = explode('_',$reponse);
                        $questionId = $radioRes[2];
                        $reponseId = $value;
                        $current_question = $tableauDeQuestions[$questionId];

                        $response = $rr->find($reponseId);
                        $history->setResponse($response);

                        // Array indexed by ID
                        foreach ($current_question->getReponses() as $rep){
                            $tabReponses[$rep->getId()] = $rep;
                        }
                        if($tabReponses[$reponseId]->getIsCorrect() == true){
                            $user->setScore($user->getScore()+$current_question->getValue());
                        } else {
                            $user->setScore($user->getScore()-$current_question->getValue());
                        }
                        $em->persist($history);
                    }
                }


                $user->setLastAttempt($atmDate);
                $user->setnbTry(0);
                $em->persist($user);
                $em->flush();
                
                $this->addFlash('success',"Vous avez terminé le qcm ! ");
                return $this->redirectToRoute('main');
            }

        return $this->render('qcm/main_qcm.html.twig', ['questions' => $questions, 'answers' => []]);
    }


}
