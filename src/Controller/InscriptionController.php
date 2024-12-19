<?php

namespace App\Controller;

use App\Entity\Jeton;
use App\Entity\JetonInscription;
use App\Entity\Utilisateur;
use App\Repository\JetonInscriptionRepository;
use App\Repository\JetonRepository;
use App\Entity\ExpirationUtil;
use App\Util\HasherUtil;
use App\Util\TokenGeneratorUtil;
use App\Util\MailUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Service\JetonService;
use App\Service\MailService;

class InscriptionController extends AbstractController
{

    private $emailService;
    private $entityManager; // Ajouter EntityManagerInterface

    public function __construct(
        MailService $emailService,
        EntityManagerInterface $entityManager // Injecter EntityManagerInterface
    ) {
        $this->emailService = $emailService;
        $this->entityManager = $entityManager; // Initialiser EntityManagerInterface
    }


    #[Route('/inscription', name: 'inscription', methods: ['POST'])]
    public function inscription(Request $request): JsonResponse
    {
        try {
            // Récupérer les données JSON de la requête
            $data = json_decode($request->getContent(), true);
    
            // Vérifier si les champs requis sont présents
            if (!isset($data['nom'], $data['dateNaissance'], $data['email'], $data['motDePasse'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'data' => null,
                    'error' => [
                        'code' => 400,
                        'message' => 'Données manquantes.'
                    ]
                ], 400);
            }
    
            // Vérifier si l'email existe déjà
            $existingUser = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['mail' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse([
                    'status' => 'error',
                    'data' => null,
                    'error' => [
                        'code' => 409,
                        'message' => 'Cet email est déjà utilisé.'
                    ]
                ], 409);
            }
    
            // Hachage du mot de passe
            $hashedPassword = HasherUtil::hashPassword($data['motDePasse']);
    
            // Créer un objet Jeton avec durée par défaut
            // $jeton = $this->jetonService->createJeton();
            $jeton = new Jeton();
    
            // Insérer le jeton dans la base
            $this->entityManager->persist($jeton);  // Ajouter l'entité Jeton à la gestion d'entités

            // Enregistrer dans la base de données
            $this->entityManager->flush();  // Utilisation d'EntityManager pour insérer
    
            // Créer un JetonInscription
            $jetonInscription = new JetonInscription(
                $data['email'], 
                $hashedPassword, 
                $data['nom'], 
                new \DateTime($data['dateNaissance']),
                $jeton // L'objet Jeton déjà créé
            );
            
            // Insérer JetonInscription dans la base
            // Insérer le jeton dans la base
            $this->entityManager->persist($jetonInscription);  // Ajouter l'entité Jeton à la gestion d'entités

            // Enregistrer dans la base de données
            $this->entityManager->flush();  // Utilisation d'EntityManager pour insérer    
            // Créer un objet PHPMailer pour envoyer l'e-mail avec le lien de validation
            //$mailer = $this->emailService->createMailerFromJetonInscription($jetonInscription);
    
            // Envoie l'e-mail
            $this->emailService->sendEmail($jetonInscription); // Appel correct de la méthode sendEmail
    
            // Réponse en cas de succès
            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'message' => 'Veuillez vérifier votre e-mail pour confirmer votre inscription.'
                ]
            ], 200);
    
        } catch (\Exception $e) {
            // Gestion des erreurs
            return new JsonResponse([
                'status' => 'error',
                'data' => null,
                'error' => [
                    'code' => 500,
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
    



    #[Route('/confirm/{jeton}', name: 'confirm', methods: ['GET'])]
    public function confirmInscription(string $jeton): JsonResponse
    {
        try {
            // Chercher le jeton d'inscription par son jeton
            $token = $this->entityManager->getRepository(Jeton::class)->findOneBy(['jeton' => $jeton]);
            $jetonInscription = $this->entityManager->getRepository(JetonInscription::class)->findOneBy(['jeton' => $token->getId()]);

            /*$jetonInscription = $this->entityManager->getRepository(JetonInscription::class)
            ->createQueryBuilder('ji')
            ->join('ji.jeton', 'j')
            ->where('j.jeton = :jeton')
            ->setParameter('jeton', $jeton)
            ->getQuery()
            ->getOneOrNullResult();*/

            if (!$jetonInscription) {
                return new JsonResponse([
                    'status' => 'error',
                    'data' => null,
                    'error' => [
                        'code' => 404,
                        'message' => 'Jeton d\'inscription introuvable.'
                    ]
                ], 404);
            }

            // Vérifier si le jeton est expiré (méthode de l'entité JetonInscription)
            if ($jetonInscription->isExpired()) {
                return new JsonResponse([
                    'status' => 'error',
                    'data' => null,
                    'error' => [
                        'code' => 410,
                        'message' => 'Le jeton a expiré.'
                    ]
                ], 410);
            }

            // Créer un nouvel utilisateur en utilisant le constructeur de JetonInscription
            $utilisateur = new Utilisateur(
                $jetonInscription->getMail(),
                $jetonInscription->getMdp(),
                $jetonInscription->getNom(),
                $jetonInscription->getDateNaissance()
            );

            // Insérer l'utilisateur dans la base de données
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();

            // Supprimer le jeton d'inscription après validation
            $this->entityManager->remove($jetonInscription);
            $this->entityManager->flush();
            

             // Supprimer le jeton correspondant à l'inscription
            $this->entityManager->persist($token);
            $this->entityManager->flush();
            

            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'message' => 'Inscription confirmée avec succès.'
                ]
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'data' => null,
                'error' => [
                    'code' => 500,
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    
}
