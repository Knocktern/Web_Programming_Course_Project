document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
        if (!confirm(element.dataset.confirm)) event.preventDefault();
    });
});

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.notice').forEach((notice) => {
            notice.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            notice.style.opacity = '0';
            notice.style.transform = 'translateY(-10px)';
            setTimeout(() => notice.remove(), 500);
        });
    }, 6000);
});

let lastScrollTop = 0;
window.addEventListener('scroll', () => {
    const header = document.querySelector('.site-header');
    if (!header) return;
    
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    
    if (currentScroll > lastScrollTop && currentScroll > 80) {
        // Scroll Down
        header.classList.add('header-hidden');
    } else {
        // Scroll Up
        header.classList.remove('header-hidden');
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
}, { passive: true });