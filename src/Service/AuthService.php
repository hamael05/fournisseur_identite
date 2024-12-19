<?php
namespace App\Service;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Util\HasherUtil;



class AuthService 
{
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(
        UtilisateurRepository $utilisateurRepository,
    ) {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function checkLogin(string $mail, string $password): bool
    {
        // Recherche de l'utilisateur par son email
        $utilisateur = $this->utilisateurRepository->findOneBy(['mail' => $mail]);

        if (!$utilisateur) {
            return false;
        }

       if(HasherUtil::verifyPassword($password,$utilisateur->getMdp()))
       {
            return true;
       }
       else{
        return false;
       }

    }
}
