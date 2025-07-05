<title>Découvrez le Bénin - Votre Guide Touristique</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php
    // Utiliser le chemin relatif correct pour inclure database.php
    include_once(__DIR__ . "/../config/database.php");
    ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4A5568',    // Gris foncé
                        secondary: '#718096',  // Gris moyen
                        dark: '#2D3748',       // Gris très foncé
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    animation: {
                        'slide-down': 'slideDown 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'scale-in': 'scaleIn 0.5s ease-out',
                    },
                    keyframes: {
                        slideDown: {
                            '0%': { transform: 'translateY(-100%)' },
                            '100%': { transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(100%)' },
                            '100%': { transform: 'translateY(0)' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>

<style>
   
        /* Footer Styles */
        .footer {
            background: linear-gradient(to bottom, #718096, #1f2937);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #f97316, #718096);
        }

        .footer-link {
            position: relative;
            transition: all 0.3s ease;
        }

        .footer-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #f97316;
            transition: width 0.3s ease;
        }

        .footer-link:hover::after {
            width: 100%;
        }

        .footer-link:hover {
            color: #f97316;
            transform: translateX(5px);
        }

        .social-icon {
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            transform: translateY(-5px) scale(1.1);
            color: #f97316;
        }

        .newsletter-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .newsletter-input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }

        .newsletter-btn {
            background: linear-gradient(45deg, #f97316, #718096);
            transition: all 0.3s ease;
        }

        .newsletter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .animate-pulse-slow {
            animation: pulse 2s infinite;
        }
</style>

 <!-- Footer -->
 <footer class="footer pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-16">
                <!-- À propos -->
                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-white">PLANS</h3>
                    <p class="text-gray-300">Découvrez le Bénin en vous déplaçant comme bon vous semble</p>
                    <p class="text-gray-300">Nous vous proposons des plans pour vous aider à découvrir le Bénin</p>
                    <a href="pages\plan.php" class="inline-block bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 hover:from-blue-700 hover:to-blue-900">Voir les plans</a>
                    <div class="flex space-x-4">
                        <a href="#" class="social-icon text-white hover:text-secondary">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon text-white hover:text-secondary">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon text-white hover:text-secondary">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-icon text-white hover:text-secondary">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Liens rapides -->
                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-white">Liens rapides</h3>
                    <ul class="space-y-2">
                        <li><a href="../gestionnaire/destinations.php" class="footer-link text-gray-300">Destinations</a></li>
                        <li><a href="../gestionnaire/hotels.php" class="footer-link text-gray-300">Hôtels</a></li>
                        <li><a href="../gestionnaire/evenements.php" class="footer-link text-gray-300">Événements</a></li>
                        <li><a href="../gestionnaire/circuits.php" class="footer-link text-gray-300">Circuits</a></li>
                        
                    </ul>
                </div>

                <!-- Contact -->
                <div class="space-y-4 lg:mr-8">
                    <h3 class="text-xl font-bold text-white">Contact</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center space-x-2 text-gray-300">
                            <i class="fas fa-map-marker-alt text-secondary"></i>
                            <span>Cotonou, Bénin</span>
                        </li>
                        <li class="flex items-center space-x-2 text-gray-300">
                            <i class="fas fa-phone text-secondary"></i>
                            <span>+229 0164780067/ <br>
                            +229 0190077139</span>
                        </li>
                        <li class="flex items-center space-x-2 text-gray-300">
                            <i class="fas fa-envelope text-secondary"></i>
                            <span>contactbenintourisme@gmail.com</span>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="space-y-4 lg:ml-8">
                    <h3 class="text-xl font-bold text-white">Newsletter</h3>
                    <p class="text-gray-300">Inscrivez-vous pour recevoir nos dernières actualités et offres spéciales.</p>
                    <!-- Notification -->
                    <div id="newsletter-notification" class="hidden">
                        <div class="success-message hidden bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="notification-text"></span>
                            </div>
                        </div>
                        <div class="error-message hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="notification-text"></span>
                            </div>
                        </div>
                    </div>
                    <form id="newsletter-form" action="/ProjetBinome/pages/mail.php" method="POST" class="space-y-3">
                        <input type="email" name="email" placeholder="Votre email" 
                               class="newsletter-input w-full px-4 py-2 rounded-lg text-white placeholder-gray-400 focus:outline-none" required>
                        <button type="submit" 
                                class="newsletter-btn w-full px-4 py-2 text-white rounded-lg font-semibold">
                            S'inscrire
                        </button>
                    </form>
                </div>
            </div>

            <!-- Divider -->
            <div class="h-px bg-gradient-to-r from-transparent via-white/20 to-transparent my-8"></div>

            <!-- Copyright -->
            <div class="text-center text-gray-300">
                <p>&copy; <?= date('Y') ?> Bénin Tourisme. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

<script>
    // Fonction pour afficher la notification
    function showNotification(message, type = 'success') {
        const notificationDiv = document.getElementById('newsletter-notification');
        const successMessage = notificationDiv.querySelector('.success-message');
        const errorMessage = notificationDiv.querySelector('.error-message');
        
        // Cacher les deux messages
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        
        // Sélectionner le bon message et mettre à jour le texte
        const messageDiv = type === 'success' ? successMessage : errorMessage;
        messageDiv.querySelector('.notification-text').textContent = message;
        
        // Afficher la notification
        notificationDiv.classList.remove('hidden');
        messageDiv.classList.remove('hidden');
        
        // Faire disparaître la notification après 5 secondes
        setTimeout(() => {
            messageDiv.classList.add('hidden');
            notificationDiv.classList.add('hidden');
        }, 5000);
    }

    // Gestion du formulaire de newsletter
    document.getElementById('newsletter-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = e.target.querySelector('input[type="email"]').value;
        
        try {
            const response = await fetch('/ProjetBinome/pages/mail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(email)}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(data.message, 'success');
                e.target.reset();
            } else {
                showNotification(data.error || 'Une erreur est survenue', 'error');
            }
        } catch (error) {
            showNotification('Une erreur est survenue lors de l\'inscription', 'error');
            console.error('Erreur:', error);
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>