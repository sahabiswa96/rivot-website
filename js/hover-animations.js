
function playBatterySequenceReverse() {
    let frame = 0;
    const interval = setInterval(() => {
       
        frame++;
        if (frame >= maxFrames) clearInterval(interval);
    }, 40); 


function playControllerSequenceReverse() {
    let frame = 0;
    const interval = setInterval(() => {
       
        frame++;
        if (frame >= maxFrames) clearInterval(interval);
    }, 40);
}

function playRimSequenceReverse() {
    let frame = 0;
    const interval = setInterval(() => {
     
        frame++;
        if (frame >= maxFrames) clearInterval(interval);
    }, 40);
}

function playApuSequenceReverse() {
    let frame = 0;
    const interval = setInterval(() => {
        
        frame++;
        if (frame >= maxFrames) clearInterval(interval);
    }, 40);
}

function playChassisSequenceReverse() {
    let frame = 0;
    const interval = setInterval(() => {
        
        frame++;
        if (frame >= maxFrames) clearInterval(interval);
    }, 40);
}


function playMotorSequence() {
    let frame = 0;
    const interval = setInterval(() => {
        
        frame++;
        if (frame >= maxFrames) clearInterval(interval);
    }, 80); 
}


function playSecondAnimation() {
    let frame = 0;
    const imageContainer = document.querySelector('.carbon-belt-container');
    const interval = setInterval(() => {
        const translateY = -frame * 0.1; 
        imageContainer.style.transform = `translateY(${translateY - 50}%)`;
        frame++;
        if (frame >= maxFrames) clearInterval(interval);
    }, 40);
}