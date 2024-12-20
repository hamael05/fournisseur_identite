<?php

namespace App\Entity;

use App\Repository\TentativePinFailedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TentativePinFailedRepository::class)]
#[ORM\Table(name: "tentative_pin_failed")]
class TentativePinFailed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(name:"nb_tentative_restant",type: "integer")]
    private int $nbTentativeRestant; 

    #[ORM\ManyToOne(targetEntity: Pin::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Pin $pin;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Utilisateur $utilisateur;
    private static int $defaultNbTentativeRestant =3;

    public function __construct(int $nbTentativeRestant, Pin $pin, Utilisateur $user)
    {
        if($nbTentativeRestant == -1){
            $nbTentativeRestant = self::$defaultNbTentativeRestant;
        }
        $this->pin = $pin;
        $this->utilisateur = $user;
        $this->nbTentativeRestant = $nbTentativeRestant-1;
    }

    // Getters and Setters
    public function getId(): int { return $this->id; }
    public function getNbTentativeRestant(): int { return $this->nbTentativeRestant; }
    public function setNbTentativeRestant(int $nb): void { $this->nbTentativeRestant = $nb;  }
    public function getPin(): Pin { return $this->pin; }
    public function setPin(Pin $pin): void { $this->pin = $pin; }

    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $utilisateur): void { $this->utilisateur = $utilisateur; }

    public function moinsUnTentativeRestant(): void { $this->setNbTentativeRestant($this->nbTentativeRestant - 1); }


}