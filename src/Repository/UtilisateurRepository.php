<?php 
namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Utilisateur::class);
    }

    public function save(Utilisateur $utilisateur):void{
        $this->_em->persist($utilisateur);
        $this->_em->flush();
    }
}