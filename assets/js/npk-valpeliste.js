/**
 * NPK Valpeliste - Interactive functionality with improved height handling
 * Works on mobile and desktop devices
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all cards after a short delay to ensure all content is rendered
    setTimeout(initializeCards, 100);
    
    // Re-measure on window resize or orientation change
    window.addEventListener('resize', debounce(updateAllCardHeights, 250));
    window.addEventListener('orientationchange', function() {
        // Wait for orientation change to complete
        setTimeout(updateAllCardHeights, 300);
    });
});

/**
 * Initialize all cards with proper event listeners and initial heights
 */
function initializeCards() {
    // Find all cards and read more buttons
    const cards = document.querySelectorAll('.valpeliste-card');
    const readMoreButtons = document.querySelectorAll('.valpeliste-read-more');
    
    // First measure all content heights
    measureAllContentHeights();
    
    // Then add click handlers to all read more buttons
    readMoreButtons.forEach(function(button) {
        button.setAttribute('role', 'button');
        button.setAttribute('aria-expanded', 'false');
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const card = this.closest('.valpeliste-card');
            const cardBody = this.closest('.valpeliste-card-body');
            const isExpanded = cardBody.classList.contains('expanded');
            
            // Toggle the state
            cardBody.classList.toggle('expanded');
            this.classList.toggle('expanded');
            this.setAttribute('aria-expanded', !isExpanded ? 'true' : 'false');
            
            // Update heights
            if (!isExpanded) {
                expandCardContent(cardBody);
            } else {
                collapseCardContent(cardBody);
            }
        });
    });
    
    // Expand featured kennel cards by default
    document.querySelectorAll('.valpeliste-card.featured-kennel').forEach(function(card) {
        const button = card.querySelector('.valpeliste-read-more');
        if (button && button.getAttribute('aria-expanded') === 'false') {
            button.click();
        }
    });
    
    // On mobile devices (<768px), expand the first card automatically for better UX
    if (window.innerWidth < 768) {
        const firstCard = document.querySelector('.valpeliste-card');
        if (firstCard) {
            const button = firstCard.querySelector('.valpeliste-read-more');
            if (button && button.getAttribute('aria-expanded') === 'false') {
                button.click();
            }
        }
    }
}

/**
 * Measure and store the natural heights of all collapsible content
 */
function measureAllContentHeights() {
    // Process all parent sections
    document.querySelectorAll('.valpeliste-parents').forEach(measureSectionHeight);
    
    // Process all notes sections
    document.querySelectorAll('.valpeliste-notes').forEach(measureSectionHeight);
}

/**
 * Accurately measure the true height of a section
 */
function measureSectionHeight(section) {
    if (!section) return;
    
    // Clone the element to measure it without affecting the page layout
    const clone = section.cloneNode(true);
    
    // Set styles to make it visible but not affect layout
    Object.assign(clone.style, {
        position: 'absolute',
        visibility: 'hidden',
        display: 'block',
        maxHeight: 'none',
        height: 'auto',
        overflow: 'visible'
    });
    
    // Append to body, measure, then remove
    document.body.appendChild(clone);
    const height = clone.offsetHeight;
    document.body.removeChild(clone);
    
    // Store the natural height plus a small buffer for safety
    section.dataset.naturalHeight = (height + 5) + 'px';
    
    return height;
}

/**
 * Apply the correct height for transitions
 */
function expandCardContent(cardBody) {
    const parents = cardBody.querySelector('.valpeliste-parents');
    const notes = cardBody.querySelector('.valpeliste-notes');
    
    // Set height to auto for parents section
    if (parents) {
        parents.style.maxHeight = 'none';
        parents.style.opacity = '1';
        parents.style.padding = '10px';
        parents.style.margin = '10px 0';
    }
    
    // Set height to auto for notes section
    if (notes) {
        notes.style.maxHeight = 'none';
        notes.style.opacity = '1';
        parents.style.padding = '10px';
        parents.style.margin = '10px 0';
    }
}

/**
 * Collapse card content by setting max-height to 0
 */
function collapseCardContent(cardBody) {
    const parents = cardBody.querySelector('.valpeliste-parents');
    const notes = cardBody.querySelector('.valpeliste-notes');
    
    if (parents) parents.style.maxHeight = '0px';
    if (notes) notes.style.maxHeight = '0px';
}

/**
 * Update heights for all card content (useful after window resize)
 */
function updateAllCardHeights() {
    // Re-measure all heights
    measureAllContentHeights();
    
    // Update expanded cards
    document.querySelectorAll('.valpeliste-card-body.expanded').forEach(function(cardBody) {
        expandCardContent(cardBody);
    });
}

/**
 * Debounce function to limit frequent calls
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}
