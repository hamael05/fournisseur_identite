<?php

namespace App\Entity;

use App\Util\TokenGeneratorUtil;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "jeton")]
class Jeton
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "text")]
    private string $jeton;

    #[ORM\Embedded(class: ExpirationUtil::class)]
    private ExpirationUtil $expirationUtil;

    
    private static float $defaultDureeJeton = 1.5;

    public function __construct(float $duree)
    {

        if($duree == -1){
            $duree = self::$defaultDureeJeton;
        }
        $this->expirationUtil = (new ExpirationUtil($duree));
        
        $this->jeton = TokenGeneratorUtil::generateToken();
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getJeton(): string
    {
        return $this->jeton;
    }

    public function setJeton(string $jeton): self
    {
        $this->jeton = $jeton;
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

    //méthodes
      /**
     * Vérifie si la date d'expiration du jeton est dépassée.
     */
    public function isExpired(Jeton $jeton): bool
    {
        return $jeton->getExpirationUtil()->getDateExpiration() < new \DateTime();
    }

}
