-- Insertion d'un compte de test dans la table gestionnaires
INSERT INTO gestionnaires (
    id, user_id, nom, email, telephone, type_compte, statut_paiement, date_inscription, mot_de_passe
) VALUES (
    4, 4, 'DINA', 'akadimadina0@gmail.com', '0164780067', 'destination', 'valide', NOW(),
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);

-- Le mot de passe est 'password' (hashé avec password_hash())
-- Le statut_paiement est 'valide' pour permettre l'accès au dashboard
-- Le type_compte est 'destination' pour tester le tableau de bord correspondant

-- Vérifier que le compte existe
SELECT * FROM gestionnaires WHERE email = 'akadimadina0@gmail.com';
