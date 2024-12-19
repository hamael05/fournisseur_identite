<?php

namespace App\Entity;

use App\Util\TokenGeneratorUtil;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "jeton_authentificaion")]
class JetonAuthentification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\OneToOne(targetEntity: Jeton::class)]
    #[ORM\JoinColumn(name: "id_jeton", referencedColumnName: "id", unique: true)]
    private Jeton $jeton;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Utilisateur $utilisateur;

    public function __construct(Utilisateur $user, Jeton $jeton)
    {
        $this->jeton = $jeton;
        $this->utilisateur = $user;
        
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getJeton(): Jeton
    {
        return $this->jeton;
    }

    public function setJeton(Jeton $jeton): self
    {
        $this->jeton = $jeton;
        return $this;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function isExpired(): bool
    {
        return $this->getJeton()->getExpirationUtil()->getDateExpiration() < new \DateTime();
    }

}
