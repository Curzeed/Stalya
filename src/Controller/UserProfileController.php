<?php

namespace App\Controller;

use App\Form\ModifyPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProfileController extends AbstractController
{

    /**
     * @Route("/profile", name="user_profile")
     */
    public function modifyPassword(Request $request, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $em) : Response {

        $user = $this->getUser();
        $formModif = $this->createForm(ModifyPasswordType::class);

        $formModif->handleRequest($request);

        if($formModif->isSubmitted() && $formModif->isValid()){
            $newPassword = $formModif->getData()['newPassword'];
            $oldPassword = $formModif->getData()['password'];

            if($passwordEncoder->isPasswordValid($user,$oldPassword)){
                $hashPassword = $passwordEncoder->hashPassword($user,$newPassword);
                $user->setPassword($hashPassword);
                $em->flush();
                $this->addFlash('success',"Votre mot de passe a été changé avec succès");
                return $this->redirectToRoute('user_profile');
            }else{
                $this->addFlash('error', 'Votre ancien mot de passe ne correspond à celui que vous avez rentré.');
            }
        }
        return $this->renderForm('user_profile/index.html.twig',['formModif' => $formModif]);
    }
}
