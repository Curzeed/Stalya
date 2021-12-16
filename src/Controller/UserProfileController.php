<?php

namespace App\Controller;

use App\Form\ModifyPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
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

    /**
     * @Route("/profile/link", name="user_profile_discord_link")
     *
     */
    public function linkDiscord(Request $request, EntityManagerInterface $em) : Response{
        $provider = new \Wohali\OAuth2\Client\Provider\Discord([
            'clientId' => '920981831384440843',
            'clientSecret' => 'ablnGro0-LICBWQX67JpVtb3iHVGOHMv',
            'redirectUri' => 'http://localhost:8000/profile/link'
        ]);
        $authUrl = $provider->getAuthorizationUrl();
        if(isset($_GET['code'])){
            $code = $_GET['code'];

            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);
                $user = $provider->getResourceOwner($token);
                //dd( "https://cdn.discordapp.com/avatars/".$user->getId()."/".$user->getAvatarHash().'.gif');
                $sessionUser = $this->getUser();

                $sessionUser->setDiscordId($user->getId());
                $em->flush();

            } catch (IdentityProviderException $e) {
                dd($e->getMessage());
            }

        }

        return $this->redirect($authUrl);
    }
}
