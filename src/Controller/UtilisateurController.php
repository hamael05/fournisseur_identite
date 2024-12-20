<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UtilisateurController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository)
    {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[Route('/utilisateur/modifier-nom', name: 'modifier_nom', methods: ['POST'])]
    public function modifierNom(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['id'], $data['nom'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Données manquantes.'
                ], 400);
            }

            $result = $this->utilisateurRepository->updateNom($data['id'], $data['nom']);

            if (!$result) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé.'
                ], 404);
            }

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


    #[Route('/utilisateur/modifier-mdp', name: 'modifier_mdp', methods: ['POST'])]
    public function modifierMdp(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['id'], $data['mdp'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Données manquantes.'
                ], 400);
            }

            $result = $this->utilisateurRepository->updateMdp($data['id'], $data['mdp']);

            if (!$result) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé.'
                ], 404);
            }

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

    #[Route('/utilisateur/modifier-date-naissance', name: 'modifier_date_naissance', methods: ['POST'])]
    public function modifierDateNaissance(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['id'], $data['dateNaissance'])) {
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

            $result = $this->utilisateurRepository->updateDateNaissance($data['id'], $date);

            if (!$result) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé.'
                ], 404);
            }

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
    
}
?>