<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "jeton_inscription")]
class JetonInscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $mail;

    #[ORM\Column(type: "text")]
    private string $mdp;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateNaissance;

    #[ORM\OneToOne(targetEntity: Jeton::class)]
    #[ORM\JoinColumn(name: "id_jeton", referencedColumnName: "id", unique: true)]
    private Jeton $jeton;


    public function __construct(
        string $mail,
        string $mdp,
        string $nom,
        \DateTimeInterface $dateNaissance,
        Jeton $jeton
    ) {
        $this->mail = $mail;
        $this->mdp = $mdp;
        $this->nom = $nom;
        $this->dateNaissance = $dateNaissance;
        $this->jeton = $jeton;
    }

    // Getters and Setters
    public function getId(): int { return $this->id; }
    public function getMail(): string { return $this->mail; }
    public function setMail(string $mail): self { $this->mail = $mail; return $this; }

    public function getMdp(): string { return $this->mdp; }
    public function setMdp(string $mdp): self { $this->mdp = $mdp; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getDateNaissance(): \DateTimeInterface { return $this->dateNaissance; }
    public function setDateNaissance(\DateTimeInterface $dateNaissance): self { $this->dateNaissance = $dateNaissance; return $this; }

    public function getJeton(): Jeton { return $this->jeton; }
    public function setJeton(Jeton $jeton): self { $this->jeton = $jeton; return $this; }

    // méthodes
    public function isExpired(): bool
    {
        return $this->getJeton()->getExpirationUtil()->getDateExpiration() < new \DateTime();
    }

}
