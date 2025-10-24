<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Entity\User;

final class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json([
                'error' => 'Email and password are required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $password = $data['password'];

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if (!$user) {
            return $this->json([
                'error' => 'Invalid credentials.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'error' => 'Invalid credentials.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);

        return $this->json([
            'message' => 'Login successful!',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ]);
    }
}
