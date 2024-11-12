<?php

namespace App\Entity;

use App\Repository\CreditCardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CreditCardRepository::class)]
class CreditCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    #[Assert\CardScheme(
        schemes: [Assert\CardScheme::VISA, Assert\CardScheme::MASTERCARD],
        message: 'Your credit card number is invalid.',
    )]
    private ?string $number = null;

    #[ORM\Column(length: 5)]
    #[Assert\Regex(
        pattern:"/^(0[1-9]|1[0-2])\/\d{2}$/",
        message:"Expiration date must be in the format MM/YY"
    )]
    private ?string $expirationDate = null;

    #[ORM\Column(length: 3)]
    #[Assert\Regex(
        pattern:"/^\d{3}$/",
        message:"Le CVV doit être un nombre entre 000 et 999."
    )]
    private ?string $cvv = null;
    

    #[ORM\ManyToOne(inversedBy: 'creditCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ofUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(string $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    public function setCvv(string $cvv): static
    {
        $this->cvv = $cvv;
        return $this;
    }

    public function getOfUser(): ?User
    {
        return $this->ofUser;
    }

    public function setOfUser(?User $ofUser): static
    {
        $this->ofUser = $ofUser;

        return $this;
    }
}
