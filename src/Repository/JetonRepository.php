<?php

namespace App\Repository;

use App\Entity\Jeton;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class JetonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jeton::class);
    }

    /**
     * Insère un nouvel enregistrement dans la table jeton_inscription.
     */
    public function insertJeton(Jeton $jeton): void
    {
        $this->_em->persist($jeton);
        $this->_em->flush();
    }

    /**
     * Supprime un jeton après validation.
     */
    public function remove(int $id): void
    {
        $jeton = $this->find($id);

        if ($jeton) {
            $this->_em->remove($jeton);
            $this->_em->flush();
        }
    }

  
}
