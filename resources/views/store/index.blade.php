@extends('layouts.app')

@section('title', 'GG Store — цифровые товары')

@section('content')

<div class="page-shell">
    <header class="site-header">
        <button class="catalog-button" id="catalogButton" type="button" aria-expanded="false" aria-controls="catalogMenu">
            <span class="grid-icon"><i></i><i></i><i></i><i></i></span>Каталог
        </button>

        <div class="search-box">
            <div class="search-box-input">
                <input type="search" placeholder="Игра, приложение или услуга..." aria-label="Поиск">
                <button class="search-favorite" type="button" aria-label="Избранное" aria-pressed="false"><div>♡</div></button>
            </div>
            <button class="search-submit" type="button" aria-label="Найти"><div>⌕</div></button>
        </div>
        <button class="account-button" type="button" aria-label="Профиль" onclick="alert('Личный кабинет будет доступен после авторизации')">♟</button>
    </header>

    <main>
            <section class="hero" aria-label="Баннеры">
                <div class="inverted-radius">
                    <div class="hero-track" id="heroTrack">
                        @foreach(($banners ?? []) as $banner)
                            <article class="hero-slide  {{ $loop->first ? 'active' : '' }}">
                                <div class="hero-copy">
                                    <span>{{ $banner['eyebrow'] }}</span>
                                    <strong>{{ $banner['title'] }}</strong>
                                    <small>{{ $banner['text'] }}</small>
                                </div>
                                <div class="hero-art">{{ $banner['badge'] }}<br><b>SALE</b></div>
                            </article>
                        @endforeach
                    </div>
                </div>
                <div class="button-container">
                    <button class="hero-arrow prev" id="heroPrev" type="button" aria-label="Предыдущий слайд">←</button>
                    <button class="hero-arrow next" id="heroNext" type="button" aria-label="Следующий слайд">→</button>
                </div>
                <div class="hero-dots" id="heroDots"></div>
            </section>

        <div class="section-pay">
            <section class="services-row" aria-label="Сервисы">
                @foreach(($services ?? []) as $service)
                    <button class="service-card service-{{ \Illuminate\Support\Str::slug($service['name']) }}" type="button">
                        <span class="service-icon">{{ $service['icon'] }}</span><span>{{ $service['name'] }}</span>
                    </button>
                @endforeach
                <button class="service-card service-more" type="button"><span class="service-icon">+</span><span>ещё 841</span></button>
            </section>

            <hr/>

            @php $steamTopup = collect($topups ?? [])->firstWhere('sku', 'STEAM-TOPUP-500') ?? collect($topups ?? [])->first(); @endphp
            <section class="steam-panel">
                <div class="steam-title">
                    <span class="steam-logo">S</span>
                    <div class="text-promo">
                        <div class="inline-block">
                            <b>Пополнение Steam</b>
                            <small>5%</small>
                        </div>
                        <div class="promo-block">
                            <div class="promo-text">Ввести промокод</div>
                        </div>
                    </div>
                </div>
                <div class="steam-login"><span>♟</span><input placeholder="Логин Steam" aria-label="Логин Steam"><i>i</i></div>
                <div class="amount-box">
                    <div class="amount">
                        <small>Сумма</small>
                        <input id="steamAmount" type="number" min="1" step="1" value="500">
                    </div>
                    <div class="currency-switch" id="currencySwitch">
                        <button class="active" data-currency="RUB" type="button">
                            <div>₽</div>
                        </button>
                        <button data-currency="USD" type="button">
                            <div>$</div>
                        </button>
                        <button data-currency="KZT" type="button">
                            <div>₸</div>
                        </button>
                    </div>
                </div>
                <button class="pay-button" id="steamPayButton" type="button" @if($steamTopup) data-topup-sku="{{ $steamTopup['sku'] }}" @endif>Оплатить 500 ₽</button>
            </section>
        </div>

        @php
            $displayProducts = $products ?? [];
            if (empty($displayProducts)) {
                $displayProducts = [
                    ['sku'=>'demo-doom', 'name'=>'DOOM 2016 • STEAM KEY', 'price'=>990, 'currency'=>'RUB', 'label'=>'КЛЮЧ', 'short'=>'KEY'],
                    ['sku'=>'demo-cyberpunk', 'name'=>'Cyberpunk 2077 • STEAM KEY', 'price'=>1490, 'currency'=>'RUB', 'label'=>'КЛЮЧ', 'short'=>'KEY'],
                    ['sku'=>'demo-elden', 'name'=>'Elden Ring • STEAM KEY', 'price'=>1990, 'currency'=>'RUB', 'label'=>'КЛЮЧ', 'short'=>'KEY'],
                    ['sku'=>'demo-plus', 'name'=>'PlayStation Plus • 12 месяцев', 'price'=>2990, 'currency'=>'RUB', 'label'=>'ПОДПИСКА', 'short'=>'SUB'],
                    ['sku'=>'demo-steam', 'name'=>'Пополнение Steam • 1000 ₽', 'price'=>1000, 'currency'=>'RUB', 'label'=>'ПОПОЛНЕНИЕ', 'short'=>'STEAM'],
                ];
            }
        @endphp

        @foreach(['Популярные товары','Рекомендованные товары','Другие товары'] as $sectionIndex => $section)
            <section class="products-section">
                <div class="section-heading">
                    <h2>{{ $section }}</h2>
                    @if($sectionIndex === 0)
                        <div class="category-tabs">
                            @foreach(['Донат','Подписки','Предметы','Аккаунты','Ключи','Игровая валюта','Другое'] as $index => $tab)
                                <button class="{{ $index === 0 ? 'active' : '' }}" type="button">{{ $tab }}</button>
                            @endforeach
                        </div>
                    @else
                        <button type="button" class="show-all">Показать все</button>
                    @endif
                </div>
                <div class="product-grid">
                    @foreach(array_slice($displayProducts, 0, 5) as $product)
                        <article class="product-card" data-category="{{ strtolower($product['label'] === 'КЛЮЧ' ? 'ключи' : ($product['label'] === 'ПОДПИСКА' ? 'подписки' : 'донат')) }}">
                            <div class="product-image"><span>{{ $product['short'] }}</span><strong>{{ strtoupper(str($product['name'])->limit(22)) }}</strong><small>{{ $product['label'] }}</small></div>
                            <div class="product-info">
                                <div class="product-name">🎮 {{ $product['name'] }} ⚡<br><span>{{ $product['label'] }}</span></div>
                                <div class="flex-delim"></div>
                                <div class="price-row"><strong>{{ number_format($product['price'], 0, ',', ' ') }} ₽</strong><del>{{ number_format($product['price'] * 2, 0, ',', ' ') }} ₽</del></div>
                                <button class="buy-button" type="button"
                                    data-sku="{{ $product['sku'] }}"
                                    data-product="{{ $product['name'] }}"
                                    data-price="{{ $product['price'] }}"
                                    data-currency="{{ $product['currency'] === 'RUB' ? '₽' : $product['currency'] }}">Купить</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="reviews-section">
            <div class="section-heading">
                <div>
                    <h2>Последние отзывы</h2>
                    <p>Все отзывы есть с независимой площадки</p>
                </div>
                <button type="button" class="show-all">Показать все</button>
            </div>
            <div class="review-grid">
                @foreach(['Bizidin','Maks','Dmitry'] as $name)
                    <article class="review-card">
                        <div class="review-user">
                            <span class="avatar">{{ mb_substr($name,0,1) }}</span>
                            <div>
                                <b>{{ $name }}</b>
                                <div>
                                    ★★★★★
                                    <small>5.0</small>
                                </div>
                            </div>
                            <time>Сегодня</time>
                        </div>
                        <p>Отзывчивый и приятный продавец, помог не только с товаром, но и с другим вопросом. Рекомендую!</p>
                        <div class="review-product">
                            <span class="mini-art">G</span>
                            <b>Цифровой товар<br>с автоматической выдачей</b>
                            <strong>990₽</strong>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <nav>
            <a href="#">Стать продавцом</a>
            <a href="#">Бонусы</a>
            <a href="#">Поддержка</a>
            <a href="#">Гарантии</a>
            <a href="#">Отзывы</a>
        </nav>
        <div class="footer-middle">
            <div class="socials">
                <span>vk</span>
                <span>✈</span>
                <span>♪</span>
                <span>▶</span>
            </div>
            <div class="payments">
                <span>VISA</span>
                <span>МИР</span>
                <span>●●</span>
            </div>
        </div>
        <div class="footer-bottom">
            <a href="#">Политика конфиденциальности</a>
            <a href="#">Соглашение</a>
            <a href="#">Договор-оферта</a>
        </div>
    </footer>
</div>

<div class="buy-modal" id="buyModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-backdrop"></div>
    <div class="modal-card" tabindex="-1">
        <button class="modal-close" type="button" id="modalClose" aria-label="Закрыть">×</button>
        <h3 id="modalTitle">Покупка товара</h3>
        <p id="modalProduct">Товар</p>
        <div class="modal-price" id="modalPrice">0 ₽</div>
        <div class="modal-status" id="modalStatus" aria-live="polite"></div>
        <button class="pay-button modal-order" id="modal-order" type="button">Купить</button>
        <button style="margin-top: 8px" class="pay-button modal-pay" id="modal-pay" type="button" hidden>Оплатить</button>
    </div>
</div>
<div class="catalog-wrap">
    <div class="bg-catalog-menu" aria-hidden="true" id="catalogMenu">
        <div class="catalog-menu-block">
            <header class="site-header">
                <button class="catalog-button" id="closeCatalogButton" type="button" aria-expanded="false" aria-controls="catalogMenu">
                    <span class="grid-icon"><i></i><i></i><i></i><i></i></span>Каталог
                </button>

                <div class="search-box">
                    <div class="search-box-input">
                        <input type="search" placeholder="Игра, приложение или услуга..." aria-label="Поиск">
                        <button class="search-favorite" type="button" aria-label="Избранное" aria-pressed="false"><div>♡</div></button>
                    </div>
                    <button class="search-submit" type="button" aria-label="Найти"><div>⌕</div></button>
                </div>
                <button class="account-button" type="button" aria-label="Профиль" onclick="alert('Личный кабинет будет доступен после авторизации')">♟</button>
            </header>
            <div class="catalog-menu" >
                <aside class="catalog-sidebar">
                    @foreach(($categories ?? []) as $index => $category)
                        <button class="catalog-side-item {{ $index === 0 ? 'active' : '' }}" type="button">
                            {{ $category['title'] }} <span>›</span>
                        </button>
                    @endforeach
                    @if(empty($categories))
                        <button class="catalog-side-item active" type="button">Игры и игровые сервисы <span>›</span></button>
                        <button class="catalog-side-item" type="button">Игровые ценности <span>›</span></button>
                        <button class="catalog-side-item" type="button">Мобильные игры <span>›</span></button>
                        <button class="catalog-side-item" type="button">Сервисы и соцсети <span>›</span></button>
                    @endif
                </aside>
                <div class="catalog-content">
                    @foreach(['Steam','PlayStation','Xbox','Nintendo','Battle.net','Подборки'] as $catalog)
                        <div class="catalog-column {{ $catalog === 'Подборки' ? 'collections' : '' }}">
                            <h4>{{ $catalog }} <span>›</span></h4>
                            @foreach(['Игры и DLC','Пополнение баланса','Подарочные карты','Подписки','Новые аккаунты'] as $link)
                                <a href="#" onclick="event.preventDefault()">{{ $link }}</a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
