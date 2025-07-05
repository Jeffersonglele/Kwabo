<?php
include_once("../includes/navbar.php");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devenir Gestionnaire - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: '#2b6cb0',
              secondary: '#4299e1',
              accent: '#f6ad55',
            },
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
            },
          }
        }
      }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
      .fade-in {
        animation: fadeIn 0.8s ease-in-out;
      }
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
      .card-hover {
        transition: all 0.3s ease;
      }
      .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      }
      .icon-box {
        width: 60px;
        height: 60px;
      }
    </style>
</head>
<body class="bg-gray-50 font-sans">
<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary py-20 text-white">
  <div class="max-w-6xl mx-auto px-6 lg:px-8 text-center fade-in">
    <h1 class="text-4xl md:text-5xl font-bold mb-6">Devenir Gestionnaire Partenaire</h1>
    <p class="text-xl max-w-3xl mx-auto opacity-90">
      Rejoignez notre réseau exclusif de gestionnaires et contribuez à valoriser le patrimoine touristique du Bénin
    </p><br>
    <br>
    <a href="../gestionnaire/connexion.php" class="bg-primary hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105">
      <i class="fas fa-user-plus mr-2"></i> S'inscrire maintenant
    </a>
  </div>
</section>

<!-- Benefits Section -->
<section class="py-16 px-6 lg:px-8 max-w-6xl mx-auto">
  <div class="text-center mb-16 fade-in">
    <h2 class="text-3xl font-bold text-gray-800 mb-4">Pourquoi choisir notre plateforme ?</h2>
    <div class="w-24 h-1 bg-accent mx-auto"></div>
  </div>

  <div class="grid md:grid-cols-3 gap-8">
    <!-- Benefit 1 -->
    <div class="bg-white p-8 rounded-xl shadow-md card-hover fade-in" style="animation-delay: 0.1s">
      <div class="icon-box bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-chart-line text-2xl"></i>
      </div>
      <h3 class="text-xl font-semibold text-center mb-3">Visibilité accrue</h3>
      <p class="text-gray-600 text-center">
        Bénéficiez d'une exposition maximale auprès des voyageurs nationaux et internationaux
      </p>
    </div>

    <!-- Benefit 2 -->
    <div class="bg-white p-8 rounded-xl shadow-md card-hover fade-in" style="animation-delay: 0.2s">
      <div class="icon-box bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-tools text-2xl"></i>
      </div>
      <h3 class="text-xl font-semibold text-center mb-3">Outils performants</h3>
      <p class="text-gray-600 text-center">
        Tableau de bord intuitif pour gérer facilement vos lieux et événements
      </p>
    </div>

    <!-- Benefit 3 -->
    <div class="bg-white p-8 rounded-xl shadow-md card-hover fade-in" style="animation-delay: 0.3s">
      <div class="icon-box bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-hands-helping text-2xl"></i>
      </div>
      <h3 class="text-xl font-semibold text-center mb-3">Support dédié</h3>
      <p class="text-gray-600 text-center">
        Équipe disponible pour vous accompagner dans votre démarche
      </p>
    </div>
  </div>
</section>

<!-- Process Section -->
<section class="py-16 bg-gray-100">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-16 fade-in">
      <h2 class="text-3xl font-bold text-gray-800 mb-4">Comment devenir gestionnaire ?</h2>
      <div class="w-24 h-1 bg-accent mx-auto"></div>
    </div>

    <div class="grid md:grid-cols-4 gap-6">
      <!-- Step 1 -->
      <div class="flex flex-col items-center text-center fade-in" style="animation-delay: 0.1s">
        <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">1</div>
        <h3 class="font-semibold mb-2">Inscription</h3>
        <p class="text-gray-600 text-sm">Remplissez notre formulaire en ligne</p>
      </div>

      <!-- Step 2 -->
      <div class="flex flex-col items-center text-center fade-in" style="animation-delay: 0.2s">
        <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">2</div>
        <h3 class="font-semibold mb-2">Vérification</h3>
        <p class="text-gray-600 text-sm">Validation par notre équipe (48h max)</p>
      </div>

      <!-- Step 3 -->
      <div class="flex flex-col items-center text-center fade-in" style="animation-delay: 0.3s">
        <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">3</div>
        <h3 class="font-semibold mb-2">Formation</h3>
        <p class="text-gray-600 text-sm">Accès à nos tutoriels et ressources</p>
      </div>

      <!-- Step 4 -->
      <div class="flex flex-col items-center text-center fade-in" style="animation-delay: 0.4s">
        <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">4</div>
        <h3 class="font-semibold mb-2">Activation</h3>
        <p class="text-gray-600 text-sm">Accès à votre espace gestionnaire</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-white">
  <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center fade-in">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Prêt à rejoindre notre réseau ?</h2>
    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
      Rejoignez dès aujourd'hui les gestionnaires qui contribuent activement au développement du tourisme béninois
    </p>
    <div class="flex flex-col sm:flex-row justify-center gap-4">
      <a href="../pages/contact.php" class="bg-white hover:bg-gray-100 text-primary font-semibold py-3 px-8 border border-primary rounded-lg transition duration-300">
        <i class="fas fa-question-circle mr-2"></i> Nous contacter
      </a>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="py-16 bg-gray-50">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-16 fade-in">
      <h2 class="text-3xl font-bold text-gray-800 mb-4">Ils nous font confiance</h2>
      <div class="w-24 h-1 bg-accent mx-auto"></div>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
      <!-- Testimonial 1 -->
      <div class="bg-white p-8 rounded-xl shadow-sm border-l-4 border-accent fade-in">
        <div class="flex items-center mb-4">
          <img src="../assets/images/testimonial-1.jpeg" alt="Gestionnaire" class="w-12 h-12 rounded-full object-cover mr-4">
          <div>
            <h4 class="font-semibold">M. Adékambi</h4>
            <p class="text-gray-500 text-sm">Hôtel La Joie de Vivre</p>
          </div>
        </div>
        <p class="text-gray-600 italic">
          "Depuis que j'ai rejoint la plateforme, ma fréquentation a augmenté de 40%. L'outil est simple et efficace."
        </p>
      </div>

      <!-- Testimonial 2 -->
      <div class="bg-white p-8 rounded-xl shadow-sm border-l-4 border-accent fade-in" style="animation-delay: 0.2s">
        <div class="flex items-center mb-4">
          <img src="../assets/images/testimonial-2.jpeg" alt="Gestionnaire" class="w-12 h-12 rounded-full object-cover mr-4">
          <div>
            <h4 class="font-semibold">Mme Agossou</h4>
            <p class="text-gray-500 text-sm">Site historique d'Abomey</p>
          </div>
        </div>
        <p class="text-gray-600 italic">
          "La visibilité apportée à notre site est exceptionnelle. Nous recevons maintenant des visiteurs du monde entier."
        </p>
      </div>
    </div>
  </div>
</section>

<?php include('../includes/footer.php'); ?>

<script>
  // Simple intersection observer for animations
  const fadeElements = document.querySelectorAll('.fade-in');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = 1;
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  fadeElements.forEach(el => {
    el.style.opacity = 0;
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
  });
</script>
</body>
</html>