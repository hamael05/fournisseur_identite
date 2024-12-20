<?php

namespace App\Controller;

use App\Entity\JetonAuthentification;
use App\Entity\Pin;
use App\Entity\Jeton;
use App\Entity\TentativeMdpFailed;
use App\Entity\TentativePinFailed;
use App\Entity\Utilisateur;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MailService;
use OpenApi\Annotations as OA;


class AuthController extends AbstractController
{

    private $emailService;

    private $authService;
    private $entityManager; // Ajouter EntityManagerInterface

    public function __construct(
        MailService $emailService,
        AuthService $authService,
        EntityManagerInterface $entityManager // Injecter EntityManagerInterface
    ) {
        $this->emailService = $emailService;
        $this->authService = $authService;
        $this->entityManager = $entityManager; // Initialiser EntityManagerInterface
    }

    #[Route('/authentification', name: 'authentification', methods: ['POST'])]
    /**
 * @OA\Post(
 *     path="/authentification",
 *     summary="Authentifier un utilisateur et générer un PIN",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"mail", "mdp"},  // Indiquer les champs obligatoires ici
 *             @OA\Property(property="mail", type="string", description="Adresse e-mail de l'utilisateur"),
 *             @OA\Property(property="mdp", type="string", description="Mot de passe de l'utilisateur"),
 *             @OA\Property(property="nb_tentative_mdp", type="integer", description="Nombre de tentatives de mot de passe autorisées", example=-1),
 *             @OA\Property(property="nb_tentative_pin", type="integer", description="Nombre de tentatives de PIN autorisées", example=-1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Authentification réussie. Le PIN a été envoyé par e-mail.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="succès"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="message", type="string", example="Veuillez vérifier votre e-mail pour voir votre pin.")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Entrée invalide ou erreur lors de l'authentification.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="erreur"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=400),
 *                 @OA\Property(property="message", type="string", example="données manquantes")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="erreur"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=500),
 *                 @OA\Property(property="message", type="string", example="Une erreur interne est survenue.")
 *             )
 *         )
 *     )
 * )
 */

    public function authentification(Request $request) : JsonResponse
    {
        try{

            $data = json_decode($request->getContent(),true);

            if(!isset($data['mail'], $data['mdp'])){
                return new JsonResponse([
                    'status'=>'error',
                    'data'=>null,
                    'error'=>[
                        'code'=>400,
                        'message'=> 'données manquantes'
                    ]
                    ],400);
            }

            $nb_tentative_mdp = -1;
            if(isset($data['nb_tentative_mdp'])){
                $nb_tentative_mdp = $data['nb_tentative_mdp'];
            }

            $nb_tentative_pin = -1;
            if(isset($data['nb_tentative_pin'])){
                $nb_tentative_pin = $data['nb_tentative_pin'];
            }

            $duree_pin = -1;
           

            // verifier que l'utilisateur associé à l'email existe
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['mail' => $data['mail']]);
            if(!$utilisateur){
                return new JsonResponse([
                    'status'=>'error',
                    'data'=>null,
                    'error'=>[
                        'code'=>400,
                        'message'=> 'aucun utilisateur associé à ce mail'
                    ]
                    ],400);
            }

            // verifier que le mot de passe est correct
            // si mdp incorrect
            if(!$this->authService->checkLogin($utilisateur,$data['mdp'])){
                //verifier si il y a déjà une tentative_mdp_failed associé à l'user (get la tentative)
                $tentative = $this->entityManager->getRepository(TentativeMdpFailed::class)->findOneBy(['utilisateur' => $utilisateur->getId()]);
                if(!$tentative){
                    
                    $tentative = new TentativeMdpFailed($nb_tentative_mdp,$utilisateur);
                    $this->entityManager->persist($tentative);
                    $this->entityManager->flush();
                    return new JsonResponse([
                        'status'=>'error',
                        'data'=>null,
                        'error'=>[
                            'code'=>400,
                            'message'=> 'mot de passe incorrect, il vous reste '.$tentative->getNbTentativeRestant().' tentative(s)',
                        ]
                        ],400);
                }
                // si tentative mdp restante >0,nalana dia modifier-na ny any anaty base
                if($tentative->getNbTentativeRestant()>0){
                    $tentative->moinsUnTentativeRestant();

                    $this->entityManager->persist($tentative);
                    $this->entityManager->flush();

                     return new JsonResponse([
                    'status'=>'error',
                    'data'=>null,
                    'error'=>[
                        'code'=>400,
                        'message'=> 'mot de passe incorrect, il vous reste '.$tentative->getNbTentativeRestant().' tentative(s)',
                    ]
                    ],400);
                }
                //tentative ==0
                else
                {
                    //envoyer mail reinitialisation
                    $this->emailService->sendReinitialisationTentativeMdpEmail($tentative); // Appel correct de la méthode sendEmail
                    return new JsonResponse([
                        'status'=>'error',
                        'data'=>null,
                        'error'=>[
                            'code'=>400,
                            'message'=> 'Nombre de tentative de connection limite atteinte. Veuillez vérifier votre e-mail pour reinitialiser les tentatives',
                        ]
                        ],400);


                }
            }

            // si le mot de passe est correcte
            // generer un pin 
            $pin = new Pin($duree_pin,$utilisateur);
            $this->entityManager->persist($pin);
            
            $tentative = new TentativePinFailed($nb_tentative_pin,$pin, $utilisateur);
            $this->entityManager->persist($tentative);
            
            $this->entityManager->flush();

            //envoyer email
            $this->emailService->sendPinAuthEmail($pin,$utilisateur); // Appel correct de la méthode sendEmail

            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'message' => 'Veuillez vérifier votre e-mail pour voir votre pin.'
                ]
            ], 200);




        }
        catch (\Exception $e) {
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

    

    #[Route('/confirmPin', name: 'confirmPin', methods: ['POST'])]
   /**
 * @OA\Post(
 *     path="/confirmPin",
 *     summary="Valider le PIN de l'utilisateur et générer un jeton.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"pin", "id_utilisateur"}, // Champs obligatoires
 *             @OA\Property(property="pin", type="string", description="Le PIN saisi par l'utilisateur."),
 *             @OA\Property(property="id_utilisateur", type="integer", description="L'ID de l'utilisateur."),
 *             @OA\Property(property="duree_jeton", type="integer", description="Durée optionnelle du jeton en secondes.", example=-1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="PIN vérifié et jeton créé avec succès.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="message", type="string", example="Vous êtes connecté! Votre jeton a été créé!")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="PIN invalide ou tentatives épuisées.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=400),
 *                 @OA\Property(property="message", type="string", example="PIN incorrect, il vous reste X tentative(s).")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Tentatives épuisées et un nouveau PIN envoyé par email.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=400),
 *                 @OA\Property(property="message", type="string", example="Nombre de tentatives atteint. Veuillez vérifier votre e-mail pour réinitialiser les tentatives.")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="PIN expiré.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=400),
 *                 @OA\Property(property="message", type="string", example="Le PIN entré est expiré. Veuillez re-essayer de nous authentifier.")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=500),
 *                 @OA\Property(property="message", type="string", example="Une erreur interne est survenue.")
 *             )
 *         )
 *     )
 * )
 */


    public function confirmPin(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['pin'], $data['id_utilisateur'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'data' => null,
                    'error' => [
                        'code' => 400,
                        'message' => 'Données manquantes'
                    ]
                ], 400);
            }

            $duree_jeton = -1;
            if(isset($data['duree_jeton'])){
                $duree_jeton = $data['duree_jeton'];
            }

            $pin = $this->entityManager->getRepository(Pin::class)->findOneBy(['utilisateur' => $data['id_utilisateur']]);
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['id' => $data['id_utilisateur']]);
            $tentative = $this->entityManager->getRepository(TentativePinFailed::class)->findOneBy(['utilisateur' => $utilisateur->getId()]);
            
            // Vérification du pin
            
            if ($tentative->getPin()->getPin() != $data['pin']) 
            {
                // mbola misy lay tentative
                if ($tentative->getNbTentativeRestant() > 0) 
                {
                    // analana lay tentative
                    $tentative->moinsUnTentativeRestant();
                    $this->entityManager->persist($tentative);
                    $this->entityManager->flush();

                    return new JsonResponse([
                        'status' => 'error',
                        'data' => null,
                        'error' => [
                            'code' => 400,
                            'message' => 'PIN incorrect, il vous reste ' . $tentative->getNbTentativeRestant() . ' tentative(s).'
                        ]
                    ], 400);
                }

                // Tentatives épuisées
                // fafana tao anaty base lay pin
                $this->entityManager->remove($pin);
                //alefa mail lay pin vaovao
                $this->emailService->sendNewPin($tentative);
                //fafana lay tentative taloha
                $this->entityManager->remove($tentative);
        
                $this->entityManager->flush();

                return new JsonResponse([
                    'status' => 'error',
                    'data' => null,
                    'error' => [
                        'code' => 400,
                        'message' => 'Nombre de tentatives atteint. Veuillez vérifier votre e-mail pour réinitialiser les tentatives.'
                    ]
                ], 400);
            
            }

            // Cas succès => ilay pin tokony natsofoka no natsofoka
            // jerena hoe sao efa expiré lay pin
            if($pin->isExpired())
            {
               // fafana lay pin
               $this->entityManager->remove($pin);

               //fafana lay tentative
               $this->entityManager->remove($tentative);

                
                $this->entityManager->flush();

                return new JsonResponse([
                    'status' => 'error',
                    'data' => null,
                    'error' => [
                        'code' => 400,
                        'message' => 'Le PIN entré est expiré.Veuillez re-essayer de nous authentifier'
                    ]
                ], 500);
            }

            //raha mbola tsy expiré lay pin
            // tokony mamorona token 
            $jeton = new Jeton($duree_jeton);
            $this->entityManager->persist($jeton);
            $this->entityManager->flush();

            $jeton_authentification = new JetonAuthentification($utilisateur,$jeton);
            $this->entityManager->persist($jeton_authentification);
            $this->entityManager->flush();

            
            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'message' => 'Vous êtes connecté! Votre jeton a été créé!'
                ]
            ], 200);
        } 
        catch (\Exception $e) {
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




    #[Route('/sendNewPin/{id_utilisateur}', name: 'sendNewPin', methods: ['GET'])]
    /**
 * @OA\Get(
 *     path="/sendNewPin/{id_utilisateur}",
 *     summary="Envoyer un nouvel e-mail de confirmation avec un nouveau PIN.",
 *     description="Cette méthode génère un nouveau PIN pour un utilisateur et lui envoie un e-mail de confirmation.",
 *     @OA\Parameter(
 *         name="id_utilisateur",
 *         in="path",
 *         required=true,
 *         description="L'ID de l'utilisateur pour lequel un nouveau PIN doit être généré.",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="PIN généré et e-mail envoyé avec succès.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="message", type="string", example="Veuillez vérifier votre e-mail pour confirmer votre inscription.")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=500),
 *                 @OA\Property(property="message", type="string", example="Une erreur interne est survenue.")
 *             )
 *         )
 *     )
 * )
 */

    public function sendNewPin(string $id_utilisateur){
        try{
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['id' => $id_utilisateur]);
            $nb_tentative_pin = -1;
            $duree_pin = -1;

            // generer un pin 
            $pin = new Pin($duree_pin,$utilisateur);
            $this->entityManager->persist($pin);

            $tentative = new TentativePinFailed($nb_tentative_pin,$pin, $utilisateur);
            $this->entityManager->persist($tentative);

            $this->entityManager->flush();

            //envoyer email
            $this->emailService->sendPinAuthEmail($pin,$utilisateur); // Appel correct de la méthode sendEmail

            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'message' => 'Veuillez vérifier votre e-mail pour confirmer votre inscription.'
                ]
            ], 200);

        }
        catch (\Exception $e) {
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

    #[Route('/reinitialiser/{id_tentative}', name: 'reinitialiser', methods: ['GET'])]
    /**
 * @OA\Get(
 *     path="/reinitialiser/{id_tentative}",
 *     summary="Réinitialiser le nombre de tentatives restantes pour un utilisateur.",
 *     description="Cette méthode permet de réinitialiser le nombre de tentatives restants pour un utilisateur afin de permettre une nouvelle tentative de connexion.",
 *     @OA\Parameter(
 *         name="id_tentative",
 *         in="path",
 *         required=true,
 *         description="L'ID de la tentative de connexion pour laquelle le nombre de tentatives restantes doit être réinitialisé.",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tentatives réinitialisées avec succès.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="message", type="string", example="Veuillez retenter pour confirmer votre authentification.")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="integer", example=500),
 *                 @OA\Property(property="message", type="string", example="Une erreur interne est survenue.")
 *             )
 *         )
 *     )
 * )
 */

    public function reinitialiser (string $id_tentative) {
        try{
            $tentative = $this->entityManager->getRepository(TentativeMdpFailed::class)->findOneBy(['id' => $id_tentative]);
            $tentative->setNbTentativeRestant(-1);
            $this->entityManager->persist($tentative);
            $this->entityManager->flush();
            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'message' => 'Veuillez retenter pour confirmer votre authentification.'
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


}
