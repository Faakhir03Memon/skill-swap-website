/**
 * SkillSwap High-Level Animations
 * Powered by Three.js & GSAP
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- GSAP CONFIG ---
    gsap.registerPlugin(ScrollTrigger);

    // --- THREE.JS BACKGROUND ---
    const initThreeBackground = () => {
        const container = document.getElementById('canvas-container');
        if (!container) return;

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        container.appendChild(renderer.domElement);

        // Particles
        const particlesGeometry = new THREE.BufferGeometry();
        const count = 3000;
        const positions = new Float32Array(count * 3);
        const colors = new Float32Array(count * 3);

        for(let i = 0; i < count * 3; i++) {
            positions[i] = (Math.random() - 0.5) * 15;
            colors[i] = Math.random();
        }

        particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        particlesGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

        const particlesMaterial = new THREE.PointsMaterial({
            size: 0.02,
            sizeAttenuation: true,
            transparent: true,
            alphaTest: 0.001,
            blending: THREE.AdditiveBlending,
            vertexColors: true
        });

        const particles = new THREE.Points(particlesGeometry, particlesMaterial);
        scene.add(particles);

        camera.position.z = 3;

        // Mouse Interaction
        let mouseX = 0;
        let mouseY = 0;
        window.addEventListener('mousemove', (e) => {
            mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
            mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
        });

        // Animation Loop
        const clock = new THREE.Clock();

        const animate = () => {
            const elapsedTime = clock.getElapsedTime();

            particles.rotation.y = elapsedTime * 0.05;
            particles.rotation.x = -mouseY * 0.1;
            particles.rotation.y += mouseX * 0.1;

            renderer.render(scene, camera);
            requestAnimationFrame(animate);
        };

        animate();

        // Resize
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    };

    initThreeBackground();

    // --- GSAP ENTRANCE ANIMATIONS ---
    const tl = gsap.timeline();

    tl.from('.logo', {
        y: -20,
        opacity: 0,
        duration: 1,
        ease: 'power4.out'
    })
    .from('.nav-links li', {
        y: -20,
        opacity: 0,
        duration: 0.8,
        stagger: 0.1,
        ease: 'power4.out'
    }, '-=0.8')
    .from('.hero h1', {
        y: 60,
        opacity: 0,
        duration: 1.2,
        ease: 'power4.out'
    }, '-=0.5')
    .from('.hero p', {
        y: 30,
        opacity: 0,
        duration: 1,
        ease: 'power3.out'
    }, '-=0.8')
    .from('.hero-btns', {
        y: 20,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out'
    }, '-=0.6');

    // --- SCROLL TRIGGER REVEALS ---
    const revealElements = document.querySelectorAll('.reveal');
    revealElements.forEach((el) => {
        gsap.from(el, {
            scrollTrigger: {
                trigger: el,
                start: 'top 85%',
                toggleActions: 'play none none reverse'
            },
            y: 50,
            opacity: 0,
            duration: 1,
            ease: 'power3.out'
        });
    });

    // Parallax effect for cards
    gsap.to('.card', {
        scrollTrigger: {
            trigger: '#features',
            start: 'top bottom',
            end: 'bottom top',
            scrub: 1
        },
        y: (i, target) => i * 20,
        ease: 'none'
    });

    // --- MAGNETIC BUTTONS ---
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            gsap.to(btn, {
                x: x * 0.3,
                y: y * 0.3,
                duration: 0.3,
                ease: 'power2.out'
            });
        });

        btn.addEventListener('mouseleave', () => {
            gsap.to(btn, {
                x: 0,
                y: 0,
                duration: 0.5,
                ease: 'elastic.out(1, 0.3)'
            });
        });
    });

    // --- CUSTOM CURSOR (Optional but requested "high level") ---
    const cursor = document.createElement('div');
    cursor.className = 'custom-cursor';
    document.body.appendChild(cursor);

    const cursorInner = document.createElement('div');
    cursorInner.className = 'custom-cursor-inner';
    document.body.appendChild(cursorInner);

    // CSS for cursor
    const style = document.createElement('style');
    style.innerHTML = `
        .custom-cursor {
            width: 40px;
            height: 40px;
            border: 1px solid var(--primary-bright);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease-out;
            transform: translate(-50%, -50%);
        }
        .custom-cursor-inner {
            width: 8px;
            height: 8px;
            background: var(--primary-bright);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
        }
        @media (max-width: 768px) {
            .custom-cursor, .custom-cursor-inner { display: none; }
        }
    `;
    document.head.appendChild(style);

    window.addEventListener('mousemove', (e) => {
        gsap.to(cursor, {
            x: e.clientX,
            y: e.clientY,
            duration: 0.2
        });
        gsap.to(cursorInner, {
            x: e.clientX,
            y: e.clientY,
            duration: 0.05
        });
    });

    document.querySelectorAll('a, button, .card').forEach(el => {
        el.addEventListener('mouseenter', () => {
            gsap.to(cursor, { scale: 1.5, borderColor: 'var(--secondary)', duration: 0.3 });
        });
        el.addEventListener('mouseleave', () => {
            gsap.to(cursor, { scale: 1, borderColor: 'var(--primary-bright)', duration: 0.3 });
        });
    });
});
