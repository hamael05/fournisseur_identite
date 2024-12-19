<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "utilisateur")]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $mail;

    #[ORM\Column(type: "string", length: 255)]
    private string $mdp;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateNaissance;


    public function __construct(string $mail, string $mdp, string $nom, \DateTimeInterface $dateNaissance)
    {
        $this->mail = $mail;
        $this->mdp = $mdp;
        $this->nom = $nom;
        $this->dateNaissance = $dateNaissance;
    }

    // Getters and Setters
    public function getId(): int { return $this->id; }
    public function getMail(): string { return $this->mail; }
    public function setMail(string $mail): void { $this->mail = $mail; }
    public function getMdp(): string { return $this->mdp; }
    public function setMdp(string $mdp): void { $this->mdp = $mdp; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function getDateNaissance(): \DateTimeInterface { return $this->dateNaissance; }
    public function setDateNaissance(\DateTimeInterface $dateNaissance): void { $this->dateNaissance = $dateNaissance; }
}