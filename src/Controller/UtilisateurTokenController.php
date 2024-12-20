<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Jeton;
use App\Entity\JetonAuthentification;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Util\HasherUtil;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UtilisateurTokenController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository)
    {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }
    

    #[Route('/utilisateurToken/modifier-nom', name: 'modifier_nom', methods: ['POST'])]
    public function modifierNom(Request $request): JsonResponse
    {
        try {
            // Récupérer le token dans le header
            $jeton = $request->headers->get('Authorization');
            if (!$jeton) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Token manquant.'
                ], 401);
            }
    
            // Vérifier le token
            $token = $this->entityManager->getRepository(Jeton::class)->findOneBy(['jeton' => $jeton]);
            $jetonAuthentification = $this->entityManager->getRepository(JetonAuthentification::class)->findOneBy(['jeton' => $token->getId()]);
            

            if (!$jetonAuthentification) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton invalide.'
                ], 401);
            }

            if ($jetonAuthentification->isExpired()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton expiré,veuillez vous reeauthentifier.'
                ], 401);
            }
    
            // Récupérer l'utilisateur associé
            $utilisateur = $jetonAuthentification->getUtilisateur();
    
            $data = json_decode($request->getContent(), true);
    
            if (!isset($data['nom'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Données manquantes.'
                ], 400);
            }
    
            // Modifier le nom de l'utilisateur authentifié
            $utilisateur->setNom($data['nom']);
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Nom modifié avec succès.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    


    #[Route('/utilisateurToken/modifier-mdp', name: 'modifier_mdp', methods: ['POST'])]
    public function modifierMdp(Request $request): JsonResponse
    {
        try {
            // Récupérer le token dans le header
            $jeton = $request->headers->get('Authorization');
            if (!$jeton) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Token manquant.'
                ], 401);
            }
    
            // Vérifier le token
            $token = $this->entityManager->getRepository(Jeton::class)->findOneBy(['jeton' => $jeton]);
            $jetonAuthentification = $this->entityManager->getRepository(JetonAuthentification::class)->findOneBy(['jeton' => $token->getId()]);
    
            if (!$jetonAuthentification) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton invalide.'
                ], 401);
            }

            if ($jetonAuthentification->isExpired()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton expiré,veuillez vous reeauthentifier.'
                ], 401);
            }
    
            // Récupérer l'utilisateur associé
            $utilisateur = $jetonAuthentification->getUtilisateur();
    
            $data = json_decode($request->getContent(), true);
    
            if (!isset($data['mdp'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Données manquantes.'
                ], 400);
            }
    
            // Modifier le mot de passe
            $utilisateur->setMdp(password_hash($data['mdp'], PASSWORD_BCRYPT));
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Mot de passe modifié avec succès.'
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/utilisateurToken/modifier-date-naissance', name: 'modifier_date_naissance', methods: ['POST'])]
    public function modifierDateNaissance(Request $request): JsonResponse
    {
        try {
            // Récupérer le token dans le header
            $jeton = $request->headers->get('Authorization');
            if (!$jeton) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Token manquant.'
                ], 401);
            }
    
            // Vérifier le token
            $token = $this->entityManager->getRepository(Jeton::class)->findOneBy(['jeton' => $jeton]);
            $jetonAuthentification = $this->entityManager->getRepository(JetonAuthentification::class)->findOneBy(['jeton' => $token->getId()]);
    
            if (!$jetonAuthentification) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton invalide.'
                ], 401);
            }

            if ($jetonAuthentification->isExpired()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton expiré,veuillez vous reeauthentifier.'
                ], 401);
            }
    
            // Récupérer l'utilisateur associé
            $utilisateur = $jetonAuthentification->getUtilisateur();
    
            $data = json_decode($request->getContent(), true);
    
            if (!isset($data['dateNaissance'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Données manquantes.'
                ], 400);
            }
    
            try {
                $date = new \DateTime($data['dateNaissance']);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Format de date invalide.'
                ], 400);
            }
    
            // Modifier la date de naissance
            $utilisateur->setDateNaissance($date);
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Date de naissance modifiée avec succès.'
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/utilisateurToken/modifier-data-user', name: 'modifier-data-user', methods: ['POST'])]
    public function modifierDataUser(Request $request): JsonResponse
    {
        try {
            // Récupérer le token dans le header
            $jeton = $request->headers->get('Authorization');
            if (!$jeton) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Token manquant.'
                ], 401);
            }
    
            // Vérifier le token
            $token = $this->entityManager->getRepository(Jeton::class)->findOneBy(['jeton' => $jeton]);
            $jetonAuthentification = $this->entityManager->getRepository(JetonAuthentification::class)->findOneBy(['jeton' => $token->getId()]);
    
            if (!$jetonAuthentification) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton invalide.'
                ], 401);
            }

            if ($jetonAuthentification->isExpired()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Jeton expiré,veuillez vous reeauthentifier.'
                ], 401);
            }
    
            // Récupérer l'utilisateur associé
            $utilisateur = $jetonAuthentification->getUtilisateur();
    
            $data = json_decode($request->getContent(), true);
    
            if (!isset($data['dateNaissance']) && !isset($data['nom']) && !isset($data['mdp'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Données manquantes.'
                ], 400);
            }
            
            if (isset($data['dateNaissance']) && $data['dateNaissance'] != '') {
                try {
                    $date = new \DateTime($data['dateNaissance']);
                    // Modifier la date de naissance
                    $utilisateur->setDateNaissance($date);
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => 'Format de date invalide.'
                    ], 400);
                }
            }

            if (isset($data['nom']) && $data['nom'] != '') {
                $utilisateur->setNom($data['nom']);
            }

            if (isset($data['mdp']) && $data['mdp'] != '') {
                $utilisateur->setMdp(HasherUtil::hashPassword($data['mdp']));
            }
    
            
    
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'modification effectuée avec succès.'
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
}
?>