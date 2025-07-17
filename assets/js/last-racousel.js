/**
 * News Carousel Component
 * A responsive, accessible carousel with auto-play functionality
 */
class NewsCarousel {
    constructor(element) {
        this.carousel = element;
        this.track = element.querySelector('#carouselTrack');
        this.slides = element.querySelectorAll('.carousel-slide');
        this.prevBtn = element.querySelector('#prevBtn');
        this.nextBtn = element.querySelector('#nextBtn');
        this.dots = element.querySelectorAll('.carousel-dot');
        this.thumbs = element.querySelectorAll('.carousel-thumb');
        this.progressBar = element.querySelector('#carouselProgress');
        this.slideCounter = element.querySelector('#currentSlide');
        
        this.currentSlide = 0;
        this.totalSlides = this.slides.length;
        this.isPlaying = true;
        this.playInterval = null;
        this.progressInterval = null;
        this.slideTransition = false;
        
        // Configuration
        this.config = {
            autoPlayDelay: 5000, // 5 seconds
            progressUpdateInterval: 50, // Update progress every 50ms
            transitionDuration: 500, // Slide transition duration
            pauseOnHover: true,
            enableTouch: true,
            enableKeyboard: true
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupAccessibility();
        this.startAutoPlay();
        this.updateCarousel(0, false);
    }
    
    setupEventListeners() {
        // Navigation buttons
        this.prevBtn?.addEventListener('click', () => this.previousSlide());
        this.nextBtn?.addEventListener('click', () => this.nextSlide());
        
        // Dot indicators
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goToSlide(index));
        });
        
        // Thumbnail navigation
        this.thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', () => this.goToSlide(index));
        });
        
        // Hover pause/resume
        if (this.config.pauseOnHover) {
            this.carousel.addEventListener('mouseenter', () => this.pauseAutoPlay());
            this.carousel.addEventListener('mouseleave', () => this.resumeAutoPlay());
        }
        
        // Touch/swipe support
        if (this.config.enableTouch) {
            this.setupTouchEvents();
        }
        
        // Keyboard navigation
        if (this.config.enableKeyboard) {
            this.setupKeyboardEvents();
        }
        
        // Page visibility API for auto-play management
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoPlay();
            } else if (this.isPlaying) {
                this.resumeAutoPlay();
            }
        });
        
        // Window resize handler
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.updateCarousel(this.currentSlide, false);
            }, 150);
        });
    }
    
    setupTouchEvents() {
        let startX = 0;
        let startY = 0;
        let endX = 0;
        let endY = 0;
        let isDragging = false;
        
        this.carousel.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isDragging = true;
            this.pauseAutoPlay();
        }, { passive: true });
        
        this.carousel.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            endX = e.touches[0].clientX;
            endY = e.touches[0].clientY;
        }, { passive: true });
        
        this.carousel.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            
            const deltaX = startX - endX;
            const deltaY = startY - endY;
            const minSwipeDistance = 50;
            
            // Only handle horizontal swipes
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
                if (deltaX > 0) {
                    this.nextSlide();
                } else {
                    this.previousSlide();
                }
            }
            
            this.resumeAutoPlay();
        });
    }
    
    setupKeyboardEvents() {
        this.carousel.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.previousSlide();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.nextSlide();
                    break;
                case ' ':
                    e.preventDefault();
                    this.toggleAutoPlay();
                    break;
                case 'Home':
                    e.preventDefault();
                    this.goToSlide(0);
                    break;
                case 'End':
                    e.preventDefault();
                    this.goToSlide(this.totalSlides - 1);
                    break;
            }
        });
    }
    
    setupAccessibility() {
        // Add ARIA labels and roles
        this.carousel.setAttribute('role', 'region');
        this.carousel.setAttribute('aria-label', 'News carousel');
        this.carousel.setAttribute('tabindex', '0');
        
        this.track.setAttribute('role', 'group');
        this.track.setAttribute('aria-label', 'Carousel slides');
        
        // Label navigation buttons
        this.prevBtn?.setAttribute('aria-label', 'Previous slide');
        this.nextBtn?.setAttribute('aria-label', 'Next slide');
        
        // Label dot indicators
        this.dots.forEach((dot, index) => {
            dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
            dot.setAttribute('role', 'button');
            dot.setAttribute('tabindex', '0');
        });
        
        // Update slide accessibility
        this.updateSlideAccessibility();
    }
    
    updateSlideAccessibility() {
        this.slides.forEach((slide, index) => {
            const isActive = index === this.currentSlide;
            slide.setAttribute('aria-hidden', !isActive);
            slide.setAttribute('tabindex', isActive ? '0' : '-1');
        });
    }
    
    previousSlide() {
        if (this.slideTransition) return;
        const prevIndex = this.currentSlide === 0 ? this.totalSlides - 1 : this.currentSlide - 1;
        this.goToSlide(prevIndex);
    }
    
    nextSlide() {
        if (this.slideTransition) return;
        const nextIndex = this.currentSlide === this.totalSlides - 1 ? 0 : this.currentSlide + 1;
        this.goToSlide(nextIndex);
    }
    
    goToSlide(index) {
        if (this.slideTransition || index === this.currentSlide) return;
        
        this.slideTransition = true;
        const previousSlide = this.currentSlide;
        this.currentSlide = index;
        
        this.updateCarousel(index, true);
        this.resetProgress();
        
        // Re-enable transitions after animation completes
        setTimeout(() => {
            this.slideTransition = false;
        }, this.config.transitionDuration);
        
        // Announce slide change to screen readers
        this.announceSlideChange(index);
    }
    
    updateCarousel(index, animated = true) {
        // Update track position
        const translateX = -index * 100;
        this.track.style.transform = `translateX(${translateX}%)`;
        
        // Update dot indicators
        this.dots.forEach((dot, i) => {
            dot.classList.toggle('bg-primary-600', i === index);
            dot.classList.toggle('bg-neutral-300', i !== index);
            dot.classList.toggle('active', i === index);
        });
        
        // Update thumbnail indicators
        this.thumbs.forEach((thumb, i) => {
            thumb.classList.toggle('border-primary-600', i === index);
            thumb.classList.toggle('border-gray-300', i !== index);
        });
        
        // Update slide counter
        if (this.slideCounter) {
            this.slideCounter.textContent = index + 1;
        }
        
        // Update slide states
        this.slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
        
        // Update accessibility
        this.updateSlideAccessibility();
    }
    
    startAutoPlay() {
        if (!this.isPlaying) return;
        
        this.playInterval = setInterval(() => {
            this.nextSlide();
        }, this.config.autoPlayDelay);
        
        this.startProgress();
    }
    
    pauseAutoPlay() {
        this.clearIntervals();
    }
    
    resumeAutoPlay() {
        if (this.isPlaying) {
            this.startAutoPlay();
        }
    }
    
    toggleAutoPlay() {
        this.isPlaying = !this.isPlaying;
        
        if (this.isPlaying) {
            this.startAutoPlay();
        } else {
            this.pauseAutoPlay();
        }
    }
    
    startProgress() {
        let progress = 0;
        this.progressInterval = setInterval(() => {
            progress += (this.config.progressUpdateInterval / this.config.autoPlayDelay) * 100;
            
            if (progress >= 100) {
                progress = 100;
                clearInterval(this.progressInterval);
            }
            
            this.updateProgress(progress);
        }, this.config.progressUpdateInterval);
    }
    
    updateProgress(percentage) {
        if (this.progressBar) {
            this.progressBar.style.width = `${percentage}%`;
        }
    }
    
    resetProgress() {
        this.updateProgress(0);
        clearInterval(this.progressInterval);
        if (this.isPlaying) {
            this.startProgress();
        }
    }
    
    clearIntervals() {
        clearInterval(this.playInterval);
        clearInterval(this.progressInterval);
        this.playInterval = null;
        this.progressInterval = null;
    }
    
    announceSlideChange(index) {
        // Create or update announcement for screen readers
        let announcement = document.getElementById('carousel-announcement');
        if (!announcement) {
            announcement = document.createElement('div');
            announcement.id = 'carousel-announcement';
            announcement.className = 'sr-only';
            announcement.setAttribute('aria-live', 'polite');
            announcement.setAttribute('aria-atomic', 'true');
            this.carousel.appendChild(announcement);
        }
        
        const slide = this.slides[index];
        const title = slide.querySelector('h3')?.textContent || '';
        announcement.textContent = `Slide ${index + 1} of ${this.totalSlides}: ${title}`;
    }
    
    destroy() {
        this.clearIntervals();
        this.carousel.removeEventListener('mouseenter', this.pauseAutoPlay);
        this.carousel.removeEventListener('mouseleave', this.resumeAutoPlay);
        document.removeEventListener('visibilitychange', this.toggleAutoPlay);
    }
}

// Screen reader only class for accessibility
const style = document.createElement('style');
style.textContent = `
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
`;
document.head.appendChild(style);

// Initialize carousel when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const carouselElement = document.getElementById('newsCarousel');
    if (carouselElement) {
        window.newsCarousel = new NewsCarousel(carouselElement);
    }
});

// Handle page unload
window.addEventListener('beforeunload', () => {
    if (window.newsCarousel) {
        window.newsCarousel.destroy();
    }
});
