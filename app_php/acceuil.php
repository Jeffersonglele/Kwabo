<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KWABO - Guide Virtuel du Bénin</title>
  <link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
  <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon/favicon-96x96.png">
  <link rel="shortcut icon" href="assets/favicon/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #008080;
      --secondary-color: #f0f8ff;
      --text-color: #333;
      --text-light: #666;
      --bg-color: #ffffff;
      --transition-speed: 0.5s;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-color);
      line-height: 1.6;
      height: 100vh;
      overflow: hidden;
      position: relative;
    }

    .navbar {
      padding: 1.5rem;
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 100;
      display: flex;
      justify-content: space-between;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(5px);
    }

    .logo img {
      height: 40px;
      transition: transform 0.3s;
    }

    .logo img:hover {
      transform: scale(1.05);
    }

    .spline-container {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
    }

    .spline-container iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    .ai-presentation {
      position: absolute;
      bottom: 10%;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      max-width: 800px;
      text-align: center;
      z-index: 2;
    }

    .ai-content {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 2rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      animation: fadeInUp 1s ease-out;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .kwabo-title {
      font-size: clamp(1.5rem, 4vw, 2.5rem);
      color: var(--primary-color);
      margin-bottom: 1rem;
      font-weight: 700;
    }

    .kwabo-message {
      font-size: clamp(1rem, 2vw, 1.2rem);
      margin-bottom: 2rem;
      color: var(--text-color);
    }

    .cta-button {
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: 30px;
      padding: 0.8rem 2rem;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(0, 128, 128, 0.3);
    }

    .cta-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 128, 128, 0.4);
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translate(-50%, 20px);
      }
      to {
        opacity: 1;
        transform: translate(-50%, 0);
      }
    }

    @media (max-width: 768px) {
      .ai-presentation {
        bottom: 5%;
        width: 95%;
      }
      
      .ai-content {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <!-- <nav class="navbar">
    <div class="logo">
      <img src="assets/images/BT.png" alt="Bénin Tourisme">
    </div>
  </nav> -->

  <div class="spline-container">
    <iframe src='https://my.spline.design/voiceinteractionanimation-p4vbIo9xpGoMlZB6rTOukIHI/' frameborder='0'></iframe>
  </div>

  <div class="ai-presentation">
    <div class="ai-content">
      <h1 class="kwabo-title">Bonjour, je m'appelle KWABO</h1>
      <p class="kwabo-message">
        Je suis votre guide virtuel pour découvrir les trésors du Bénin, 
        terre de culture, d'histoire et de paysages à couper le souffle.
      </p>
      <button class="cta-button" id="discover-btn">Découvrir le Bénin</button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const discoverBtn = document.getElementById('discover-btn');
      
      discoverBtn.addEventListener('click', function() {
        // Animation au clic
        this.textContent = "C'est parti !";
        this.style.backgroundColor = "#006666";
        
        // Redirection après un léger délai
        setTimeout(() => {
          window.location.href = "index.php"; 
        }, 800);
      });
    });
  </script>
</body>
</html>