document.addEventListener('DOMContentLoaded', () => {
    const $ = (selector, root = document) => root?.querySelector(selector);
    const $$ = (selector, root = document) => [...(root?.querySelectorAll(selector) || [])];
    const csrfToken = $('meta[name="csrf-token"]')?.content || '';

    /* Catalog */
    const catalogButton = $('#catalogButton');
    const closeCatalogButton = $('#closeCatalogButton');
    const catalogMenu = $('#catalogMenu');
    const catalogItems = $$('.catalog-side-item');
    const catalogColumns = $$('.catalog-column');
    const pageShell = document.querySelector('.page-shell');

    const closeCatalog = () => {
        catalogMenu?.classList.remove('open');
        catalogButton?.setAttribute('aria-expanded', 'false');
        catalogMenu?.setAttribute('aria-hidden', 'true');
    };

    catalogButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        pageShell.classList.toggle('hidden');
        const open = !catalogMenu.classList.contains('open');
        catalogMenu.classList.toggle('open', open);
        catalogButton.setAttribute('aria-expanded', String(open));
        catalogMenu.setAttribute('aria-hidden', String(!open));
    });

    closeCatalogButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        pageShell.classList.toggle('hidden');
        const open = !catalogMenu.classList.contains('open');
        catalogMenu.classList.toggle('open', open);
        closeCatalogButton.setAttribute('aria-expanded', String(open));
        catalogMenu.setAttribute('aria-hidden', String(!open));
    });

    catalogItems.forEach((item, index) => {
        item.addEventListener('click', () => {
            catalogItems.forEach(el => el.classList.remove('active'));
            item.classList.add('active');
            catalogColumns.forEach((column, columnIndex) => {
                column.hidden = index > 0 && columnIndex < 5 && columnIndex !== index - 1;
            });
        });
    });

    document.addEventListener('click', (event) => {
        if (catalogMenu && !catalogMenu.contains(event.target) && !catalogButton?.contains(event.target)) closeCatalog();
    });

    /* Hero carousel */
    const slides = $$('.hero-slide');
    const dots = $('#heroDots');
    const track = $('#heroTrack');
    let currentSlide = 0;
    let carouselTimer = null;

    const goToSlide = (index) => {
        if (!slides.length) return;
        currentSlide = (index + slides.length) % slides.length;
        if (track) track.style.transform = `translateX(-${currentSlide * 100}%)`;
        slides.forEach((slide, i) => slide.classList.toggle('active', i === currentSlide));
        if (dots) {
            dots.innerHTML = slides.map((_, i) => `<button class="hero-dot ${i === currentSlide ? 'active' : ''}" data-slide="${i}" aria-label="Слайд ${i + 1}" type="button"></button>`).join('');
        }
    };
    const restartCarousel = () => {
        clearInterval(carouselTimer);
        carouselTimer = setInterval(() => goToSlide(currentSlide + 1), 5000);
    };
    $('#heroPrev')?.addEventListener('click', () => { goToSlide(currentSlide - 1); restartCarousel(); });
    $('#heroNext')?.addEventListener('click', () => { goToSlide(currentSlide + 1); restartCarousel(); });
    dots?.addEventListener('click', (event) => {
        const dot = event.target.closest('.hero-dot');
        if (!dot) return;
        goToSlide(Number(dot.dataset.slide));
        restartCarousel();
    });
    goToSlide(0);
    restartCarousel();

    /* Search */
    const searchInput = $('.search-box input');
    const productCards = $$('.product-card');
    const filterProducts = (query = '') => {
        const value = query.trim().toLowerCase();
        productCards.forEach(card => card.hidden = Boolean(value && !card.textContent.toLowerCase().includes(value)));
    };
    searchInput?.addEventListener('input', () => filterProducts(searchInput.value));
    $('.search-submit')?.addEventListener('click', () => { filterProducts(searchInput?.value || ''); searchInput?.focus(); });
    searchInput?.addEventListener('keydown', event => { if (event.key === 'Enter') filterProducts(searchInput.value); });

    /* Favorite */
    const favoriteButton = $('.search-favorite');
    favoriteButton?.addEventListener('click', () => {
        const active = favoriteButton.classList.toggle('active');
        favoriteButton.innerHTML = active ? '<div>♥</div>' : '<div>♡</div>';
        favoriteButton.setAttribute('aria-pressed', String(active));
    });

    /* Category tabs */
    $$('.category-tabs button').forEach(button => {
        button.addEventListener('click', () => {
            $$('.category-tabs button').forEach(item => item.classList.remove('active'));
            button.classList.add('active');
            const category = button.textContent.trim().toLowerCase();
            productCards.forEach(card => {
                const value = card.dataset.category?.toLowerCase() || '';
                card.hidden = category !== 'другое' && value !== category;
            });
        });
    });

    /* Currency switch — display only, no conversion per specification */
    const currencyButtons = $$('#currencySwitch button');
    const currencySymbol = { RUB: '₽', USD: '$', KZT: '₸' };
    const amountInput = $('#steamAmount');
    const steamPayButton = $('#steamPayButton');
    let selectedCurrency = 'RUB';
    const updateSteamAmount = () => {
        const amount = Math.max(1, Number(amountInput?.value || 500));
        const symbol = currencySymbol[selectedCurrency] || '₽';
        if (steamPayButton) steamPayButton.textContent = `Оплатить ${amount.toLocaleString('ru-RU')} ${symbol}`;
    };
    currencyButtons.forEach(button => button.addEventListener('click', () => {
        selectedCurrency = button.dataset.currency || 'RUB';
        currencyButtons.forEach(item => item.classList.toggle('active', item === button));
        updateSteamAmount();
    }));
    amountInput?.addEventListener('input', updateSteamAmount);
    updateSteamAmount();

    /* Buy modal */
    const modal = $('#buyModal');
    const modalProduct = $('#modalProduct');
    const modalPrice = $('#modalPrice');
    const modalOrder = $('#modal-order');
    const modalPay = $('#modal-pay');
    const modalStatus = $('#modalStatus');
    let previousFocusedElement = null;
    let selectedProduct = null;
    let currentOrderId = null;
    let currentOrderNumber = null;
    let statusTimer = null;
    let createOrderInFlight = false;
    let payOrderInFlight = false;
    let currentIdempotencyKey = null;

    const setModalStatus = (message = '', type = '') => {
        if (!modalStatus) return;
        modalStatus.textContent = message;
        modalStatus.className = `modal-status ${type}`.trim();
    };

    const resetOrderState = () => {
        currentOrderId = null;
        currentOrderNumber = null;
        createOrderInFlight = false;
        payOrderInFlight = false;
        currentIdempotencyKey = crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`;
        clearInterval(statusTimer);
        statusTimer = null;
        if (modalOrder) {
            modalOrder.hidden = false;
            modalOrder.disabled = false;
            modalOrder.textContent = 'Купить';
        }
        if (modalPay) {
            modalPay.hidden = true;
            modalPay.disabled = false;
            modalPay.textContent = 'Оплатить';
        }
    };

    const openModal = (product = {}) => {
        previousFocusedElement = document.activeElement;
        resetOrderState();
        selectedProduct = product;
        if (modalProduct) modalProduct.textContent = product.name || 'Товар';
        if (modalPrice) modalPrice.textContent = `${Number(product.price || 0).toLocaleString('ru-RU')} ${product.currency || '₽'}`;
        setModalStatus('');
        modal?.classList.add('open');
        modal?.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        requestAnimationFrame(() => modalOrder?.focus());
    };

    const closeModal = () => {
        clearInterval(statusTimer);
        statusTimer = null;
        modal?.classList.remove('open');
        modal?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        previousFocusedElement?.focus?.();
        previousFocusedElement = null;
    };

    const parseJson = async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'Ошибка запроса');
        return data;
    };

    const pollOrder = (orderId) => {
        clearInterval(statusTimer);
        let attempts = 0;
        const check = async () => {
            attempts++;
            try {
                const order = await parseJson(await fetch(`/orders/${orderId}/status`, { headers: { Accept: 'application/json' } }));
                const delivery = order.delivery;
                if (delivery?.status === 'delivered' && delivery.code) {
                    clearInterval(statusTimer);
                    statusTimer = null;
                    setModalStatus(`Товар выдан: ${delivery.code}`, 'success');
                    if (modalPay) { modalPay.textContent = 'Оплачено'; modalPay.disabled = true; }
                    return;
                }
                if (['out_of_stock', 'delivery_failed'].includes(order.status) || delivery?.status === 'failed') {
                    clearInterval(statusTimer);
                    statusTimer = null;
                    setModalStatus('Оплата подтверждена, но выдача временно недоступна. Заказ можно повторно обработать.', 'error');
                    if (modalPay) modalPay.disabled = false;
                    return;
                }
                if (attempts >= 30) {
                    clearInterval(statusTimer);
                    statusTimer = null;
                    setModalStatus(`Заказ ${order.number || ''} создан. Статус можно проверить на странице заказа.`);
                }
            } catch (error) {
                clearInterval(statusTimer);
                statusTimer = null;
                setModalStatus(error.message, 'error');
            }
        };
        check();
        statusTimer = setInterval(check, 1000);
    };

    const createOrder = async () => {
        if (!selectedProduct || createOrderInFlight || currentOrderId) return;
        createOrderInFlight = true;
        modalOrder.disabled = true;
        setModalStatus('Создаём заказ...');
        // One idempotency key per selected product/modal session protects double clicks and retries.
        const idempotencyKey = currentIdempotencyKey;
        try {
            const data = await parseJson(await fetch('/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Idempotency-Key': idempotencyKey,
                },
                body: JSON.stringify({ sku: selectedProduct.sku }),
            }));
            console.log(data);
            currentOrderId = data.order_id;
            currentOrderNumber = data.number;
            setModalStatus(`Заказ ${currentOrderNumber} создан. Теперь его можно оплатить.`);
            // Buy and Pay are intentionally separate actions.
            modalOrder.disabled = false;
            modalPay.hidden = false;
            modalPay.disabled = false;
        } catch (error) {
            modalOrder.disabled = false;
            setModalStatus(error.message, 'error');
        } finally {
            createOrderInFlight = false;
        }
    };

    const payOrder = async () => {
        if (!currentOrderId || payOrderInFlight) return;
        payOrderInFlight = true;
        modalPay.disabled = true;
        setModalStatus('Выполняем тестовую оплату...');
        try {
            const data = await parseJson(await fetch(`/orders/${currentOrderId}/pay-test`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            }));
            const order = data.order || data;
            if (order.status === 'delivered' && order.delivery?.code) {
                setModalStatus(`Товар выдан: ${order.delivery.code}`, 'success');
                modalPay.textContent = 'Оплачено';
                return;
            }
            setModalStatus('Оплата подтверждена. Ожидаем выдачу...');
            pollOrder(currentOrderId);
        } catch (error) {
            modalPay.disabled = false;
            setModalStatus(error.message, 'error');
        } finally {
            payOrderInFlight = false;
        }
    };

    modalOrder?.addEventListener('click', event => { event.preventDefault(); event.stopPropagation(); createOrder(); });
    modalPay?.addEventListener('click', event => { event.preventDefault(); event.stopPropagation(); payOrder(); });
    $('#modalClose')?.addEventListener('click', closeModal);
    $('.modal-backdrop')?.addEventListener('click', closeModal);
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal?.classList.contains('open')) closeModal(); });

    $$('.buy-button').forEach(button => {
        button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            openModal({ sku: button.dataset.sku, name: button.dataset.product, price: Number(button.dataset.price), currency: button.dataset.currency || '₽' });
        });
    });

    /* Steam panel uses the same create-then-pay flow. */
    steamPayButton?.addEventListener('click', () => {
        const sku = steamPayButton.dataset.topupSku;
        if (!sku) return;
        const amount = Math.max(1, Number(amountInput?.value || 500));
        openModal({ sku, name: `Пополнение Steam ${amount} ₽`, price: amount, currency: '₽' });
    });
});
