<?php

namespace App\Controller;

use App\Form\ModifyPasswordType;
use App\Repository\SessionRepository;
use App\Services\ServicesDiscord;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\Translation\t;

class UserProfileController extends AbstractController
{

    /**
     * @Route("/profile", name="user_profile")
     */
    public function modifyPassword(Request $request, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $em, SessionInterface $session) : Response {

        if(isset($_GET['code'])) {
            $session->set('discord_code', $_GET['code']);
            return $this->redirectToRoute('user_profile');
        }
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

        if($session->get('discord_code')) {
            $code = $session->get('discord_code');
            $dsOauthProvider = new ServicesDiscord();
            $session->remove('discord_code');
            try {
                $token = $dsOauthProvider->tryGetToken($code);
                $userInfos = $dsOauthProvider->getUserInfos($token['access_token']);
                $user->settokenDiscord($token['access_token']);
                $user->setUsernameDiscord($userInfos['username'].'#'.$userInfos['discriminator']);
                $user->setimgDiscord($userInfos['avatar']);
                $user->setDiscordId($userInfos['id']);
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
     */
    public function linkDiscord(EntityManagerInterface $em) : Response{
        $provider = new \Wohali\OAuth2\Client\Provider\Discord([
            'clientId' => $_SERVER['DISCORD_CLIENT_ID'],
            'clientSecret' => $_SERVER['DISCORD_CLIENT_SECRET'],
            'redirectUri' => $_SERVER['DISCORD_REDIRECT_URI'] // UrlGeneratorInterface::ABSOLUTE_URL
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
    public function revokeTokenDiscord( EntityManagerInterface $em, ServicesDiscord $sd, SessionInterface $session) :Response{
        if ($this->getUser()->getSession() !== null) {
            $this->addFlash('warning', 'Vous êtes inscrit a une session');
            return $this->redirectToRoute('user_profile');
        }

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
            $this->getUser()->setImgDiscord(null);
            $this->getUser()->setUsernameDiscord(null);
            $session->remove('discord_code');
            $em->flush();
            $this->addFlash('success','Compte discord délié avec succès');

        }
        return $this->redirectToRoute('user_profile');
    }
}
