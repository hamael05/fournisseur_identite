<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: "tentative_mdp_failed")]
class TentativeMdpFailed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(name:"nb_tentative_restant",type: "integer")]
    private int $nbTentativeRestant; //--

    #[ORM\Column(name:"is_locked",type: "boolean")]
    private bool $isLocked;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Utilisateur $utilisateur;
    private static $defaultNbTentativeRestant =3;

    public function __construct(Utilisateur $user)
    {
            $this->utilisateur = $user;
            $this->compteurTentatice = self::$defaultNbTentativeRestant;
            $this->isLocked = false;
    }

    // Getters and Setters
    public function getId(): int { return $this->id; }
    public function getNbTentativeRestant(): int { return $this->nbTentativeRestant; }
    public function setNbTentativeRestant(int $nbTentativeRestant): void { $this->nbTentativeRestant = $nbTentativeRestant; }
    public function getIsLocked(): bool { return $this->isLocked; }
    public function setIsLocked(bool $isLocked): void { $this->isLocked = $isLocked; }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $utilisateur): void { $this->utilisateur = $utilisateur; }


    

}