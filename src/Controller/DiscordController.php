<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


final class DiscordController extends AbstractController
{
    public function __construct(
        private readonly DiscordApiService $discordApiService,
        private readonly Security          $security,
        private string $myDiscordClientId,
        private string $herDiscordClientId
    )
    {
    }

    #[Route('/discord/connect', name: 'oauth_discord', methods: ['POST'])]
    public function connect(Request $request): Response
    {
        $token = $request->request->get('token');

        if ($this->isCsrfTokenValid('discord-auth', $token)) {
            $request->getSession()->set('discord-auth', true);
            $scope = ['identify', 'email'];
            return $this->redirect($this->discordApiService->getAuthorizationUrl($scope));
        }

        return $this->redirectToRoute('index');
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws ExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/discord/auth', name: 'oauth_discord_auth')]
    public function auth(Request $request, UserRepository $userRepo): Response
    {
        if (!$request->get('accessToken')) {
            return new Response("Bad Request", 400);
        }
        $accessToken = $request->get('accessToken');
        // TODO check si tout profile est ok sinon form pour
        $discordUser = $this->discordApiService->fetchUser($accessToken);

        $user = $userRepo->find($discordUser->id);
        if ($user) {
            return $this->redirectToRoute('index');
        }

        $user = $userRepo->findOneBy(['accessToken' => $accessToken]);
        if (!$user) {
            return new Response("Bad Request", 400);
        }

        return $this->redirectToRoute('index');
    }

    #[Route('/discord/check', name: 'oauth_discord_check')]
    public function check(EntityManagerInterface $em, Request $request, UserRepository $userRepo): Response
    {
        $accessToken = $request->get('access_token');

        if (!$accessToken) {
            return $this->render('discord/check.html.twig');
        }

        $discordUser = $this->discordApiService->fetchUser($accessToken);

        if (!isset($discordUser->id)) {
            return new Response("Bad Request", 400);
        }
        if(!$discordUser->id == $this->herDiscordClientId && !$discordUser->id == $this->myDiscordClientId) {
            return new Response("Access denied", 403);
        }

        $user = $userRepo->find($discordUser->id);

        if (!$user) {
            $user = new User();
        }
        $user->setId($discordUser->id);
        $user->setAccessToken($accessToken);
        $user->setPseudo($discordUser->username);
        $user->setEmail($discordUser->email);
        $user->setAvatar($discordUser->avatar);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('oauth_discord_auth', [
            'accessToken' => $accessToken
        ]);
    }

}

