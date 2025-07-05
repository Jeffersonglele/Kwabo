<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Intro - Patrimoine Béninois</title>
  <link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
  <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon/favicon-96x96.png">
  <link rel="shortcut icon" href="assets/favicon/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #3b82f6;
      --primary-dark: #2563eb;
      --dark: #1e1e2f;
      --darker: #10101a;
      --light: #f8fafc;
      --transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(135deg, var(--dark), var(--darker));
      color: var(--light);
      min-height: 100vh;
      overflow-x: hidden;
      line-height: 1.6;
    }

    .slide-container {
      position: relative;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
    }

    .slide {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      opacity: 0;
      transition: var(--transition);
      z-index: 1;
    }

    .slide.active {
      opacity: 1;
      z-index: 2;
    }

    .slide-content {
      width: 100%;
      max-width: 1200px;
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 3rem;
    }

    .text-box {
      flex: 1;
      padding: 2rem;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(10px);
      border-radius: 1rem;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transform: translateY(20px);
      opacity: 0;
      transition: var(--transition);
      transition-delay: 0.3s;
    }

    .slide.active .text-box {
      transform: translateY(0);
      opacity: 1;
    }

    .text-box h2 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 4vw, 3rem);
      margin-bottom: 1.5rem;
      color: var(--primary);
      font-weight: 700;
      line-height: 1.2;
    }

    .text-box p {
      font-size: clamp(1rem, 1.2vw, 1.2rem);
      white-space: pre-line;
    }

    .image-box {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      transform: scale(0.95);
      opacity: 0;
      transition: var(--transition);
      transition-delay: 0.1s;
    }

    .slide.active .image-box {
      transform: scale(1);
      opacity: 1;
    }

    .image-box img {
      max-width: 100%;
      max-height: 70vh;
      border-radius: 0.75rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
      object-fit: contain;
    }

    .thumbnails {
      position: fixed;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 0.75rem;
      z-index: 10;
    }

    .thumbnail {
      width: 80px;
      height: 50px;
      border-radius: 0.5rem;
      overflow: hidden;
      cursor: pointer;
      opacity: 0.7;
      transition: var(--transition);
      border: 2px solid transparent;
    }

    .thumbnail:hover {
      opacity: 1;
      transform: translateY(-5px);
    }

    .thumbnail.active {
      opacity: 1;
      border-color: var(--primary);
      transform: translateY(-5px);
    }

    .thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .nav-buttons {
      position: fixed;
      top: 50%;
      width: 100%;
      display: flex;
      justify-content: space-between;
      padding: 0 2rem;
      z-index: 10;
      pointer-events: none;
    }

    .nav-button {
      background: rgba(0, 0, 0, 0.5);
      color: white;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      pointer-events: all;
      border: none;
      font-size: 1.5rem;
    }

    .nav-button:hover {
      background: var(--primary);
      transform: scale(1.1);
    }

    .skip-button {
      position: fixed;
      top: 1.5rem;
      right: 1.5rem;
      background: rgba(0, 0, 0, 0.5);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 2rem;
      border: none;
      cursor: pointer;
      transition: var(--transition);
      z-index: 100;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .skip-button:hover {
      background: var(--primary);
      transform: translateY(-2px);
    }

    .progress-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: rgba(255, 255, 255, 0.1);
      z-index: 100;
    }

    .progress {
      height: 100%;
      background: var(--primary);
      width: 0%;
      transition: width linear;
    }

    /* Mobile styles */
    @media (max-width: 768px) {
      .slide-content {
        flex-direction: column;
        gap: 1.5rem;
        padding: 1rem;
      }

      .text-box {
        order: 2;
        padding: 1.5rem;
      }

      .image-box {
        order: 1;
        max-height: 40vh;
      }

      .image-box img {
        max-height: 40vh;
      }

      .thumbnails {
        bottom: 1rem;
        gap: 0.5rem;
      }

      .thumbnail {
        width: 60px;
        height: 40px;
      }

      .nav-buttons {
        padding: 0 1rem;
      }

      .nav-button {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }

      .text-box h2 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
      }

      .text-box p {
        font-size: 1rem;
      }
    }

    /* Animation for image */
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .slide.active .image-box img {
      animation: float 6s ease-in-out infinite;
    }
  </style>
</head>
<body>
  <div class="progress-bar">
    <div class="progress" id="progress"></div>
  </div>

  <div class="slide-container" id="slides-container">
    <!-- Slides will be inserted here by JavaScript -->
  </div>

  <div class="nav-buttons">
    <button class="nav-button" id="prev-btn">←</button>
    <button class="nav-button" id="next-btn">→</button>
  </div>

  <div class="thumbnails" id="thumbnails-container"></div>

  <button class="skip-button" id="skip-btn">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
      <path d="M12.5 4a.5.5 0 0 0-1 0v3.248L5.233 3.612C4.693 3.3 4 3.678 4 4.308v7.384c0 .63.692 1.01 1.233.697L11.5 8.753V12a.5.5 0 0 0 1 0V4z"/>
    </svg>
    Passer
  </button>

  <script>
    const slidesData = [
      {
        image: 'assets/images/1s.jpg',
        title: 'Restitution des objets royaux',
        description: 'Retour des trésors culturels au Bénin, symbole de la réappropriation de notre patrimoine.'
      },
      {
        image: 'assets/images/2s.jpg',
        title: 'Yves Appolinaire Pèdé',
        description: 'Artiste béninois (1959-2019) dont l\'œuvre perpétue la tradition de l\'art de cour du Danxomè, notamment l\'appliqué sur toile rendu célèbre par la famille Yèmadjè.'
      },
      {
        image: 'assets/images/5s.jpg',
        title: 'Euloge Ahanhanzo-Glèlè',
        description: 'Sculptures en terre cuite inspirées de l\'histoire du Bénin, hommage vibrant aux bâtisseurs de notre héritage culturel.'
      },
      {
        image: 'assets/images/6s.jpg',
        title: 'Œuvres d\'art en métal',
        description: 'Objets historiques pillés lors de la période coloniale, aujourd\'hui témoins de notre résilience culturelle.'
      },
      {
        image: 'assets/images/7s.jpg',
        title: 'François AZIANGUÉ',
        description: 'Artiste togolais (né en 1982) dont les sculptures métalliques rendent hommage aux femmes africaines, avec un clin d\'œil aux Demoiselles d\'Abomey.'
      },
      {
        image: 'assets/images/9.jpg',
        title: 'Le siège du roi Béhanzin',
        description: 'Symbole de résistance et d\'autorité, ce trône majestueux incarne la puissance du royaume du Dahomey.\n\nFabriqué en bois sculpté, il présente des motifs complexes de guerriers et d\'animaux, témoignant du savoir-faire artisanal.\n\nBéhanzin, dernier roi du Dahomey (1889-1894), s\'opposa farouchement à la colonisation française.'
      }
    ];

    // Variables
    let currentSlide = 0;
    let slideInterval;
    const slideDuration = 8000; // 8 seconds per slide
    let startTime;
    let progressInterval;

    // DOM Elements
    const slidesContainer = document.getElementById('slides-container');
    const thumbnailsContainer = document.getElementById('thumbnails-container');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const skipBtn = document.getElementById('skip-btn');
    const progressBar = document.getElementById('progress');

    // Initialize slides
    function initSlides() {
      slidesData.forEach((slide, index) => {
        // Create slide
        const slideEl = document.createElement('div');
        slideEl.className = `slide ${index === 0 ? 'active' : ''}`;
        
        const slideContent = document.createElement('div');
        slideContent.className = 'slide-content';
        
        const textBox = document.createElement('div');
        textBox.className = 'text-box';
        textBox.innerHTML = `<h2>${slide.title}</h2><p>${slide.description}</p>`;
        
        const imageBox = document.createElement('div');
        imageBox.className = 'image-box';
        imageBox.innerHTML = `<img src="${slide.image}" alt="${slide.title}" loading="lazy">`;
        
        slideContent.appendChild(textBox);
        slideContent.appendChild(imageBox);
        slideEl.appendChild(slideContent);
        slidesContainer.appendChild(slideEl);
        
        // Create thumbnail
        const thumb = document.createElement('div');
        thumb.className = `thumbnail ${index === 0 ? 'active' : ''}`;
        thumb.innerHTML = `<img src="${slide.image}" alt="${slide.title}" loading="lazy">`;
        thumb.addEventListener('click', () => goToSlide(index));
        thumbnailsContainer.appendChild(thumb);
      });
    }

    // Go to specific slide
    function goToSlide(index) {
      // Reset progress bar
      resetProgress();
      
      // Update current slide
      currentSlide = (index + slidesData.length) % slidesData.length;
      
      // Update active classes
      document.querySelectorAll('.slide').forEach((slide, i) => {
        slide.classList.toggle('active', i === currentSlide);
      });
      
      document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === currentSlide);
      });
      
      // Restart auto-advance
      startAutoAdvance();
    }

    // Next slide
    function nextSlide() {
      goToSlide(currentSlide + 1);
    }

    // Previous slide
    function prevSlide() {
      goToSlide(currentSlide - 1);
    }

    // Start auto-advancing
    function startAutoAdvance() {
      clearInterval(slideInterval);
      slideInterval = setInterval(nextSlide, slideDuration);
      startProgress();
    }

    // Progress bar animation
    function startProgress() {
      clearInterval(progressInterval);
      startTime = Date.now();
      progressBar.style.width = '0%';
      
      progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const progress = Math.min((elapsed / slideDuration) * 100, 100);
        progressBar.style.width = `${progress}%`;
        
        if (progress >= 100) {
          clearInterval(progressInterval);
        }
      }, 50);
    }

    function resetProgress() {
      clearInterval(progressInterval);
      progressBar.style.width = '0%';
    }

    // Skip intro
    function skipIntro() {
      window.location.href = 'main.php';
    }

    // Event listeners
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);
    skipBtn.addEventListener('click', skipIntro);

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') prevSlide();
      if (e.key === 'ArrowRight') nextSlide();
      if (e.key === 'Escape') skipIntro();
    });

    // Initialize
    initSlides();
    startAutoAdvance();

    // Pause on hover
    slidesContainer.addEventListener('mouseenter', () => {
      clearInterval(slideInterval);
      clearInterval(progressInterval);
    });

    slidesContainer.addEventListener('mouseleave', () => {
      startAutoAdvance();
    });
  </script>
</body>
</html>