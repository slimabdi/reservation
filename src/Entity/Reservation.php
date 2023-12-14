<?php


namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customerName = null;

    #[ORM\Column(length: 255)]
    private ?string $reservationDate = null;

    #[ORM\Column(length: 255)]
    private ?string $numberOfPeople = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Restaurant $restaurant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?User
    {
        return $this->customerName;
    }

    public function setCustomerName(?User $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getReservationDate(): ?string
    {
        return $this->reservationDate;
    }

    public function setReservationDate(string $reservationDate): self
    {
        $this->reservationDate = $reservationDate;

        return $this;
    }

    public function getNumberOfPeople(): ?string
    {
        return $this->numberOfPeople;
    }

    public function setNumberOfPeople(string $numberOfPeople): self
    {
        $this->numberOfPeople = $numberOfPeople;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }
}
