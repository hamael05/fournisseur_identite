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

    public  function checkLogin(Utilisateur $utilisateur, $plainPassword ): bool
    {
       if(HasherUtil::verifyPassword($plainPassword,$utilisateur->getMdp()))
       {
            return true;
       }
       else{
        return false;
       }

    }
}
