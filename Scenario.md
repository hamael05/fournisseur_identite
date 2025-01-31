# INSCRIPTION
## Scénario 1 : Inscription réussie
- Utilisateur remplit formulaire avec nom, date de naissance, email, mot de passe

Système vérifie :
- Tous champs remplis
- Email unique
- Mot de passe haché
- Crée jeton et le sauvegarde
- Génère jeton d'inscription lié à email
- E- nvoi email de confirmation
- Affiche message : "Veuillez vérifier votre email"

Cas de test : "Succès, inscription en attente"

## Scénario 2 : Formulaire incomplet
- Utilisateur soumet formulaire avec champ manquant

Système vérifie :
- Tous champs remplis (faux)
- Retourne erreur : "Données manquantes"

Cas de test : "Échec, erreur 400"

## Scénario 3 : Email déjà utilisé
- Utilisateur inscrit avec email existant

Système vérifie :
- Email déjà utilisé
- Retourne erreur : "Email déjà utilisé"

Cas de test : "Échec, erreur 409"

-------------------------------------------------------------------------------------

# CONFIRMATION D'INSCRIPTION
## Scénario 1 : Confirmation réussie
- Utilisateur clique sur lien de confirmation valide

Système vérifie :
- Jeton existant et non expiré
- Crée compte utilisateur
- Supprime jetons
- Affiche : "Inscription confirmée"

Cas de test : "Succès, utilisateur créé"

## Scénario 2 : Jeton introuvable
- Utilisateur clique sur lien invalide

Système vérifie :
- Jeton inexistant
- Retourne erreur : "Jeton introuvable"

Cas de test : "Échec, erreur 404"

## Scénario 3 : Jeton expiré
- Utilisateur clique après expiration

Système vérifie :
- Jeton expiré
- Supprime jetons expirés
- Retourne erreur : "Jeton expiré"

Cas de test : "Échec, erreur 410"

-------------------------------------------------------------------------------------

# AUTHENTIFICATION
## Scénario 1 : Authentification réussie
- Utilisateur entre email et mot de passe valides

Système vérifie :
- Email existe dans base de données
- Mot de passe correct
- Génère PIN valide et tentative de connexion
- Envoi email avec PIN d'authentification
- Affiche : "Veuillez vérifier votre email"

Cas de test : "Succès, email avec PIN envoyé"

## Scénario 2 : Email non enregistré
- Utilisateur soumet email invalide

Système vérifie :
- Email n'existe pas
- Retourne erreur : "Aucun utilisateur associé à ce mail"

Cas de test : "Échec, erreur 400"

## Scénario 3 : Mot de passe incorrect
- Utilisateur entre email et mot de passe invalides

Système vérifie :
- Email existant
- Mot de passe incorrect
- Vérifie nombre de tentatives restantes
Cas 1 : Première tentative => Affiche erreurs avec tentatives restantes
Cas 2 : Tentatives > 0 => Décrémente tentatives restantes
Cas 3 : Dernière tentative => Envoie email réinitialiser mot de passe

Cas de test : "Échec, erreur 400 et message 'Mot de passe incorrect, il vous reste X tentative(s)'"

## Scénario 4 : Nombre de tentatives atteint
- Utilisateur entre email et mot de passe invalides

Système vérifie :
- Dernière tentative effectuée
- Supprime ancien PIN
- Crée nouveau PIN
- Envoie email réinitialiser tentatives
- Retourne erreur : "Nombre de tentatives atteint. Veuillez vérifier votre email"

Cas de test : "Échec, erreur 400 et message 'Nombre de tentatives atteint. Veuillez vérifier votre e-mail pour réinitialiser les tentatives.'"

## Scénario 5 : Informations manquantes
- Utilisateur soumet email et mot de passe sans données manquantes

Système vérifie :
- Données complètes présentes (faux)
- Retourne erreur : "Données manquantes"

Cas de test : "Échec, erreur 400 et message 'Données manquantes.'"

-------------------------------------------------------------------------------------

# CONFIRMATION DU PIN
## Scénario 1 : Confirmation réussie
- Utilisateur entre PIN valide et non expiré

Système vérifie :
- PIN correspond et non expiré
- Crée jeton d'authentification lié à utilisateur
- Affiche : "Succès, le jeton a été créé et l'utilisateur est connecté"

Cas de test : "Succès, jeton créé et utilisateur connecté"

## Scénario 2 : PIN incorrect
- Utilisateur entre PIN invalide

Système vérifie :
- PIN incorrect
- Décrémente nombre de tentatives restantes
- Retourne erreur avec tentatives restantes

Cas de test : "Échec, erreur 400 et message 'PIN incorrect, il vous reste X tentative(s)'"

## Scénario 3 : PIN expiré
- Utilisateur entre PIN expiré

Système vérifie :
- PIN expiré
- Supprime ancien PIN et tentative échouée
- Retourne erreur : "Le PIN entré est expiré. Veuillez re-essayer de nous authentifier."

Cas de test : "Échec, erreur 400 et message 'Le PIN entré est expiré. Veuillez re-essayer de nous authentifier.'"

## Scénario 4 : Informations manquantes
- Utilisateur soumet PIN sans données complètes

Système vérifie :
- Données manquantes présentes (faux)
- Retourne erreur : "Données manquantes."

Cas de test : "Échec, erreur 400 et message 'Données manquantes.'"

-------------------------------------------------------------------------------------

# ENVOI D'UN NOUVEAU PIN
## Scénario 1 : Envoi de nouveau PIN réussi
- Utilisateur envoie requête GET avec id_utilisateur valide

Système vérifie :
- Utilisateur existe dans base de données
- Nouveau PIN généré avec succès
- Tâche d'email de confirmation effectuée
- Affiche : "Succès, email envoyé avec succès"

Cas de test : "Succès, email envoyé avec succès"

## Scénario 2 : Utilisateur non trouvé
Utilisateur envoie requête GET avec id_utilisateur invalide

Système vérifie :
- Utilisateur n'existe pas
- Retourne erreur : "Utilisateur non trouvé"

Cas de test : "Échec, erreur 404 indiquant utilisateur non trouvé"

-------------------------------------------------------------------------------------

# RÉINITIALISATION DES TENTATIVES DE MOT DE PASSE
## Scénario 1 : Réinitialisation réussie
- Utilisateur envoie requête GET avec id_tentative valide

Système vérifie :
- Tentative existante dans base de données
- Réinitialise nombre de tentatives à -1
- Met à jour base de données avec succès
- Affiche : "Réinitialisation réussie"

Cas de test : "Succès, réinitialisation effectuée avec code HTTP 200"

-------------------------------------------------------------------------------------

# MODIFICATION DES DONNÉES UTILISATEUR
## Scénario 1 : Modification réussie
- Utilisateur authentifié soumet une requête avec les données à modifier

Système vérifie :
- Token valide présent
- Jeton non expiré
- Données entrées valides
- Système modifie les données utilisateur
- Affiche message : "Modification effectuée avec succès."

Cas de test : "Succès, données modifiées"

## Scénario 2 : Token manquant ou invalide
- Utilisateur non authentifié ou jeton expiré soumet une requête

Système vérifie :
- Absence ou invalidité du token
- Jeton expiré
- Système retourne erreur : "Token manquant ou invalide."

Cas de test : "Échec, erreur 401"

## Scénario 3 : Données invalides
- Utilisateur soumet une requête avec des données invalides

Système vérifie :
- Absence de certains champs requis
- Format de date invalide
- Système retourne erreur appropriée selon le cas :
"Données manquantes."
"Format de date invalide."

Cas de test : "Échec, erreur appropriée"

