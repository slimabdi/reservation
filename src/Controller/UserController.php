<?php

namespace App\Controller;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class UserController extends AbstractController
{
    #[Route('/', name: 'app_user', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }
    #[Route('/users', name: 'get_users', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepository->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ];
        }

        return $this->json($data);
    }
    #[Route('/api/user/create', name: 'api_create_user', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $password = $data['password'] ?? null;
        $email = $data['email'] ?? null;

        if (!$name || !$password || !$email) {
            return new JsonResponse(['error' => 'Invalid data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Check if the user with the provided email already exists
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existingUser) {
            return new JsonResponse(['error' => 'User with the provided email already exists'], JsonResponse::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setName($name);
        $user->setPassword($password);
        $user->setEmail($email);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User created successfully', 'id' => $user->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Check out this great product: '.$user->getName());

        // or render a template
        // in the template, print things with {{ product.name }}
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }

    #[Route('/api/user/login', name: 'api_user_login', methods: ['POST'])]
    public function loginUser(Request $request, AuthenticationUtils $authenticationUtils, JWTEncoderInterface $jwtEncoder, EntityManagerInterface $entityManager, AuthenticationManagerInterface $authenticationManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $name = $data['username'] ?? null;

    // You can use AuthenticationException instead of BadCredentialsException
    if (!$name) {
        throw new AuthenticationException('Username not provided');
    }

    // Use the AuthenticationManager service to manually authenticate the user
    $token = new UsernamePasswordToken($name, null, 'main', ['ROLE_USER']);
    try {
        $authenticatedToken = $authenticationManager->authenticate($token);
    } catch (AuthenticationException $e) {
        // Handle authentication failure
        return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    // If authentication is successful, generate a JWT token
    $token = $jwtEncoder->encode(['username' => $name]);

    // Return the token as a JSON response
    return new JsonResponse(['token' => $token], JsonResponse::HTTP_OK);
}
}
