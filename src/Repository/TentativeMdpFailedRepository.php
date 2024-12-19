<?php 
namespace App\Repository;

use App\Entity\TentativeMdpFailed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TentativeMdpFailedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, TentativeMdpFailed::class);
    }

}