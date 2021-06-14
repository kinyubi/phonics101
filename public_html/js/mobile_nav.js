let mobileNav = {
    open: () => {
        document.getElementById('mobile-menu-overlay').classList.add('mobile-menu-overlay-show');
    },
    close: () => {
        document.getElementById('mobile-menu-overlay').classList.remove('mobile-menu-overlay-show');
    }
}
