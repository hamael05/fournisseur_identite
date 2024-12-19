<?php

namespace App\Util;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailUtil
{
    /**
     * Envoie un e-mail à l'aide d'un objet PHPMailer.
     *
     * @param PHPMailer $mailer
     * @return bool True si l'e-mail est envoyé avec succès, False sinon.
     * @throws Exception
     */
    public static function sendMail(MailerInterface $mailer): bool
    {
       try {
        $mailer->send();
        echo 'Message envoye avec succès';
        return true;
       } catch (Exception $e) {
        echo 'Le message n a pas ou etre envoye . Erreur : {$mailer->ErrorInfo}';
       }
       return false;
    }

}
