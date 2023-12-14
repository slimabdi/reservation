<?php

namespace App\Controller;
namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ReservationController extends AbstractController
{
    #[Route('/api/reservation/create/{restaurantId}', name: 'api_create_reservation', methods: ['POST'])]
    public function makeReservation(Request $request, EntityManagerInterface $entityManager, int $restaurantId): JsonResponse
    {
        $restaurant = $entityManager->getRepository(Restaurant::class)->find($restaurantId);

        if (!$restaurant) {
            return new JsonResponse(['error' => 'Restaurant not found for id ' . $restaurantId], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $reservationDate = $data['reservation_date'] ?? null;
        $numberOfPeople = $data['number_of_people'] ?? null;
        $customerName = $data['customer_name'] ?? null;

        if (!$reservationDate || !$numberOfPeople || !$customerName) {
            return new JsonResponse(['error' => 'Invalid data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Fetch the User entity based on the provided username
        $user = $entityManager->getRepository(User::class)->findOneBy(['name' => $customerName]);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found for username ' . $customerName], JsonResponse::HTTP_NOT_FOUND);
        }

        $reservation = new Reservation();
        $reservation->setCustomerName($user);
        $reservation->setReservationDate($reservationDate);
        $reservation->setNumberOfPeople($numberOfPeople);
        $reservation->setRestaurant($restaurant);

        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reservation created successfully', 'id' => $reservation->getId()], JsonResponse::HTTP_CREATED);
    }
}
