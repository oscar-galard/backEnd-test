<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/api/profile', name: 'app_profile', methods: ['GET', 'OPTIONS'])]
    public function index(Request $request, LoggerInterface $logger): JsonResponse
    {
        $logger->info('Profile request received', [
            'method' => $request->getMethod(),
            'headers' => $request->headers->all(),
        ]);

        if ($request->isMethod('OPTIONS')) {
            return new JsonResponse([], 204);
        }

        $user = $this->getUser();

        return $this->json([
            'message' => 'You are authorized',
            'user' => $user->getUserIdentifier(),
        ]);
    }
}
