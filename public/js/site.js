document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('[data-menu-toggle]')
    const navigation = document.querySelector('[data-site-nav]')

    if (menuToggle && navigation) {
        menuToggle.addEventListener('click', () => {
            const isOpen = navigation.classList.toggle('is-open')

            menuToggle.classList.toggle('is-open', isOpen)
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false')
        })

        navigation.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navigation.classList.remove('is-open')
                menuToggle.classList.remove('is-open')
                menuToggle.setAttribute('aria-expanded', 'false')
            })
        })
    }

    document.querySelectorAll('[data-print-page]').forEach((button) => {
        button.addEventListener('click', () => window.print())
    })
})
