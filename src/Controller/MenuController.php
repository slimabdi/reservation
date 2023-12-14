<?php

namespace App\Controller;

use Psr\Log\LoggerInterface; // Import the LoggerInterface
use App\Entity\Menu;
use App\Entity\Restaurant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    private LoggerInterface $logger; // Declare the logger property

    // Inject the logger service in the constructor
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/api/restaurant/menu', name: 'api_create_menu', methods: ['POST'])]
    public function createMenu(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $restaurantId = $data['restaurant_id'] ?? null;

        if ($restaurantId === null) {
            return new JsonResponse(['error' => 'Invalid or missing restaurant_id parameter'], 400);
        }

        $restaurant = $entityManager->getRepository(Restaurant::class)->find($restaurantId);

        if (!$restaurant) {
            return new JsonResponse(['error' => 'Restaurant not found for id ' . $restaurantId], 404);
        }


        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;

        if (!$name || !$description) {
            return new JsonResponse(['error' => 'Invalid data provided'], Response::HTTP_BAD_REQUEST);
        }

        $menu = new Menu();
        $menu->setName($name);
        $menu->setDescription($description);
        $menu->setRestaurant($restaurant);

        $entityManager->persist($menu);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Menu created successfully', 'id' => $restaurant->getId()], Response::HTTP_CREATED);
    }

    #[Route('/api/restaurant/{restaurantId}/menu', name: 'api_get_menus_by_restaurant', methods: ['GET'])]
    public function getMenusByRestaurant(int $restaurantId): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Retrieve menus for a specific restaurant
        $menus = $entityManager->getRepository(Menu::class)->findBy(['restaurant' => $restaurantId]);

        // You can customize the response format based on your needs
        $formattedMenus = [];
        foreach ($menus as $menu) {
            $formattedMenus[] = $this->formatMenu($menu);
        }

        return $this->json($formattedMenus);
    }

    private function formatMenu(Menu $menu): array
    {
        return [
            'id' => $menu->getId(),
            'name' => $menu->getName(),
            'description' => $menu->getDescription(),
            // Add other fields as needed
        ];
    }
}
