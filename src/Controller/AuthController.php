<?php

namespace App\Controller;


use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use SteamAuth\SteamOpenId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController
{
    /**
     * @var ProductRepository
     */
    private $userRepository;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(UserRepository $userRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
    }

    private function createSteamAuthInstance(Request $request)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $returnTo = $this->urlGenerator->generate('auth_steam_verify');

        $steamAuth = new SteamOpenId([
            'realm' => $baseUrl,
            'return_to' => $baseUrl . $returnTo,
        ]);

        return $steamAuth;
    }

    public function steamAuthRedirect(Request $request)
    {
        $steamAuth = $this->createSteamAuthInstance($request);
        return new RedirectResponse($steamAuth->getRedirectUrl());
    }

    public function steamAuthVerify(Request $request)
    {
        $steamAuth = $this->createSteamAuthInstance($request);
        $steamid = $steamAuth->verifyAssertion($request->query->all());
        $user = $this->userRepository->findBySteamid($steamid);

        if($steamid === false)
        {
            throw new \Exception('Failed to authenticated with Steam');
        }

        if(!$user)
        {
            $user = new User($steamid);
            $this->userRepository->add($user);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'steamid' => $user->getSteamid(),
            'name' => $user->getName()
        ]);
    }

    public function index()
    {
    }
}