# Gestion des inscriptions et paiements - Bénin Tourisme

Ce document explique le flux d'inscription et de paiement pour les gestionnaires de la plateforme Bénin Tourisme.

## Architecture du système

### Fichiers principaux

1. **inscription_gestion.php**
   - Formulaire d'inscription pour les nouveaux gestionnaires
   - Validation des données côté client et serveur
   - Initialisation du processus de paiement via FedaPay
   - Stockage temporaire des données d'inscription en session

2. **success.php**
   - Page de retour après paiement
   - Vérification du statut de la transaction FedaPay
   - Création du compte si le paiement est validé
   - Envoi des notifications par email

3. **payment_failed.php**
   - Page affichée en cas d'échec ou d'annulation du paiement
   - Aucun compte n'est créé dans ce cas

4. **send_admin_notification.php**
   - Gère l'envoi des notifications par email

## Flux d'inscription

### 1. Soumission du formulaire
- L'utilisateur remplit le formulaire d'inscription
- Les données sont validées côté client (JavaScript) et serveur (PHP)
- Si les données sont valides, on initialise un paiement FedaPay

### 2. Processus de paiement
- Redirection vers la page de paiement FedaPay
- L'utilisateur effectue ou annule le paiement
- Redirection vers `success.php` ou `payment_failed.php` selon le cas

### 3. Traitement du paiement (success.php)
1. Récupération de l'ID de transaction depuis l'URL
2. Vérification du statut du paiement via l'API FedaPay
3. Si le paiement est approuvé :
   - Création du compte dans la base de données
   - Envoi d'un email de confirmation
   - Nettoyage des données de session
4. Si le paiement a échoué ou a été annulé :
   - Redirection vers `payment_failed.php`
   - Aucun compte n'est créé

## Sécurité

- Tous les mots de passe sont hachés avant stockage
- Validation stricte des entrées utilisateur
- Vérification du statut du paiement avant toute action
- Gestion des erreurs et journalisation
- Protection contre les inscriptions en double

## Configuration requise

- PHP 7.4 ou supérieur
- Extension PDO pour la base de données
- Clé API FedaPay valide
- Accès SMTP pour l'envoi d'emails

## Journalisation

Les événements importants sont journalisés pour le débogage :
- Tentatives de paiement
- Échecs de validation
- Erreurs de base de données
- Échecs d'envoi d'emails

## Dépannage

### Problèmes courants
1. **Paiement annulé mais compte créé**
   - Vérifier que la redirection vers `payment_failed.php` fonctionne
   - S'assurer que `exit()` est appelé après chaque redirection

2. **Emails non reçus**
   - Vérifier les logs d'erreurs PHP
   - Vérifier la configuration SMTP
   - Vérifier le dossier spam

3. **Erreurs de base de données**
   - Vérifier la connexion à la base de données
   - Vérifier les permissions de la table `gestionnaires`

## Améliorations possibles

1. Implémenter un système de file d'attente pour les emails
2. Ajouter des tests automatisés
3. Améliorer la gestion des erreurs utilisateur
4. Ajouter plus de logs pour le débogage
