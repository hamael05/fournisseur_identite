<?php

namespace App\Util;

class HasherUtil
{
    /**
     * Hache un mot de passe avec bcrypt.
     *
     * @param string $plainPassword Le mot de passe en clair.
     * @return string Le mot de passe haché.
     */
    public static function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    /**
     * Vérifie si un mot de passe en clair correspond à un mot de passe haché.
     *
     * @param string $plainPassword Le mot de passe en clair.
     * @param string $hashedPassword Le mot de passe haché.
     * @return bool True si les mots de passe correspondent, sinon False.
     */
    public static function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Vérifie si le mot de passe haché doit être re-haché (ex : si l'algorithme a changé).
     *
     * @param string $hashedPassword Le mot de passe haché.
     * @return bool True si un re-hachage est nécessaire, sinon False.
     */
    public static function needsRehash(string $hashedPassword): bool
    {
        return password_needs_rehash($hashedPassword, PASSWORD_BCRYPT);
    }
}
