<?php
require_once __DIR__ . '/site.php';
$settings = site_settings();
$contactMethods = site_contacts();
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#a0107b">
  <title>Сайт от <?= site_e(site_money('landing')) ?> и реклама для привлечения клиентов — uweb.by</title>
  <meta name="description" content="Нужны заявки? Начнём с сайта от <?= site_e(site_money('landing')) ?>. Разработка, мобильная версия и формы. Контекстная реклама — от <?= site_e(site_money('adsMonthly')) ?>/мес., рекламный бюджет отдельно.">
  <link rel="icon" href="/favicon.svg">
  <link rel="preload" href="/fonts/golos-text-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="/assets/landing.css?v=motion-faq-calc-1">
  <script id="site-settings" type="application/json"><?= json_encode(['prices' => $settings['prices'], 'currency' => $settings['currency'], 'extras' => $settings['extras'], 'calculator' => $settings['calculator']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
  <script src="/assets/landing.js?v=motion-faq-calc-1" defer></script>
</head>
<body>
  <svg class="icon-defs" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><defs>
<symbol id="icon-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></symbol>
<symbol id="icon-screen" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4M2 7h20"/></symbol>
<symbol id="icon-message" viewBox="0 0 24 24"><path d="M21 11a8 8 0 0 1-8 8H7l-4 3V7a5 5 0 0 1 5-5h5a8 8 0 0 1 8 9Z"/><path d="M7 8h9M7 12h6"/></symbol>
<symbol id="icon-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
<symbol id="icon-phone" viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/></symbol>
<symbol id="icon-cursor" viewBox="0 0 24 24"><path d="m4 3 16 9-7 2-3 7-6-18Z"/></symbol>
<symbol id="icon-rocket" viewBox="0 0 24 24"><path d="M13 17 7 11C10 5 15 2 22 2c0 7-3 12-9 15ZM7 11l-4 1-2 5 7-1M13 17l-1 6 6-3 1-6M5 20l-3 2"/><circle cx="16.5" cy="7.5" r="1.5"/></symbol>

<symbol id="icon-call" viewBox="0 0 24 24"><path d="M7 3H4a2 2 0 0 0-2 2c0 9.4 7.6 17 17 17a2 2 0 0 0 2-2v-3l-5-2-2 2a14 14 0 0 1-7-7l2-2-2-5Z"/></symbol>
<symbol id="icon-email" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m3 6 9 7 9-7"/></symbol>
<symbol id="icon-telegram" viewBox="0 0 24 24"><path d="m2 10 20-8-4 20-6-7-5 4v-6L2 10Zm5 3L22 2M12 15 22 2"/></symbol>
<symbol id="icon-viber" viewBox="0 0 24 24"><path d="M21 11a8 8 0 0 1-8 8H7l-4 3V7a5 5 0 0 1 5-5h5a8 8 0 0 1 8 9Z"/><path d="m8 7 2 3-1 1 3 3 1-1 3 2M14 6a4 4 0 0 1 4 4"/></symbol>
<symbol id="icon-close" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></symbol>
<symbol id="icon-pause" viewBox="0 0 24 24"><path d="M8 5v14M16 5v14"/></symbol>
<symbol id="icon-reset" viewBox="0 0 24 24"><path d="M4 10a8 8 0 1 1 1 8M4 4v6h6"/></symbol>
<symbol id="icon-play" viewBox="0 0 24 24"><path d="m7 4 13 8-13 8V4Z"/></symbol>

<symbol id="icon-target" viewBox="0 0 24 24"><circle cx="11" cy="13" r="8"/><circle cx="11" cy="13" r="4"/><path d="m11 13 9-10M16 3h4v4"/></symbol>
<symbol id="icon-layout" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="18" rx="2"/><path d="M2 8h20M9 8v13"/></symbol>
<symbol id="icon-key" viewBox="0 0 24 24"><circle cx="8" cy="8" r="5"/><path d="m12 12 9 9M17 17l3-3M19 19l3-3"/></symbol>
<symbol id="icon-settings" viewBox="0 0 24 24"><path d="M4 5h16M4 12h16M4 19h16"/><circle cx="8" cy="5" r="2" fill="white"/><circle cx="16" cy="12" r="2" fill="white"/><circle cx="10" cy="19" r="2" fill="white"/></symbol>
<symbol id="icon-shield" viewBox="0 0 24 24"><path d="m12 2 9 4v6c0 5-5 9-9 11-4-2-9-6-9-11V6l9-4Z"/><path d="m7 12 3 3 6-6"/></symbol>
<symbol id="icon-arrow-right" viewBox="0 0 24 24"><path d="M4 12h16m-6-6 6 6-6 6"/></symbol>
</defs></svg>
  <a class="skip" href="#main">К содержимому</a>
  <header class="header">
    <div class="container header-inner">
      <a class="logo" href="#" aria-label="uweb.by — на главную">uweb<span>.</span>by</a>
      <nav aria-label="Навигация по странице"><a href="#price">Стоимость сайта</a><a href="#growth">Как получать заявки</a><a href="#results">Результаты</a></nav>
      <a class="header-contact" href="#contacts" data-open-contacts aria-haspopup="dialog" aria-controls="contacts-dialog" aria-expanded="false">Наши контакты <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a>
    </div>
  </header>
  <main id="main">
    <section class="hero" aria-labelledby="hero-title">
      <div class="container hero-grid">
        <div class="hero-copy">
          <p class="eyebrow"><span class="accent-line"></span> САЙТЫ ДЛЯ БИЗНЕСА В БЕЛАРУСИ</p>
          <h1 id="hero-title">Нужны заявки?<br><span>Начнём с сайта.</span></h1>
          <div class="hero-price">Разработка <strong>от <?= site_e(site_money('landing')) ?></strong></div>
          <p class="hero-lead">Сделаем сайт для вашей услуги.<br>Поможем привлечь на него клиентов рекламой.</p>
          <a class="button primary" href="#contact">Рассчитать мой сайт <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a>
          <p class="button-note">Состав работ и цену согласуем до старта.</p>
          <div class="hero-tags"><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-phone"/></svg> Мобильная версия</span><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-message"/></svg> Формы связи</span></div>
        </div>
        <figure class="hero-picture" aria-labelledby="hero-caption">
          <div class="hero-studio" aria-hidden="true">
            <div class="studio-orbit orbit-one"></div><div class="studio-orbit orbit-two"></div>
            <div class="studio-window">
              <div class="studio-browser"><span class="browser-dots"><i></i><i></i><i></i></span><span>ваш-сайт.by</span></div>
              <div class="studio-window-content"><span class="studio-brand">ВАША КОМПАНИЯ<span>.</span></span><div class="studio-title">Услуга, которую<br>легко выбрать.</div><div class="studio-copy-lines"><i></i><i></i></div><span class="studio-action">Оставить заявку <svg class="icon arrow" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14"/></svg></span><div class="studio-shapes"><i></i><i></i><i></i></div></div>
              <div class="studio-window-tiles"><i></i><i></i><i></i></div>
            </div>
            <div class="studio-phone"><div class="studio-phone-camera"></div><span>Ваш сайт</span><div class="studio-phone-cover"><i></i><i></i><b></b></div><div class="studio-phone-lines"><i></i><i></i></div><span class="studio-phone-action">Заявка</span><div class="studio-phone-tiles"><i></i><i></i><i></i></div></div>
            <div class="studio-ad"><svg class="icon" viewBox="0 0 24 24"><use href="#icon-search"/></svg><span>Клиент ищет вашу услугу</span></div>
            <div class="studio-cursor"><svg class="icon" viewBox="0 0 24 24"><use href="#icon-cursor"/></svg></div>
            <div class="studio-lead"><span><svg class="icon" viewBox="0 0 24 24"><use href="#icon-check"/></svg></span><div><strong>Обращение с сайта</strong><span>Контакт клиента — у вас</span></div></div>
          </div>
          <figcaption id="hero-caption">Реклама → сайт → обращение</figcaption>
          <button class="motion-toggle" type="button" aria-pressed="false" hidden><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-pause"/></svg><span>Остановить движение</span></button>
        </figure>
      </div>
    </section>

    <section class="section path-section" id="path" aria-labelledby="path-title">
      <div class="container">
        <div class="section-heading"><span class="eyebrow">КАК ЭТО РАБОТАЕТ</span><h2 id="path-title">От поиска — к обращению.</h2></div>
        <div class="path-grid">
          <article class="path-card"><div class="path-art search-art" aria-hidden="true"><div class="search-window"><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-search"/></svg><span>Нужна ваша услуга</span></div><div class="search-result"><span>Реклама вашей компании</span><i></i><i></i></div></div><div class="path-caption"><span class="step-number">01</span><div><h3>Вас находят</h3><p>Реклама приводит посетителей.</p></div></div><span class="path-arrow" aria-hidden="true">→</span></article>
          <article class="path-card"><div class="path-art website-art" aria-hidden="true"><div class="mini-browser"><div class="mini-bar"><i></i><i></i><i></i></div><div class="mini-body"><div class="mini-lines"><i></i><i></i><b></b></div><div class="mini-shape"></div></div><div class="mini-tiles"><i></i><i></i><i></i></div></div></div><div class="path-caption"><span class="step-number">02</span><div><h3>Понимают предложение</h3><p>Сайт помогает выбрать вас.</p></div></div><span class="path-arrow" aria-hidden="true">→</span></article>
          <article class="path-card"><div class="path-art inquiry-art" aria-hidden="true"><div class="contact-illustration"><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-message"/></svg></span><div><i></i><i></i></div><b><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-check"/></svg></b></div></div><div class="path-caption"><span class="step-number">03</span><div><h3>Оставляют контакт</h3><p>Вы получаете обращение.</p></div></div></article>
        </div>
      </div>
    </section>

    <section class="section formats" id="price" aria-labelledby="price-title">
      <div class="container">
        <div class="section-heading split-heading"><div><span class="eyebrow">ПЕРВЫЙ ШАГ — ВАШ САЙТ</span><h2 id="price-title">Начните с подходящего формата.</h2></div><p>Цена — за разработку.<br>Продвижение подключается отдельно.</p></div>
        <div class="price-grid">
          <article class="price-card featured"><div class="format-art landing-art" aria-hidden="true"><div class="format-browser"><span></span><div class="format-hero"><i></i><b></b></div><div class="format-row"><i></i><i></i><i></i></div></div><span class="format-art-badge">01</span></div><div class="price-content"><h3>Лендинг</h3><p>Для одной услуги и рекламы.</p><div class="price-value"><small>от</small> <?= site_e(site_price('landing')) ?> <small><?= site_e($settings['currency']) ?></small></div><a class="button primary" href="#contact" data-service="Лендинг">Рассчитать лендинг <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div></article>
          <article class="price-card"><div class="format-art company-art" aria-hidden="true"><div class="format-browser"><span></span><div class="company-cover"><i></i><b></b></div><div class="company-pages"><i></i><i></i><i></i></div></div><span class="format-art-badge">02</span></div><div class="price-content"><h3>Сайт компании</h3><p>Для нескольких направлений.</p><div class="price-value"><small>от</small> <?= site_e(site_price('company')) ?> <small><?= site_e($settings['currency']) ?></small></div><a class="button secondary" href="#contact" data-service="Сайт компании">Рассчитать сайт <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div></article>
          <article class="price-card"><div class="format-art shop-art" aria-hidden="true"><div class="format-browser"><span></span><div class="shop-products"><i></i><i></i><i></i><i></i><i></i><i></i></div></div><span class="format-art-badge">03</span></div><div class="price-content"><h3>Интернет-магазин</h3><p>Для каталога и заказов товаров.</p><div class="price-value"><small>от</small> <?= site_e(site_price('shop')) ?> <small><?= site_e($settings['currency']) ?></small></div><a class="button secondary" href="#contact" data-service="Интернет-магазин">Рассчитать магазин <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div></article>
        </div>
        <div class="included-strip" aria-label="В каждом формате"><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-screen"/></svg> Дизайн</span><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-phone"/></svg> Мобильная версия</span><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-message"/></svg> Формы заявок</span><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-rocket"/></svg> Запуск и доступы</span></div>
        <p class="cost-note">Итог зависит от объёма. Домен, хостинг и платные интеграции оплачиваются отдельно.</p>
      </div>
    </section>



    <section class="section calculator-section" id="calculator" aria-labelledby="calculator-title">
      <div class="container"><div class="section-heading"><span class="eyebrow">СОБЕРИТЕ СВОЙ ЗАПУСК</span><h2 id="calculator-title">Прикинем бюджет?</h2><p class="calculator-intro">Выберите сайт и продвижение. Точную стоимость согласуем после обсуждения задачи.</p></div>
        <p class="calculator-fallback">Для расчёта можно сразу <a href="#contact">оставить заявку</a>. Форматы и начальные цены — выше.</p>
        <div class="calculator-box" id="calculator-box" hidden>
          <div class="calculator-options">
            <fieldset class="calculator-group"><legend><span>01</span> Какой сайт делаем</legend><div class="calculator-formats">
              <label><input type="radio" name="calc-format" value="Лендинг" checked><span><strong>Лендинг</strong><span>от <?= site_e(site_money('landing')) ?></span></span></label>
              <label><input type="radio" name="calc-format" value="Сайт компании"><span><strong>Сайт компании</strong><span>от <?= site_e(site_money('company')) ?></span></span></label>
              <label><input type="radio" name="calc-format" value="Интернет-магазин"><span><strong>Интернет-магазин</strong><span>от <?= site_e(site_money('shop')) ?></span></span></label>
            </div></fieldset>
            <fieldset class="calculator-group"><legend><span>02</span> Как привлекать клиентов</legend><div class="calculator-promotions">
              <label><input type="checkbox" id="calc-ads"><span><strong>Контекстная реклама</strong><span>от <?= site_e(site_money('adsMonthly')) ?>/мес.</span></span></label>
              <label><input type="checkbox" id="calc-seo"><span><strong>SEO-продвижение</strong><span>от <?= site_e(site_money('seoMonthly')) ?>/мес.</span></span></label>
            </div><div class="campaign-counter" id="calc-campaign-counter" hidden><label for="calc-campaigns">Рекламных кампаний</label><div><button type="button" data-campaign-delta="-1" aria-label="Уменьшить число кампаний">−</button><input id="calc-campaigns" type="number" min="1" max="<?= (int) $settings['calculator']['maxCampaigns'] ?>" value="1" inputmode="numeric"><button type="button" data-campaign-delta="1" aria-label="Увеличить число кампаний">+</button></div><p>+<?= site_e(site_money('additionalCampaign')) ?>/мес. за каждую следующую.</p></div></fieldset>
            <fieldset class="calculator-group"><legend><span>03</span> Дополнительные задачи</legend><div class="calculator-extras">
              <?php foreach ($settings['extras'] as $id => $extra): ?>
              <label><input type="checkbox" data-calc-extra="<?= site_e($id) ?>"><span><strong><?= site_e($extra['label']) ?></strong><span><?= $extra['price'] === null ? 'Стоимость уточним' : '+' . site_e(number_format($extra['price'], floor($extra['price']) === (float) $extra['price'] ? 0 : 2, ',', ' ') . ' ' . $settings['currency']) ?></span></span></label>
              <?php endforeach; ?>
            </div></fieldset>
          </div>
          <aside class="calculator-summary" aria-label="Предварительный расчёт"><span class="eyebrow">ОРИЕНТИР ПО БЮДЖЕТУ</span><div class="estimate-main"><span>Разработка</span><strong id="calc-one-time">от <?= site_e(site_money('landing')) ?></strong><span>разово</span></div><div class="estimate-monthly" id="calc-monthly-block" hidden><span>Продвижение</span><strong id="calc-monthly"></strong><span>в месяц</span></div><div class="calculator-breakdown" id="calc-breakdown"></div><p class="calculator-unknown" id="calc-unknown" hidden></p><p class="calculator-budget-note" id="calc-ad-budget" hidden>Бюджет на клики — отдельно.</p><a class="button primary" href="#contact" id="calculator-transfer">Перенести в заявку <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a><p class="calculator-summary-note">Выбор перенесётся в форму. Останется указать контакты.</p><span class="sr-only" id="calculator-live" role="status" aria-live="polite"></span></aside>
        </div>
      </div>
    </section>

    <section class="section trust" aria-labelledby="trust-title">
      <div class="container"><div class="section-heading"><span class="eyebrow">ПОНЯТНО, ЗА ЧТО ВЫ ПЛАТИТЕ</span><h2 id="trust-title">Рабочий сайт. Ваш контроль.</h2></div>
        <div class="trust-grid">
          <article><div class="trust-card-top"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-phone"/></svg></span><i>01</i></div><h3>Удобно с телефона</h3><p>Проверяем текст, кнопки и формы на мобильном экране.</p></article>
          <article><div class="trust-card-top"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-shield"/></svg></span><i>02</i></div><h3>Проверка перед запуском</h3><p>Проверяем заявки, контактные ссылки и основные сценарии.</p></article>
          <article><div class="trust-card-top"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-key"/></svg></span><i>03</i></div><h3>Сайт и доступы — ваши</h3><p>Передаём файлы, материалы и необходимые доступы.</p></article>
          <article><div class="trust-card-top"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-check"/></svg></span><i>04</i></div><h3>Стоимость до старта</h3><p>Состав, цену и сроки согласуем заранее. Дополнительные работы — отдельно.</p></article>
          <article><div class="trust-card-top"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-settings"/></svg></span><i>05</i></div><h3>Свой код или CMS</h3><p>Учитываем нужные функции, удобство редактирования и ваш бюджет.</p></article>
          <article><div class="trust-card-top"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-target"/></svg></span><i>06</i></div><h3>Сайт и продвижение</h3><p>Можем продолжить работу с рекламой или SEO после запуска.</p></article>
        </div>
      </div>
    </section>

    <section class="section process" id="process" aria-labelledby="process-title">
      <div class="container"><div class="section-heading"><span class="eyebrow">КАК МЫ РАБОТАЕМ</span><h2 id="process-title">От вашей задачи — к запуску.</h2></div>
        <div class="process-box">
          <div class="process-box-top"><strong>Понятный процесс</strong><span>Сайт и его развитие</span></div>
          <nav class="process-tabs" aria-label="Этапы работы">
            <?php foreach (site_process() as $index => $step): ?>
            <a class="process-tab" id="process-tab-<?= site_e($step['id']) ?>" href="#process-panel-<?= site_e($step['id']) ?>" data-process-tab="<?= $index ?>"><span><?= sprintf('%02d', $index + 1) ?></span><?= site_e($step['tab']) ?></a>
            <?php endforeach; ?>
          </nav>
          <div class="process-panels">
            <?php foreach (site_process() as $index => $step): ?>
            <article class="process-step" id="process-panel-<?= site_e($step['id']) ?>" data-process-step>
              <div class="process-visual" aria-hidden="true">
                <?php if ($step['id'] === 'task'): ?>
                <div class="process-map"><div class="process-goal"><svg class="icon" viewBox="0 0 24 24"><use href="#icon-target"/></svg><span>Цель: обращения</span></div><div class="map-connectors"></div><div class="map-nodes"><span>Услуга</span><span>Клиент</span><span>Заявка</span></div></div>
                <?php elseif ($step['id'] === 'structure'): ?>
                <div class="process-wire"><span><i>01</i><b>Предложение</b></span><span><i>02</i><b>Преимущества</b></span><span><i>03</i><b>Форма заявки</b></span></div>
                <?php elseif ($step['id'] === 'design'): ?>
                <div class="process-design"><div class="design-browser"><span>Ваш стиль</span><div><i></i><b></b></div><p><i></i><i></i><i></i></p></div><div class="design-swatches"><i></i><i></i><i></i></div></div>
                <?php elseif ($step['id'] === 'development'): ?>
                <div class="process-devices"><div class="process-device-screen"><svg class="icon" viewBox="0 0 24 24"><use href="#icon-screen"/></svg></div><div class="process-device-phone"><svg class="icon" viewBox="0 0 24 24"><use href="#icon-phone"/></svg></div><span><svg class="icon" viewBox="0 0 24 24"><use href="#icon-check"/></svg> Формы проверены</span></div>
                <?php elseif ($step['id'] === 'launch'): ?>
                <div class="process-launch"><div><svg class="icon" viewBox="0 0 24 24"><use href="#icon-rocket"/></svg></div><span>ваш-сайт.by <svg class="icon" viewBox="0 0 24 24"><use href="#icon-check"/></svg></span></div>
                <?php else: ?>
                <div class="process-promotion"><div><span><svg class="icon" viewBox="0 0 24 24"><use href="#icon-search"/></svg></span><b>Поиск</b></div><i>→</i><div><span><svg class="icon" viewBox="0 0 24 24"><use href="#icon-screen"/></svg></span><b>Сайт</b></div><i>→</i><div><span><svg class="icon" viewBox="0 0 24 24"><use href="#icon-message"/></svg></span><b>Контакт</b></div></div>
                <?php endif; ?>
              </div>
              <div class="process-copy"><span class="process-stage-label">Этап <?= sprintf('%02d', $index + 1) ?> из 06</span><h3><?= site_e($step['title']) ?></h3><p><?= site_e($step['description']) ?></p><strong class="process-result-label">Что вы получаете</strong><ul><?php foreach ($step['results'] as $result): ?><li><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-check"/></svg><?= site_e($result) ?></li><?php endforeach; ?></ul><?php if ($step['id'] === 'promotion'): ?><a class="process-promotion-cta" href="#contact" data-process-promotion>Обсудить сайт и рекламу <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a><?php endif; ?></div>
            </article>
            <?php endforeach; ?>
          </div>
          <div class="process-footer"><p>План адаптируем под объём вашего проекта.</p><div class="process-controls" hidden><button type="button" class="process-prev" aria-label="Предыдущий этап"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-arrow-right"/></svg></button><span class="process-counter" role="status" aria-live="polite">01 / 06</span><button type="button" class="process-next" aria-label="Следующий этап"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-arrow-right"/></svg></button></div></div>
        </div>
      </div>
    </section>

    <section class="section growth" id="growth" aria-labelledby="growth-title">
      <div class="container growth-grid">
        <div class="growth-intro"><span class="eyebrow light">СЛЕДУЮЩИЙ ШАГ — ПОСЕТИТЕЛИ</span><h2 id="growth-title">Сайт готов.<br><span>Запустим рекламу.</span></h2><p>Приведём людей, которые уже ищут ваши товары или услуги.</p><div class="ad-visual" aria-hidden="true"><div class="ad-visual-search"><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-search"/></svg><span>Вашу услугу ищут</span></div><div class="ad-visual-result"><span class="ad-visual-mark">Реклама</span><strong>Ваша компания</strong><div><i></i><i></i></div><span class="ad-destination"><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-screen"/></svg> Переход на ваш сайт <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></span></div><span class="ad-click"><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-cursor"/></svg></span></div></div>
        <div class="launch-card" aria-labelledby="launch-title">
          <h3 id="launch-title">Выберите свой старт</h3>
          <div class="package-tabs" role="group" aria-label="Вариант запуска"><button type="button" data-package="site" aria-pressed="true">Только сайт</button><button type="button" data-package="ads" aria-pressed="false">Сайт + реклама</button></div>
          <div class="launch-line"><span class="launch-symbol"><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-screen"/></svg></span><div><span id="package-site-label">Разработка сайта</span><strong>от <span id="package-site-price"><?= site_e(site_price('landing')) ?></span> <?= site_e($settings['currency']) ?></strong><span>разово</span></div></div>
          <div class="launch-line promotion-line" id="promotion-price" hidden><span class="launch-symbol"><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-search"/></svg></span><div><span>Настройка и ведение рекламы</span><strong>от <?= site_e(site_money('adsMonthly')) ?></strong><span>в месяц · одна кампания</span></div></div>
          <div class="package-hint" id="package-hint" role="status" aria-live="polite">Начнём с сайта. Рекламу можно подключить позже.</div>
          <p class="ad-budget-note" id="ad-budget-note" hidden>Бюджет на клики оплачивается отдельно.</p>
          <a class="button primary" id="package-cta" href="#contact">Обсудить такой запуск <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a>
          <details class="campaign-details"><summary>Если кампаний несколько</summary><p><?= site_e(site_money('adsMonthly')) ?>/мес. за первую, +<?= site_e(site_money('additionalCampaign')) ?>/мес. за каждую следующую. Настройка и ведение входят в стоимость.</p></details>
        </div>
      </div>
    </section>

    <section class="section results" id="results" aria-labelledby="results-title">
      <div class="container">
        <div class="section-heading"><span class="eyebrow">ПРОДВИЖЕНИЕ В ДЕЛЕ</span><h2 id="results-title">Результаты наших проектов.</h2></div>
        <div class="results-grid">
          <article class="result-card ppc-result"><span class="result-tag">КОНТЕКСТНАЯ РЕКЛАМА</span><div class="result-number">+80–95<span>%</span></div><h3>заявок по проектам</h3><div class="index-chart" role="img" aria-label="Индекс заявок: до работы 100, после 180–195. Общий диапазон по трём проектам."><div class="chart-row"><span>До</span><div class="chart-track"><i class="baseline ppc-before"></i></div><strong>100</strong></div><div class="chart-row"><span>После</span><div class="chart-track"><i class="after ppc-after"></i></div><strong>180–195</strong></div></div><p class="index-label">Индекс: исходный уровень = 100</p><div class="project-tags"><span>WEBUILD</span><span>Wooden House Bel</span><span>DASTO</span></div></article>
          <article class="result-card seo-result"><span class="result-tag">SEO · BONDARKA</span><div class="result-number">+215<span>%</span></div><h3>органического трафика</h3><div class="index-chart" role="img" aria-label="Индекс органического трафика: до работы 100, после 315."><div class="chart-row"><span>До</span><div class="chart-track"><i class="baseline seo-before"></i></div><strong>100</strong></div><div class="chart-row"><span>После</span><div class="chart-track"><i class="after seo-after"></i></div><strong>315</strong></div></div><p class="index-label">Индекс: исходный уровень = 100</p><div class="project-tags"><span>Производство</span><span>Поисковое продвижение</span></div></article>
        </div>
        <p class="cost-note">Диапазон по рекламе общий для трёх проектов. Результаты нового проекта зависят от спроса, предложения и бюджета.</p>
      </div>
    </section>

    <section class="section faq" aria-labelledby="faq-title">
      <div class="container faq-grid">
        <div><span class="eyebrow">ПЕРЕД СТАРТОМ</span><h2 id="faq-title">Есть вопросы?</h2><p class="faq-intro">Выберите вопрос — покажем готовый ответ. Свой вопрос можно задать <?= isset($contactMethods['telegram']) ? 'в Telegram' : 'через контакты' ?>.</p><a class="faq-direct-link" href="<?= site_e($contactMethods['telegram']['href'] ?? '/#contacts') ?>">Задать свой вопрос <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div>
        <div class="faq-interactive">
          <div class="faq-list faq-fallback" id="faq-fallback">
            <?php foreach (site_faq() as $item): ?>
            <details data-faq-id="<?= site_e($item['id']) ?>"><summary><?= site_e($item['question']) ?></summary><p data-faq-answer><?= site_e($item['answer']) ?></p></details>
            <?php endforeach; ?>
          </div>
          <div class="faq-chat" id="faq-chat" hidden>
            <div class="faq-chat-header"><span class="faq-chat-avatar" aria-hidden="true">u.</span><div><strong>uweb.by</strong><span>Готовые ответы</span></div><button class="faq-reset" type="button" aria-label="Начать диалог заново"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-reset"/></svg><span>Сначала</span></button></div>
            <div class="faq-chat-log" id="faq-chat-log" role="log" aria-live="polite" aria-relevant="additions text" aria-label="Вопросы и готовые ответы" tabindex="0"><div class="chat-message chat-answer chat-welcome">Здравствуйте! Здесь готовые ответы про сайты и продвижение. Выберите вопрос или напишите свой <?= isset($contactMethods['telegram']) ? 'в Telegram' : 'через контакты' ?>.</div></div>
            <div class="faq-chat-bottom"><div class="faq-choices" aria-label="Выберите вопрос"></div>
              <?php if (isset($contactMethods['telegram'])): ?>
              <form class="faq-question-form" id="faq-question-form" action="<?= site_e($contactMethods['telegram']['href']) ?>" method="get" target="_blank" rel="noopener noreferrer"><label class="sr-only" for="faq-custom-question">Свой вопрос в Telegram</label><input id="faq-custom-question" name="text" maxlength="1000" placeholder="Свой вопрос — в Telegram" autocomplete="off"><button type="submit" aria-label="Открыть Telegram с моим вопросом"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-telegram"/></svg></button></form>
              <?php else: ?>
              <a class="button secondary" href="#contacts" data-open-contacts>Задать свой вопрос</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section contact" id="contact" aria-labelledby="contact-title">
      <div class="container contact-grid">
        <div><span class="eyebrow light">НАЧНЁМ С ВАШЕГО БИЗНЕСА</span><h2 id="contact-title">Сайт — первый шаг.<br><span>Цель — обращения.</span></h2><p class="contact-lead">Подберём формат сайта и обсудим, как привлекать клиентов.</p><div class="contact-path" aria-hidden="true"><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-screen"/></svg><b>Сайт</b></span><i>→</i><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-search"/></svg><b>Реклама</b></span><i>→</i><span><svg class="icon " viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-message"/></svg><b>Контакт</b></span></div><div class="contact-person"><span class="person-avatar" aria-hidden="true"><?= site_e(preg_match('/^./us', $settings['contacts']['name'], $initial) ? $initial[0] : 'u') ?></span><div><strong><?= site_e($settings['contacts']['name']) ?></strong><a href="#contacts" data-open-contacts aria-haspopup="dialog" aria-controls="contacts-dialog" aria-expanded="false">Выбрать способ связи <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div></div></div>
        <form id="project-form" action="/send-mail.php" method="post">
          <div class="form-estimate" id="form-estimate" hidden><strong>Ваш предварительный расчёт</strong><div id="form-estimate-lines"></div><a href="#calculator">Изменить расчёт</a></div>
          <input type="hidden" name="estimate_source" value=""><input type="hidden" name="campaigns" value="1"><input type="hidden" name="seo" value=""><input type="hidden" name="extras" value="">
          <label for="service">Какой сайт вам нужен?</label><select id="service" name="service"><option value="Индивидуальное решение">Помогите выбрать формат</option><option value="Лендинг">Лендинг — от <?= site_e(site_money('landing')) ?></option><option value="Сайт компании">Сайт компании — от <?= site_e(site_money('company')) ?></option><option value="Интернет-магазин">Интернет-магазин — от <?= site_e(site_money('shop')) ?></option></select>
          <label class="promotion-option" for="promotion"><input id="promotion" type="checkbox" name="promotion" value="ppc"><span>Обсудить рекламу для этого сайта</span></label>
          <div class="form-row"><div><label for="name">Ваше имя</label><input id="name" name="name" autocomplete="given-name" maxlength="80" required placeholder="Как к вам обращаться"></div><div><label for="client-contact">Контакт для ответа</label><input id="client-contact" name="contact" maxlength="160" required placeholder="Телефон или Telegram"></div></div>
          <details class="form-more"><summary>Добавить описание задачи</summary><label class="sr-only" for="message">Пара слов о задаче</label><textarea id="message" name="message" rows="3" maxlength="3000" placeholder="Что продаёте и какой сайт нужен"></textarea></details>
          <div class="honeypot" aria-hidden="true"><label>Не заполняйте<input name="website" tabindex="-1" autocomplete="off"></label></div>
          <button class="button primary" type="submit"><span id="submit-label">Получить расчёт</span><svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></button>
          <p id="form-status" role="status" aria-live="polite"></p><p class="form-note">Контакт используем для ответа на вашу заявку.</p>
        </form>
      </div>
    </section>

    <section class="section contact-directory" id="contacts" aria-labelledby="contacts-title">
      <div class="container"><div class="section-heading"><span class="eyebrow">НА СВЯЗИ</span><h2 id="contacts-title">Наши контакты.</h2><p class="directory-intro">Выберите, как вам удобнее обсудить проект.</p></div><div class="contact-directory-grid"><?php site_contact_cards(); ?></div></div>
    </section>
  </main>
  <footer><div class="container footer-inner"><a class="logo" href="#" aria-label="uweb.by — на главную">uweb<span>.</span>by</a><span>Сайт. Реклама. Продвижение.</span><a href="<?= site_e($contactMethods['email']['href']) ?>"><?= site_e($settings['contacts']['email']) ?> <svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div></footer>

  <details class="quick-contact" id="quick-contact">
    <summary class="quick-contact-toggle" aria-label="Открыть контакты" aria-controls="quick-contact-panel"><svg class="icon widget-open-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-message"/></svg><svg class="icon widget-close-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-close"/></svg></summary>
    <div class="quick-contact-panel" id="quick-contact-panel"><strong>Свяжитесь с нами</strong><div class="contact-method-list"><?php site_contact_cards(); ?></div></div>
  </details>
  <dialog class="contacts-dialog" id="contacts-dialog" aria-labelledby="dialog-contacts-title">
    <form method="dialog" class="dialog-close-form"><button class="dialog-close" type="submit" aria-label="Закрыть контакты" autofocus><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-close"/></svg></button></form>
    <span class="eyebrow">ОБСУДИМ ВАШ ПРОЕКТ</span><h2 id="dialog-contacts-title">Наши контакты</h2><div class="contact-method-list"><?php site_contact_cards(); ?></div>
  </dialog>
</body>
</html>
