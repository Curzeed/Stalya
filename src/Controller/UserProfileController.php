<?php

namespace App\Controller;

use App\Form\ModifyPasswordType;
use App\Services\ServicesDiscord;
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
        }if(isset($_GET['code'])) {
            $code = $_GET['code'];
            $provider = new \Wohali\OAuth2\Client\Provider\Discord([
                'clientId' => '920981831384440843',
                'clientSecret' => 'ablnGro0-LICBWQX67JpVtb3iHVGOHMv',
                'redirectUri' => 'http://localhost:8000/profile'
            ]);
            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);
                $user = $provider->getResourceOwner($token);
                //dd( "https://cdn.discordapp.com/avatars/".$user->getId()."/".$user->getAvatarHash().'.gif');

                $sessionUser = $this->getUser();
                $sessionUser->settokenDiscord($token);
                $sessionUser->setDiscordId($user->getId());
                $em->flush();
                $this->addFlash('success', 'Compte lié à discord avec succès !');
                $this->redirectToRoute('user_profile');
            } catch (IdentityProviderException $e) {
                dump($e->getMessage());
            }
        }

        return $this->renderForm('user_profile/index.html.twig',['formModif' => $formModif]);
    }

    /**
     * @Route("/profile/link", name="user_profile_discord_link")
     *
     */
    public function linkDiscord(EntityManagerInterface $em) : Response{
        $provider = new \Wohali\OAuth2\Client\Provider\Discord([
            'clientId' => '920981831384440843',
            'clientSecret' => 'ablnGro0-LICBWQX67JpVtb3iHVGOHMv',
            'redirectUri' => 'http://localhost:8000/profile'
        ]);
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['identify']
        ]);
        // && $this->getUser()->getDiscordId() == null && $this->getUser()->gettokenDiscord() == null

        return $this->redirect($authUrl);
    }

    /**
     * @Route ("/profile/discord/revoke" , name="profile_revoke_discord")
     */
    public function revokeTokenDiscord( EntityManagerInterface $em, ServicesDiscord $sd) :Response{
        if($this->getUser()->getDiscordId() != null && $this->getUser()->gettokenDiscord() != null){
            $revokeURL = 'https://discordapp.com/api/oauth2/token/revoke';
            $sd->logout($revokeURL,array(
                'token' => $this->getUser()->getTokenDiscord(),
                'token_type_hint' => 'access_token',
                'clientId' => '920981831384440843',
                'client_secret' => 'ablnGro0-LICBWQX67JpVtb3iHVGOHMv'
            ));
            $this->getUser()->setDiscordId(null);
            $this->getUser()->settokenDiscord(null);
            $em->flush();
            $this->addFlash('success','Compte discord délié avec succès');

        }
        return $this->redirectToRoute('user_profile');
    }
}
