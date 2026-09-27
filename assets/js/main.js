document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
        if (!confirm(element.dataset.confirm)) event.preventDefault();
    });
});

// Keep messages and instructions visible until the user leaves the page.
// Give wide report tables their own scroll area on smaller screens.
document.querySelectorAll('main table').forEach((table) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'table-scroll';
    wrapper.tabIndex = 0;
    wrapper.setAttribute('role', 'region');
    wrapper.setAttribute('aria-label', 'Scrollable table');
    table.before(wrapper);
    wrapper.append(table);
});

document.querySelectorAll('.site-header nav a').forEach((link) => {
    if (new URL(link.href).pathname === window.location.pathname) {
        link.setAttribute('aria-current', 'page');
    }
});
