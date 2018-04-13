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
use Symfony\Component\HttpKernel\KernelInterface;
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
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(UserRepository $userRepository, UrlGeneratorInterface $urlGenerator, KernelInterface $kernel)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->kernel = $kernel;
    }

    private function createSteamAuthInstance(Request $request)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $returnTo = 'steam_authenticate.html';

        if($this->kernel->getEnvironment() === 'dev')
        {
            // probably should be configurable
            // even when the env is not dev
            $baseUrl = 'http://localhost:3000/';
        }

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

        if($steamid === false)
        {
            return new JsonResponse([
                'message' => 'Failed to authenticate with Steam'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->findBySteamid($steamid);

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