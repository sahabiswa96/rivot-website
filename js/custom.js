const totalFrames = 120;
const sets = [
    { folder: "grayscooty", thumbId: "grayThumb" },
    { folder: "grayscootywithlight", thumbId: "grayLightThumb" },
    { folder: "greenscooty", thumbId: "greenThumb" },
    { folder: "greenscootywithlight", thumbId: "greenLightThumb" }
];

let currentSetIndex = 0;
let currentFrame = 1;
let isDragging = false;
let lastX = 0;
const dragSpeed = 0.25; // 🔥 control rotation speed (higher = faster)

const image = document.getElementById("sequenceImage");
const thumbs = sets.map(s => document.getElementById(s.thumbId));
const progressBar = document.getElementById("progressFill");
const progressText = document.getElementById("progressText");
const loaderOverlay = document.getElementById("loaderOverlay");
const viewer = document.getElementById("viewer");

function formatFrame(num) {
    return String(num).padStart(4, "0");
}

function updateImage(frame, setIndex = currentSetIndex) {
    image.src = `${sets[setIndex].folder}/${formatFrame(frame)}.webp`;
}

function updateThumbHighlight() {
    thumbs.forEach((thumb, i) => {
        thumb.classList.toggle("active", i === currentSetIndex);
    });
}

if (window.innerWidth > 768) {
    // Desktop
    image.addEventListener("mousedown", (e) => {
        isDragging = true;
        lastX = e.clientX;
        image.style.cursor = "grabbing"; // 🖐️ hand while dragging
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
        image.style.cursor = "ew-resize"; // ↔️ back to resize cursor
    });

    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        const deltaX = e.clientX - lastX;
        const frameChange = Math.round(deltaX * dragSpeed);

        if (frameChange !== 0) {
            currentFrame = (currentFrame + frameChange) % totalFrames;
            if (currentFrame <= 0) currentFrame += totalFrames;
            updateImage(currentFrame);
            lastX = e.clientX;
        }
    });

    // Mobile Touch
    image.addEventListener("touchstart", (e) => {
        isDragging = true;
        lastX = e.touches[0].clientX;
    });

    document.addEventListener("touchend", () => {
        isDragging = false;
    });

    document.addEventListener("touchmove", (e) => {
        if (!isDragging) return;
        const deltaX = e.touches[0].clientX - lastX;
        const frameChange = Math.round(deltaX * dragSpeed);

        if (frameChange !== 0) {
            currentFrame = (currentFrame + frameChange) % totalFrames;
            if (currentFrame <= 0) currentFrame += totalFrames;
            updateImage(currentFrame);
            lastX = e.touches[0].clientX;
        }
    });
}

// Prevent dragging image ghost
image.addEventListener("dragstart", (e) => e.preventDefault());

// Thumbnail click to switch sets
thumbs.forEach((thumb, i) => {
    thumb.addEventListener("click", () => {
        currentSetIndex = i;
        updateThumbHighlight();
        updateImage(currentFrame);
    });
});

// Preload all images
async function preloadImages() {
    let loaded = 0;
    const urls = sets.flatMap(set =>
        Array.from({ length: totalFrames }, (_, i) => `${set.folder}/${formatFrame(i + 1)}.webp`)
    );
    await Promise.all(urls.map(url =>
        new Promise((resolve) => {
            const img = new Image();
            img.src = url;
            img.onload = img.onerror = () => {
                loaded++;
                const percent = (loaded / urls.length) * 100;
                progressBar.style.width = `${percent}%`;
                progressText.textContent = `Loading... ${Math.round(percent)}%`;
                resolve();
            };
        })
    ));
}

// After preload, show viewer
preloadImages().then(() => {
    loaderOverlay.style.display = "none";
    viewer.style.display = "flex";
    updateThumbHighlight();
    updateImage(currentFrame);
});

function createScrollAnimation(
    canvasId,
    imagePathPrefix,
    frameCount,
    sectionElement
) {
    const canvas = document.getElementById(canvasId);
    const context = canvas.getContext("2d", { alpha: true }); // Enable alpha for overlay effect
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight * 3;

    // Increase base scale for larger images
    const BASE_SCALE = 2.0; // Increased from 1.5 to 2.0
    const OVERLAP_AMOUNT = 300; // How many pixels the image can overlap into left panel

    const images = [];
    let imagesLoaded = 0;
    let ready = false;
    let lastFrameIndex = 1;
    let currentProgress = 0;
    let targetProgress = 0;

    // Preload images
    for (let i = 1; i <= frameCount; i++) {
        const img = new Image();
        img.src = `${imagePathPrefix}/${String(i).padStart(4, "0")}.webp`;
        img.onload = () => {
            imagesLoaded++;
            if (imagesLoaded === frameCount) {
                ready = true;
                drawImage(1);
            }
        };
        images.push(img);
    }

    function drawImage(frameNumber) {
        const img = images[frameNumber - 1];
        if (!img || !img.complete) return;

        const canvasRatio = canvas.width / canvas.height;
        const imgRatio = img.width / img.height;

        let drawWidth, drawHeight;
        
        // Calculate larger base dimensions
        if (imgRatio > canvasRatio) {
            drawWidth = canvas.width * BASE_SCALE;
            drawHeight = (canvas.width / imgRatio) * BASE_SCALE;
        } else {
            drawHeight = canvas.height * BASE_SCALE;
            drawWidth = (canvas.height * imgRatio) * BASE_SCALE;
        }

        // Get scroll progress
        const rect = sectionElement.getBoundingClientRect();
        const scrollProgress = Math.max(0, Math.min(1, 
            (window.innerHeight - rect.top) / (window.innerHeight + rect.height)
        ));

        // Clear the entire canvas
        context.clearRect(0, 0, canvas.width, canvas.height);

        // Calculate position with overlap
        let x = (canvas.width - drawWidth) / 2;
        const y = (canvas.height - drawHeight) / 2;

        // Add horizontal movement based on scroll
        const moveAmount = OVERLAP_AMOUNT * scrollProgress;
        x -= moveAmount; // Move left as user scrolls

        // Add scale effect based on scroll
        const scaleIncrease = 0.3; // Maximum additional scale
        const currentScale = BASE_SCALE + (scaleIncrease * scrollProgress);
        
        // Calculate scaled dimensions
        const scaledWidth = drawWidth * (currentScale / BASE_SCALE);
        const scaledHeight = drawHeight * (currentScale / BASE_SCALE);

        // Add perspective effect
        context.save();
        context.translate(canvas.width / 2, canvas.height / 2);
        
        // Add slight rotation based on scroll
        const maxRotation = 5; // degrees
        const rotation = (maxRotation * scrollProgress) * (Math.PI / 180);
        context.rotate(rotation);

        // Draw with enhanced shadow for depth
        context.shadowColor = 'rgba(0, 0, 0, 0.3)';
        context.shadowBlur = 30 * scrollProgress;
        context.shadowOffsetX = -15 * scrollProgress;
        context.shadowOffsetY = 15 * scrollProgress;

        // Draw the image
        context.drawImage(
            img, 
            -scaledWidth / 2 - moveAmount, 
            -scaledHeight / 2, 
            scaledWidth, 
            scaledHeight
        );

        context.restore();
    }

    function updateAnimation() {
        if (!ready) return;

        const rect = sectionElement.getBoundingClientRect();
        const scrollProgress = (window.innerHeight - rect.top) / (window.innerHeight + rect.height);
        
        if (scrollProgress >= 0 && scrollProgress <= 1) {
            const frameIndex = Math.max(1, Math.min(frameCount, 
                Math.round(scrollProgress * frameCount)
            ));
            
            if (frameIndex !== lastFrameIndex) {
                lastFrameIndex = frameIndex;
                drawImage(frameIndex);
            }
        }
    }

    // Add scroll event listener
    window.addEventListener('scroll', () => {
        requestAnimationFrame(updateAnimation);
    });

    // Initial draw
    drawImage(1);

    // Handle resize
    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight * 3;
        drawImage(lastFrameIndex);
    });

    return () => {
        window.removeEventListener('scroll', updateAnimation);
        window.removeEventListener('resize', drawImage);
    };
}

// Main initialization code
if (window.innerWidth > 768) {
    const animationCleanups = [];

    // Create scroll animations with new smooth logic
    animationCleanups.push(
        createScrollAnimation("canvas1", "battery", 120, document.getElementById("section1"))
    );
    animationCleanups.push(
        createScrollAnimation("canvas2", "controler", 120, document.getElementById("section2"))
    );
    animationCleanups.push(
        createScrollAnimation("canvas3", "motor", 120, document.getElementById("section3"))
    );
    animationCleanups.push(
        createScrollAnimation("canvas4", "rim", 120, document.getElementById("section4"))
    );

    const sections = [
        { section: "section1", canvas: "canvas1", title: "title1", desc: "desc1" },
        { section: "section2", canvas: "canvas2", title: "title2", desc: "desc2" },
        { section: "section3", canvas: "canvas3", title: "title3", desc: "desc3" },
        { section: "section4", canvas: "canvas4", title: "title4", desc: "desc4" }
    ];

    let visibilityAnimationId = null;
    
    function updateVisibility() {
        let active = 0;
        let closest = Infinity;
        
        sections.forEach((s, i) => {
            const rect = document.getElementById(s.section).getBoundingClientRect();
            const dist = Math.abs(rect.top + rect.height / 2 - window.innerHeight / 2);
            if (dist < closest) {
                closest = dist;
                active = i;
            }
        });

        sections.forEach((s, i) => {
            const isActive = i === active;
            const canvas = document.getElementById(s.canvas);
            const title = document.getElementById(s.title);
            const desc = document.getElementById(s.desc);
            
            canvas.style.opacity = isActive ? "1" : "0";
            title.classList.toggle("active", isActive);
            desc.classList.toggle("active", isActive);
        });
    }

    function handleVisibilityScroll() {
        if (visibilityAnimationId) {
            cancelAnimationFrame(visibilityAnimationId);
        }
        visibilityAnimationId = requestAnimationFrame(updateVisibility);
    }

    window.addEventListener("scroll", handleVisibilityScroll, { passive: true });
    window.addEventListener("resize", handleVisibilityScroll);
    window.addEventListener("load", updateVisibility);

    // Cleanup on page unload
    window.addEventListener("unload", () => {
        animationCleanups.forEach(cleanup => cleanup());
        window.removeEventListener("scroll", handleVisibilityScroll);
        window.removeEventListener("resize", handleVisibilityScroll);
    });
}

// Add CSS for smooth transitions
if (!document.getElementById('smooth-animation-styles')) {
    const style = document.createElement('style');
    style.id = 'smooth-animation-styles';
    style.textContent = `
        canvas {
            transition: opacity 0.3s ease-in-out;
            will-change: opacity;
        }
        
        .title, .desc {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
            will-change: opacity, transform;
        }
        
        .title.active, .desc.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        .title:not(.active), .desc:not(.active) {
            opacity: 0;
            transform: translateY(20px);
        }
    `;
    document.head.appendChild(style);
}

// Update canvas styles in CSS
const style = document.createElement('style');
style.textContent = `
    canvas {
        position: fixed;
        top: 0;
        right: 0;
        width: 100vw;
        height: 100vh;
        pointer-events: none;
        z-index: 1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    canvas.active {
        opacity: 1;
        z-index: 2; /* Increase z-index when active */
    }

    #slid-right {
        position: relative;
        overflow: visible; /* Allow content to overflow */
    }
`;
document.head.appendChild(style);

    const motorClickArea = document.getElementById("motorClickArea");
    const motorPlayer = document.getElementById("motorSequencePlayer");
    const motorImage = document.getElementById("motorSequenceImage");
    const motorOverlayFull = document.getElementById("motorOverlayFull");
    const motorCloseBtn = document.getElementById("motorCloseBtn");

    const totalMotorFrames = 120;

    function formatMotorFrame(i) {
        return String(i).padStart(4, "0");
    }

    function animateToLeftPanel() {
        // Calculate center position of the viewport
        const viewportCenterX = window.innerWidth / 2;
        const viewportCenterY = window.innerHeight / 2;
        
        const img = document.createElement("img");
        img.src = "motor/0120.webp";
        img.style.position = "fixed";
        
        // Start position - right side of the screen
        img.style.left = (window.innerWidth - 300) + "px";
        img.style.top = (window.innerHeight / 2) + "px";
        img.style.width = "300px";
        img.style.opacity = "1";
        img.style.zIndex = "9999";
        img.style.transition = "all 1s ease-out";
        document.body.appendChild(img);

        // First move to center
        setTimeout(() => {
            // Move to center of the screen
            img.style.left = (viewportCenterX - 150) + "px"; // Center horizontally (300px width / 2 = 150)
            img.style.top = (viewportCenterY - 115) + "px"; // Center vertically (assuming height is similar)
            img.style.transform = "scale(3.5)"; // Scale up the image as it moves
        }, 50);

        // Then fade out
        setTimeout(() => {
            img.style.opacity = "0"; // Fade out the image
        }, 800);

        // Finally remove and start sequence
        setTimeout(() => {
            document.body.removeChild(img);
            playMotorSequenceReverse();
        }, 1050);
    }

    let secondAnimationActive = false;
    let scrollDirection = 'none';
    let lastScrollTop = 0;

    function playMotorSequenceReverse() {
      motorPlayer.style.display = "flex";

let frame = totalMotorFrames;
const startX = 0;
const endX = -window.innerWidth / 5;

const totalSteps = totalMotorFrames;
let step = 0;

const interval = setInterval(() => {
    motorImage.src = `motor/${formatMotorFrame(frame)}.webp`;

    const percent = step / totalSteps;
    const currentX = startX + percent * endX;

    motorImage.style.transform = `translateX(${currentX}px)`;
    motorImage.style.transition = 'transform 0.04s linear';

    frame--;
    step++;

            if (frame < 1) {
                clearInterval(interval);

                // After sequence fully finishes (image 0001 shown), show text with slide-up
setTimeout(() => {
    const bigText = document.getElementById("motorBigText");
    const subText = document.getElementById("motorSubText");

    // Start far below (200px) for bigger upward motion
    bigText.style.transform = "translateY(60px)";
    bigText.style.opacity = "0";
    bigText.style.transition = "none";

    subText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transition = "none";

    // Force reflow to ensure the transition kicks in
    void bigText.offsetWidth;

    // Now animate to visible state
    bigText.style.transition = "opacity 1.2s ease-out, transform 1.2s ease-out";
    bigText.style.opacity = "1";
    bigText.style.transform = "translateY(-100px)";

    subText.style.transition = "opacity 1.4s ease-out, transform 1.4s ease-out";
    subText.style.opacity = "1";
    subText.style.transform = "translateY(0)";
}, 300);
                
                // After sequence fully finishes, animate first text
                //showFirstAnimation();
                // Add scroll listener for handling both animations
                window.addEventListener('scroll', handleScrollAnimations);
            }
        }, 40);
    }

    function showFirstAnimation() {
        const bigText = document.getElementById("motorBigText");
        const subText = document.getElementById("motorSubText");
        const motorSequenceImage = document.getElementById("motorSequenceImage");

        // Show the motor sequence image
        if (motorSequenceImage) {
            motorSequenceImage.style.opacity = "1";
            motorSequenceImage.style.transition = "opacity 0.4s ease-out";
        }

       // Now animate to visible state
    bigText.style.transition = "opacity 1.2s ease-out, transform 1.2s ease-out";
    bigText.style.opacity = "1";
    bigText.style.transform = "translateY(-100px)";

    subText.style.transition = "opacity 1.4s ease-out, transform 1.4s ease-out";
    subText.style.opacity = "1";
    subText.style.transform = "translateY(0)";

        secondAnimationActive = false;
    }

    function hideFirstAnimation() {
        const bigText = document.getElementById("motorBigText");
        const subText = document.getElementById("motorSubText");

        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }

    function handleScrollAnimations() {
        const currentScrollTop = window.scrollY;
        
        // Determine scroll direction
        if (currentScrollTop > lastScrollTop) {
            scrollDirection = 'down';
        } else {
            scrollDirection = 'up';
        }
        lastScrollTop = currentScrollTop;

        const windowHeight = window.innerHeight;
        const triggerPoint = windowHeight * 0.2;

        if (scrollDirection === 'down' && currentScrollTop > triggerPoint && !secondAnimationActive) {
            // Scrolling down past trigger point - show second animation
            secondAnimationActive = true;
            hideFirstAnimation();
            playSecondAnimation();
        } else if (scrollDirection === 'up' && currentScrollTop < triggerPoint && secondAnimationActive) {
            // Scrolling up past trigger point - show first animation
            secondAnimationActive = false;
            hideSecondAnimation();
            showFirstAnimation();
        }
    }

    function hideSecondAnimation() {
        const secondContainer = document.getElementById("secondAnimationContainer");
        if (secondContainer) {
            const imageContainer = secondContainer.children[0];
            const textContainer = secondContainer.children[1];
            const h2 = textContainer.querySelector('h2');
            const p = textContainer.querySelector('p');

            // Animate out in reverse order
            p.style.opacity = "0";
            p.style.transform = "translateX(-50px)";
            
            h2.style.opacity = "0";
            h2.style.transform = "translateX(-50px)";
            
            textContainer.style.opacity = "0";
            textContainer.style.transform = "translateX(-100px)";
            
            imageContainer.style.opacity = "0";
            imageContainer.style.transform = "translateY(50px)";

            // Remove container after animations complete
            setTimeout(() => {
                if (secondContainer.parentNode) {
                    secondContainer.parentNode.removeChild(secondContainer);
                }
            }, 800);
        }
    }

    function playSecondAnimation() {
        // First hide the motor sequence image
        const motorSequenceImage = document.getElementById("motorSequenceImage");
        if (motorSequenceImage) {
            motorSequenceImage.style.opacity = "0";
            motorSequenceImage.style.transition = "opacity 0.4s ease-out";
        }

        // Remove existing second animation container if it exists
        const existingContainer = document.getElementById("secondAnimationContainer");
        if (existingContainer) {
            existingContainer.parentNode.removeChild(existingContainer);
        }

        // Create new elements
        const newContainer = document.createElement("div");
        newContainer.id = "secondAnimationContainer";
        newContainer.style.cssText = `
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            top: 0;
            left: 0;
        `;

        // Create image container for animation
        const imageContainer = document.createElement("div");
        imageContainer.style.cssText = `
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            margin-bottom: 30px;
        `;

        const newImage = document.createElement("img");
        newImage.src = "motor/0001.png";
        newImage.style.cssText = `
            max-width: 80%;
        `;
        imageContainer.appendChild(newImage);

        // Create text container for animation with left-to-right slide
        const textContainer = document.createElement("div");
        textContainer.style.cssText = `
            opacity: 0;
            transform: translateX(-100px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            transition-delay: 0.3s;
            text-align: left;
            position: absolute;
            left: 55%;
        `;

        textContainer.innerHTML = `
            <h2 class="motorBigText"><span class="highlight">CARBON</span> BELT</h2>
            <p class="motorSubText">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
        `;

        // Append elements to container
        newContainer.appendChild(imageContainer);
        newContainer.appendChild(textContainer);
        motorPlayer.appendChild(newContainer);

        // Trigger animations with slight delay
        requestAnimationFrame(() => {
            // Trigger image animation first
            setTimeout(() => {
                imageContainer.style.opacity = "1";
                imageContainer.style.transform = "translateY(0)";
            }, 100);

            // Trigger text container animation
            setTimeout(() => {
                textContainer.style.opacity = "1";
                textContainer.style.transform = "translateX(0)";
                
                // Trigger individual text elements animation
                const h2 = textContainer.querySelector('h2');
                const p = textContainer.querySelector('p');
                
                h2.style.opacity = "1";
                h2.style.transform = "translateX(0)";
                
                p.style.opacity = "1";
                p.style.transform = "translateX(0)";
            }, 300);
        });
    }

    document.getElementById("batteryClickArea").addEventListener("click", () => {
        alert("Battery section clicked!");
        // Or trigger some battery animation or popup
    });

    document.getElementById("controllerClickArea").addEventListener("click", () => {
        alert("Controller section clicked!");
        // You can open a controller overlay or show text
    });

    document.getElementById("motorClickArea").addEventListener("click", () => {
        motorOverlayFull.style.display = "block";
        animateToLeftPanel();
    });

    document.getElementById("rimClickArea").addEventListener("click", () => {
        alert("Rim section clicked!");
        // Another animation or popup here
    });



 

    motorCloseBtn.addEventListener("click", () => {
        motorOverlayFull.style.display = "none";
        motorPlayer.style.display = "none";

        motorOverlayFull.style.display = "none";
    motorPlayer.style.display = "none";

    // Reset transform and styles
    motorImage.style.transform = "none";
    motorImage.style.transition = "none";

    // Reset image to first frame
    motorImage.src = `motor/${formatMotorFrame(totalMotorFrames)}.webp`;

    document.getElementById("motorBigText").classList.remove("active");
document.getElementById("motorSubText").classList.remove("active");
    });



    function resetMotorOverlay() {
        motorPlayer.style.display = "none";
        motorOverlayFull.style.display = "none";
        secondAnimationActive = false;
        scrollDirection = 'none';
        lastScrollTop = 0;

        // Remove scroll listener
        window.removeEventListener('scroll', handleScrollAnimations);

        // Reset elements
        const motorImage = document.getElementById("motorSequenceImage");
        const bigText = document.getElementById("motorBigText");
        const subText = document.getElementById("motorSubText");
        const secondContainer = document.getElementById("secondAnimationContainer");

        if (secondContainer) {
            secondContainer.parentNode.removeChild(secondContainer);
        }

        motorImage.style.transform = "none";
        motorImage.style.transition = "none";
        motorImage.style.opacity = "1";
        motorImage.src = `motor/${formatMotorFrame(totalMotorFrames)}.webp`;

        bigText.style.opacity = "0";
        subText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
        subText.style.transform = "translateY(60px)";
    }
    motorCloseBtn.addEventListener("click", () => {
        resetMotorOverlay();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            resetMotorOverlay();
        }
    });
