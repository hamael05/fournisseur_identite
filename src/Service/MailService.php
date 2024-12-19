<?php
namespace App\Service;

use App\Entity\JetonInscription;
use App\Entity\TentativeMdpFailed;
use App\Entity\Utilisateur;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Entity\Pin;

class MailService
{
    private $mailer;

    private static string $defaultMailAdress = 'h1hedy2@gmail.com';
    private static string $defaultMailerName = 'Fournisseur d identite';
    private static string $defaultMailerPassword = 'ajyw yoib doxd pjpt';

    // Injection du service PHPMailer
    public function __construct()
    {
        // Instanciation de PHPMailer
        $this->mailer = new PHPMailer(true);
    }

    // La fonction modifiée pour retourner un objet PHPMailer, pas un Email Symfony
    public function createMailerFromJetonInscription(JetonInscription $jetonInscription): PHPMailer
    {
        // Configuration de PHPMailer
        $this->mailer->isSMTP();                            // Envoi via SMTP
        $this->mailer->Host = 'smtp.gmail.com';             // Serveur SMTP de Gmail
        $this->mailer->SMTPAuth = true;                     // Authentification SMTP
        $this->mailer->Username = self::$defaultMailAdress;     // Votre email
        $this->mailer->Password = self::$defaultMailerPassword;   // Votre mot de passe d'application
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Chiffrement TLS
        $this->mailer->Port = 587;                          // Port SMTP (587 pour TLS)
        
        // Définir l'expéditeur et le destinataire
        $this->mailer->setFrom(self::$defaultMailAdress, self::$defaultMailerName);
        $this->mailer->addAddress($jetonInscription->getMail()); // Ajouter le destinataire
        $this->mailer->isHTML(true);                          // Format HTML
        $this->mailer->Subject = 'Confirmez votre inscription'; // Sujet de l'email
        $this->mailer->Body    = 'Cliquez sur ce lien pour confirmer votre inscription : <a href="http://localhost:8000/confirm/' . $jetonInscription->getJeton()->getJeton() . '">Confirmer</a>'; // Corps de l'email avec lien
        
        return $this->mailer;
    }
    

    // Fonction pour envoyer l'email via PHPMailer
    public function sendJetonInscriptionEmail(JetonInscription $jetonInscription): bool
    {
        try {
            // Créer l'email avec PHPMailer
            $this->createMailerFromJetonInscription($jetonInscription);
            
            // Envoi de l'email via PHPMailer
            $this->mailer->send();
            echo 'Message envoyé avec succès';
            return true;
        } catch (Exception $e) {
            echo 'Le message n\'a pas pu être envoyé. Erreur : ' . $this->mailer->ErrorInfo;
            return false;
        }
    }

    // pour pin
    public function createMailerFromPin(Pin $pin, Utilisateur $user): PHPMailer
    {
        // Configuration de PHPMailer
        $this->mailer->isSMTP();                            // Envoi via SMTP
        $this->mailer->Host = 'smtp.gmail.com';             // Serveur SMTP de Gmail
        $this->mailer->SMTPAuth = true;                     // Authentification SMTP
        $this->mailer->Username = self::$defaultMailAdress;     // Votre email
        $this->mailer->Password = self::$defaultMailerPassword;   // Votre mot de passe d'application
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Chiffrement TLS
        $this->mailer->Port = 587;                          // Port SMTP (587 pour TLS)
        
        // Définir l'expéditeur et le destinataire
        $this->mailer->setFrom(self::$defaultMailAdress, self::$defaultMailerName);
        $this->mailer->addAddress($user->getMail()); // Ajouter le destinataire
        $this->mailer->isHTML(true);                          // Format HTML
        $this->mailer->Subject = 'Confirmez votre votre auth avec ce PIN'; // Sujet de l'email
        $this->mailer->Body    = 'PIN : <h3> ' . $pin->getPin(). ' </h3>'; // Corps de l'email avec lien
        
        return $this->mailer;
    }

    public function sendPinAuthEmail(Pin $pin, Utilisateur $user): bool
    {
        try {
            // Créer l'email avec PHPMailer
            $this->createMailerFromPin($pin,$user);
            
            // Envoi de l'email via PHPMailer
            $this->mailer->send();
            echo 'Message envoyé avec succès';
            return true;
        } catch (Exception $e) {
            echo 'Le message n\'a pas pu être envoyé. Erreur : ' . $this->mailer->ErrorInfo;
            return false;
        }
    }

    // pour tentative mdp : reinitialisation
    public function createMailerFromTentativeMdpFailed(TentativeMdpFailed $tentative): PHPMailer
    {
        // Configuration de PHPMailer
        $this->mailer->isSMTP();                            // Envoi via SMTP
        $this->mailer->Host = 'smtp.gmail.com';             // Serveur SMTP de Gmail
        $this->mailer->SMTPAuth = true;                     // Authentification SMTP
        $this->mailer->Username = self::$defaultMailAdress;     // Votre email
        $this->mailer->Password = self::$defaultMailerPassword;   // Votre mot de passe d'application
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Chiffrement TLS
        $this->mailer->Port = 587;                          // Port SMTP (587 pour TLS)
        
        // Définir l'expéditeur et le destinataire
        $this->mailer->setFrom(self::$defaultMailAdress, self::$defaultMailerName);
        $this->mailer->addAddress($tentative->getUtilisateur()->getMail()); // Ajouter le destinataire
        $this->mailer->isHTML(true);                          // Format HTML
        $this->mailer->Subject = 'Reinitialisation des tentatives de connetion'; // Sujet de l'email
        $this->mailer->Body    = 'Cliquez sur ce lien pour reinitialiser vos tentatives de connection : <a href="http://localhost:8000/reinitialiser/' . $tentative->getId() . '">Confirmer</a>'; // Corps de l'email avec lien
        
        return $this->mailer;
    }

    public function sendReinitialisationTentativeMdpEmail(TentativeMdpFailed $tentative): bool
    {
        try {
            // Créer l'email avec PHPMailer
            $this->createMailerFromTentativeMdpFailed($tentative);
            
            // Envoi de l'email via PHPMailer
            $this->mailer->send();
            echo 'Message envoyé avec succès';
            return true;
        } catch (Exception $e) {
            echo 'Le message n\'a pas pu être envoyé. Erreur : ' . $this->mailer->ErrorInfo;
            return false;
        }
    }
}
?>