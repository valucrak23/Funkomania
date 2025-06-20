document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('starfield');
    if (!canvas) {
        console.error('No se encontró el elemento canvas con id "starfield".');
        return;
    }
    
    const ctx = canvas.getContext('2d');

    function setCanvasSize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    setCanvasSize();

    window.addEventListener('resize', setCanvasSize);

    const numStars = 500;
    let stars = [];

    function createStars() {
        stars = [];
        for (let i = 0; i < numStars; i++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                radius: Math.random() * 1.5,
                alpha: Math.random(),
                speed: Math.random() * 0.2 + 0.1
            });
        }
    }
    createStars();
    window.addEventListener('resize', createStars);

    function drawStars() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        for (const star of stars) {
            ctx.beginPath();
            ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255, 255, 255, ${star.alpha})`;
            ctx.fill();
        }
    }

    function updateStars() {
        for (const star of stars) {
            star.y -= star.speed;
            if (star.y < 0) {
                star.y = canvas.height;
                star.x = Math.random() * canvas.width;
            }
        }
    }

    function animate() {
        drawStars();
        updateStars();
        requestAnimationFrame(animate);
    }

    animate();
}); 