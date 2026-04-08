// Custom JavaScript for RIVOT Website
// Enhanced Preloader with Prioritized Loading
document.addEventListener('DOMContentLoaded', function() {
    const loaderOverlay = document.getElementById('loaderOverlay');
    const progressBar = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    // Priority assets to load first
    const priorityAssets = [
        'img/logo.webp', // Logo
        'battery/0001.webp', // Battery first frame
        'controler/0001.webp', // Controller first frame
        'motor/0001.webp' // Motor first frame
    ];
    
    // Scooter sequence frames (0001.webp to 0120.webp)
    const scooterFrames = Array.from({ length: 120 }, (_, i) => 
        `grayscooty/${String(i + 1).padStart(4, "0")}.webp`
    );
    
    let loadedCount = 0;
    const totalPriorityAssets = priorityAssets.length + scooterFrames.length;
    
    // Function to update progress
    function updateProgress(current, total, message) {
        const percent = Math.min(Math.floor((current / total) * 100), 100);
        progressBar.style.width = `${percent}%`;
        progressText.textContent = `${message} ${percent}%`;
    }
    
    // Function to load priority assets
    function loadPriorityAssets() {
        // Load logo, battery, controller, and motor first frames
        priorityAssets.forEach(asset => {
            const img = new Image();
            img.src = asset;
            img.onload = img.onerror = () => {
                loadedCount++;
                updateProgress(loadedCount, totalPriorityAssets, 'Loading...');
                checkAllPriorityAssetsLoaded();
            };
        });
        
        // Load all scooter frames (0001.webp to 0120.webp)
        scooterFrames.forEach(frame => {
            const img = new Image();
            img.src = frame;
            img.onload = img.onerror = () => {
                loadedCount++;
                updateProgress(loadedCount, totalPriorityAssets, 'Loading...');
                checkAllPriorityAssetsLoaded();
            };
        });
    }
    
    // Function to check if all priority assets are loaded
    function checkAllPriorityAssetsLoaded() {
        if (loadedCount >= totalPriorityAssets) {
            // All priority assets loaded, show initial content
            showInitialContent();
        }
    }
    
    // Function to show initial content
    function showInitialContent() {
        // Hide loader
        loaderOverlay.style.opacity = '0';
        setTimeout(() => {
            loaderOverlay.style.display = 'none';
            
            // Show first sections
            const sections = ['section1', 'section2', 'section3'];
            sections.forEach(id => {
                const section = document.getElementById(id);
                if (section) {
                    section.style.opacity = '1';
                    section.style.visibility = 'visible';
                }
            });
            
            // Initialize scroll-based lazy loading for remaining components
            initLazyLoading();
        }, 500);
    }
    
    // Start loading process
    loadPriorityAssets();
});

// Add this CSS to ensure proper initial state
const lazyLoadStyles = document.createElement('style');
lazyLoadStyles.textContent = `
    #section1, #section2, #section3 {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s ease-in-out;
    }
    
    #section4, #section5, #section6, #section7, #section8, 
    #section9, #section10, #section11, #section12, #section13 {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s ease-in-out;
    }
    
    #section4.loaded, #section5.loaded, #section6.loaded, 
    #section7.loaded, #section8.loaded, #section9.loaded, 
    #section10.loaded, #section11.loaded, #section12.loaded, 
    #section13.loaded {
        opacity: 1;
        visibility: visible;
    }
    
    #loaderOverlay {
        transition: opacity 0.5s ease-in-out;
    }
`;
document.head.appendChild(lazyLoadStyles);

// Prevent scroll restoration
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

// Hide scrollbars
var style = document.createElement('style');
style.innerHTML = `
    html {
        scrollbar-width: none;
    }
    html::-webkit-scrollbar {
        display: none;
    }
`;
document.head.appendChild(style);

// Smooth scroll animations
function initSmoothScrollAnimations() {
    const sections = document.querySelectorAll('[id^="section"]');
    const totalSections = sections.length;
    
    // Set initial states
    sections.forEach(section => {
        section.style.opacity = '1';
        section.style.visibility = 'visible';
        section.style.transform = 'translateY(50px)';
        section.style.transition = 'transform 0.8s ease-out';
    });
    
    // Handle scroll animations
    function handleScrollAnimations() {
        const scrollPosition = window.scrollY;
        const windowHeight = window.innerHeight;
        
        sections.forEach((section, index) => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            // Calculate how far the section is from viewport center
            const distanceFromCenter = Math.abs(
                (sectionTop + sectionHeight/2) - (scrollPosition + windowHeight/2)
            );
            
            // Calculate opacity based on distance from center
            const maxDistance = windowHeight;
            const opacity = Math.max(0, 1 - (distanceFromCenter / maxDistance));
            
            // Apply transform based on scroll position
            if (scrollPosition + windowHeight > sectionTop && 
                scrollPosition < sectionTop + sectionHeight) {
                const scrollProgress = (scrollPosition + windowHeight - sectionTop) / 
                                      (windowHeight + sectionHeight);
                const translateY = 50 * (1 - Math.min(1, scrollProgress * 2));
                section.style.transform = `translateY(${translateY}px)`;
            }
        });
    }
    
    // Initial call
    handleScrollAnimations();
    
    // Throttled scroll event listener
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(handleScrollAnimations, 16); // ~60fps
    }, { passive: true });
    
    // Handle resize
    window.addEventListener('resize', handleScrollAnimations, { passive: true });
}

// Enhanced lazy loading with sequential loading
function initLazyLoading() {
    const componentFolders = [
        "rim", "apu", "chassis", "headlight",
        "chargingCable", "body", "chargingport", "dashboard", "boost", "bootspace"
    ];
    
    // Track which components are loading
    const loadingComponents = new Set();
    const loadedComponents = new Set();
    
    // Load components sequentially
    async function loadComponentsSequentially() {
        for (const folder of componentFolders) {
            if (loadedComponents.has(folder)) continue;
            
            try {
                loadingComponents.add(folder);
                await loadComponentFrames(folder);
                loadedComponents.add(folder);
                loadingComponents.delete(folder);
                
                // Update UI to show component is loaded
                const sectionIndex = componentFolders.indexOf(folder) + 4;
                const section = document.getElementById(`section${sectionIndex}`);
                if (section) {
                    section.classList.add('loaded');
                }
            } catch (error) {
                console.error(`Error loading ${folder}:`, error);
                loadingComponents.delete(folder);
            }
        }
    }
    
    // Start loading components when page is ready
    if (document.readyState === 'complete') {
        loadComponentsSequentially();
    } else {
        window.addEventListener('load', loadComponentsSequentially);
    }
    
    // Also load on scroll as backup
    function checkAndLoadComponents() {
        const windowHeight = window.innerHeight;
        const scrollY = window.scrollY;
        
        componentFolders.forEach((folder, index) => {
            if (loadedComponents.has(folder) || loadingComponents.has(folder)) return;
            
            const sectionIndex = index + 4; // Start from section4
            const section = document.getElementById(`section${sectionIndex}`);
            if (!section) return;
            
            const sectionTop = section.offsetTop;
            const sectionBottom = sectionTop + section.offsetHeight;
            
            // Load if section is near viewport (within 2 screen heights)
            if (scrollY + windowHeight * 2 > sectionTop && 
                scrollY < sectionBottom + windowHeight) {
                loadingComponents.add(folder);
                loadComponentFrames(folder)
                    .then(() => {
                        loadedComponents.add(folder);
                        section.classList.add('loaded');
                    })
                    .catch(error => {
                        console.error(`Error loading ${folder}:`, error);
                    })
                    .finally(() => {
                        loadingComponents.delete(folder);
                    });
            }
        });
    }
    
    // Add scroll listener with throttling
    let ticking = false;
    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                checkAndLoadComponents();
                ticking = false;
            });
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', onScroll, { passive: true });
}

// Enhanced component frame loading with error handling
async function loadComponentFrames(folder) {
    return new Promise((resolve, reject) => {
        let loadedCount = 0;
        const totalFrames = 120;
        const errors = [];
        
        // Load images in batches to avoid overwhelming the browser
        const batchSize = 5;
        let currentBatch = 0;
        
        function loadBatch() {
            const start = currentBatch * batchSize + 1;
            const end = Math.min(start + batchSize - 1, totalFrames);
            
            for (let i = start; i <= end; i++) {
                const img = new Image();
                img.src = `${folder}/${String(i).padStart(4, "0")}.webp`;
                
                img.onload = () => {
                    loadedCount++;
                    if (loadedCount === totalFrames) {
                        if (errors.length > 0) {
                            reject(new Error(`Failed to load ${errors.length} images for ${folder}`));
                        } else {
                            resolve();
                        }
                    } else if (i === end && loadedCount < totalFrames) {
                        currentBatch++;
                        setTimeout(loadBatch, 50); // Small delay between batches
                    }
                };
                
                img.onerror = () => {
                    errors.push(i);
                    loadedCount++;
                    if (loadedCount === totalFrames) {
                        if (errors.length > 0) {
                            reject(new Error(`Failed to load ${errors.length} images for ${folder}`));
                        } else {
                            resolve();
                        }
                    } else if (i === end && loadedCount < totalFrames) {
                        currentBatch++;
                        setTimeout(loadBatch, 50);
                    }
                };
            }
        }
        
        // Start loading the first batch
        loadBatch();
    });
}

// DOM Content Loaded Event Handler
document.addEventListener('DOMContentLoaded', function() {
    // Initialize smooth scroll animations
    initSmoothScrollAnimations();
    
    // Define total sections and section height
    const totalSections = 13;
    const sectionHeightVh = 1200; // Each section is 1200vh
    let sectionHeightPx = window.innerHeight * sectionHeightVh / 100; // Convert 1200vh to pixels
    let totalScrollHeight = totalSections * sectionHeightPx; // Total height in pixels
    
    // Position the footer
    const footer = document.querySelector('footer');
    footer.style.position = 'absolute';
    footer.style.top = totalScrollHeight + 'px';
    footer.style.width = '100%';
    
    // Update footer position on window resize
    window.addEventListener('resize', function() {
        sectionHeightPx = window.innerHeight * sectionHeightVh / 100;
        totalScrollHeight = totalSections * sectionHeightPx;
        footer.style.top = totalScrollHeight + 'px';
    });
    
    // Handle scroll behavior for section 13 and footer visibility
    let lastScrollTime = 0;
    const scrollCooldown = 1000; // Prevent rapid scroll events
    let isFooterVisible = false;
    
    // Add scroll event listener to wrapper13
    const wrapper13 = document.querySelector('#section13 .canvas-wrapper');
    if (wrapper13) {
        wrapper13.addEventListener('scroll', function(e) {
            // Only trigger footer when scrolled to bottom of wrapper13
            const wrapper = e.target;
            const scrollBottom = wrapper.scrollHeight - wrapper.scrollTop - wrapper.clientHeight;
            
            if (scrollBottom < 50 && !isFooterVisible) { // Near bottom threshold
                // Add active class to section13
                document.getElementById('section13').classList.add('active');
                // Show footer
                footer.style.opacity = '1';
                footer.style.visibility = 'visible';
                footer.classList.add('visible');
                // Scroll main window to section13 position (keeping slid-left in place)
                window.scrollTo({
                    top: document.getElementById('section13').offsetTop,
                    behavior: 'smooth'
                });
                isFooterVisible = true;
            }
        }, { passive: true });
    }
    
    window.addEventListener('scroll', function(e) {
        const now = Date.now();
        if (now - lastScrollTime < scrollCooldown) return;
        lastScrollTime = now;
        
        const scrollPosition = window.scrollY;
        const windowHeight = window.innerHeight;
        const section13 = document.getElementById('section13');
        const section13Top = section13.offsetTop;
        const footerTop = totalScrollHeight;
        
        // Check if scrolled to or past section 13
        if (scrollPosition >= section13Top - windowHeight * 0.8) {
            if (!isFooterVisible) {
                // Add active class to section13
                section13.classList.add('active');
                // Show footer
                footer.style.opacity = '1';
                footer.style.visibility = 'visible';
                footer.classList.add('visible');
                // Scroll to align both panels at section 13
                window.scrollTo({
                    top: section13Top,
                    behavior: 'smooth'
                });
                isFooterVisible = true;
            }
        } else {
            if (isFooterVisible) {
                // Remove active class from section13
                section13.classList.remove('active');
                // Hide footer
                footer.style.opacity = '0';
                footer.style.visibility = 'hidden';
                footer.classList.remove('visible');
                isFooterVisible = false;
            }
        }
    }, { passive: false });
});

// Add this new function to handle the scroll behavior for wrapper13
function setupWrapper13Scroll() {
    const wrapper13 = document.querySelector('#wrapper13');
    const slidLeft = document.getElementById('slid-left');
    const slidRight = document.getElementById('slid-right');
    const footer = document.querySelector('footer');
    
    if (!wrapper13) return;
    
    let isScrolling = false;
    let lastScrollTop = 0;
    
    wrapper13.addEventListener('scroll', function() {
        if (isScrolling) return;
        
        const scrollTop = wrapper13.scrollTop;
        const scrollHeight = wrapper13.scrollHeight;
        const clientHeight = wrapper13.clientHeight;
        const scrollBottom = scrollHeight - scrollTop - clientHeight;
        
        // Only trigger when scrolling down near the bottom
        if (scrollBottom < 50 && scrollTop > lastScrollTop) {
            isScrolling = true;
            
            // Add active class to section13
            document.getElementById('section13').classList.add('active');
            
            // Show footer
            footer.style.opacity = '1';
            footer.style.visibility = 'visible';
            footer.classList.add('visible');
            
            // Scroll slid-right to show footer
            slidRight.scrollTo({
                top: slidRight.scrollHeight,
                behavior: 'smooth'
            });
            
            // Keep slid-left in place
            slidLeft.scrollTo({
                top: slidLeft.scrollHeight,
                behavior: 'smooth'
            });
            
            setTimeout(() => {
                isScrolling = false;
            }, 1000);
        }
        
        lastScrollTop = scrollTop;
    });
}

// Call the function when the page loads
window.addEventListener('load', setupWrapper13Scroll);

// Image Preload and Viewer
document.querySelectorAll('#slid-right canvas').forEach(canvas => {
    const allowedIds = ['canvas1', 'canvas2', 'canvas4', 'canvas5', 'canvas6', 'canvas7', 'canvas8', 'canvas9', 'canvas10', 'canvas11', 'canvas12', 'canvas13'];
    const allowedIds2 = ['canvas3'];
    if (allowedIds.includes(canvas.id)) {
        canvas.style.objectFit = 'contain';
        canvas.style.paddingLeft = '150px';
    }
    if (allowedIds2.includes(canvas.id)) {
        canvas.style.objectFit = 'contain';
        canvas.style.paddingLeft = '10px';
    }
});

const totalFrames = 120;
let currentFrame = 1;
let currentFolder = 'grayscooty'; // Default folder
let isDragging = false;
let lastX = 0;
const dragSpeed = 0.25;
let framePending = false;
const image = document.getElementById("sequenceImage");
const progressBar = document.getElementById("progressFill");
const progressText = document.getElementById("progressText");
const loaderOverlay = document.getElementById("loaderOverlay");
const viewer = document.getElementById("viewer");
const componentFolders = [
    "battery", "controler", "motor", "rim", "apu", "chassis", "headlight",
    "chargingCable", "body", "chargingport", "dashboard", "boost", "bootspace"
];
const preloadedComponents = new Set();

function formatFrame(num) {
    return String(num).padStart(4, "0");
}

function updateImage(frame, folder) {
    const src = `${folder}/${formatFrame(frame)}.webp`;
    image.src = src;
    image.onerror = () => {
        console.warn(`Image failed to load: ${src}, reverting to default`);
        image.src = `grayscooty/0001.webp`; // Fallback to a known good image
    };
}

function updateFrame() {
    if (framePending) {
        updateImage(currentFrame, currentFolder);
        framePending = false;
    }
}

function preloadComponentImages(folder, frameCount) {
    for (let i = 1; i <= frameCount; i++) {
        const img = new Image();
        img.src = `${folder}/${String(i).padStart(4, "0")}.webp`;
    }
}

// Mouse and Touch Events for Image Interaction
if (window.innerWidth > 768) {
    image.addEventListener("mousedown", (e) => {
        isDragging = true;
        lastX = e.clientX;
        image.style.cursor = "grabbing";
    });
    
    document.addEventListener("mouseup", () => {
        isDragging = false;
        image.style.cursor = "ew-resize";
    });
    
    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        const deltaX = e.clientX - lastX;
        const frameChange = Math.round(deltaX * dragSpeed);
        if (frameChange !== 0) {
            currentFrame = (currentFrame + frameChange) % totalFrames;
            if (currentFrame <= 0) currentFrame += totalFrames;
            framePending = true;
            requestAnimationFrame(updateFrame);
            lastX = e.clientX;
        }
    });
    
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
            framePending = true;
            requestAnimationFrame(updateFrame);
            lastX = e.touches[0].clientX;
        }
    });
}

image.addEventListener("dragstart", (e) => e.preventDefault());

// Enhanced function to show rotation icon
function showRotationIcon() {
    const rotationIcon = document.querySelector('.rotation-icon-container');
    const viewer = document.getElementById('viewer');
    
    if (rotationIcon && viewer) {
        // Remove any inline display styles
        rotationIcon.style.removeProperty('display');
        
        // Force display flex
        rotationIcon.style.display = 'flex';
        rotationIcon.style.opacity = '0.8';
        rotationIcon.style.visibility = 'visible';
        rotationIcon.style.zIndex = '10';
        
        // Adjust for mobile
        if (window.innerWidth <= 767) {
            rotationIcon.style.bottom = '20px';
            rotationIcon.style.left = '50%';
            rotationIcon.style.transform = 'translateX(-50%)';
        }
    }
}

// Updated preloadImages function
async function preloadImages() {
    let loaded = 0;
    const urls = [];
    for (let i = 1; i <= totalFrames; i++) {
        urls.push(`grayscooty/${formatFrame(i)}.webp`);
    }
    
    await Promise.all(urls.map(url =>
        new Promise((resolve) => {
            const img = new Image();
            img.src = url;
            img.onload = () => {
                loaded++;
                const percent = (loaded / urls.length) * 100;
                progressBar.style.width = `${percent}%`;
                progressText.textContent = `Loading... ${Math.round(percent)}%`;
                resolve();
            };
            img.onerror = () => {
                console.warn(`Failed to preload image: ${url}, using fallback`);
                resolve(); // Continue preloading even if an image fails
            };
        })
    ));
    
    // Ensure sequenceImage has a valid src after preload
    viewer.style.display = "flex";
    updateImage(currentFrame, currentFolder);
    
    // Show rotation icon
    showRotationIcon();
}

// Initialize Preloading
preloadImages().then(() => {
    window.scrollTo(0, 0); // Scroll to top before hiding loader
    loaderOverlay.style.display = "none";
    viewer.style.display = "flex";
    updateImage(currentFrame, currentFolder);
    preloadComponentImages(componentFolders[0], 120);
    preloadComponentImages(componentFolders[1], 120);
    preloadComponentImages(componentFolders[2], 120);
    preloadedComponents.add(0);
    preloadedComponents.add(1);
    preloadedComponents.add(2);
    new WOW().init();
    setTimeout(() => {
        document.getElementById("nx100").style.animation = "slideUp 3s ease-out forwards";
        setTimeout(() => {
            document.getElementById("performance").style.animation = "slideUp 2s ease-out forwards";
        }, 3000);
        setTimeout(() => {
            document.getElementById("description").style.animation = "slideUp 2s ease-out forwards";
        }, 4000);
    }, 100);
});

// Scroll Event for Component Preloading
window.addEventListener("scroll", () => {
    const scrollPosition = window.scrollY;
    const sectionHeight = 10 * window.innerHeight;
    const currentSectionIndex = Math.floor(scrollPosition / sectionHeight);
    const sectionsToPreload = [
        currentSectionIndex + 1,
        currentSectionIndex + 2
    ].filter(i => i < 13 && !preloadedComponents.has(i));
    
    sectionsToPreload.forEach(i => {
        preloadComponentImages(componentFolders[i], 120);
        preloadedComponents.add(i);
    });
});

// Scroll Canvas Animation
window.addEventListener('load', function() {
    window.scrollTo(0, 0);
});

function createScrollAnimation(canvasId, imagePathPrefix, frameCount, sectionElement) {
    const canvas = document.getElementById(canvasId);
    const context = canvas.getContext("2d", { alpha: true });
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    canvas.style.position = 'fixed';
    canvas.style.transition = 'transform 0.1s ease-out, opacity 0.3s ease-in-out';
    canvas.style.transformOrigin = 'center center';
    canvas.style.zIndex = '2';
    canvas.style.pointerEvents = 'none';
    canvas.style.opacity = '0';
    
    const images = [];
    let imagesLoaded = 0;
    let ready = false;
    let lastFrameIndex = 1;
    
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
        img.onerror = () => {
            console.error(`Failed to load image: ${img.src}`);
        };
        images.push(img);
    }
    
    function drawImage(frameNumber) {
        const img = images[frameNumber - 1];
        if (!img || !img.complete) return;
        
        context.clearRect(0, 0, canvas.width, canvas.height);
        canvas.style.transform = 'none';
        
        const maxWidth = window.innerWidth;
        const maxHeight = window.innerHeight;
        const imgWidth = img.naturalWidth;
        const imgHeight = img.naturalHeight;
        const imgAspectRatio = imgWidth / imgHeight;
        const viewportAspectRatio = maxWidth / maxHeight;
        
        let drawWidth, drawHeight;
        
        if (imgAspectRatio > viewportAspectRatio) {
            drawWidth = maxWidth;
            drawHeight = maxWidth / imgAspectRatio;
        } else {
            drawHeight = maxHeight;
            drawWidth = maxHeight * imgAspectRatio;
        }
        
        const x = (canvas.width - drawWidth) / 2;
        const y = (canvas.height - drawHeight) / 2;
        
        context.drawImage(img, x, y, drawWidth, drawHeight);
    }
    
    function updateAnimation(scrollProgress, isScrollingUp, title, desc, index) {
        if (!ready) {
            canvas.style.opacity = "0";
            return;
        }
        
        if (index === 0 && scrollProgress <= 0.2) {
            canvas.style.opacity = "1";
            drawImage(1);
        } else if (scrollProgress >= 0.2 && scrollProgress <= 0.85) {
            const animationProgress = (scrollProgress - 0.2) / 0.65;
            let frameIndex;
            
            if (animationProgress <= 0.5) {
                frameIndex = Math.max(1, Math.min(frameCount, Math.round(animationProgress * 2 * frameCount)));
            } else {
                frameIndex = Math.max(1, Math.min(frameCount, Math.round((1 - (animationProgress - 0.5) * 2) * frameCount)));
            }
            
            if (frameIndex !== lastFrameIndex) {
                lastFrameIndex = frameIndex;
                drawImage(frameIndex);
            }
            
            canvas.style.opacity = "1";
        } else {
            canvas.style.opacity = "0";
        }
        
        if (scrollProgress >= 0.4 && scrollProgress <= 0.7) {
            title.classList.add("active");
            desc.classList.add("active");
        } else {
            title.classList.remove("active");
            desc.classList.remove("active");
        }
    }
    
    return { updateAnimation, drawImage };
}

// Initialize Canvas Animations for Desktop
if (window.innerWidth > 768) {
    const animations = [
        { canvasId: "canvas1", path: "battery", section: document.getElementById("section1"), animation: createScrollAnimation("canvas1", "battery", 120, document.getElementById("section1")) },
        { canvasId: "canvas2", path: "controler", section: document.getElementById("section2"), animation: createScrollAnimation("canvas2", "controler", 120, document.getElementById("section2")) },
        { canvasId: "canvas3", path: "motor", section: document.getElementById("section3"), animation: createScrollAnimation("canvas3", "motor", 120, document.getElementById("section3")) },
        { canvasId: "canvas4", path: "rim", section: document.getElementById("section4"), animation: createScrollAnimation("canvas4", "rim", 120, document.getElementById("section4")) },
        { canvasId: "canvas5", path: "apu", section: document.getElementById("section5"), animation: createScrollAnimation("canvas5", "apu", 120, document.getElementById("section5")) },
        { canvasId: "canvas6", path: "chassis", section: document.getElementById("section6"), animation: createScrollAnimation("canvas6", "chassis", 120, document.getElementById("section6")) },
        { canvasId: "canvas7", path: "headlight", section: document.getElementById("section7"), animation: createScrollAnimation("canvas7", "headlight", 120, document.getElementById("section7")) },
        { canvasId: "canvas8", path: "chargingCable", section: document.getElementById("section8"), animation: createScrollAnimation("canvas8", "chargingCable", 120, document.getElementById("section8")) },
        { canvasId: "canvas9", path: "body", section: document.getElementById("section9"), animation: createScrollAnimation("canvas9", "body", 120, document.getElementById("section9")) },
        { canvasId: "canvas10", path: "chargingport", section: document.getElementById("section10"), animation: createScrollAnimation("canvas10", "chargingport", 120, document.getElementById("section10")) },
        { canvasId: "canvas11", path: "dashboard", section: document.getElementById("section11"), animation: createScrollAnimation("canvas11", "dashboard", 120, document.getElementById("section11")) },
        { canvasId: "canvas12", path: "boost", section: document.getElementById("section12"), animation: createScrollAnimation("canvas12", "boost", 120, document.getElementById("section12")) },
        { canvasId: "canvas13", path: "bootspace", section: document.getElementById("section13"), animation: createScrollAnimation("canvas13", "bootspace", 120, document.getElementById("section13")) }
    ];
    
    const sections = [
        { section: "section1", canvas: "canvas1", title: "title1", desc: "desc1" },
        { section: "section2", canvas: "canvas2", title: "title2", desc: "desc2" },
        { section: "section3", canvas: "canvas3", title: "title3", desc: "desc3" },
        { section: "section4", canvas: "canvas4", title: "title4", desc: "desc4" },
        { section: "section5", canvas: "canvas5", title: "title5", desc: "desc5" },
        { section: "section6", canvas: "canvas6", title: "title6", desc: "desc6" },
        { section: "section7", canvas: "canvas7", title: "title7", desc: "desc7" },
        { section: "section8", canvas: "canvas8", title: "title8", desc: "desc8" },
        { section: "section9", canvas: "canvas9", title: "title9", desc: "desc9" },
        { section: "section10", canvas: "canvas10", title: "title10", desc: "desc10" },
        { section: "section11", canvas: "canvas11", title: "title11", desc: "desc11" },
        { section: "section12", canvas: "canvas12", title: "title12", desc: "desc12" },
        { section: "section13", canvas: "canvas13", title: "title13", desc: "desc13" }
    ];
    
    let visibilityAnimationId = null;
    let lastScrollY = window.scrollY;
    
    function updateAnimations() {
        const currentScrollY = window.scrollY;
        const isScrollingUp = currentScrollY < lastScrollY;
        lastScrollY = currentScrollY;
        
        animations.forEach((anim, i) => {
            const rect = anim.section.getBoundingClientRect();
            const totalScrollRange = window.innerHeight + rect.height;
            const scrollProgress = (window.innerHeight - rect.top) / totalScrollRange;
            
            if (scrollProgress >= 0 && scrollProgress <= 1) {
                const sectionData = sections[i];
                const title = document.getElementById(sectionData.title);
                const desc = document.getElementById(sectionData.desc);
                anim.animation.updateAnimation(scrollProgress, isScrollingUp, title, desc, i);
            } else {
                const canvas = document.getElementById(anim.canvasId);
                canvas.style.opacity = "0";
            }
        });
    }
    
    function handleScroll() {
        if (visibilityAnimationId) {
            cancelAnimationFrame(visibilityAnimationId);
        }
        visibilityAnimationId = requestAnimationFrame(updateAnimations);
    }
    
    window.addEventListener("scroll", handleScroll, { passive: true });
    
    window.addEventListener("resize", () => {
        animations.forEach(anim => {
            const canvas = document.getElementById(anim.canvasId);
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            anim.animation.drawImage(1);
        });
        handleScroll();
    });
    
    window.addEventListener("load", () => {
        window.scrollTo(0, 0);
        new WOW().init();
        updateAnimations();
    });
    
    window.addEventListener("unload", () => {
        window.removeEventListener("scroll", handleScroll);
        window.removeEventListener("resize", handleScroll);
    });
}

// Add smooth animation styles if not present
if (!document.getElementById('smooth-animation-styles')) {
    const style = document.createElement('style');
    style.id = 'smooth-animation-styles';
    style.textContent = `
        canvas {
            transition: opacity 0.3s ease-in-out;
            will-change: opacity;
            pointer-events: none;
        }
        .title, .desc {
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-out;
            will-change: opacity, transform;
        }
        .title.active {
            opacity: 1;
            transform: translateY(0);
        }
        .title {
            opacity: 0;
            transform: translateY(20px);
        }
        .desc.active {
            opacity: 1;
            transform: translateX(0);
        }
        .desc {
            opacity: 0;
            transform: translateX(100px);
        }
    `;
    document.head.appendChild(style);
}

// Component Overlay Scripts
let isOverlayOpen = false;
const componentSpecificStyles = {
    'battery': { width: '100vw', height: '58vh' },
    'headlight': { width: '100vw', height: '68vh' },
    'chargingCable': { width: '100vw', height: '458px' },
    'dashboard': { width: '100vw', height: '58vh' },
    'boost': { width: '100vw', height: '58vh' },
    'chargingport': { width: '100vw', height: '65vh' },
    'bootspace': { width: '100vw', height: '75vh' }
};

// Component data with individual text and images
const componentInfo = {
    motor: {
        title: "RecoEngine Motor",
        description: "Crafted for those who demand elegance in motion, this motor redefines power with silence. Every ride feels effortless, every climb inspiring, and every moment infused with precision. It's not just movement it's the art of riding perfected.",
        image: "motor/0100.webp",
        images: [
            {
                image: "Curbon_belt/0075.webp",
                title: "CarbonCore <span class='highlight'>Belt</span>",
                description: "A reinforced carbon fiber belt ensures maintenance free, silent power transmission, resistant to wear, dust, and moisture for lasting reliability."
            },
            {
                image: "motor/0076.webp",
                title: "Stator & <span class='highlight'>Rotor</span>",
                description: "The stator and rotor work in perfect harmony, providing smooth, efficient power delivery for a seamless and high performance ride every time."
            },
            {
                image: "motor/0077.webp",
                title: "Aluminum <span class='highlight'>Housing</span>",
                description: "The aluminum alloy housing delivers exceptional strength, efficient heat dissipation, and lasting protection for reliable performance in every ride."
            },
            {
                image: "motor/0078.webp",
                title: "Motor <span class='highlight'>Shaft</span>",
                description: "Sculpted from the world's strongest alloys, it ensures flawless torque transfer and delivers smooth, reliable performance ride after ride."
            },
            {
                image: "motor/0079.webp",
                title: "150 Nm <span class='highlight'>Torque</span>",
                description: "Experience 150 Nm of instant torque, offering explosive acceleration and unmatched climbing power for effortless control across any terrain, anytime."
            }
        ]
    },
    battery: {
        title: "Copper Fusion System",
        description: "Behold the masterpiece of engineering designed beyond imagination for those who demand perfection. With unparalleled thermal intelligence and military grade resilience, it delivers relentless power wrapped in sublime sophistication. This isn't just a battery it's the soul of the future.",
        image: "battery/0075.webp",
        images: [
            {
                image: "battery/0075.webp",
                title: "Battery <span class='highlight'>Case</span>",
                description: "Sleek, silent, and scientifically superior the world's most advanced defense for the world's most advanced power."
            },
            {
                image: "battery/0076.webp",
                title: "Thermal Management <span class='highlight'>System</span>",
                description: "Intelligent heat control layer engineered beneath the battery, ensuring safe, stable, and ultra efficient performance even under extreme conditions."
            },
            {
                image: "battery/0077.webp",
                title: "Battery <span class='highlight'>Module</span>",
                description: "The pinnacle of energy storage. Every cell is precisely arranged to deliver unmatched reliability, endurance, and performance. More than power, it's perfection crafted over time."
            },
            {
                image: "battery/0078.webp",
                title: "Battery Management <span class='highlight'>System</span>",
                description: "Intelligent system that balances cells, regulates temperature, and harmonizes voltage ensuring safety, longevity, and uncompromised performance."
            },
            {
                image: "battery/0079.webp",
                title: "Pressure <span class='highlight'>Relief Screw</span>",
                description: "Rarely mastered, engineered for you precision venting that protects your battery under extreme pressure. A masterpiece of safety, crafted for the few who demand perfection."
            }
        ]
    },
    controler: {
        title: "PowerCore Controller",
        description: "Experience smooth acceleration and intuitive power that responds to your slightest touch. Engineered for exceptional efficiency and thermal resilience, it maximizes your range and amplifies every ride with effortless control and excitement.",
        image: "controler/0120.webp",
        images: [
            {
                image: "controler/0120.webp",
                title: "FlowGrid <span class='highlight'>Grill</span>",
                description: "An aerodynamically engineered grill that channels airflow with surgical precision, actively safeguarding thermal balance at every mile. Designed as the first line of defense, it transforms cooling into a statement of engineering luxury."
            },
            {
                image: "controler/0119.webp",
                title: "Aluminum <span class='highlight'>Casing</span>",
                description: "Crafted from aerospace grade aluminum,the casing embodies strength lighter than steel yet tougher than time. It is not just protection it is architecture built to outlast generations of mobility."
            },
            {
                image: "controler/0118.webp",
                title: "Control <span class='highlight'>Unit</span>",
                description: "An automotive grade microcontroller executing millions of calculations per second for flawless torque, speed, and efficiency."
            },
            {
                image: "controler/0117.webp",
                title: "Power <span class='highlight'>MOSFET</span>",
                description: "Next gen SiC MOSFETs delivering lightning fast switching and over 99% efficiency for powerful, controlled acceleration."
            },
            {
                image: "controler/0116.webp",
                title: "Stability <span class='highlight'>Capacitor</span>",
                description: "Ultra premium capacitors storing instant, stable power for smooth acceleration, lasting performance, and world class efficiency."
            }
        ]
    },
    rim: {
        title: "AeroDynamic Wheel",
        description: "Forged with planet strength metallic alloy and diamond cut precision, these wheels set a new global standard. Integrated with advanced TPMS and tubeless tire technology, they deliver flawless performance and unshakable safety. Engineered for those who command the road, anywhere on Earth.",
        image: "rim/0001.webp",
        images: [
            {
                image: "rim/0001.webp",
                title: "Forged <span class='highlight'>Wheels</span>",
                description: "Forged from planet strength alloy with diamond cut precision, the nx100 wheels deliver flawless performance. With TPMS and tubeless tech, they ensure unshakable safety on any terrain."
            },
            {
                image: "rim/0002.webp",
                title: "Braking <span class='highlight'>System</span>",
                description: "Crafted with oversized discs and smart regenerative control, it ensures unmatched power, precision, and confidence, making every stop smooth and refined."
            },
            {
                image: "rim/0003.webp",
                title: "TPMS <span class='highlight'>Live</span>",
                description: "A technology reserved for only the most premium scooters, TPMS elevates safety and performance by giving riders intelligent, real-time tire insights."
            },
            {
                image: "rim/0004.webp",
                title: "Durable <span class='highlight'>Tires</span>",
                description: "Built with reinforced layers and high quality materials to resist punctures, cuts, and wear, significantly extending tire life and reducing the risk of roadside issues."
            },
            {
                image: "rim/0005.webp",
                title: "Alloy <span class='highlight'>Wheels</span>",
                description: "The alloy wheels feature a meticulously crafted design that complements the vehicle's aggressive stance, making a powerful style statement even when standing still."
            }
        ]
    },
    apu: {
        title: "Auxiliary Power Unit",
        description: "The world's first built in emergency backup,Confidence that never runs out. The APU stands by silently, ready when you need it most. A seamless promise of freedom, crafted for riders who demand reliability without compromise.",
        image: "apu/0111.webp",
        images: [
            {
                image: "apu/0111.webp",
                title: "Power<span class='highlight'>Reserve</span>",
                description: "The APU delivers precise emergency power, extending 10 –15 km safely while protecting the system."
            },
            {
                image: "apu/0112.webp",
                title: "Backup <span class='highlight'>Power</span>",
                description: "Turning unused space into essential function, the exhaust now houses a seamless emergency power unit."
            },
            {
                image: "apu/0113.webp",
                title: "Effortless & <span class='highlight'>Automatic</span>",
                description: "When the main battery runs low, the APU seamlessly takes over no buttons, no fuss, just calm, reliable power."
            },
            {
                image: "apu/0114.webp",
                title: "Seamless System <span class='highlight'>Integration</span>",
                description: "Fully integrated with the VCU, the APU seamlessly takes over, ensuring smooth, controlled rides to safety."
            },
            {
                image: "apu/0115.webp",
                title: "Power<span class='highlight'>Reserve</span>",
                description: "The APU delivers precise emergency power, extending 10 –15 km safely while protecting the system."
            }
        ]
    },
    chassis: {
        title: "RIVOT Frame",
        description: "With a colossal 45-litre boot, the RIVOT nx100 unlocks new dimensions of freedom. Its trellis mono cradle fusion makes it the world's strongest, smartest, and most indulgent chassis. Space, strength, and sophistication crafted to carry your world with effortless grace",
        image: "chassis/0001.webp",
        images: [
            {
                image: "chassis/0001.webp",
                title: "Telescopic <span class='highlight'>Forks</span>",
                description: "RIVOT nx100's precision engineered telescopic forks blend rugged strength with elegance, offering unmatched stability, smooth control, and a confident ride everywhere."
            },
            {
                image: "chassis/0002.webp",
                title: "Chassis <span class='highlight'>Design</span>",
                description: "Chessi is where strategy meets sophistication a modern reimagination of the world's most timeless game. Designed for brilliance, built for champions."
            },
            {
                image: "chassis/0003.webp",
                title: "Storage <span class='highlight'>Space</span>",
                description: "Redesigned from the ground up, the chassis unlocks class leading underseat storage spacious enough for two helmets, groceries, or a weekend bag."
            },
            {
                image: "chassis/0004.webp",
                title: "Material <span class='highlight'>Build</span>",
                description: "A hybrid of steel trellis and lightweight aluminum delivers uncompromising strength, sharp handling, and longer range in perfect balance."
            },
            {
                image: "chassis/0005.webp",
                title: "Electric <span class='highlight'>Integration</span>",
                description: "Purpose built for electric, the chassis ensures a low center of gravity, flawless weight distribution, and seamless battery integration for a natural ride."
            }
        ]
    },
    headlight: {
        title: "LED HEADLIGHT",
        description: "Illuminate your journey with unseen elegance. Command the night with sculpted light that speaks before you do, turning every ride into a statement. Crafted in Belagavi, it's not just light it's your silent signature in motion.",
        image: "headlight/0101.webp",
        images: [
            {
                image: "headlight/0101.webp",
                title: "UniBeam <span class='highlight'>Pro</span>",
                description: "Adaptive illumination for riders precision, performance, and unmatched style with commanding road presence."
            },
            {
                image: "headlight/0102.webp",
                title: "DRL <span class='highlight'>Lights</span>",
                description: "DRL lights your ride with confidence and style, making you visible and commanding attention day or night. Sleek, modern, and unmistakably bold every journey shines with presence."
            },
            {
                image: "headlight/0103.webp",
                title: "RIVOT <span class='highlight'>RideCam</span>",
                description: "World's First Integrated RideCam: Capture journeys, monitor remotely, and access incident data all seamlessly built beneath the headlight."
            },
            {
                image: "headlight/0104.webp",
                title: "Adaptive Rider <span class='highlight'>Assistance</span>",
                description: "Adaptive Rider Assistance System: Intuitive lighting that adjusts with every turn for safer, smoother, and more confident night rides."
            },
            {
                image: "headlight/0105.webp",
                title: "Modern <span class='highlight'>Design</span>",
                description: "A Design to Fall For. Meticulously crafted and designed in Belagavi, India, blending iconic style with cutting edge function."
            }
        ]
    },
    chargingCable: {
        title: "Retractable Cable",
        description: "The world's first EV with a retractable charging cable no tangles, no compromises. Effortless connection meets timeless elegance and convenience. Redefining how you power every journey.",
        image: "chargingCable/0118.webp",
        images: [
            {
                image: "chargingCable/0118.webp",
                title: "Integrated On Board <span class='highlight'>Charger</span>",
                description: "The fully integrated on board charger is seamlessly housed within the bodywork, eliminating the bulky external brick and freeing up valuable storage space."
            },
            {
                image: "chargingCable/0119.webp",
                title: "Rapid 1KM/Minute <span class='highlight'>Charging</span>",
                description: "Convenience meets power. The system delivers an ultra fast charge rate of 1 kilometer per minute, getting you back on the road in record time."
            },
            {
                image: "chargingCable/0120.webp",
                title: "Effortless Automatic <span class='highlight'>Retraction</span>",
                description: "A gentle tug is all it takes the spring loaded system smoothly retracts the cable, keeping it neat, clean, and hassle free after every charge."
            },
            {
                image: "chargingCable/0117.webp",
                title: "Thermal Management <span class='highlight'>System</span>",
                description: "Engineered for peace of mind. An advanced thermal management system regulates charger temperature, ensuring maximum efficiency and safety even during rapidcharging."
            },
            {
                image: "chargingCable/0116.webp",
                title: "Durable, All Weather <span class='highlight'>Engineering</span>",
                description: "Built for real world use. The reinforced cable and sealed mechanism withstand thousands of retractions and endure rain, sun, and dust delivering long lasting reliability."
            }
        ]
    },
    body: {
        title: "nx100 Vanguard",
        description: "Every sculpted curve and intentional line inspires pride, confidence, and a feeling of pure luxury. This is design that moves not just you, but those who see you a rolling masterpiece crafted to turn journeys into experiences and riders into icons.",
        image: "body/0001.webp",
        images: [
            {
                image: "body/0001.webp",
                title: "A Sculpture in <span class='highlight'>Motion</span>",
                description: "Every curve is purposeful, blending aggressive style with aerodynamic elegance making the nx100 a head turning masterpiece of design."
            },
            {
                image: "body/0002.webp",
                title: "Ergonomic <span class='highlight'>Harmony</span>",
                description: "Rider and passenger enjoy perfect posture and all day comfort, reducing fatigue and enhancing the joy of every journey together."
            },
            {
                image: "body/0003.webp",
                title: "Seamless <span class='highlight'>Practicality</span>",
                description: "Innovative features like gesture controlled boot and hidden APU integrate flawlessly, delivering convenience and \"wow\" moments without cluttering design."
            },
            {
                image: "body/0004.webp",
                title: "Built to <span class='highlight'>Endure</span>",
                description: "Premium, resilient materials with IP67 protection ensure durability, safety, and lasting quality for daily commutes and adventurous rides."
            },
            {
                image: "body/0005.webp",
                title: "Premium <span class='highlight'>Finishes</span>",
                description: "High quality paint and textures resist wear, reflect light beautifully, and enhance the sense of luxury."
            }
        ]
    },
    chargingport: {
        title: "FlashCharging Port",
        description: "Perfectly poised beneath the headlamp, the Flash Charger port offers effortless public charging. Its strategic placement eliminates bending or reversing transforming charging into an act of elegant simplicity and thoughtful luxury.",
        image: "chargingport/0062.webp",
        images: [
            {
                image: "chargingport/0062.webp",
                title: "Front <span class='highlight'>Access</span>",
                description: "Strategically placed for effortless connection no awkward bending or reversing. Charging is now a seamless, user-first experience."
            },
            {
                image: "chargingport/0063.webp",
                title: "Effortless <span class='highlight'>Connection</span>",
                description: "Simply walk up and plug in. The ideal placement transforms public charging from a chore into an act of elegant simplicity."
            },
            {
                image: "chargingport/0064.webp",
                title: "All Weather <span class='highlight'>Ready</span>",
                description: "Engineered to withstand rain, dust, and daily use, ensuring reliable performance in any environment."
            },
            {
                image: "chargingport/0065.webp",
                title: "Rapid <span class='highlight'>Refueling</span>",
                description: "Designed to support high-speed charging, turning minutes into miles and minimizing your wait."
            },
            {
                image: "chargingport/0066.webp",
                title: "Design <span class='highlight'>Integration</span>",
                description: "Sleekly integrated below the headlamp, maintaining the vehicle's clean aesthetics while adding advanced functionality."
            }
        ]
    },
    dashboard: {
        title: "Pinnacle Display",
        description: "Step into a new era of riding sophistication with a cockpit designed to inspire confidence and elevate every journey. Crafted with precision and elegance, it transforms the way you connect with your ride making every moment on the road an experience in itself",
        image: "dashboard/0087.webp",
        images: [
            {
                image: "dashboard/0087.webp",
                title: "Screen Size & <span class='highlight'>Display</span>",
                description: "Experience the Dominant 8.8 Inch Display the largest in its class. Command every detail with unmatched clarity, precision, and control."
            },
            {
                image: "dashboard/0088.webp",
                title: "Keyless <span class='highlight'>Entry</span>",
                description: "Simply approach and ride your presence is the key. Powered by advanced BLE technology, it offers instant recognition and effortless entry, blending security with elegance"
            },
            {
                image: "dashboard/0089.webp",
                title: "Smart <span class='highlight'>Navigation</span>",
                description: "Fully Integrated Smart Navigation seamlessly connected maps with real time routing and intuitive controls, crafted to keep your journey effortless and refined."
            },
            {
                image: "dashboard/0090.webp",
                title: "Personalized Rider <span class='highlight'>Profiles</span>",
                description: "Create Personalized Rider Profiles intelligent settings that seamlessly adapt to your preferences, delivering a ride that feels uniquely yours every time."
            },
            {
                image: "dashboard/0091.webp",
                title: "Document <span class='highlight'>Storage</span>",
                description: "Secure Digital Document Storage keeps your insurance, registration, and license details organized in one encrypted hub offering seamless access, convenience, and peace of mind wherever you go."
            }
        ]
    },
    boost: {
        title: "PowerBoost",
        description: "Unleash 5 seconds of explosive acceleration for confident overtakes and thrilling bursts of power engineered for decisive moments with precision and safety.",
        image: "boost/0077.webp",
        images: [
            {
                image: "boost/0077.webp",
                title: "Boost Activation <span class='highlight'>Button</span>",
                description: "Ergonomically designed and glove friendly, this tactile handlebar switch engages Boost instantly no need to take your eyes off the road."
            },
            {
                image: "boost/0078.webp",
                title: "Confident Highway <span class='highlight'>Overtaking</span>",
                description: "Pass trucks and slower vehicles safely while merging effortlessly into fast moving traffic. Power on Demand Conquer steep inclines and maintain momentum with instant, controlled torque."
            },
            {
                image: "boost/0079.webp",
                title: "Seamless Traffic <span class='highlight'>Merges</span>",
                description: "Enter highways or busy traffic circles with ease, matching the flow instantly for a smooth, confident ride."
            },
            {
                image: "boost/0080.webp",
                title: "Conquer Steep <span class='highlight'>Inclines</span>",
                description: "Conquer every climb with confidence. Extra torque ensures smooth momentum and consistent speed, even on the steepest, toughest hills."
            },
            {
                image: "boost/0081.webp",
                title: "Safety Through <span class='highlight'>Performance</span>",
                description: "Boost is engineered for both power and safety, enabling quick, predictable maneuvers when it matters most."
            }
        ]
    },
    bootspace: {
        title: "Air Gesture Key",
        description: "A simple wave unlocks expansive storage, seamlessly blending sophisticated technology with effortless functionality. This elegant innovation redefines convenience, elevating every journey with intuitive, hands free accessibility for the modern rider.",
        image: "bootspace/0093.webp",
        images: [
            {
                image: "bootspace/0093.webp",
                title: "Foot Gesture <span class='highlight'>Activation</span>",
                description: "Unlock storage with a simple foot wave. Enjoy keyless, hands free access that combines style, ease, and modern convenience redefining effortless mobility for every rider. "
            },
            {
                image: "bootspace/0094.webp",
                title: "Generous  <span class='highlight'> 45L Capacity</span>",
                description: "Enjoy unmatched storage freedom with the world's largest 45L boot effortlessly accommodating two full face helmets and all your essentials, with generous space to spare."
            },
            {
                image: "bootspace/0095.webp",
                title: "Front Access <span class='highlight'>Design</span>",
                description: "A smart forward opening seat makes loading and unloading effortless even in the busiest city spots bringing unmatched ease, style, and everyday convenience to every ride. "
            },
            {
                image: "bootspace/0096.webp",
                title: "All Weather <span class='highlight'>Reliability</span>",
                description: "Rain or shine, enjoy flawless access every time. The IP67- rated sensor ensures dependable gesture control, delivering worry free convenience in all weather conditions."
            },
            {
                image: "bootspace/0097.webp",
                title: "Seamless <span class='highlight'>Integration</span>",
                description: "Seamless integration transforms storage smart technology meets sleek styling, creating a secure, stylish boot that delivers effortless convenience and modern sophistication."
            }
        ]
    }
};

function escKeyHandler(e) {
    if (e.key === "Escape") {
        resetAllOverlays();
    }
}

// Story Overlay Script
let transitionTimeout = null;
function showStoryOverlay() {
    if (isOverlayOpen) return;
    isOverlayOpen = true;
    
    const hoverOverlay = document.createElement("div");
    hoverOverlay.id = "hoverOverlay";
    hoverOverlay.style.position = "fixed";
    hoverOverlay.style.top = "0";
    hoverOverlay.style.left = "0";
    hoverOverlay.style.width = "100%";
    hoverOverlay.style.height = "100%";
    hoverOverlay.style.background = "rgba(0, 0, 0, 0.95)";
    hoverOverlay.style.zIndex = "1000";
    hoverOverlay.style.opacity = "0";
    hoverOverlay.style.transition = "opacity 0.5s ease-out";
    document.body.appendChild(hoverOverlay);
    
    setTimeout(() => {
        hoverOverlay.style.opacity = "0.5";
    }, 50);
    
    const img = document.createElement("img");
    img.id = "storyTransitionImage";
    img.src = "img/story-image.jpg";
    img.alt = "Story Preview";
    img.style.position = "fixed";
    img.style.left = "50%";
    img.style.top = "100%";
    img.style.transform = "translate(-50%, 0%) scale(1)";
    img.style.maxWidth = "100vw";
    img.style.maxHeight = "100vh";
    img.style.width = "auto";
    img.style.height = "auto";
    img.style.objectFit = "contain";
    img.style.opacity = "1";
    img.style.zIndex = "1001";
    img.style.transition = "transform 2s ease-out, opacity 2s ease-out";
    document.body.appendChild(img);
    
    img.onerror = () => {
        console.error("Failed to load story-image.jpg");
        proceedToStoryOverlay();
    };
    
    setTimeout(() => {
        img.style.transform = "translate(-50%, -50%)";
        img.style.opacity = "0";
    }, 600);
    
    function proceedToStoryOverlay() {
        if (!isOverlayOpen) {
            cleanupTransitionElements();
            return;
        }
        
        cleanupTransitionElements();
        
        const storyOverlay = document.getElementById("storyOverlayFull");
        const storyFrame = document.getElementById("storyFrame");
        const storyCloseBtn = document.getElementById("storyCloseBtn");
        
        storyOverlay.style.display = "block";
        storyFrame.src = "story.html";
        storyCloseBtn.style.opacity = "1";
        storyCloseBtn.style.pointerEvents = "auto";
        
        storyFrame.onerror = () => {
            console.error("Failed to load story.html");
            const errorMsg = document.createElement("div");
            errorMsg.style.color = "white";
            errorMsg.style.textAlign = "center";
            errorMsg.style.marginTop = "20%";
            errorMsg.textContent = "Failed to load the story. Please try again later.";
            storyOverlay.appendChild(errorMsg);
        };
    }
    
    function cleanupTransitionElements() {
        const hoverOverlay = document.getElementById("hoverOverlay");
        const img = document.getElementById("storyTransitionImage");
        if (hoverOverlay && hoverOverlay.parentNode) {
            document.body.removeChild(hoverOverlay);
        }
        if (img && img.parentNode) {
            document.body.removeChild(img);
        }
    }
    
    transitionTimeout = setTimeout(proceedToStoryOverlay, 2600);
}

function closeStoryOverlay() {
    if (!isOverlayOpen) return;
    isOverlayOpen = false;
    
    const storyOverlay = document.getElementById("storyOverlayFull");
    const storyCloseBtn = document.getElementById("storyCloseBtn");
    const storyFrame = document.getElementById("storyFrame");
    
    storyOverlay.style.display = "none";
    storyCloseBtn.style.opacity = "0";
    storyCloseBtn.style.pointerEvents = "none";
    storyFrame.src = '';
    
    if (transitionTimeout) {
        clearTimeout(transitionTimeout);
        transitionTimeout = null;
    }
    
    const hoverOverlay = document.getElementById("hoverOverlay");
    const img = document.getElementById("storyTransitionImage");
    if (hoverOverlay && hoverOverlay.parentNode) {
        document.body.removeChild(hoverOverlay);
    }
    if (img && img.parentNode) {
        document.body.removeChild(img);
    }
}

const footer = document.querySelector('footer');
const contentWrapper = document.getElementById('content_wrapper');
function checkFooterVisibility() {
  const footerRect = footer.getBoundingClientRect();
  const windowHeight = window.innerHeight;
  if (footerRect.top < windowHeight) {
    contentWrapper.classList.add('footer-visible');
  } else {
    contentWrapper.classList.remove('footer-visible');
  }
}
window.addEventListener('scroll', checkFooterVisibility);

// Component Click Areas and Elements
const motorClickArea = document.getElementById("motorClickArea");
const motorPlayer = document.getElementById("motorSequencePlayer");
const motorImage = document.getElementById("motorSequenceImage");
const motorOverlayFull = document.getElementById("motorOverlayFull");
const motorCloseBtn = document.getElementById("motorCloseBtn");
const batteryClickArea = document.getElementById("batteryClickArea");
const batteryPlayer = document.getElementById("batterySequencePlayer");
const batteryImage = document.getElementById("batterySequenceImage");
const batteryOverlayFull = document.getElementById("batteryOverlayFull");
const batteryCloseBtn = document.getElementById("batteryCloseBtn");
const controllerClickArea = document.getElementById("controllerClickArea");
const controllerPlayer = document.getElementById("controllerSequencePlayer");
const controllerImage = document.getElementById("controllerSequenceImage");
const controllerOverlayFull = document.getElementById("controllerOverlayFull");
const controllerCloseBtn = document.getElementById("controllerCloseBtn");
const rimClickArea = document.getElementById("rimClickArea");
const rimPlayer = document.getElementById("rimSequencePlayer");
const rimImage = document.getElementById("rimSequenceImage");
const rimOverlayFull = document.getElementById("rimOverlayFull");
const rimCloseBtn = document.getElementById("rimCloseBtn");
const apuClickArea = document.getElementById("apuClickArea");
const apuPlayer = document.getElementById("apuSequencePlayer");
const apuImage = document.getElementById("apuSequenceImage");
const apuOverlayFull = document.getElementById("apuOverlayFull");
const apuCloseBtn = document.getElementById("apuCloseBtn");
const chassisClickArea = document.getElementById("chassisClickArea");
const chassisPlayer = document.getElementById("chassisSequencePlayer");
const chassisImage = document.getElementById("chassisSequenceImage");
const chassisOverlayFull = document.getElementById("chassisOverlayFull");
const chassisCloseBtn = document.getElementById("chassisCloseBtn");
const headlightClickArea = document.getElementById("headlightClickArea");
const headlightPlayer = document.getElementById("headlightSequencePlayer");
const headlightImage = document.getElementById("headlightSequenceImage");
const headlightOverlayFull = document.getElementById("headlightOverlayFull");
const headlightCloseBtn = document.getElementById("headlightCloseBtn");
const chargingCableClickArea = document.getElementById("chargingCableClickArea");
const chargingCablePlayer = document.getElementById("chargingCableSequencePlayer");
const chargingCableImage = document.getElementById("chargingCableSequenceImage");
const chargingCableOverlayFull = document.getElementById("chargingCableOverlayFull");
const chargingCableCloseBtn = document.getElementById("chargingCableCloseBtn");
const bodyClickArea = document.getElementById("bodyClickArea");
const bodyPlayer = document.getElementById("bodySequencePlayer");
const bodyImage = document.getElementById("bodySequenceImage");
const bodyOverlayFull = document.getElementById("bodyOverlayFull");
const bodyCloseBtn = document.getElementById("bodyCloseBtn");
const chargingPortClickArea = document.getElementById("chargingPortClickArea");
const chargingPortPlayer = document.getElementById("chargingPortSequencePlayer");
const chargingPortImage = document.getElementById("chargingPortSequenceImage");
const chargingPortOverlayFull = document.getElementById("chargingPortOverlayFull");
const chargingPortCloseBtn = document.getElementById("chargingPortCloseBtn");
const dashboardClickArea = document.getElementById("dashboardClickArea");
const dashboardPlayer = document.getElementById("dashboardSequencePlayer");
const dashboardImage = document.getElementById("dashboardSequenceImage");
const dashboardOverlayFull = document.getElementById("dashboardOverlayFull");
const dashboardCloseBtn = document.getElementById("dashboardCloseBtn");
const boostClickArea = document.getElementById("boostClickArea");
const boostPlayer = document.getElementById("boostSequencePlayer");
const boostImage = document.getElementById("boostSequenceImage");
const boostOverlayFull = document.getElementById("boostOverlayFull");
const boostCloseBtn = document.getElementById("boostCloseBtn");
const bootSpaceClickArea = document.getElementById("bootSpaceClickArea");
const bootSpacePlayer = document.getElementById("bootSpaceSequencePlayer");
const bootSpaceImage = document.getElementById("bootSpaceSequenceImage");
const bootSpaceOverlayFull = document.getElementById("bootSpaceOverlayFull");
const bootSpaceCloseBtn = document.getElementById("bootSpaceCloseBtn");

// Frame counts for each component
const totalMotorFrames = 120;
const totalBatteryFrames = 120;
const totalControllerFrames = 120;
const totalRimFrames = 120;
const totalApuFrames = 120;
const totalChassisFrames = 120;
const totalHeadlightFrames = 120;
const totalChargingCableFrames = 120;
const totalBodyFrames = 120;
const totalChargingPortFrames = 120;
const totalDashboardFrames = 120;
const totalBoostFrames = 120;
const totalBootSpaceFrames = 120;

function formatMotorFrame(i) {
    return String(i).padStart(4, "0");
}

// Motor animation tracking variables
let motorTempImage = null;
let motorAnimationTimeout = null;

// Add CSS for component scroll down indicator
const componentScrollIndicatorStyles = document.createElement('style');
componentScrollIndicatorStyles.textContent = `
    .component-scroll-down-indicator {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        color: #ffffff;
        font-size: 14px;
        font-family: 'Montserrat', sans-serif;
        cursor: pointer;
        z-index: 10002;
        opacity: 0;
        transition: opacity 0.5s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .component-scroll-down-indicator.active {
        opacity: 1;
        animation: bounceScroll 2s infinite;
    }
    
    .component-scroll-down-indicator .scroll-icon {
        width: 30px;
        height: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .component-scroll-down-indicator {
        width: 100%;
        height: auto;
        animation: pulse 2s infinite;
    }
    
    @keyframes bounceScroll {
        0%, 20%, 50%, 80%, 100% {
            transform: translateX(-50%) translateY(0);
        }
        40% {
            transform: translateX(-50%) translateY(-10px);
        }
        60% {
            transform: translateX(-50%) translateY(-5px);
        }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    /* Mobile adjustments */
    @media (max-width: 767px) {
        .component-scroll-down-indicator {
            bottom: 20px;
        }
    }
    
    .component-scroll-up-button {
        position: fixed;
        bottom: 40px;
        right: 40px;
        width: 50px;
        height: 50px;
        background-color: #CE6723;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        z-index: 10003;
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .component-scroll-up-button.visible {
        opacity: 1;
    }
    
    .component-scroll-up-button:hover {
        background-color: #b55a1f;
    }
`;
document.head.appendChild(componentScrollIndicatorStyles);

// Create component scroll down indicator
function createComponentScrollDownIndicator(componentPlayer) {
    const indicatorId = `${componentPlayer.id}ScrollDownIndicator`;
    
    // Check if indicator already exists
    if (document.getElementById(indicatorId)) {
        return;
    }
    
    const scrollDownIndicator = document.createElement('div');
    scrollDownIndicator.id = indicatorId;
    scrollDownIndicator.className = 'component-scroll-down-indicator';
    scrollDownIndicator.innerHTML = `
        <div class="scroll-icon">
        </div>
        <span>Scroll Down</span>
    `;
    
    componentPlayer.appendChild(scrollDownIndicator);
}

// Initialize component scroll down indicators when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const componentPlayers = [
        "motorSequencePlayer",
        "batterySequencePlayer",
        "controllerSequencePlayer",
        "rimSequencePlayer",
        "apuSequencePlayer",
        "chassisSequencePlayer",
        "headlightSequencePlayer",
        "chargingCableSequencePlayer",
        "bodySequencePlayer",
        "chargingPortSequencePlayer",
        "dashboardSequencePlayer",
        "boostSequencePlayer",
        "bootSpaceSequencePlayer"
    ];
    
    componentPlayers.forEach(playerId => {
        const player = document.getElementById(playerId);
        if (player) {
            createComponentScrollDownIndicator(player);
        }
    });
});

// Add scroll event listener to component overlays
function addComponentOverlayScrollListener(componentPlayer) {
    const scrollDownIndicatorId = `${componentPlayer.id}ScrollDownIndicator`;
    const scrollDownIndicator = document.getElementById(scrollDownIndicatorId);
    
    if (!componentPlayer || !scrollDownIndicator) return;
    
    let isScrolling = false;
    
    componentPlayer.addEventListener('wheel', function(e) {
        if (isScrolling) return;
        
        // Only trigger if scroll down indicator is active
        if (scrollDownIndicator.classList.contains('active')) {
            isScrolling = true;
            
            // Hide scroll indicator
            scrollDownIndicator.classList.remove('active');
            setTimeout(() => {
                scrollDownIndicator.style.display = "none";
            }, 500);
            
            // Trigger merchandise section display
            const merchContainerId = `${componentPlayer.id}MerchandiseContainer`;
            const merchContainer = document.getElementById(merchContainerId);
            if (merchContainer) {
                // If merchandise container exists, show first merchandise section
                const navigateEvent = new Event('navigateToMerch');
                navigateEvent.sectionIndex = 1;
                componentPlayer.dispatchEvent(navigateEvent);
            }
            
            // Reset after animation
            setTimeout(() => {
                isScrolling = false;
            }, 1000);
        }
    }, { passive: false });
    
    // Add click event to scroll indicator
    scrollDownIndicator.addEventListener('click', function() {
        if (scrollDownIndicator.classList.contains('active')) {
            scrollDownIndicator.classList.remove('active');
            setTimeout(() => {
                scrollDownIndicator.style.display = "none";
            }, 500);
            
            // Trigger merchandise section display
            const merchContainerId = `${componentPlayer.id}MerchandiseContainer`;
            const merchContainer = document.getElementById(merchContainerId);
            if (merchContainer) {
                // If merchandise container exists, show first merchandise section
                const navigateEvent = new Event('navigateToMerch');
                navigateEvent.sectionIndex = 1;
                componentPlayer.dispatchEvent(navigateEvent);
            }
        }
    });
}

// Initialize the scroll listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const componentPlayers = [
        "motorSequencePlayer",
        "batterySequencePlayer",
        "controllerSequencePlayer",
        "rimSequencePlayer",
        "apuSequencePlayer",
        "chassisSequencePlayer",
        "headlightSequencePlayer",
        "chargingCableSequencePlayer",
        "bodySequencePlayer",
        "chargingPortSequencePlayer",
        "dashboardSequencePlayer",
        "boostSequencePlayer",
        "bootSpaceSequencePlayer"
    ];
    
    componentPlayers.forEach(playerId => {
        const player = document.getElementById(playerId);
        if (player) {
            addComponentOverlayScrollListener(player);
        }
    });
});

// Generic Component Overlay with Merchandise Sections Integration
function initComponentOverlayWithMerchandise(componentName, overlayPlayer, overlayImage, bigTextId, subTextId) {
    const componentData = componentInfo[componentName];
    if (!componentData) return;
    
    const overlayFull = document.getElementById(`${componentName}OverlayFull`);
    const closeBtn = document.getElementById(`${componentName}CloseBtn`);
    const bigText = document.getElementById(bigTextId);
    const subText = document.getElementById(subTextId);
    
    let isMerchandiseMode = false;
    let currentMerchSection = 0;
    const totalMerchSections = 5; // 5 merchandise sections for each component
    let isAnimating = false;
    
    // Remove existing merchandise container if any
    const existingMerchContainer = document.getElementById(`${overlayPlayer.id}MerchandiseContainer`);
    if (existingMerchContainer) {
        existingMerchContainer.remove();
    }
    
    // Create merchandise sections container
    const merchContainer = document.createElement('div');
    merchContainer.id = `${overlayPlayer.id}MerchandiseContainer`;
    merchContainer.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        overflow: hidden;
        background: #000;
        z-index: 10;
    `;
    
    // Create merchandise sections structure with simplified content
    const merchSections = componentData.images || [];
    
    // Create left side for images
    const merchLeft = document.createElement('div');
    merchLeft.className = 'merchandise-left';
    merchLeft.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 55%;
        height: 100%;
        background: #000;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 5;
    `;
    
    // Create image container with multiple images
    const imageContainer = document.createElement('div');
    imageContainer.className = 'component-image-container';
    imageContainer.style.cssText = `
        position: relative;
        width: 80%;
        height: 80%;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    // Determine which images to use based on component
    let imagesToUse = [];
    
    if (componentName === 'motor') {
        // For motor, use carbon belt images
        imagesToUse = [
            'Curbon_belt/0075.webp',
            'Curbon_belt/0076.webp',
            'Curbon_belt/0077.webp',
            'Curbon_belt/0078.webp',
            'Curbon_belt/0079.webp'
        ];
    } else if (componentData.images && componentData.images.length > 0) {
        // For other components, use their specific images
        imagesToUse = componentData.images.map(img => img.image);
    } else {
        // Fallback to component frames if no specific images
        for (let i = 1; i <= 5; i++) {
            imagesToUse.push(`${componentName}/${String(i + 75).padStart(4, "0")}.webp`);
        }
    }
    
    // Create and append images
    imagesToUse.forEach((src, index) => {
        const componentImg = document.createElement('img');
        componentImg.className = 'component-image';
        componentImg.src = src;
        componentImg.alt = `${componentName} Image ${index + 1}`;
        componentImg.style.cssText = `
            width: 100%;
            height: auto;
            object-fit: contain;
            position: absolute;
            top: 0;
            left: 0;
            opacity: ${index === 0 ? 1 : 0};
            transition: opacity 0.5s ease-in-out;
        `;
        
        // Add error handling for the images
        componentImg.onerror = function() {
            console.error(`Failed to load ${src}`);
            // Try alternative paths
            const altSrc = src.replace('Curbon_belt', 'carbon_belt');
            if (altSrc !== src) {
                this.src = altSrc;
                this.onerror = function() {
                    console.error(`Failed to load ${altSrc}`);
                    // Try one more path with .png extension
                    this.src = src.replace('.webp', '.png');
                    this.onerror = function() {
                        console.error(`Failed to load ${src.replace('.webp', '.png')}`);
                        // If all fails, show a placeholder or hide the image
                        this.style.display = 'none';
                    };
                };
            } else {
                // Try one more path with .png extension
                this.src = src.replace('.webp', '.png');
                this.onerror = function() {
                    console.error(`Failed to load ${src.replace('.webp', '.png')}`);
                    // If all fails, show a placeholder or hide the image
                    this.style.display = 'none';
                };
            }
        };
        
        imageContainer.appendChild(componentImg);
    });
    
    merchLeft.appendChild(imageContainer);
    
    // Create right side for content
    const merchRight = document.createElement('div');
    merchRight.className = 'merchandise-right';
    merchRight.style.cssText = `
        position: absolute;
        top: 0;
        right: 0;
        width: 45%;
        height: 100%;
        background: #000;
        overflow: hidden;
        z-index: 5;
    `;
    
    const contentSections = document.createElement('div');
    contentSections.className = 'content-sections';
    contentSections.style.cssText = `
        position: relative;
        height: 100%;
        transition: transform 1s ease-out;
    `;
    
    // Create content sections
    merchSections.forEach((section, index) => {
        const contentSection = document.createElement('div');
        contentSection.className = 'content-section';
        contentSection.id = `${componentName}-merch-section-${index}`;
        contentSection.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            opacity: 1;
            transition: opacity 1.5s ease, transform 1s ease-out;
            z-index: 6;
        `;
        
        if (index === 0) contentSection.classList.add('active');
        
        const content = document.createElement('div');
        content.innerHTML = `
            <h1 class="model-title">${section.title}</h1>
            <p class="model-description">${section.description}</p>
        `;
        
        contentSection.appendChild(content);
        contentSections.appendChild(contentSection);
    });
    
    merchRight.appendChild(contentSections);
    
    // Create section indicator
    const sectionIndicator = document.createElement('div');
    sectionIndicator.className = 'section-indicator';
    sectionIndicator.style.cssText = `
        position: fixed;
        right: 30px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        gap: 10px;
        z-index: 100;
    `;
    
    // Create dots for sections (including component section)
    for (let i = 0; i <= totalMerchSections; i++) {
        const dot = document.createElement('div');
        dot.className = 'section-dot';
        if (i === 0) dot.classList.add('active');
        dot.dataset.section = i;
        dot.addEventListener('click', () => navigateToSection(i));
        sectionIndicator.appendChild(dot);
    }
    
    // Create scroll up button
    const scrollUpButton = document.createElement('button');
    scrollUpButton.className = 'component-scroll-up-button';
    scrollUpButton.innerHTML = '↑';
    scrollUpButton.title = 'Scroll to Top';
    scrollUpButton.addEventListener('click', () => {
        navigateToSection(0);
    });
    
    // Assemble merchandise container
    merchContainer.appendChild(merchLeft);
    merchContainer.appendChild(merchRight);
    merchContainer.appendChild(sectionIndicator);
    merchContainer.appendChild(scrollUpButton);
    overlayPlayer.appendChild(merchContainer);
    
    // Function to update component image based on current section
    function updateComponentImage(sectionIndex) {
        const images = imageContainer.querySelectorAll('.component-image');
        images.forEach((img, index) => {
            img.style.opacity = index === sectionIndex ? 1 : 0;
        });
    }
    
    // Navigation functions
    function navigateToSection(index) {
        if (isAnimating || index < 0 || index > totalMerchSections) return;
        
        isAnimating = true;
        
        // Update dots
        sectionIndicator.querySelectorAll('.section-dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
        
        if (index === 0) {
            // Show component section
            isMerchandiseMode = false;
            overlayImage.style.display = 'block';
            bigText.style.display = 'block';
            subText.style.display = 'block';
            merchContainer.style.display = 'none';
            
            // Show scroll down indicator when going back to component section
            const scrollDownIndicator = document.getElementById(`${overlayPlayer.id}ScrollDownIndicator`);
            if (scrollDownIndicator) {
                scrollDownIndicator.style.display = "flex";
                setTimeout(() => {
                    scrollDownIndicator.classList.add("active");
                }, 300);
            }
            
            // Hide scroll up button
            scrollUpButton.classList.remove('visible');
        } else {
            // Show merchandise section
            if (!isMerchandiseMode) {
                isMerchandiseMode = true;
                overlayImage.style.display = 'none';
                bigText.style.display = 'none';
                subText.style.display = 'none';
                merchContainer.style.display = 'block';
            }
            
            // Hide scroll down indicator when navigating to merchandise sections
            const scrollDownIndicator = document.getElementById(`${overlayPlayer.id}ScrollDownIndicator`);
            if (scrollDownIndicator) {
                scrollDownIndicator.style.display = "none";
            }
            
            // Show scroll up button
            scrollUpButton.classList.add('visible');
            
            currentMerchSection = index - 1;
            
            // Update component image
            updateComponentImage(currentMerchSection);
            
            // Update merchandise sections
            contentSections.querySelectorAll('.content-section').forEach((section, i) => {
                section.classList.remove('active', 'prev', 'next');
                if (i === currentMerchSection) {
                    section.classList.add('active');
                    // Ensure the text is visible
                    const description = section.querySelector('.model-description');
                    if (description) {
                        description.style.display = 'block';
                        description.style.opacity = '1';
                        description.style.color = '#ccc';
                    }
                } else if (i < currentMerchSection) {
                    section.classList.add('prev');
                } else {
                    section.classList.add('next');
                }
            });
        }
        
        setTimeout(() => {
            isAnimating = false;
        }, 1000);
    }
    
    // Listen for custom navigation event
    overlayPlayer.addEventListener('navigateToMerch', function(e) {
        navigateToSection(e.sectionIndex || 1);
    });
    
    // Initialize controlled scrolling
    let accumulatedDelta = 0;
    let lastScrollTime = 0;
    const scrollThreshold = 50; // Minimum scroll distance to trigger section change
    const scrollCooldown = 500; // Cooldown period in milliseconds
    
    function handleWheel(e) {
        if (isAnimating) return;
        
        const now = Date.now();
        if (now - lastScrollTime < scrollCooldown) return;
        
        accumulatedDelta += e.deltaY;
        
        if (Math.abs(accumulatedDelta) >= scrollThreshold) {
            if (accumulatedDelta > 0) { // Scroll down
                if (!isMerchandiseMode) {
                    navigateToSection(1); // Go to first merchandise section
                } else if (currentMerchSection < totalMerchSections - 1) {
                    navigateToSection(currentMerchSection + 2); // +2 because section 0 is component
                }
            } else { // Scroll up
                if (isMerchandiseMode) {
                    if (currentMerchSection > 0) {
                        navigateToSection(currentMerchSection); // Go to previous merchandise section
                    } else {
                        navigateToSection(0); // Go back to component section
                    }
                }
            }
            
            accumulatedDelta = 0;
            lastScrollTime = now;
        }
        
        e.preventDefault();
    }
    
    // Add event listener with passive: false to allow preventDefault
    overlayPlayer.addEventListener('wheel', handleWheel, { passive: false });
    
    // Touch handlers for mobile
    let touchStartY = 0;
    
    function handleTouchStart(e) {
        touchStartY = e.touches[0].clientY;
    }
    
    function handleTouchEnd(e) {
        if (isAnimating) return;
        
        const touchEndY = e.changedTouches[0].clientY;
        const deltaY = touchEndY - touchStartY;
        
        if (Math.abs(deltaY) > 50) {
            if (deltaY < 0) { // Swipe up
                if (!isMerchandiseMode) {
                    navigateToSection(1);
                } else if (currentMerchSection < totalMerchSections - 1) {
                    navigateToSection(currentMerchSection + 2);
                }
            } else { // Swipe down
                if (isMerchandiseMode) {
                    if (currentMerchSection > 0) {
                        navigateToSection(currentMerchSection);
                    } else {
                        navigateToSection(0);
                    }
                }
            }
        }
    }
    
    // Add event listeners
    overlayPlayer.addEventListener('touchstart', handleTouchStart, { passive: true });
    overlayPlayer.addEventListener('touchend', handleTouchEnd, { passive: true });
    
    // Add CSS for merchandise sections if not already added
    if (!document.getElementById('merchandise-section-styles')) {
        const style = document.createElement('style');
        style.id = 'merchandise-section-styles';
        style.textContent = `
            .model-title {
                font-size: 72px;
                font-weight: 300;
                margin-bottom: 20px;
                line-height: 1.1;
                color: #FFFFFF;
            }
            .model-title .highlight {
                color: #CE6723;
            }
            .model-description {
                font-size: 16px !important;
                line-height: 1.5 !important;
                margin-bottom: 40px;
                color: #777 !important;
                max-width: 80%;
                display: block;
                margin-bottom: 15px !important;
            }
            .section-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.3);
                cursor: pointer;
                transition: all 0.3s;
            }
            .section-dot.active {
                background-color: #CE6723;
                transform: scale(1.3);
            }
            .content-section.active {
                transform: translateY(0);
                opacity: 1;
            }
            .content-section.next {
                transform: translateY(100%);
                opacity: 0;
            }
            .content-section.prev {
                transform: translateY(-100%);
                opacity: 0;
            }
            .component-image-container {
                position: relative;
                width: 80%;
                height: 80%;
            }
            .component-image {
                width: 100%;
                height: auto;
                object-fit: contain;
                position: absolute;
                top: 0;
                left: 0;
                transition: opacity 0.5s ease-in-out;
            }
        `;
        document.head.appendChild(style);
    }
}

// Enhanced overlay reset function
function resetAllOverlays() {
    // Reset all component overlays
    resetMotorOverlay();
    resetBatteryOverlay();
    resetControllerOverlay();
    resetRimOverlay();
    resetApuOverlay();
    resetChassisOverlay();
    resetHeadlightOverlay();
    resetChargingCableOverlay();
    resetBodyOverlay();
    resetChargingPortOverlay();
    resetDashboardOverlay();
    resetBoostOverlay();
    resetBootSpaceOverlay();
    
    // Reset story overlay
    closeStoryOverlay();
    
    // Ensure overlay state is reset
    isOverlayOpen = false;
    
    // Clear any pending timeouts
    if (motorAnimationTimeout) {
        clearTimeout(motorAnimationTimeout);
        motorAnimationTimeout = null;
    }
}

// Modified animateToLeftPanel function
function animateToLeftPanel(component) {
    // Reset all overlays first
    resetAllOverlays();
    
    isOverlayOpen = true;
    let overlay;
    switch (component) {
        case "motor": 
            overlay = motorOverlayFull; 
            break;
        case "battery": overlay = batteryOverlayFull; break;
        case "controler": overlay = controllerOverlayFull; break;
        case "rim": overlay = rimOverlayFull; break;
        case "apu": overlay = apuOverlayFull; break;
        case "chassis": overlay = chassisOverlayFull; break;
        case "headlight": overlay = headlightOverlayFull; break;
        case "chargingCable": overlay = chargingCableOverlayFull; break;
        case "body": overlay = bodyOverlayFull; break;
        case "chargingport": overlay = chargingPortOverlayFull; break;
        case "dashboard": overlay = dashboardOverlayFull; break;
        case "boost": overlay = boostOverlayFull; break;
        case "bootspace": overlay = bootSpaceOverlayFull; break;
        default: console.error("Unknown component:", component); isOverlayOpen = false; return;
    }
    
    overlay.style.display = "block";
    
    const img = document.createElement("img");
    img.src = `${component}/0120.webp`;
    img.style.position = "fixed";
    img.style.left = "100%";
    img.style.top = "50%";
    img.style.transform = "translateY(-50%) scale(0.5)";
    
    const specificStyles = componentSpecificStyles[component];
    if (specificStyles) {
        img.style.width = specificStyles.width;
        img.style.height = specificStyles.height;
        img.style.maxWidth = 'none';
        img.style.maxHeight = 'none';
    } else {
        img.style.maxWidth = '100vw';
        img.style.maxHeight = '100vh';
        img.style.width = 'auto';
        img.style.height = 'auto';
    }
    
    img.style.objectFit = "contain";
    img.style.opacity = "1";
    img.style.zIndex = "9999";
    img.style.transition = "left 2s ease-out, transform 2s ease-out, opacity 2s ease-out";
    
    document.body.appendChild(img);
    
    setTimeout(() => {
        img.style.left = "50%";
        img.style.transform = "translate(-50%, -50%) scale(1)";
    }, 50);
    
    setTimeout(() => {
        img.style.opacity = "0";
    }, 1800);
    
    const timeoutId = setTimeout(() => {
        document.body.removeChild(img);
        
        switch (component) {
            case "motor": playMotorSequenceReverse(); break;
            case "battery": playBatterySequenceReverse(); break;
            case "controler": playControllerSequenceReverse(); break;
            case "rim": playRimSequenceReverse(); break;
            case "apu": playApuSequenceReverse(); break;
            case "chassis": playChassisSequenceReverse(); break;
            case "headlight": playHeadlightSequenceReverse(); break;
            case "chargingCable": playChargingCableSequenceReverse(); break;
            case "body": playBodySequenceReverse(); break;
            case "chargingport": playChargingPortSequenceReverse(); break;
            case "dashboard": playDashboardSequenceReverse(); break;
            case "boost": playBoostSequenceReverse(); break;
            case "bootspace": playBootSpaceSequenceReverse(); break;
        }
    }, 2050);
    
    if (component === "motor") {
        motorAnimationTimeout = timeoutId;
    }
}

// Enhanced reset functions for each component
function resetMotorOverlay() {
    if (!isOverlayOpen && motorOverlayFull.style.display !== "block") return;
    
    motorOverlayFull.style.display = "none";
    motorPlayer.style.display = "none";
    motorCloseBtn.style.opacity = "0";
    motorCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("motorBigText");
    const subText = document.getElementById("motorSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (motorImage) {
        motorImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("motorSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('motorSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetBatteryOverlay() {
    if (!isOverlayOpen && batteryOverlayFull.style.display !== "block") return;
    
    batteryOverlayFull.style.display = "none";
    batteryPlayer.style.display = "none";
    batteryCloseBtn.style.opacity = "0";
    batteryCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("batteryBigText");
    const subText = document.getElementById("batterySubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (batteryImage) {
        batteryImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("batterySequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('batterySequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetControllerOverlay() {
    if (!isOverlayOpen && controllerOverlayFull.style.display !== "block") return;
    
    controllerOverlayFull.style.display = "none";
    controllerPlayer.style.display = "none";
    controllerCloseBtn.style.opacity = "0";
    controllerCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("controllerBigText");
    const subText = document.getElementById("controllerSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (controllerImage) {
        controllerImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("controllerSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('controllerSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetRimOverlay() {
    if (!isOverlayOpen && rimOverlayFull.style.display !== "block") return;
    
    rimOverlayFull.style.display = "none";
    rimPlayer.style.display = "none";
    rimCloseBtn.style.opacity = "0";
    rimCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("rimBigText");
    const subText = document.getElementById("rimSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (rimImage) {
        rimImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("rimSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('rimSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetApuOverlay() {
    if (!isOverlayOpen && apuOverlayFull.style.display !== "block") return;
    
    apuOverlayFull.style.display = "none";
    apuPlayer.style.display = "none";
    apuCloseBtn.style.opacity = "0";
    apuCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("apuBigText");
    const subText = document.getElementById("apuSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (apuImage) {
        apuImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("apuSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('apuSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetChassisOverlay() {
    if (!isOverlayOpen && chassisOverlayFull.style.display !== "block") return;
    
    chassisOverlayFull.style.display = "none";
    chassisPlayer.style.display = "none";
    chassisCloseBtn.style.opacity = "0";
    chassisCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("chassisBigText");
    const subText = document.getElementById("chassisSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (chassisImage) {
        chassisImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("chassisSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('chassisSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetHeadlightOverlay() {
    if (!isOverlayOpen && headlightOverlayFull.style.display !== "block") return;
    
    headlightOverlayFull.style.display = "none";
    headlightPlayer.style.display = "none";
    headlightCloseBtn.style.opacity = "0";
    headlightCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("headlightBigText");
    const subText = document.getElementById("headlightSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (headlightImage) {
        headlightImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("headlightSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('headlightSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetChargingCableOverlay() {
    if (!isOverlayOpen && chargingCableOverlayFull.style.display !== "block") return;
    
    chargingCableOverlayFull.style.display = "none";
    chargingCablePlayer.style.display = "none";
    chargingCableCloseBtn.style.opacity = "0";
    chargingCableCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("chargingCableBigText");
    const subText = document.getElementById("chargingCableSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (chargingCableImage) {
        chargingCableImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("chargingCableSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('chargingCableSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetBodyOverlay() {
    if (!isOverlayOpen && bodyOverlayFull.style.display !== "block") return;
    
    bodyOverlayFull.style.display = "none";
    bodyPlayer.style.display = "none";
    bodyCloseBtn.style.opacity = "0";
    bodyCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("bodyBigText");
    const subText = document.getElementById("bodySubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (bodyImage) {
        bodyImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("bodySequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('bodySequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetChargingPortOverlay() {
    if (!isOverlayOpen && chargingPortOverlayFull.style.display !== "block") return;
    
    chargingPortOverlayFull.style.display = "none";
    chargingPortPlayer.style.display = "none";
    chargingPortCloseBtn.style.opacity = "0";
    chargingPortCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("chargingPortBigText");
    const subText = document.getElementById("chargingPortSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (chargingPortImage) {
        chargingPortImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("chargingPortSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('chargingPortSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetDashboardOverlay() {
    if (!isOverlayOpen && dashboardOverlayFull.style.display !== "block") return;
    
    dashboardOverlayFull.style.display = "none";
    dashboardPlayer.style.display = "none";
    dashboardCloseBtn.style.opacity = "0";
    dashboardCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("dashboardBigText");
    const subText = document.getElementById("dashboardSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (dashboardImage) {
        dashboardImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("dashboardSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('dashboardSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetBoostOverlay() {
    if (!isOverlayOpen && boostOverlayFull.style.display !== "block") return;
    
    boostOverlayFull.style.display = "none";
    boostPlayer.style.display = "none";
    boostCloseBtn.style.opacity = "0";
    boostCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("boostBigText");
    const subText = document.getElementById("boostSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (boostImage) {
        boostImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("boostSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('boostSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

function resetBootSpaceOverlay() {
    if (!isOverlayOpen && bootSpaceOverlayFull.style.display !== "block") return;
    
    bootSpaceOverlayFull.style.display = "none";
    bootSpacePlayer.style.display = "none";
    bootSpaceCloseBtn.style.opacity = "0";
    bootSpaceCloseBtn.style.pointerEvents = "none";
    
    const bigText = document.getElementById("bootSpaceBigText");
    const subText = document.getElementById("bootSpaceSubText");
    
    if (bigText) {
        bigText.style.transition = "none";
        bigText.style.opacity = "0";
        bigText.style.transform = "translateY(60px)";
    }
    
    if (subText) {
        subText.style.transition = "none";
        subText.style.opacity = "0";
        subText.style.transform = "translateY(60px)";
    }
    
    if (bootSpaceImage) {
        bootSpaceImage.style.transform = "translateX(0)";
    }
    
    // Reset scroll down indicator
    const scrollDownIndicator = document.getElementById("bootSpaceSequencePlayerScrollDownIndicator");
    if (scrollDownIndicator) {
        scrollDownIndicator.style.display = "none";
        scrollDownIndicator.classList.remove("active");
    }
    
    // Remove merchandise container if exists
    const merchContainer = document.getElementById('bootSpaceSequencePlayerMerchandiseContainer');
    if (merchContainer) {
        merchContainer.remove();
    }
}

// Motor Overlay Script 
function playMotorSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    motorCloseBtn.style.opacity = "0";
    motorCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("motorBigText");
    const subText = document.getElementById("motorSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    motorPlayer.style.display = "flex";
    motorImage.style.transition = 'transform 0.03s linear';
    let frame = totalMotorFrames;
    const startX = 0;
    const endX = -413.295;
    const totalSteps = totalMotorFrames;
    let step = 0;
    const interval = setInterval(() => {
        motorImage.src = `motor/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * (endX - startX);
        motorImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 2.5s ease-out, transform 2.5s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 3s ease-out, transform 3s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                motorCloseBtn.style.opacity = "1";
                motorCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("motorSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after motor animation
                initComponentOverlayWithMerchandise('motor', motorPlayer, motorImage, 'motorBigText', 'motorSubText');
            }, 300);
        }
    }, 30);
}

function playBatterySequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    batteryCloseBtn.style.opacity = "0";
    batteryCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("batteryBigText");
    const subText = document.getElementById("batterySubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    batteryPlayer.style.display = "flex";
    batteryImage.style.transition = 'transform 0.03s linear';
    let frame = totalBatteryFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalBatteryFrames;
    let step = 0;
    const interval = setInterval(() => {
        batteryImage.src = `battery/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        batteryImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                batteryCloseBtn.style.opacity = "1";
                batteryCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("batterySequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after battery animation
                initComponentOverlayWithMerchandise('battery', batteryPlayer, batteryImage, 'batteryBigText', 'batterySubText');
            }, 300);
        }
    }, 30);
}

function playControllerSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    controllerCloseBtn.style.opacity = "0";
    controllerCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("controllerBigText");
    const subText = document.getElementById("controllerSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    controllerPlayer.style.display = "flex";
    controllerImage.style.transition = 'transform 0.03s linear';
    let frame = totalControllerFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalControllerFrames;
    let step = 0;
    const interval = setInterval(() => {
        controllerImage.src = `controler/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        controllerImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                controllerCloseBtn.style.opacity = "1";
                controllerCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("controllerSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after controller animation
                initComponentOverlayWithMerchandise('controler', controllerPlayer, controllerImage, 'controllerBigText', 'controllerSubText');
            }, 300);
        }
    }, 30);
}

function playRimSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    rimCloseBtn.style.opacity = "0";
    rimCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("rimBigText");
    const subText = document.getElementById("rimSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    rimPlayer.style.display = "flex";
    rimImage.style.transition = 'transform 0.03s linear';
    let frame = totalRimFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalRimFrames;
    let step = 0;
    const interval = setInterval(() => {
        rimImage.src = `rim/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * (endX - startX);
        rimImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                rimCloseBtn.style.opacity = "1";
                rimCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("rimSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after rim animation
                initComponentOverlayWithMerchandise('rim', rimPlayer, rimImage, 'rimBigText', 'rimSubText');
            }, 300);
        }
    }, 30);
}

function playApuSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    apuCloseBtn.style.opacity = "0";
    apuCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("apuBigText");
    const subText = document.getElementById("apuSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    apuPlayer.style.display = "flex";
    apuImage.style.transition = 'transform 0.03s linear';
    let frame = totalApuFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalApuFrames;
    let step = 0;
    const interval = setInterval(() => {
        apuImage.src = `apu/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        apuImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                apuCloseBtn.style.opacity = "1";
                apuCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("apuSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after apu animation
                initComponentOverlayWithMerchandise('apu', apuPlayer, apuImage, 'apuBigText', 'apuSubText');
            }, 300);
        }
    }, 30);
}

function playChassisSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    chassisCloseBtn.style.opacity = "0";
    chassisCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("chassisBigText");
    const subText = document.getElementById("chassisSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    chassisPlayer.style.display = "flex";
    chassisImage.style.transition = 'transform 0.03s linear';
    let frame = totalChassisFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalChassisFrames;
    let step = 0;
    const interval = setInterval(() => {
        chassisImage.src = `chassis/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * (endX - startX);
        chassisImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                chassisCloseBtn.style.opacity = "1";
                chassisCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("chassisSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after chassis animation
                initComponentOverlayWithMerchandise('chassis', chassisPlayer, chassisImage, 'chassisBigText', 'chassisSubText');
            }, 300);
        }
    }, 30);
}

function playHeadlightSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    headlightCloseBtn.style.opacity = "0";
    headlightCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("headlightBigText");
    const subText = document.getElementById("headlightSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    headlightPlayer.style.display = "flex";
    headlightImage.style.transition = 'transform 0.03s linear';
    let frame = totalHeadlightFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalHeadlightFrames;
    let step = 0;
    const interval = setInterval(() => {
        headlightImage.src = `headlight/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        headlightImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                headlightCloseBtn.style.opacity = "1";
                headlightCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("headlightSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after headlight animation
                initComponentOverlayWithMerchandise('headlight', headlightPlayer, headlightImage, 'headlightBigText', 'headlightSubText');
            }, 300);
        }
    }, 30);
}

function playChargingCableSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    chargingCableCloseBtn.style.opacity = "0";
    chargingCableCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("chargingCableBigText");
    const subText = document.getElementById("chargingCableSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    chargingCablePlayer.style.display = "flex";
    chargingCableImage.style.transition = 'transform 0.03s linear';
    let frame = totalChargingCableFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalChargingCableFrames;
    let step = 0;
    const interval = setInterval(() => {
        chargingCableImage.src = `chargingCable/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        chargingCableImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                chargingCableCloseBtn.style.opacity = "1";
                chargingCableCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("chargingCableSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after chargingCable animation
                initComponentOverlayWithMerchandise('chargingCable', chargingCablePlayer, chargingCableImage, 'chargingCableBigText', 'chargingCableSubText');
            }, 300);
        }
    }, 30);
}

function playBodySequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    bodyCloseBtn.style.opacity = "0";
    bodyCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("bodyBigText");
    const subText = document.getElementById("bodySubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    bodyPlayer.style.display = "flex";
    bodyImage.style.transition = 'transform 0.03s linear';
    let frame = totalBodyFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalBodyFrames;
    let step = 0;
    const interval = setInterval(() => {
        bodyImage.src = `body/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * (endX - startX);
        bodyImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                bodyCloseBtn.style.opacity = "1";
                bodyCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("bodySequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after body animation
                initComponentOverlayWithMerchandise('body', bodyPlayer, bodyImage, 'bodyBigText', 'bodySubText');
            }, 300);
        }
    }, 30);
}

function playChargingPortSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    chargingPortCloseBtn.style.opacity = "0";
    chargingPortCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("chargingPortBigText");
    const subText = document.getElementById("chargingPortSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    chargingPortPlayer.style.display = "flex";
    chargingPortImage.style.transition = 'transform 0.03s linear';
    let frame = totalChargingPortFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalChargingPortFrames;
    let step = 0;
    const interval = setInterval(() => {
        chargingPortImage.src = `chargingport/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        chargingPortImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                chargingPortCloseBtn.style.opacity = "1";
                chargingPortCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("chargingPortSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after chargingport animation
                initComponentOverlayWithMerchandise('chargingport', chargingPortPlayer, chargingPortImage, 'chargingPortBigText', 'chargingPortSubText');
            }, 300);
        }
    }, 30);
}

function playDashboardSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    dashboardCloseBtn.style.opacity = "0";
    dashboardCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("dashboardBigText");
    const subText = document.getElementById("dashboardSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    dashboardPlayer.style.display = "flex";
    dashboardImage.style.transition = 'transform 0.03s linear';
    let frame = totalDashboardFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5; // Adjust based on your design
    const totalSteps = totalDashboardFrames;
    let step = 0;
    const interval = setInterval(() => {
        dashboardImage.src = `dashboard/${formatMotorFrame(frame)}.webp`; // Fixed path
        const percent = step / totalSteps;
        const currentX = startX + percent * (endX - startX);
        dashboardImage.style.transform = `translateX(${currentX}px)`;
        dashboardImage.style.opacity = "1"; // Ensure image is visible
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                dashboardCloseBtn.style.opacity = "1";
                dashboardCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("dashboardSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after dashboard animation
                initComponentOverlayWithMerchandise('dashboard', dashboardPlayer, dashboardImage, 'dashboardBigText', 'dashboardSubText');
            }, 300);
        }
    }, 30);
}

function playBoostSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    boostCloseBtn.style.opacity = "0";
    boostCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("boostBigText");
    const subText = document.getElementById("boostSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    boostPlayer.style.display = "flex";
    boostImage.style.transition = 'transform 0.03s linear';
    let frame = totalBoostFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalBoostFrames;
    let step = 0;
    const interval = setInterval(() => {
        boostImage.src = `boost/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        boostImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                boostCloseBtn.style.opacity = "1";
                boostCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("boostSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after boost animation
                initComponentOverlayWithMerchandise('boost', boostPlayer, boostImage, 'boostBigText', 'boostSubText');
            }, 300);
        }
    }, 30);
}

function playBootSpaceSequenceReverse() {
    document.removeEventListener('keydown', escKeyHandler);
    bootSpaceCloseBtn.style.opacity = "0";
    bootSpaceCloseBtn.style.pointerEvents = "none";
    const bigText = document.getElementById("bootSpaceBigText");
    const subText = document.getElementById("bootSpaceSubText");
    bigText.style.opacity = "0";
    bigText.style.transform = "translateY(60px)";
    subText.style.opacity = "0";
    subText.style.transform = "translateY(60px)";
    bootSpacePlayer.style.display = "flex";
    bootSpaceImage.style.transition = 'transform 0.03s linear';
    let frame = totalBootSpaceFrames;
    const startX = 0;
    const endX = -window.innerWidth / 5;
    const totalSteps = totalBootSpaceFrames;
    let step = 0;
    const interval = setInterval(() => {
        bootSpaceImage.src = `bootspace/${formatMotorFrame(frame)}.webp`;
        const percent = step / totalSteps;
        const currentX = startX + percent * endX;
        bootSpaceImage.style.transform = `translateX(${currentX}px)`;
        frame--;
        step++;
        if (frame < 1) {
            clearInterval(interval);
            setTimeout(() => {
                bigText.style.transition = "opacity 0.8s ease-out, transform 0.8s ease-out";
                bigText.style.opacity = "1";
                bigText.style.transform = "translateY(-100px)";
                subText.style.transition = "opacity 1.0s ease-out, transform 1.0s ease-out";
                subText.style.opacity = "1";
                subText.style.transform = "translateY(0)";
                document.addEventListener('keydown', escKeyHandler);
                bootSpaceCloseBtn.style.opacity = "1";
                bootSpaceCloseBtn.style.pointerEvents = "auto";
                
                // Show scroll down indicator
                const scrollDownIndicator = document.getElementById("bootSpaceSequencePlayerScrollDownIndicator");
                if (scrollDownIndicator) {
                    scrollDownIndicator.style.display = "flex";
                    setTimeout(() => {
                        scrollDownIndicator.classList.add("active");
                    }, 300);
                }
                
                // Initialize merchandise sections after bootspace animation
                initComponentOverlayWithMerchandise('bootspace', bootSpacePlayer, bootSpaceImage, 'bootSpaceBigText', 'bootSpaceSubText');
            }, 300);
        }
    }, 30);
}

// Event Listeners for Click Areas
motorClickArea.addEventListener("click", () => animateToLeftPanel("motor"));
batteryClickArea.addEventListener("click", () => animateToLeftPanel("battery"));
controllerClickArea.addEventListener("click", () => animateToLeftPanel("controler"));
rimClickArea.addEventListener("click", () => animateToLeftPanel("rim"));
apuClickArea.addEventListener("click", () => animateToLeftPanel("apu"));
chassisClickArea.addEventListener("click", () => animateToLeftPanel("chassis"));
headlightClickArea.addEventListener("click", () => animateToLeftPanel("headlight"));
chargingCableClickArea.addEventListener("click", () => animateToLeftPanel("chargingCable"));
bodyClickArea.addEventListener("click", () => animateToLeftPanel("body"));
chargingPortClickArea.addEventListener("click", () => animateToLeftPanel("chargingport"));
dashboardClickArea.addEventListener("click", () => animateToLeftPanel("dashboard"));
boostClickArea.addEventListener("click", () => animateToLeftPanel("boost"));
bootSpaceClickArea.addEventListener("click", () => animateToLeftPanel("bootspace"));

// Event Listeners for Close Buttons
motorCloseBtn.addEventListener("click", resetMotorOverlay);
batteryCloseBtn.addEventListener("click", resetBatteryOverlay);
controllerCloseBtn.addEventListener("click", resetControllerOverlay);
rimCloseBtn.addEventListener("click", resetRimOverlay);
apuCloseBtn.addEventListener("click", resetApuOverlay);
chassisCloseBtn.addEventListener("click", resetChassisOverlay);
headlightCloseBtn.addEventListener("click", resetHeadlightOverlay);
chargingCableCloseBtn.addEventListener("click", resetChargingCableOverlay);
bodyCloseBtn.addEventListener("click", resetBodyOverlay);
chargingPortCloseBtn.addEventListener("click", resetChargingPortOverlay);
dashboardCloseBtn.addEventListener("click", resetDashboardOverlay);
boostCloseBtn.addEventListener("click", resetBoostOverlay);
bootSpaceCloseBtn.addEventListener("click", resetBootSpaceOverlay);

// Story Close Button Event Listener
document.getElementById("storyCloseBtn").addEventListener("click", closeStoryOverlay);

// ESC Key Event Listener
document.addEventListener('keydown', escKeyHandler);

// Scroll Down and Scroll Up Indicator Logic
const scrollDown = document.getElementById('scrollDown');
const scrollUp = document.getElementById('scrollUp');
function updateScrollIndicatorsVisibility() {
    const scrollPosition = window.scrollY;
    const sectionHeight = window.innerHeight;
    const currentSectionIndex = Math.floor(scrollPosition / sectionHeight);
    
    if (currentSectionIndex === 0) {
        scrollDown.classList.add('active');
        scrollUp.classList.remove('active');
    } else {
        scrollDown.classList.remove('active');
        scrollUp.classList.add('active');
    }
}

scrollDown.addEventListener('click', () => {
    window.scrollTo({
        top: window.innerHeight,
        behavior: 'smooth'
    });
});

// FIXED: Changed from navigation to page reload
scrollUp.addEventListener('click', (e) => {
    e.preventDefault(); // Prevent default anchor navigation
    window.location.reload(); // Reload the index page
});

window.addEventListener('scroll', updateScrollIndicatorsVisibility);
window.addEventListener('load', updateScrollIndicatorsVisibility);

// Mobile Layout JavaScript - Title above, Image center, Description below
// Mobile detection function
function isMobile() {
    return window.innerWidth <= 767;
}

// Component data with corrected mobile descriptions
const componentData = [
    {
        title: "Copper Fusion System",
        image: "battery/0075.webp",
        description: "Unstoppable energy, engineered for safety, endurance, and limitless journeys."
    },
    {
        title: "PowerCore Controller", 
        image: "controler/0120.webp",
        description: "Take control with precision and power at your fingertips"
    },
    {
        title: "RecoEngine Motor",
        image: "motor/0100.webp", 
        description: "Whisper quiet torque moves you with graceful, effortless power"
    },
    {
        title: "AeroDynamic Wheel",
        image: "rim/0001.webp",
        description: "Engineered for the turn, built for the thrill."
    },
    {
        title: "Auxiliary Power Unit",
        image: "apu/0111.webp",
        description: "World's first built in backup, delivering vital minutes of freedom."
    },
    {
        title: "RIVOT Frame",
        image: "chassis/0001.webp",
        description: "Architecture of Freedom Engineered for the Electric Age."
    },
    {
        title: "VisionX Headlamp",
        image: "headlight/0101.webp",
        description: "The world's first intelligent headlamp, engineered for the elite rider."
    },
    {
        title: "Retractable Cable",
        image: "chargingCable/0118.webp",
        description: "Pull, charge, and retract smart, seamless, and space free."
    },
    {
        title: "nx100 Vanguard",
        image: "body/0001.webp",
        description: "Every curve exudes luxury, confidence, and effortless presence on every ride"
    },
    {
        title: "FlashCharging Port",
        image: "chargingport/0062.webp", 
        description: "The front mounted charging port offers the ultimate in effortless, user centric public charging."
    },
    {
        title: "Pinnacle Display",
        image:" dashboard/0087.webp",
        description: "The world's most commanding EV console experience"
    },
    {
        title: "PowerBoost",
        image: "boost/0077.webp",
        description: "Confidently conquer every overtake with a single button press."
    },
    {
        title: " Air Gesture Key",
        image: "bootspace/0093.webp",
        description: "Effortless access, spacious secure storage unlocked with a simple wave."
    }
];

// Mobile layout initialization
function initMobileLayout() {
    if (isMobile()) {
        // Hide canvas animations on mobile
        const canvases = document.querySelectorAll('#slid-right canvas');
        canvases.forEach(canvas => {
            canvas.style.display = 'none';
        });
        
        // Create structured layout for each component
        const wrappers = document.querySelectorAll('.canvas-wrapper');
        wrappers.forEach((wrapper, index) => {
            if (componentData[index]) {
                // Clear existing content
                wrapper.innerHTML = '';
                
                // Create title element
                const titleElement = document.createElement('h2');
                titleElement.style.cssText = `
                    width: 100%;
                    text-align: center;
                    font-size: 28px;
                    font-weight: bold;
                    color: #fff;
                    margin-bottom: 20px;
                    padding: 0 20px;
                    line-height: 1.3;
                    z-index: 3;
                `;
                titleElement.innerHTML = componentData[index].title;
                
                // Create image container
                const imageContainer = document.createElement('div');
                imageContainer.style.cssText = `
                    width: 100%;
                    height: 60vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 20px 0;
                    position: relative;
                `;
                
                // Create static image
                const staticImg = document.createElement('img');
                staticImg.style.cssText = `
                    width: 280px;
                    height: 280px;
                    object-fit: contain;
                    z-index: 1;
                `;
                staticImg.src = componentData[index].image;
                staticImg.alt = componentData[index].title;
                
                // Create description element
                const descElement = document.createElement('p');
                descElement.style.cssText = `
                    width: 100%;
                    text-align: center;
                    font-size: 16px;
                    color: #ccc;
                    margin-top: 20px;
                    padding: 0 20px;
                    line-height: 1.5;
                    z-index: 3;
                `;
                descElement.textContent = componentData[index].description;
                
                // Create click area
                const clickArea = document.createElement('div');
                clickArea.className = 'click-area';
                clickArea.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 2;
                    background: transparent;
                    cursor: pointer;
                `;
                
                // Copy original click functionality
                const originalClickArea = wrapper.querySelector('.click-area');
                if (originalClickArea) {
                    clickArea.addEventListener('click', function() {
                        originalClickArea.click();
                    });
                }
                
                // Append elements in order: Title -> Image -> Description
                imageContainer.appendChild(staticImg);
                imageContainer.appendChild(clickArea);
                
                wrapper.appendChild(titleElement);
                wrapper.appendChild(imageContainer);
                wrapper.appendChild(descElement);
                
                // Style the wrapper
                wrapper.style.cssText = `
                    position: relative;
                    width: 100%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 40px 20px;
                    background: #000;
                    border-bottom: 1px solid #333;
                `;
            }
        });
        
        // Hide the fixed-text container on mobile
        const fixedText = document.querySelector('.fixed-text');
        if (fixedText) {
            fixedText.style.display = 'none';
        }
    }
}

// Update the existing DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
    // Add mobile layout initialization
    initMobileLayout();
    
    // ... keep your existing code ...
});

document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    
    // Mobile detection function
    function isMobile() {
        return window.innerWidth <= 767;
    }
    
    // Function to keep header visible on mobile
    function keepHeaderVisible() {
        if (isMobile()) {
            header.style.position = 'fixed';
            header.style.top = '0';
            header.style.left = '0';
            header.style.width = '100%';
            header.style.zIndex = '10000';
            header.style.display = 'block';
            header.style.opacity = '1';
            header.style.visibility = 'visible';
            header.style.background = '#000';
            
            // Ensure MeanMenu elements stay visible
            const meanBar = document.querySelector('.mean-bar');
            if (meanBar) {
                meanBar.style.position = 'fixed';
                meanBar.style.top = '0';
                meanBar.style.width = '100%';
                meanBar.style.zIndex = '10002';
                meanBar.style.background = '#000';
            }
            
            const meanReveal = document.querySelector('.meanmenu-reveal');
            if (meanReveal) {
                meanReveal.style.display = 'block';
                meanReveal.style.opacity = '1';
                meanReveal.style.visibility = 'visible';
            }
        }
    }
    
    // Run on load
    keepHeaderVisible();
    
    // Run on scroll and resize
    window.addEventListener('scroll', keepHeaderVisible, { passive: true });
    window.addEventListener('resize', keepHeaderVisible);
});

// Handle window resize
window.addEventListener('resize', function() {
    if (isMobile()) {
        initMobileLayout();
    }
});

 $(document).ready(function(){
      // Toggle mobile menu
      $('.menu-toggle2').click(function() {
        $(this).toggleClass('open');
        $('.nav-menu2').toggleClass('active');
      });
      // Handle navigation on menu item click for mobile
      $('.has-submenu2 > a').click(function(e) {
        if ($(window).width() <= 768) {
          // Navigate to the href directly without preventing default
          window.location.href = $(this).attr('href');
        }
      });
      // Toggle submenu and icon when clicking the "+" or "−" icon
      $('.submenu-toggle').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $this = $(this);
        const $submenu = $this.next('.submenu2');
        // Close all other submenus
        $('.submenu2').not($submenu).removeClass('active');
        $('.submenu-toggle').not($this).removeClass('active');
        // Toggle current submenu
        const isActive = $submenu.hasClass('active');
        $submenu.toggleClass('active');
        $this.toggleClass('active', !isActive);
      });
      // Close menu when clicking outside
      $(document).click(function(e) {
        if (!$(e.target).closest('.navbar2').length) {
          $('.nav-menu2').removeClass('active');
          $('.submenu2').removeClass('active');
          $('.menu-toggle2').removeClass('open');
          $('.submenu-toggle').removeClass('active');
        }
      });
    });

// Add 'loading' class to body during load
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('loading');
    
    window.addEventListener('load', function() {
        document.body.classList.remove('loading');
    });
});

// Enhanced touch gestures for mobile 3D viewer
function initMobile3DViewer() {
    if (isMobile()) {
        const viewer = document.getElementById('viewer');
        const image = document.getElementById('sequenceImage');
        
        if (!viewer || !image) return;
        
        let touchStartX = 0;
        let touchStartY = 0;
        let isTouching = false;
        let currentFrame = 1;
        const totalFrames = 120;
        
        // Add pinch-to-zoom functionality
        let initialDistance = null;
        let currentScale = 1;
        let maxScale = 3;
        let minScale = 0.5;
        
        viewer.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                // Single touch for rotation
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                isTouching = true;
            } else if (e.touches.length === 2) {
                // Two touches for pinch-to-zoom
                initialDistance = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                e.preventDefault();
            }
        });
        
        viewer.addEventListener('touchmove', function(e) {
            if (e.touches.length === 1 && isTouching) {
                // Single touch rotation
                const touchX = e.touches[0].clientX;
                const deltaX = touchX - touchStartX;
                
                if (Math.abs(deltaX) > 10) {
                    const frameChange = Math.round(deltaX / 5);
                    currentFrame = (currentFrame + frameChange) % totalFrames;
                    if (currentFrame <= 0) currentFrame += totalFrames;
                    
                    image.src = `grayscooty/${String(currentFrame).padStart(4, "0")}.webp`;
                    touchStartX = touchX;
                }
            } else if (e.touches.length === 2 && initialDistance !== null) {
                // Pinch-to-zoom
                const currentDistance = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                
                const scale = currentDistance / initialDistance;
                const newScale = Math.max(minScale, Math.min(maxScale, currentScale * scale));
                
                image.style.transform = `scale(${newScale})`;
                e.preventDefault();
            }
        });
        
        viewer.addEventListener('touchend', function(e) {
            if (e.touches.length === 0) {
                isTouching = false;
                initialDistance = null;
                
                // Smoothly return to original scale
                if (currentScale !== 1) {
                    setTimeout(() => {
                        image.style.transition = 'transform 0.3s ease-out';
                        image.style.transform = 'scale(1)';
                        setTimeout(() => {
                            image.style.transition = '';
                            currentScale = 1;
                        }, 300);
                    }, 1000);
                }
            }
        });
        
        // Double tap to zoom
        let lastTap = 0;
        viewer.addEventListener('touchend', function(e) {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;
            
            if (tapLength < 300 && tapLength > 0) {
                // Double tap detected
                if (currentScale === 1) {
                    currentScale = maxScale;
                    image.style.transition = 'transform 0.3s ease-out';
                    image.style.transform = `scale(${maxScale})`;
                } else {
                    currentScale = 1;
                    image.style.transition = 'transform 0.3s ease-out';
                    image.style.transform = 'scale(1)';
                }
                
                setTimeout(() => {
                    image.style.transition = '';
                }, 300);
            }
            
            lastTap = currentTime;
        });
    }
}

// Mobile 360° Rotation Icon Handler
function initMobileRotationIcon() {
    if (window.innerWidth <= 767) {
        const viewer = document.getElementById('viewer');
        const rotationIcon = document.querySelector('.rotation-icon-container');
        
        if (viewer && rotationIcon) {
            // Always show the icon on mobile
            rotationIcon.style.display = 'flex';
            rotationIcon.style.opacity = '0.8';
            viewer.classList.add('active');
            
            // Handle viewer visibility changes
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        viewer.classList.add('active');
                        rotationIcon.style.display = 'flex';
                        rotationIcon.style.opacity = '0.8';
                    } else {
                        viewer.classList.remove('active');
                        rotationIcon.style.display = 'none';
                    }
                });
            }, { threshold: 0.1 });
            
            observer.observe(viewer);
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                observer.disconnect();
            });
        }
    }
}

// Initialize mobile 3D viewer when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initMobile3DViewer();
    initMobileRotationIcon();
});

// Reinitialize on window resize/orientation change
window.addEventListener('resize', function() {
    // Debounce resize events
    clearTimeout(window.resizeTimer);
    window.resizeTimer = setTimeout(function() {
        initMobile3DViewer();
        initMobileRotationIcon();
    }, 250);
});

// Enhanced lazy loading for image sequences
function initEnhancedLazyLoading() {
    const imageContainers = document.querySelectorAll('.canvas-wrapper, #viewer');
    const observerOptions = {
        root: null,
        rootMargin: '200px 0px',
        threshold: 0.1
    };
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const container = entry.target;
                const images = container.querySelectorAll('img[data-src]');
                
                images.forEach(img => {
                    if (img.getAttribute('data-src')) {
                        img.src = img.getAttribute('data-src');
                        img.removeAttribute('data-src');
                    }
                });
                
                observer.unobserve(container);
            }
        });
    }, observerOptions);
    
    imageContainers.forEach(container => {
        imageObserver.observe(container);
    });
}

// Initialize enhanced lazy loading
document.addEventListener('DOMContentLoaded', function() {
    initEnhancedLazyLoading();
});

// Update the preloadImages function to use data-src attributes
function updatePreloadForLazyLoading() {
    const images = document.querySelectorAll('#sequenceImage, .canvas-wrapper img');
    images.forEach(img => {
        if (img.src && !img.hasAttribute('data-src')) {
            img.setAttribute('data-src', img.src);
            img.removeAttribute('src');
        }
    });
    // Ensure sequenceImage retains its src
    const sequenceImage = document.getElementById('sequenceImage');
    if (sequenceImage && !sequenceImage.src) {
        sequenceImage.src = `grayscooty/${formatFrame(1)}.webp`;
    }
}

// Call this after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    updatePreloadForLazyLoading();
});

// Add smooth transition styles
const smoothTransitionStyles = document.createElement('style');
smoothTransitionStyles.textContent = `
    body {
        background-color: #000;
        overflow-x: hidden;
    }
    
    [id^="section"] {
        background-color: #000;
        will-change: transform;
    }
    
    .canvas-wrapper {
        background-color: #000;
    }
    
    #loaderOverlay {
        background-color: #000;
    }
    
.component-overlay {
        background-color: #000;
    }
`;
document.head.appendChild(smoothTransitionStyles);

// Initialize all functionality when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile layout
    initMobileLayout();
    
    // Initialize mobile 3D viewer
    initMobile3DViewer();
    initMobileRotationIcon();
    
    // Initialize enhanced lazy loading
    initEnhancedLazyLoading();
    updatePreloadForLazyLoading();
    
    // Initialize component scroll down indicators
    const componentPlayers = [
        "motorSequencePlayer",
        "batterySequencePlayer",
        "controllerSequencePlayer",
        "rimSequencePlayer",
        "apuSequencePlayer",
        "chassisSequencePlayer",
        "headlightSequencePlayer",
        "chargingCableSequencePlayer",
        "bodySequencePlayer",
        "chargingPortSequencePlayer",
        "dashboardSequencePlayer",
        "boostSequencePlayer",
        "bootSpaceSequencePlayer"
    ];
    
    componentPlayers.forEach(playerId => {
        const player = document.getElementById(playerId);
        if (player) {
            createComponentScrollDownIndicator(player);
            addComponentOverlayScrollListener(player);
        }
    });
    
    // Add CSS for merchandise sections if not already added
    if (!document.getElementById('merchandise-section-styles')) {
        const style = document.createElement('style');
        style.id = 'merchandise-section-styles';
        style.textContent = `
            .model-title {
                font-size: 72px;
                font-weight: 300;
                margin-bottom: 20px;
                line-height: 1.1;
                color: #FFFFFF;
            }
            .model-title .highlight {
                color: #CE6723;
            }
            .model-description {
                font-size: 18px;
                line-height: 1.6;
                margin-bottom: 40px;
                color: #ccc;
                max-width: 80%;
                display: block;
            }
            .section-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.3);
                cursor: pointer;
                transition: all 0.3s;
            }
            .section-dot.active {
                background-color: #CE6723;
                transform: scale(1.3);
            }
            .content-section.active {
                transform: translateY(0);
                opacity: 1;
            }
            .content-section.next {
                transform: translateY(100%);
                opacity: 0;
            }
            .content-section.prev {
                transform: translateY(-100%);
                opacity: 0;
            }
            .component-image-container {
                position: relative;
                width: 80%;
                height: 80%;
            }
            .component-image {
                width: 100%;
                height: auto;
                object-fit: contain;
                position: absolute;
                top: 0;
                left: 0;
                transition: opacity 0.5s ease-in-out;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Set up scroll animations
    initSmoothScrollAnimations();
    
    // Set up wrapper13 scroll behavior
    setupWrapper13Scroll();
    
    // Initialize WOW.js for animations
    if (typeof WOW !== 'undefined') {
        new WOW().init();
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    // Debounce resize events
    clearTimeout(window.resizeTimer);
    window.resizeTimer = setTimeout(function() {
        if (isMobile()) {
            initMobileLayout();
        }
        initMobile3DViewer();
        initMobileRotationIcon();
    }, 250);
});

// Handle page visibility changes to optimize performance
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, pause animations or heavy processes
    } else {
        // Page is visible, resume animations or processes
    }
});

// Clean up event listeners when page unloads
window.addEventListener('beforeunload', function() {
    window.removeEventListener('scroll', handleScrollAnimations);
    window.removeEventListener('resize', handleResize);
});