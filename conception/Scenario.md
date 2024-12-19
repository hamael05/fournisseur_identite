# Inscription 
- scenario par défaut:
    la personne insert: 
    - mail
    - mdp 
    puis appuie sur :
    - btn envoyer email => url miantso fonction insert 

    # traitement 
    - hacher le mdp
    - générer un jeton 
    - construire le lien 
    - insertion mdp haché, mail, jeton et date d'expiration (parametrable) dans la table : jeton_inscription
    - fonction envoie email lien 
    - la personne appuie le lien dans l'email
    - fonction validation inscription : le token est-il dans la table et est-il non expiré (fonction isExiperedJeton), si oui insertion dans la table user()

# Authentification multifacteur avec confirmation PIN sur email 
- scenario par défaut:
    - mail
    - mdp 
    - btn envoyer PIN  => url miantso fonction  

    # traitement
    - fonction checkLogin(mail,mdp)
    - générer code PIN (et date d'expiration)
    - insérer code PIN dans table pin_authentification
    - fonction envoie code PIN any am mail
    - (user entre code PIN)
    - fonction estValidePIN => mitovy amlay généré ve sady mbola tsy expiré, si oui, login 
    - login => 
        - générer jeton 
        - insertion dans jeton_authentification 
    


