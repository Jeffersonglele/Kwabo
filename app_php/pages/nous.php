<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qui sommes-nous</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #008000;
            --primary-dark: #006400;
            --primary-light: #e6f7e6;
            --secondary-color: #f8a51b;
            --secondary-dark: #e69500;
            --dark-color: #333;
            --light-color: #f9f9f9;
            --gray-light: #e9e9e9;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 5px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            --border-radius: 12px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: var(--dark-color);
            background-color: #fafafa;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 120px 20px 100px;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==');
            opacity: 0.6;
        }
        
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }
        
        h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            position: relative;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            animation: fadeInDown 1s ease both;
        }
        
        header p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 30px;
            position: relative;
            opacity: 0.9;
            animation: fadeIn 1.5s ease both 0.3s;
        }
        
        h2 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin: 60px 0 40px;
            position: relative;
            display: inline-block;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 80px;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            border-radius: 3px;
        }
        
        section {
            background-color: white;
            padding: 50px;
            margin-bottom: 50px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, var(--primary-light), transparent);
            border-radius: 0 0 0 100%;
            z-index: 0;
        }
        
        section:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        
        .intro-text {
            font-size: 1.2rem;
            margin-bottom: 40px;
            color: #555;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        
        .team-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: center;
            margin-top: 50px;
        }
        
        .team-member {
            flex: 1 1 400px;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 40px 30px;
            background-color: white;
            border-radius: var(--border-radius);
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            position: relative;
            z-index: 1;
            border: 1px solid var(--gray-light);
        }
        
        .team-member:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: var(--secondary-color);
        }
        
        .member-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 8px solid white;
            box-shadow: var(--shadow-md);
            margin-bottom: 25px;
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }
        
        .team-member:hover .member-photo {
            border-color: var(--secondary-color);
            transform: scale(1.05);
        }
        
        .member-info h3 {
            color: var(--primary-color);
            font-size: 1.6rem;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .member-info h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background-color: var(--secondary-color);
        }
        
        .member-info strong {
            color: var(--secondary-dark);
            display: block;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .member-info p {
            color: #666;
            margin-bottom: 25px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary-color);
            transition: var(--transition);
        }
        
        .social-links a:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-3px);
        }
        
        .mission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .mission-card {
            background-color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border-left: 5px solid var(--primary-color);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .mission-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 128, 0, 0.03), transparent);
            z-index: -1;
        }
        
        .mission-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-left-color: var(--secondary-color);
        }
        
        .mission-card h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 1.4rem;
        }
        
        .mission-card h3 i {
            margin-right: 15px;
            color: var(--secondary-color);
            font-size: 1.8rem;
        }
        
        .project-details {
            background: linear-gradient(135deg, var(--primary-light), white);
            position: relative;
            overflow: hidden;
        }
        
        .project-details::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgMjAwIj48cGF0aCBkPSJNNDAgMTYwTDE2MCA0MEwxNjAgMTYwWiIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJyZ2JhKDAsMTI4LDAsMC4wNSkiIHN0cm9rZS13aWR0aD0iMiIvPjwvc3ZnPg==') no-repeat;
            opacity: 0.5;
            z-index: 0;
        }
        
        .project-details ul {
            list-style-type: none;
            padding-left: 0;
            position: relative;
            z-index: 1;
        }
        
        .project-details li {
            margin-bottom: 20px;
            position: relative;
            padding-left: 40px;
            font-size: 1.1rem;
        }
        
        .project-details li::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: white;
            background-color: var(--secondary-color);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            left: 0;
            top: 2px;
        }
        
        .vision-text {
            font-size: 1.2rem;
            line-height: 1.9;
            position: relative;
            padding: 30px 40px;
            background-color: var(--primary-light);
            border-radius: var(--border-radius);
            border-left: 5px solid var(--secondary-color);
        }
        
        .vision-text::before {
            content: '\f06e';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--secondary-color);
            font-size: 3rem;
            position: absolute;
            left: 10px;
            top: 10px;
            opacity: 0.2;
            z-index: 0;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        @media (max-width: 992px) {
            header {
                padding: 100px 20px 80px;
                clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
            }
            
            h1 {
                font-size: 2.8rem;
            }
            
            h2 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            header {
                padding: 80px 20px 60px;
            }
            
            h1 {
                font-size: 2.3rem;
            }
            
            header p {
                font-size: 1.1rem;
            }
            
            h2 {
                font-size: 1.8rem;
                margin: 40px 0 30px;
            }
            
            section {
                padding: 30px;
            }
            
            .team-member {
                padding: 30px 20px;
            }
            
            .member-photo {
                width: 160px;
                height: 160px;
            }
        }
        
        @media (max-width: 576px) {
            header {
                clip-path: polygon(0 0, 100% 0, 100% 97%, 0 100%);
            }
            
            h1 {
                font-size: 2rem;
            }
            
            h2 {
                font-size: 1.6rem;
            }
            
            .mission-grid {
                grid-template-columns: 1fr;
            }
            
            .vision-text {
                padding: 20px;
                font-size: 1.1rem;
            }
        }

        /* Corrections pour la navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        body {
            padding-top: 70px; /* Compensation pour la navbar fixe */
        }

        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/navbar.php'); ?>
    
    <header>
        <div class="container">
            <h1>Qui sommes-nous</h1>
            <p>Découvrez l'équipe passionnée derrière KWABO et notre engagement pour promouvoir les richesses touristiques du Bénin</p>
        </div>
    </header>

    <div class="container">
        <section>
            <h2>Notre Équipe</h2>
            <p class="intro-text">Nous sommes AKADI Madina et GLELE Jefferson, deux étudiants en Licence 2 Informatique de Gestion, unis par notre passion pour le développement web et le tourisme béninois.</p>
            
            <div class="team-container">
                <div class="team-member">
                    <img src="../assets/images/photo-madina.jpeg" alt="AKADI Madina" class="member-photo">
                    <div class="member-info">
                        <h3>AKADI Madina</h3>
                        <strong>Développeur Full Stack</strong>
                        <p> Passionné par l'expérience utilisateur et la mise en valeur du patrimoine culturel à travers le numérique, son approche méthodique et son intérêt pour l'écotourisme ont été essentiels dans la conception de cette plateforme.</p>
                        <div class="social-links">
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                            <a href="https://github.com/madina-akd" aria-label="GitHub"><i class="fab fa-github"></i></a>
                            <a href="#" aria-label="Portfolio"><i class="fas fa-globe"></i></a>
                        </div>
                    </div>
                </div>

                <div class="team-member">
                    <img src="../assets/images/Me.jpg" alt="GLELE Jefferson" class="member-photo">
                    <div class="member-info">
                        <h3>GLELE Jefferson</h3>
                        <strong>Développeur Full Stack</strong>
                        <p> Passionné par l'expérience utilisateur et la mise en valeur du patrimoine culturel à travers le numérique, son approche méthodique et son intérêt pour l'écotourisme ont été essentiels dans la conception de cette plateforme.</p>
                        <div class="social-links">
                            <a href="https://www.linkedin.com/in/jefferson-glele-0855a0372?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=ios_app" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                            <a href="https://github.com/Jeffersonglele" aria-label="GitHub"><i class="fab fa-github"></i></a>
                            <a href="https://jeffersonsite.addpotion.com/" aria-label="Portfolio"><i class="fas fa-globe"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="project-details">
            <h2>Notre Projet</h2>
            <p>Ce site a été développé dans le cadre de notre stage académique de Licence 2, avec pour objectifs :</p>
            <ul>
                <li>Promouvoir les richesses touristiques du Bénin de manière innovante</li>
                <li>Créer une plateforme complète pour les touristes nationaux et internationaux</li>
                <li>Mettre en pratique nos compétences en développement web full-stack</li>
                <li>Contribuer au développement du secteur touristique par des solutions digitales</li>
                <li>Offrir une vitrine moderne pour les professionnels du tourisme béninois</li>
            </ul>
        </section>

        <section>
            <h2>Nos Engagements</h2>
            
            <div class="mission-grid">
                <div class="mission-card">
                    <h3><i class="fas fa-landmark"></i> Valorisation du patrimoine</h3>
                    <p>Nous nous engageons à mettre en lumière les sites touristiques méconnus du Bénin à travers une plateforme digitale moderne et accessible.</p>
                </div>
                
                <div class="mission-card">
                    <h3><i class="fas fa-laptop-code"></i> Innovation technologique</h3>
                    <p>Nous appliquons les dernières technologies web pour offrir une expérience utilisateur fluide et intuitive.</p>
                </div>
                
                <div class="mission-card">
                    <h3><i class="fas fa-globe-africa"></i> Promotion locale</h3>
                    <p>Notre priorité est de soutenir l'économie locale en mettant en avant les acteurs du tourisme béninois.</p>
                </div>
                
                <div class="mission-card">
                    <h3><i class="fas fa-graduation-cap"></i> Formation continue</h3>
                    <p>Nous nous formons continuellement pour améliorer cette plateforme et proposer des fonctionnalités innovantes.</p>
                </div>
            </div>
        </section>

        <section>
            <h2>Notre Vision</h2>
            <div class="vision-text">
                <p>Nous croyons fermement que le numérique peut révolutionner l'expérience touristique au Bénin. Notre ambition est de positionner le Bénin comme une destination phare du tourisme ouest-africain grâce à des solutions digitales innovantes qui répondent aux besoins des voyageurs modernes tout en préservant l'authenticité de nos richesses culturelles et naturelles.</p>
                <p>À travers cette plateforme, nous souhaitons créer un pont entre la tradition et la modernité, en valorisant notre patrimoine tout en adoptant les technologies les plus récentes pour offrir une expérience utilisateur exceptionnelle.</p>
            </div>
        </section>
    </div>

    <script>
        // Animation au défilement améliorée
        document.addEventListener('DOMContentLoaded', function() {
            const animateOnScroll = (elements, animation) => {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add(animation);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });
                
                elements.forEach(element => {
                    element.style.opacity = 0;
                    observer.observe(element);
                });
            };
            
            // Animations pour les sections
            const sections = document.querySelectorAll('section');
            animateOnScroll(sections, 'fade-in-up');
            
            // Animation pour les cartes d'équipe
            const teamMembers = document.querySelectorAll('.team-member');
            teamMembers.forEach((member, index) => {
                member.style.transitionDelay = `${index * 0.1}s`;
                animateOnScroll([member], 'fade-in-up');
            });
            
            // Animation pour les cartes de mission
            const missionCards = document.querySelectorAll('.mission-card');
            missionCards.forEach((card, index) => {
                card.style.transitionDelay = `${index * 0.1}s`;
                animateOnScroll([card], 'fade-in-up');
            });
            
            // Ajout des styles d'animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fade-in-up {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .fade-in-up {
                    animation: fade-in-up 0.8s ease forwards;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>