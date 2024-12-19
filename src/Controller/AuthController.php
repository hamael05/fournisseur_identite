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
    public function authentification(Request $request) : JsonResponse
    {
        try{

            $data = json_decode($request->getContent(),true);

            if(!isset($data['mail'], $data['mdp'],$data['duree_pin'])){
                return new JsonResponse([
                    'status'=>'error',
                    'data'=>null,
                    'error'=>[
                        'code'=>400,
                        'message'=> 'données manquantes'
                    ]
                    ],400);
            }

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
                    $tentative = new TentativeMdpFailed($utilisateur);
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
            $pin = new Pin($data['duree_pin'],$utilisateur);
            $this->entityManager->persist($pin);
            
            $tentative = new TentativePinFailed($pin, $utilisateur);
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
            $jeton = new Jeton(-1);
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
    public function sendNewPin(string $id_utilisateur){
        try{
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['id' => $id_utilisateur]);

            // generer un pin 
            $pin = new Pin(-1,$utilisateur);
            $this->entityManager->persist($pin);

            $tentative = new TentativePinFailed($pin, $utilisateur);
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
