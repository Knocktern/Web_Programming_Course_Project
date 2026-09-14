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