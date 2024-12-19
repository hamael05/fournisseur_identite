<?php

namespace App\Entity;

use App\Entity\ExpirationUtil;
use App\Util\PinGeneratorUtil;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PinRepository")
 */
#[ORM\Entity]
#[ORM\Table(name: "pin")]
class Pin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:"id",type: "integer")]
    private int $id;

    #[ORM\Column(name:"pin",type: "string", length: 6)]
    private string $pin;

    #[ORM\Embedded(class: ExpirationUtil::class)]
    private ExpirationUtil $expirationUtil;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Utilisateur $utilisateur;

    private static float $defaultDureePin = 10;

    public function __construct(int $duree , Utilisateur $user)
    {
            if($duree == -1){
                $duree = self::$defaultDureePin;
            }
            $this->expirationUtil = (new ExpirationUtil($duree));

            $this->pin = PinGeneratorUtil::generatePin();

            $this->utilisateur = $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPin(): string
    {
        return $this->pin;
    }

    public function setPin(int $pin): self
    {
        $this->pin = $pin;
        return $this;
    }

    public function getExpirationUtil(): ExpirationUtil
    {
        return $this->expirationUtil;
    }

    public function setExpirationUtil(ExpirationUtil $expirationUtil): self
    {
        $this->expirationUtil = $expirationUtil;
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
}
