document.addEventListener('DOMContentLoaded', function() {
    const galaxy = document.getElementById('galaxy');
    const settings = hoaGalaxySettings;

    // Set background
    if (settings.background_type === 'gradient') {
        galaxy.style.backgroundImage = `linear-gradient(${settings.background_gradient_direction}, ${settings.background_color_1}, ${settings.background_color_2})`;
    } else {
        galaxy.style.backgroundColor = settings.background_color_1;
    }
    
    // Create stars
    const starFragment = document.createDocumentFragment();
    for (let i = 0; i < settings.star_count; i++) {
        const star = document.createElement('div');
        star.classList.add('star');
        
        const x = Math.random() * 100;
        const y = Math.random() * 100;
        const size = Math.random() * (settings.star_size_max - settings.star_size_min) + settings.star_size_min;
        const colors = settings.star_colors;
        const color = colors[Math.floor(Math.random() * colors.length)];
        const delay = Math.random() * 5;
        
        star.style.left = `${x}%`;
        star.style.top = `${y}%`;
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;
        star.style.setProperty('--star-color', color);
        star.style.setProperty('--opacity', settings.star_opacity);
        star.style.animationDelay = `${delay}s`;
        
        starFragment.appendChild(star);
    }
    
    // Create shooting stars
    const shootingFragment = document.createDocumentFragment();
    for (let i = 0; i < settings.shooting_count; i++) {
        const shootingStar = document.createElement('div');
        shootingStar.classList.add('shooting-star');
        
        const x = Math.random() * 100;
        const y = Math.random() * 100;
        const colors = settings.shooting_colors;
        const color = colors[Math.floor(Math.random() * colors.length)];
        const delay = Math.random() * 15;
        const duration = Math.random() * 2 + 1;
        const size = settings.shooting_size;
        
        shootingStar.style.left = `${x}%`;
        shootingStar.style.top = `${y}%`;
        shootingStar.style.width = `${size}px`;
        shootingStar.style.height = `${size}px`;
        shootingStar.style.setProperty('--shooting-color', color);
        shootingStar.style.animationDelay = `${delay}s`;
        shootingStar.style.animationDuration = `${duration}s`;
        
        shootingFragment.appendChild(shootingStar);
    }
    
    galaxy.appendChild(starFragment);
    galaxy.appendChild(shootingFragment);
    
    function resizeGalaxy() {
        galaxy.style.height = window.innerHeight + 'px';
    }
    
    resizeGalaxy();
    window.addEventListener('resize', resizeGalaxy);
    window.addEventListener('orientationchange', resizeGalaxy);
});