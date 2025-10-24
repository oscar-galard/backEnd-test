<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/api/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        
        return $this->json([
            'message' => 'Hello ' . $user->getUserIdentifier() . ', this is your dashboard!',
            'user' => [
                'name' => $user->getUserIdentifier()
            ]
        ]);
    }
}