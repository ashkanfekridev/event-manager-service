document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-checkout-root]')

    if (! root) {
        return
    }

    const selectedSeats = new Map()
    const seatButtons = root.querySelectorAll('.seat:not(:disabled)')
    const nextButton = root.querySelector('#to-customer')
    const selectedLabels = root.querySelector('#selected-labels')
    const selectedCount = root.querySelector('#selected-count')
    const selectedTotal = root.querySelector('#selected-total')
    const seatSelectionStatus = root.querySelector('#seat-selection-status')
    const sectionButtons = root.querySelectorAll('[data-section-target]')
    const seatSections = root.querySelectorAll('[data-seat-section]')

    if (! nextButton) {
        return
    }

    const showMessage = (container, message) => {
        container.replaceChildren()

        const alert = document.createElement('div')
        alert.className = 'alert error'
        alert.textContent = message
        container.append(alert)
    }

    const syncSummary = () => {
        selectedLabels.textContent = selectedSeats.size
            ? [...selectedSeats.values()].map((seat) => seat.label).join('، ')
            : 'انتخاب نشده'
        selectedCount.textContent = selectedSeats.size.toLocaleString('fa-IR')
        selectedTotal.textContent = [...selectedSeats.values()]
            .reduce((total, seat) => total + seat.price, 0)
            .toLocaleString('fa-IR')
        nextButton.disabled = selectedSeats.size === 0
        nextButton.textContent = selectedSeats.size
            ? `ادامه با ${selectedSeats.size.toLocaleString('fa-IR')} صندلی`
            : 'ادامه و ثبت اطلاعات'
        seatSelectionStatus.textContent = selectedSeats.size
            ? `${selectedSeats.size.toLocaleString('fa-IR')} صندلی انتخاب شده`
            : 'هنوز صندلی انتخاب نکرده‌اید'
    }

    const showStep = (activeStep) => {
        [1, 2, 3].forEach((step) => {
            root.querySelector(`#step-${step}`).hidden = step !== activeStep

            const indicator = root.querySelector(`[data-step-indicator="${step}"]`)
            indicator.classList.toggle('active', step === activeStep)
            indicator.classList.toggle('done', step < activeStep)
        })
    }

    sectionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            sectionButtons.forEach((item) => {
                const isActive = item === button
                item.classList.toggle('active', isActive)
                item.setAttribute('aria-selected', isActive ? 'true' : 'false')
                item.setAttribute('tabindex', isActive ? '0' : '-1')
            })
            seatSections.forEach((section) => {
                section.hidden = section.dataset.seatSection !== button.dataset.sectionTarget
            })
        })
    })

    seatButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const seatId = Number(button.dataset.seat)

            if (selectedSeats.has(seatId)) {
                selectedSeats.delete(seatId)
                button.classList.remove('selected')
                button.setAttribute('aria-pressed', 'false')
            } else if (selectedSeats.size < 10) {
                selectedSeats.set(seatId, {
                    label: button.dataset.label,
                    price: Number(button.dataset.price),
                })
                button.classList.add('selected')
                button.setAttribute('aria-pressed', 'true')
            }

            syncSummary()
        })
    })

    nextButton.addEventListener('click', () => showStep(2))
    root.querySelector('#back-to-seats').addEventListener('click', () => showStep(1))

    let currentOrder = null
    const customerForm = root.querySelector('#step-2')

    customerForm.addEventListener('submit', async (event) => {
        event.preventDefault()

        const submitButton = event.submitter
        const checkoutError = root.querySelector('#checkout-error')
        submitButton.disabled = true
        submitButton.textContent = 'در حال ثبت رزرو…'
        checkoutError.replaceChildren()

        const body = Object.fromEntries(new FormData(customerForm))
        body.performance_seat_ids = [...selectedSeats.keys()]

        try {
            const response = await fetch(root.dataset.reservationUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', Accept: 'application/json'},
                body: JSON.stringify(body),
            })
            const payload = await response.json()

            if (! response.ok) {
                const validationMessage = payload.errors ? Object.values(payload.errors).flat()[0] : null
                showMessage(checkoutError, validationMessage || payload.message || 'رزرو انجام نشد.')

                return
            }

            currentOrder = payload.data
            root.querySelector('#order-reference').textContent = currentOrder.reference
            root.querySelector('#reserved-until').textContent = new Date(currentOrder.reserved_until).toLocaleTimeString('fa-IR', {
                hour: '2-digit',
                minute: '2-digit',
            })
            showStep(3)
        } catch {
            showMessage(checkoutError, 'ارتباط با سرور برقرار نشد. دوباره تلاش کنید.')
        } finally {
            submitButton.disabled = false
            submitButton.textContent = 'ثبت رزرو و ادامه پرداخت'
        }
    })

    const confirmButton = root.querySelector('#confirm-payment')

    confirmButton.addEventListener('click', async () => {
        if (! currentOrder) {
            return
        }

        const paymentError = root.querySelector('#payment-error')
        confirmButton.disabled = true
        confirmButton.textContent = 'در حال تأیید پرداخت…'
        paymentError.replaceChildren()

        try {
            const confirmationUrl = root.dataset.confirmUrlTemplate.replace('__REFERENCE__', encodeURIComponent(currentOrder.reference))
            const response = await fetch(confirmationUrl, {method: 'POST', headers: {Accept: 'application/json'}})
            const payload = await response.json()

            if (response.ok) {
                window.location.href = payload.data.ticket_url

                return
            }

            showMessage(paymentError, payload.message || 'پرداخت انجام نشد.')
        } catch {
            showMessage(paymentError, 'ارتباط با سرور برقرار نشد. دوباره تلاش کنید.')
        }

        confirmButton.disabled = false
        confirmButton.textContent = 'تلاش مجدد برای پرداخت'
    })
})
