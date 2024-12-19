<?php

namespace App\Repository;

use App\Entity\JetonInscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class JetonInscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JetonInscription::class);
    }

    /**
     * Insère un nouvel enregistrement dans la table jeton_inscription.
     */
    public function insertJetonInscription(JetonInscription $jetonInscription): void
    {
        $this->_em->persist($jetonInscription);
        $this->_em->flush();
    }

    /**
     * Recherche un jeton d'inscription par son token.
     */
    public function findByToken(string $token): ?JetonInscription
    {
        return $this->createQueryBuilder('ji')
            ->join('ji.jeton', 'j')
            ->where('j.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Supprime un jeton après validation.
     */
    public function remove(int $id): void
    {
        $jetonInscription = $this->find($id);

        if ($jetonInscription) {
            $this->_em->remove($jetonInscription);
            $this->_em->flush();
        }
    }


}
