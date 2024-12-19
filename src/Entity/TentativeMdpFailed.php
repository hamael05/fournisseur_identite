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

    #[ORM\Column(type: "integer")]
    private int $compteurTentative;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateDerniereTentative;

    #[ORM\Column(type: "boolean")]
    private bool $isLocked;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $unlockTime;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Utilisateur $utilisateur;

    // Getters and Setters
    public function getId(): int { return $this->id; }
    public function getCompteurTentative(): int { return $this->compteurTentative; }
    public function setCompteurTentative(int $compteurTentative): void { $this->compteurTentative = $compteurTentative; }
    public function getDateDerniereTentative(): \DateTimeInterface { return $this->dateDerniereTentative; }
    public function setDateDerniereTentative(\DateTimeInterface $dateDerniereTentative): void { $this->dateDerniereTentative = $dateDerniereTentative; }
    public function getIsLocked(): bool { return $this->isLocked; }
    public function setIsLocked(bool $isLocked): void { $this->isLocked = $isLocked; }
    public function getUnlockTime(): \DateTimeInterface { return $this->unlockTime; }
    public function setUnlockTime(\DateTimeInterface $unlockTime): void { $this->unlockTime = $unlockTime; }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $utilisateur): void { $this->utilisateur = $utilisateur; }
}