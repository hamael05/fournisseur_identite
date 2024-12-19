<?php

namespace App\Util;

class TokenGeneratorUtil
{
    /**
     * Génère un jeton aléatoire composé uniquement de caractères alphanumériques.
     *
     * @param int $length La longueur du jeton à générer.
     * @return string Le jeton généré.
     */
    public static function generateToken(int $length = 32): string
    {
        // Liste des caractères autorisés : lettres majuscules, minuscules et chiffres
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, $charactersLength - 1);
            $randomString .= $characters[$randomIndex];
        }

        return $randomString;
    }
}
