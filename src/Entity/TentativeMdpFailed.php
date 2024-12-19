<?php

namespace App\Entity;

use App\Repository\TentativeMdpFailedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TentativeMdpFailedRepository::class)]
#[ORM\Table(name: "tentative_mdp_failed")]
class TentativeMdpFailed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(name:"nb_tentative_restant",type: "integer")]
    private int $nbTentativeRestant; 

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Utilisateur $utilisateur;
    private static $defaultNbTentativeRestant =3;

    public function __construct(Utilisateur $user)
    {
            $this->utilisateur = $user;
            $this->nbTentativeRestant = self::$defaultNbTentativeRestant-1;
    }

    // Getters and Setters
    public function getId(): int { return $this->id; }
    public function getNbTentativeRestant(): int { return $this->nbTentativeRestant; }
    public function setNbTentativeRestant(int $nb): void { 
        if($nb == -1){
            $nb = self::$defaultNbTentativeRestant;
        }
        $this->nbTentativeRestant = $nb;  
    }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $utilisateur): void { $this->utilisateur = $utilisateur; }

    public function moinsUnTentativeRestant(): void { $this->setNbTentativeRestant($this->nbTentativeRestant - 1); }


}