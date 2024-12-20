<?php
//3218
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

use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;

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
    /**
     * Inscription d'un nouvel utilisateur.
     *
     * @OA\Post(
     *     path="/inscription",
     *     summary="Inscription d'un nouvel utilisateur",
     *     description="Crée un nouveau compte utilisateur et envoie un email de confirmation.",
     *     tags={"Inscription"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email","motDePasse","nom"},
     *             @OA\Property(property="nom", type="string", example="Dupont"),
     *             @OA\Property(property="dateNaissance", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="email", type="string", format="email", example="dupont@example.com"),
     *             @OA\Property(property="motDePasse", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="message", type="string", example="Veuillez vérifier votre e-mail pour confirmer votre inscription.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données manquantes ou invalides",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="object", 
     *                 @OA\Property(property="code", type="integer", example=400),
     *                 @OA\Property(property="message", type="string", example="Données manquantes.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Email déjà utilisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="object", 
     *                 @OA\Property(property="code", type="integer", example=409),
     *                 @OA\Property(property="message", type="string", example="Cet email est déjà utilisé.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="object", 
     *                 @OA\Property(property="code", type="integer", example=500),
     *                 @OA\Property(property="message", type="string", example="Erreur interne du serveur.")
     *             )
     *         )
     *     )
     * )
     */
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
            $jeton = new Jeton(-1);
    
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
            $this->emailService->sendJetonInscriptionEmail($jetonInscription); // Appel correct de la méthode sendEmail
    
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
    /**
     * Confirme l'inscription d'un utilisateur.
     *
     * @OA\Get(
     *     path="/confirm/{jeton}",
     *     summary="Confirme l'inscription d'un utilisateur",
     *     description="Valide le jeton d'inscription et crée un nouvel utilisateur.",
     *     tags={"Inscription"},
     *     @OA\Parameter(
     *         name="jeton",
     *         in="path",
     *         description="Le jeton d'inscription unique.",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription confirmée avec succès.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Inscription confirmée avec succès.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Jeton d'inscription introuvable.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=404),
     *                 @OA\Property(property="message", type="string", example="Jeton d'inscription introuvable.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=410,
     *         description="Le jeton a expiré.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=410),
     *                 @OA\Property(property="message", type="string", example="Le jeton a expiré.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur interne.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=500),
     *                 @OA\Property(property="message", type="string", example="Message d'erreur détaillé.")
     *             )
     *         )
     *     )
     * )
     */
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
                // Supprimer le jeton d'inscription après validation
            $this->entityManager->remove($jetonInscription);
            $this->entityManager->flush();
            

             // Supprimer le jeton correspondant à l'inscription
            $this->entityManager->persist($token);
            $this->entityManager->flush();
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
            $this->entityManager->remove($token);
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
?>