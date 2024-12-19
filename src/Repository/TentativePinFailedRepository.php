<?php 
namespace App\Repository;

use App\Entity\TentativePinFailed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TentativePinFailedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, TentativePinFailed::class);
    }

}