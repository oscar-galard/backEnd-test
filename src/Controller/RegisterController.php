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

final class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'app_api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager  // ← MAKE SURE THIS LINE EXISTS!
    ): JsonResponse
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        // Validate required fields
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json([
                'error' => 'Email and password are required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $password = $data['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid email format.'], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exists
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return $this->json(['error' => 'User with this email already exists.'], Response::HTTP_CONFLICT);
        }

        // Create new user
        $user = new User();
        $user->setEmail($email);

        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        // ✅ NOW $jwtManager IS DEFINED AND CAN BE USED
        $token = $jwtManager->create($user);

        return $this->json([
            'message' => 'User registered successfully!',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ], Response::HTTP_CREATED);
    }
}
