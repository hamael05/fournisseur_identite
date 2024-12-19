<?php
namespace App\Service;

use App\Entity\JetonInscription;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mailer;

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
        $this->mailer->Username = 'h1hedy2@gmail.com';     // Votre email
        $this->mailer->Password = 'ajyw yoib doxd pjpt';   // Votre mot de passe d'application
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Chiffrement TLS
        $this->mailer->Port = 587;                          // Port SMTP (587 pour TLS)
        
        // Définir l'expéditeur et le destinataire
        $this->mailer->setFrom('h1hedy2@gmail.com', '$this->defaultAppName');
        $this->mailer->addAddress($jetonInscription->getMail()); // Ajouter le destinataire
        $this->mailer->isHTML(true);                          // Format HTML
        $this->mailer->Subject = 'Confirmez votre inscription'; // Sujet de l'email
        $this->mailer->Body    = 'Cliquez sur ce lien pour confirmer votre inscription : <a href="http://localhost:8000/confirm/' . $jetonInscription->getJeton()->getJeton() . '">Confirmer</a>'; // Corps de l'email avec lien
        
        return $this->mailer;
    }

    // Fonction pour envoyer l'email via PHPMailer
    public function sendEmail(JetonInscription $jetonInscription): bool
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
}
?>