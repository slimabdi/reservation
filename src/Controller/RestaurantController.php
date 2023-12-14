<?php

namespace App\Controller;
use App\Entity\Restaurant;
use App\Entity\Menu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantController extends AbstractController
{
    #[Route('/test', name: 'app_restaurant')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!'
        ]);
    }

    #[Route('/api/restaurants', name: 'get_all_restaurants', methods: ['GET'])]
    public function getAllRestaurants(): JsonResponse
    {
        $restaurants = $this->getDoctrine()->getRepository(Restaurant::class)->findAll();

        $data = [];

        foreach ($restaurants as $restaurant) {
            $data[] = [
                'id' => $restaurant->getId(),
                'name' => $restaurant->getName(),
                'description' => $restaurant->getDescription(),
                // Add other fields as needed
            ];
        }

        return $this->json($data);
    }

    #[Route('/restaurant', name: 'api_restaurant_create', methods: ['POST'])]
    public function createRestaurant(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;

        if (!$name || !$description) {
            return new JsonResponse(['error' => 'Invalid data provided'], Response::HTTP_BAD_REQUEST);
        }

        $restaurant = new Restaurant();
        $restaurant->setName($name);
        $restaurant->setDescription($description);

        $entityManager->persist($restaurant);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Restaurant created successfully', 'id' => $restaurant->getId()], Response::HTTP_CREATED);
    }

    #[Route('/restaurant/{id}', name: 'api_restaurant_id', methods: ['GET'])]
    public function getRestaurant(EntityManagerInterface $entityManager, int $id): Response
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);
    
        if (!$restaurant) {
            throw $this->createNotFoundException(
                'No restaurant found for id '.$id
            );
        }
        return $this->json(['id' => $restaurant->getId(), 'name' => $restaurant->getName()]);
    }

    #[Route('/api/restaurant/{id}', name: 'api_restaurant_update', methods: ['PUT'])]
    public function updateRestaurant(int $id, Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);

        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found for id ' . $id);
        }

        $data = json_decode($request->getContent(), true);

        // Update restaurant fields
        $restaurant->setName($data['name'] ?? $restaurant->getName());
        $restaurant->setDescription($data['description'] ?? $restaurant->getDescription());

        $entityManager->flush();

        return $this->json([
            'message' => 'Restaurant updated successfully',
            // Add other fields as needed
        ]);
    }

    #[Route('/api/restaurant/{id}', name: 'api_restaurant_delete', methods: ['DELETE'])]
    public function deleteRestaurant(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($id);

        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant not found for id ' . $id);
        }

        $entityManager->remove($restaurant);
        $entityManager->flush();

        return $this->json([
            'message' => 'Restaurant deleted successfully',
        ]);
    }

    #[Route('/api/restaurant/search', name: 'api_search_restaurants', methods: ['GET'])]
    public function searchRestaurants(Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
    
        $query = $request->query->get('query');
    
        if ($query === null) {
            return new JsonResponse(['error' => 'Missing query parameter'], 400);
        }
    
        try {
            $restaurants = $entityManager->getRepository(Restaurant::class)->searchByNameOrDescription($query);
    
            $formattedRestaurants = [];
            foreach ($restaurants as $restaurant) {
                $formattedRestaurants[] = $this->formatRestaurant($restaurant);
            }
    
            return $this->json($formattedRestaurants);
        } catch (\Exception $e) {
            // Log or handle the exception
            return new JsonResponse(['error' => 'An error occurred during the search.'], 500);
        }
    }
    
    
    
    private function formatRestaurant(Restaurant $restaurant): array
    {
        return [
            'id' => $restaurant->getId(),
            'name' => $restaurant->getName(),
            'description' => $restaurant->getDescription(),
        ];
    }
}
