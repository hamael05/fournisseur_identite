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

    public function updateNom(int $id, string $nom): bool
    {
        $utilisateur = $this->find($id);
        if (!$utilisateur) {
            return false;
        }
        $utilisateur->setNom($nom);
        $this->_em->flush();

        return true;
    }

    public function updateMdp(int $id, string $mdp): bool
    {
        $utilisateur = $this->find($id);
        if (!$utilisateur) {
            return false;
        }
        $utilisateur->setMdp($mdp);
        $this->_em->flush();

        return true;
    }

    public function updateDateNaissance(int $id, \DateTimeInterface $dateNaissance): bool
    {
        $utilisateur = $this->find($id);
        if (!$utilisateur) {
            return false;
        }
        $utilisateur->setDateNaissance($dateNaissance);
        $this->_em->flush();

        return true;
    }
}