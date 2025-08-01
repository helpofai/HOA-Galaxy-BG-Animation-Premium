<div class="galaxy-bg" id="galaxy" style="z-index: <?php echo esc_attr($settings['z_index']); ?>"></div>

<style>
.galaxy-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    min-height: -webkit-fill-available;
    background: transparent;
    overflow: hidden;
    pointer-events: none;
}

.star {
    position: absolute;
    border-radius: 50%;
    animation: twinkle 5s infinite ease-in-out;
    background: var(--star-color, #fff);
    opacity: var(--opacity, 0.8);
    z-index: 1;
}

@keyframes twinkle {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: var(--opacity, 0.8); transform: scale(1.2); }
}

.shooting-star {
    position: absolute;
    border-radius: 50%;
    animation: shoot 3s linear infinite;
    background: var(--shooting-color, #fff);
    opacity: 0;
    z-index: 2;
    box-shadow: 0 0 6px 2px var(--shooting-color, #fff);
}

@keyframes shoot {
    0% {
        transform: translateX(0) translateY(0) rotate(45deg);
        opacity: 1;
    }
    70% {
        opacity: 1;
    }
    100% {
        transform: translateX(100vw) translateY(-100vh) rotate(45deg);
        opacity: 0;
    }
}

@media (max-width: 768px) {
    .galaxy-bg {
        height: 100vh;
        height: -webkit-fill-available;
    }
    .star {
        animation: twinkle-mobile 5s infinite ease-in-out;
    }
    @keyframes twinkle-mobile {
        0%, 100% { opacity: 0.2; transform: scale(1); }
        50% { opacity: var(--opacity, 0.6); transform: scale(1.1); }
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const galaxy = document.getElementById('galaxy');
    const settings = <?php echo json_encode($settings); ?>;
    
    // Create stars
    const starFragment = document.createDocumentFragment();
    for (let i = 0; i < settings.star_count; i++) {
        const star = document.createElement('div');
        star.classList.add('star');
        
        const x = Math.random() * 100;
        const y = Math.random() * 100;
        const size = Math.random() * (settings.star_size_max - settings.star_size_min) + settings.star_size_min;
        const colors = settings.star_colors.split(',');
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
        const colors = settings.shooting_colors.split(',');
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
</script>
