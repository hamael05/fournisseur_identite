<?php

namespace App\Util;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ExpirationUtil
{
    #[ORM\Column(type: "datetime", name: "date_insertion", nullable: false)]
    private \DateTimeInterface $dateInsertion;

    #[ORM\Column(type: "datetime", name: "date_expiration", nullable: false)]
    private \DateTimeInterface $dateExpiration;

    #[ORM\Column(type: "float", name: "duree", nullable: false)]
    private float $duree; // Durée en heures

    /**
     * Constructeur pour initialiser ExpirationUtil avec une durée en heures.
     *
     * @param float $duree Durée en heures
     */
    public function __construct(float $duree)
    {
        $this->duree = $duree;
        $this->dateInsertion = new \DateTime(); // Date et heure actuelles
        $this->dateExpiration = $this->calculerDateExpiration(); // Calculer la date d'expiration
    }

    public function getDateInsertion(): \DateTimeInterface
    {
        return $this->dateInsertion;
    }

    public function setDateInsertion(\DateTimeInterface $dateInsertion): self
    {
        $this->dateInsertion = $dateInsertion;
        return $this;
    }

    public function getDateExpiration(): \DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(): self
    {
        $this->dateExpiration = $this->calculerDateExpiration();
        return $this;
    }

    /**
     * Calcule la date d'expiration en fonction de la durée en heures.
     *
     * @return \DateTimeInterface
     */
    public function calculerDateExpiration(): \DateTimeInterface
    {
        if ($this->dateInsertion === null) {
            $this->dateInsertion = new \DateTime(); // Date actuelle si non définie
        }

        // Convertir la date d'insertion en timestamp
        $timestampInsertion = $this->dateInsertion->getTimestamp();

        // Ajouter la durée en secondes (duree en heures * 3600 secondes par heure)
        $timestampExpiration = $timestampInsertion + (int)($this->duree * 3600);

        // Créer une nouvelle instance de \DateTimeImmutable à partir du timestamp
        return (new \DateTimeImmutable())->setTimestamp($timestampExpiration);
    }

    public function getDuree(): float
    {
        return $this->duree;
    }

    public function setDuree(float $duree): self
    {
        $this->duree = $duree;
        return $this;
    }
}
