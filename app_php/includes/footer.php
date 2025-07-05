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
                    <a href="../pages/plan.php" class="inline-block bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 hover:from-blue-700 hover:to-blue-900">Voir les plans</a>
                    <div class="flex space-x-4">
                        <a href="#" class="social-icon text-white hover:text-secondary">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon text-white hover:text-secondary">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                        <a href="https://www.instagram.com/benintourisme2025/?next=%2F#" class="social-icon text-white hover:text-secondary">
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
                        <li><a href="../pages/destinations.php" class="footer-link text-gray-300">Destinations</a></li>
                        <li><a href="../pages/hotel.php" class="footer-link text-gray-300">Hôtels</a></li>
                        <li><a href="../pages/evenements.php" class="footer-link text-gray-300">Événements</a></li>
                        <li><a href="../pages/circuits.php" class="footer-link text-gray-300">Circuits</a></li>
                        <li><a href="../pages/blog.php" class="footer-link text-gray-300">Blog</a></li>
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
                            <a href="mailto:contactbenintourisme@gmail.com" class="hover:text-primary transition-colors">
                                <span>contactbenintourisme@gmail.com</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="space-y-4 lg:ml-8">
                    <h3 class="text-xl font-bold text-white">Newsletter</h3>
                    <p class="text-gray-300">Inscrivez-vous pour recevoir nos dernières actualités et offres spéciales.</p>
                    <form id="newsletterForm" class="newsletter-form space-y-3">
                        <input type="email" name="email" placeholder="Votre email" required
                            class="newsletter-input w-full px-4 py-2 rounded-lg text-white placeholder-gray-400 focus:outline-none">
                        <button type="submit" 
                            class="newsletter-btn w-full px-4 py-2 text-white rounded-lg font-semibold">
                            S'inscrire
                        </button>
                    </form>
                    <div id="newsletterMessage" class="hidden mt-2 p-2 rounded"></div>
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
document.getElementById('newsletterForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const email = form.querySelector('input[type="email"]').value;
    const submitButton = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('newsletterMessage');
    
    try {
        submitButton.disabled = true;
        submitButton.textContent = 'Inscription en cours...';
        messageDiv.className = 'hidden';
        
        // Utilisation du chemin absolu
        const response = await fetch('/config/mail.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}`
        });
        
        console.log('Réponse du serveur:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Réponse d\'erreur:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Données reçues:', data);
        
        if (data.success) {
            messageDiv.className = 'bg-green-100 text-green-700 p-2 rounded';
            messageDiv.textContent = data.message;
            form.reset();
        } else {
            messageDiv.className = 'bg-red-100 text-red-700 p-2 rounded';
            messageDiv.textContent = data.error || 'Une erreur est survenue lors de l\'inscription à la newsletter';
        }
        messageDiv.classList.remove('hidden');
        
    } catch (error) {
        console.error('Erreur détaillée:', error);
        messageDiv.className = 'bg-red-100 text-red-700 p-2 rounded';
        messageDiv.textContent = 'Une erreur est survenue lors de l\'inscription à la newsletter. Veuillez réessayer plus tard.';
        messageDiv.classList.remove('hidden');
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'S\'inscrire';
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>