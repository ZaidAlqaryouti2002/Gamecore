const sidebar = document.querySelector('#admin-sidebar');
const toggle = document.querySelector('.admin-nav-toggle');
const closeButton = document.querySelector('.admin-nav-close');
const backdrop = document.querySelector('[data-admin-backdrop]');
const main = document.querySelector('.admin-main');
const skipLink = document.querySelector('.admin-skip-link');
const mobile = window.matchMedia('(max-width: 991px)');

function setMenuOpen(open, restoreFocus = false) {
    sidebar.classList.toggle('is-open', open);
    document.body.classList.toggle('admin-menu-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    backdrop.hidden = !open;
    main.inert = open;
    skipLink.inert = open;
    sidebar.inert = mobile.matches && !open;

    if (open) {
        closeButton.focus();
    } else if (restoreFocus) {
        toggle.focus();
    }
}

toggle.addEventListener('click', () => setMenuOpen(true));
closeButton.addEventListener('click', () => setMenuOpen(false, true));
backdrop.addEventListener('click', () => setMenuOpen(false, true));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
        setMenuOpen(false, true);
    }
});
mobile.addEventListener('change', () => setMenuOpen(false));
setMenuOpen(false);
