const form = document.querySelector('#project-form');
const status = document.querySelector('#form-status');
const submitButton = form.querySelector('button[type="submit"]');
const submitLabel = document.querySelector('#submit-label');
const promotion = document.querySelector('#promotion');
const packageButtons = [...document.querySelectorAll('[data-package]')];
const siteSettings = JSON.parse(document.querySelector('#site-settings').textContent);
const priceFormatter = new Intl.NumberFormat('ru-BY', { maximumFractionDigits: 2 });
const formatPrices = {
  'Лендинг': siteSettings.prices.landing,
  'Сайт компании': siteSettings.prices.company,
  'Интернет-магазин': siteSettings.prices.shop,
  'Индивидуальное решение': siteSettings.prices.landing,
};

function syncPackage() {
  const withAds = promotion.checked;
  packageButtons.forEach(button => {
    button.setAttribute('aria-pressed', String((button.dataset.package === 'ads') === withAds));
  });
  document.querySelector('#promotion-price').hidden = !withAds;
  document.querySelector('#ad-budget-note').hidden = !withAds;
  document.querySelector('#package-hint').textContent = withAds
    ? 'Разработка — разово. Реклама — отдельная ежемесячная услуга.'
    : 'Начнём с сайта. Рекламу можно подключить позже.';
  const service = form.elements.service.value;
  document.querySelector('#package-site-label').textContent = service === 'Индивидуальное решение'
    ? 'Разработка сайта'
    : service;
  document.querySelector('#package-site-price').textContent = priceFormatter.format(formatPrices[service]);
  form.dispatchEvent(new CustomEvent('quotechange'));
}

packageButtons.forEach(button => button.addEventListener('click', () => {
  promotion.checked = button.dataset.package === 'ads';
  syncPackage();
}));
promotion.addEventListener('change', syncPackage);
form.elements.service.addEventListener('change', syncPackage);
document.querySelectorAll('[data-service]').forEach(link => link.addEventListener('click', () => {
  form.elements.service.value = link.dataset.service;
  syncPackage();
}));
// Also restores the package display when the browser restores form state.
window.addEventListener('pageshow', syncPackage);
syncPackage();

form.addEventListener('submit', async event => {
  event.preventDefault();
  if (submitButton.disabled) return;
  for (const name of ['name', 'contact']) form.elements[name].value = form.elements[name].value.trim();
  if (!form.reportValidity()) return;
  const payload = Object.fromEntries(new FormData(form));
  submitButton.disabled = true;
  form.setAttribute('aria-busy', 'true');
  submitLabel.textContent = 'Отправляем…';
  status.textContent = '';
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 15000);
  try {
    const response = await fetch(form.action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload),
      signal: controller.signal,
    });
    const result = await response.json().catch(() => null);
    if (!result) throw new Error('Сервер не смог обработать заявку. Свяжитесь с нами через «Наши контакты» или попробуйте позже.');
    if (!response.ok || result.ok !== true) throw new Error(result.message || 'Не удалось отправить заявку.');
    status.textContent = result.message || 'Спасибо! Заявка отправлена.';
    status.dataset.state = 'success';
    form.reset();
    syncPackage();
  } catch (error) {
    status.textContent = error.name === 'AbortError'
      ? 'Сервер не ответил вовремя. Свяжитесь с нами через «Наши контакты» или попробуйте позже.'
      : error.message === 'Failed to fetch'
        ? 'Нет связи с сервером. Попробуйте позже или откройте «Наши контакты».'
        : error.message;
    status.dataset.state = 'error';
  } finally {
    clearTimeout(timeout);
    submitButton.disabled = false;
    form.removeAttribute('aria-busy');
    submitLabel.textContent = 'Получить расчёт';
  }
});

// One server-rendered contact list is reused in the directory, dialog and widget.
const contactDialog = document.querySelector('#contacts-dialog');
const quickContact = document.querySelector('#quick-contact');
const quickToggle = quickContact.querySelector('summary');
const contactOpeners = [...document.querySelectorAll('[data-open-contacts]')];
let contactOpener;
function closeQuickContact(restoreFocus = false) {
  quickContact.open = false;
  if (restoreFocus) quickToggle.focus({ preventScroll: true });
}
quickContact.addEventListener('toggle', () => {
  quickToggle.setAttribute('aria-label', quickContact.open ? 'Закрыть контакты' : 'Открыть контакты');
});
document.addEventListener('click', event => {
  if (quickContact.open && !quickContact.contains(event.target)) closeQuickContact();
});
document.addEventListener('keydown', event => {
  if (event.key === 'Escape' && quickContact.open && !contactDialog.open) {
    closeQuickContact(quickContact.contains(document.activeElement));
  }
});
quickContact.querySelectorAll('a').forEach(link => link.addEventListener('click', () => closeQuickContact()));
contactOpeners.forEach(opener => opener.addEventListener('click', event => {
  if (typeof contactDialog.showModal !== 'function') return;
  event.preventDefault();
  closeQuickContact();
  contactOpener = opener;
  contactDialog.showModal();
  document.body.classList.add('dialog-open');
  contactOpeners.forEach(link => link.setAttribute('aria-expanded', 'true'));
  contactDialog.querySelector('.dialog-close').focus({ preventScroll: true });
}));
contactDialog.addEventListener('close', () => {
  document.body.classList.remove('dialog-open');
  contactOpeners.forEach(link => link.setAttribute('aria-expanded', 'false'));
  if (contactOpener?.isConnected) contactOpener.focus({ preventScroll: true });
});
contactDialog.addEventListener('click', event => {
  if (event.target !== contactDialog) return;
  const bounds = contactDialog.getBoundingClientRect();
  if (event.clientX < bounds.left || event.clientX > bounds.right
      || event.clientY < bounds.top || event.clientY > bounds.bottom) contactDialog.close();
});
contactDialog.querySelectorAll('.contact-method').forEach(link => {
  link.addEventListener('click', () => contactDialog.close());
});

// Motion is decorative. Content and contacts remain available without JavaScript.
const root = document.documentElement;
const motionPreference = matchMedia('(prefers-reduced-motion: reduce)');
const motionButton = document.querySelector('.motion-toggle');
const heroStudio = document.querySelector('.hero-studio');
const charts = [...document.querySelectorAll('.index-chart')];
const revealItems = [...document.querySelectorAll('.path-card, .price-card, .launch-card, .result-card, .contact-directory-grid .contact-method')];
let motionPaused = false;
function updateMotion() {
  const reduced = motionPreference.matches;
  root.classList.toggle('motion-enabled', !reduced);
  root.classList.toggle('motion-paused', motionPaused);
  motionButton.hidden = reduced;
  motionButton.setAttribute('aria-pressed', String(motionPaused));
  motionButton.querySelector('span').textContent = motionPaused ? 'Запустить движение' : 'Остановить движение';
  motionButton.querySelector('use').setAttribute('href', motionPaused ? '#icon-play' : '#icon-pause');
  if (reduced || motionPaused) {
    charts.forEach(chart => chart.classList.add('is-filled'));
    revealItems.forEach(item => item.classList.add('is-shown'));
  }
}
motionButton.addEventListener('click', () => {
  motionPaused = !motionPaused;
  updateMotion();
});
motionPreference.addEventListener('change', updateMotion);
updateMotion();
if ('IntersectionObserver' in window && !motionPreference.matches) {
  const chartObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-filled');
      chartObserver.unobserve(entry.target);
    });
  }, { threshold: 0.35 });
  charts.forEach(chart => {
    chart.classList.add('chart-ready');
    chartObserver.observe(chart);
  });
  const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-shown');
      revealObserver.unobserve(entry.target);
    });
  }, { threshold: 0.12 });
  revealItems.forEach((item, index) => {
    item.style.setProperty('--reveal-delay', `${(index % 3) * 80}ms`);
    item.classList.add('reveal-ready');
    revealObserver.observe(item);
  });
  new IntersectionObserver(entries => {
    heroStudio.classList.toggle('motion-offscreen', !entries[0].isIntersecting);
  }, { threshold: 0 }).observe(heroStudio);
}
document.addEventListener('visibilitychange', () => {
  heroStudio.classList.toggle('motion-page-hidden', document.hidden);
});

// FAQ chat uses authored answers from the server-rendered fallback, not a live agent.
const faqFallback = document.querySelector('#faq-fallback');
const faqChat = document.querySelector('#faq-chat');
const faqLog = document.querySelector('#faq-chat-log');
const faqChoices = faqChat.querySelector('.faq-choices');
const faqReset = faqChat.querySelector('.faq-reset');
const faqWelcome = faqLog.querySelector('.chat-welcome').cloneNode(true);
const faqTopics = [...faqFallback.querySelectorAll('[data-faq-id]')].map(item => ({
  id: item.dataset.faqId,
  question: item.querySelector('summary').textContent.trim(),
  answer: item.querySelector('[data-faq-answer]').textContent.trim(),
}));
const askedTopics = new Set();
let faqTimer;
let faqVersion = 0;
function scrollFaq() {
  faqLog.scrollTo({ top: faqLog.scrollHeight, behavior: motionPreference.matches || motionPaused ? 'auto' : 'smooth' });
}
function addFaqMessage(text, className) {
  const message = document.createElement('div');
  message.className = `chat-message ${className}`;
  message.textContent = text;
  faqLog.append(message);
  scrollFaq();
  return message;
}
function updateFaqButtons(busy = false) {
  faqChoices.querySelectorAll('button').forEach(button => {
    const answered = askedTopics.has(button.dataset.topic);
    button.disabled = busy || answered;
    button.classList.toggle('is-answered', answered);
  });
}
faqTopics.forEach(topic => {
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'faq-question-choice';
  button.dataset.topic = topic.id;
  button.setAttribute('aria-controls', 'faq-chat-log');
  button.textContent = topic.question;
  button.addEventListener('click', event => {
    if (askedTopics.has(topic.id) || faqLog.getAttribute('aria-busy') === 'true') return;
    askedTopics.add(topic.id);
    const keyboard = event.detail === 0;
    const version = faqVersion;
    addFaqMessage(topic.question, 'chat-question');
    const reply = document.createElement('div');
    reply.className = 'chat-message chat-answer chat-typing';
    reply.innerHTML = '<span class="sr-only">Подготавливаем готовый ответ</span><span class="typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>';
    faqLog.append(reply);
    faqLog.setAttribute('aria-busy', 'true');
    updateFaqButtons(true);
    scrollFaq();
    faqTimer = setTimeout(() => {
      if (version !== faqVersion) return;
      reply.classList.remove('chat-typing');
      reply.textContent = topic.answer;
      reply.classList.add('chat-arrived');
      faqLog.removeAttribute('aria-busy');
      updateFaqButtons();
      scrollFaq();
      if (keyboard) faqLog.focus({ preventScroll: true });
    }, motionPreference.matches || motionPaused ? 0 : 650);
  });
  faqChoices.append(button);
});
faqReset.addEventListener('click', () => {
  clearTimeout(faqTimer);
  faqVersion += 1;
  askedTopics.clear();
  faqLog.removeAttribute('aria-busy');
  faqLog.replaceChildren(faqWelcome.cloneNode(true));
  faqLog.scrollTop = 0;
  updateFaqButtons();
});
faqChat.hidden = false;
faqFallback.hidden = true;

// Process stages advance only on user input; ordinary links work without JS.
const processBox = document.querySelector('.process-box');
const processTabList = processBox.querySelector('.process-tabs');
const processTabs = [...processBox.querySelectorAll('[data-process-tab]')];
const processPanels = [...processBox.querySelectorAll('[data-process-step]')];
const processPrevious = processBox.querySelector('.process-prev');
const processNext = processBox.querySelector('.process-next');
let processIndex = 0;
function selectProcess(index, focus = false) {
  processIndex = Math.max(0, Math.min(processPanels.length - 1, index));
  processTabs.forEach((tab, itemIndex) => {
    const active = itemIndex === processIndex;
    tab.setAttribute('aria-selected', String(active));
    tab.tabIndex = active ? 0 : -1;
    processPanels[itemIndex].hidden = !active;
  });
  processPrevious.disabled = processIndex === 0;
  processNext.disabled = processIndex === processPanels.length - 1;
  processBox.querySelector('.process-counter').textContent = `${String(processIndex + 1).padStart(2, '0')} / 06`;
  const tab = processTabs[processIndex];
  const left = tab.getBoundingClientRect().left - processTabList.getBoundingClientRect().left + processTabList.scrollLeft;
  const scrollTarget = left - (processTabList.clientWidth - tab.offsetWidth) / 2;
  processTabList.scrollTo({ left: scrollTarget, behavior: motionPreference.matches || motionPaused ? 'auto' : 'smooth' });
  if (focus) tab.focus({ preventScroll: true });
}
processTabList.setAttribute('role', 'tablist');
processTabs.forEach((tab, index) => {
  tab.setAttribute('role', 'tab');
  tab.setAttribute('aria-controls', processPanels[index].id);
  processPanels[index].setAttribute('role', 'tabpanel');
  processPanels[index].setAttribute('aria-labelledby', tab.id);
  processPanels[index].tabIndex = 0;
  tab.addEventListener('click', event => { event.preventDefault(); selectProcess(index); });
  tab.addEventListener('keydown', event => {
    let next;
    if (event.key === 'ArrowRight') next = (index + 1) % processTabs.length;
    else if (event.key === 'ArrowLeft') next = (index + processTabs.length - 1) % processTabs.length;
    else if (event.key === 'Home') next = 0;
    else if (event.key === 'End') next = processTabs.length - 1;
    else return;
    event.preventDefault();
    selectProcess(next, true);
  });
});
processPrevious.addEventListener('click', () => selectProcess(processIndex - 1));
processNext.addEventListener('click', () => selectProcess(processIndex + 1));
processBox.querySelector('[data-process-promotion]').addEventListener('click', () => {
  promotion.checked = true;
  syncPackage();
});
processBox.classList.add('is-enhanced');
processBox.querySelector('.process-controls').hidden = false;
selectProcess(0);

// The calculator uses only published settings; unknown extra rates stay unpriced.
const calculatorBox = document.querySelector('#calculator-box');
const calcFormatInputs = [...calculatorBox.querySelectorAll('[name="calc-format"]')];
const calcAds = document.querySelector('#calc-ads');
const calcSeo = document.querySelector('#calc-seo');
const calcCampaigns = document.querySelector('#calc-campaigns');
const calcExtraInputs = [...calculatorBox.querySelectorAll('[data-calc-extra]')];
const calcFormatKeys = { 'Лендинг': 'landing', 'Сайт компании': 'company', 'Интернет-магазин': 'shop' };
const calcPreview = document.querySelector('#form-estimate');
function estimateMoney(value) {
  return `${priceFormatter.format(value)} ${siteSettings.currency}`;
}
function estimateCampaigns() {
  const value = Math.floor(Number(calcCampaigns.value));
  return Math.max(1, Math.min(siteSettings.calculator.maxCampaigns, Number.isFinite(value) ? value : 1));
}
function estimateRow(label, amount) {
  const row = document.createElement('div');
  const title = document.createElement('span');
  const value = document.createElement('strong');
  title.textContent = label;
  value.textContent = amount;
  row.append(title, value);
  return row;
}
function activateEstimate() {
  form.elements.estimate_source.value = 'calculator';
}
function renderCalculator() {
  const service = form.elements.service.value;
  const selectedFormat = Object.hasOwn(calcFormatKeys, service) ? service : 'Лендинг';
  calcFormatInputs.forEach(input => { input.checked = input.value === selectedFormat; });
  calcAds.checked = promotion.checked;
  const campaigns = estimateCampaigns();
  document.querySelector('#calc-campaign-counter').hidden = !calcAds.checked;
  calculatorBox.querySelector('[data-campaign-delta="-1"]').disabled = campaigns <= 1;
  calculatorBox.querySelector('[data-campaign-delta="1"]').disabled = campaigns >= siteSettings.calculator.maxCampaigns;
  const base = siteSettings.prices[calcFormatKeys[selectedFormat]];
  let oneTime = base;
  const unknown = [];
  const extras = [];
  const breakdown = document.querySelector('#calc-breakdown');
  breakdown.replaceChildren(estimateRow(service === 'Индивидуальное решение' ? 'Формат уточним' : selectedFormat, `от ${estimateMoney(base)}`));
  calcExtraInputs.filter(input => input.checked).forEach(input => {
    const id = input.dataset.calcExtra;
    const extra = siteSettings.extras[id];
    extras.push(id);
    if (extra.price === null) {
      unknown.push(extra.label);
      breakdown.append(estimateRow(extra.label, 'Уточним'));
    } else {
      oneTime += extra.price;
      breakdown.append(estimateRow(extra.label, `+${estimateMoney(extra.price)}`));
    }
  });
  const adsMonthly = calcAds.checked ? siteSettings.prices.adsMonthly + (campaigns - 1) * siteSettings.prices.additionalCampaign : 0;
  const seoMonthly = calcSeo.checked ? siteSettings.prices.seoMonthly : 0;
  const monthly = adsMonthly + seoMonthly;
  if (calcAds.checked) breakdown.append(estimateRow(`Реклама · кампаний: ${campaigns}`, `от ${estimateMoney(adsMonthly)}/мес.`));
  if (calcSeo.checked) breakdown.append(estimateRow('SEO', `от ${estimateMoney(seoMonthly)}/мес.`));
  document.querySelector('#calc-one-time').textContent = `от ${estimateMoney(oneTime)}`;
  document.querySelector('#calc-monthly-block').hidden = !(calcAds.checked || calcSeo.checked);
  document.querySelector('#calc-monthly').textContent = `от ${estimateMoney(monthly)}`;
  document.querySelector('#calc-ad-budget').hidden = !calcAds.checked;
  const unknownNote = document.querySelector('#calc-unknown');
  unknownNote.hidden = unknown.length === 0;
  unknownNote.textContent = 'Цена выбранных допработ пока не включена в сумму — оценим их отдельно.';
  const active = form.elements.estimate_source.value === 'calculator';
  calcPreview.hidden = !active;
  if (active) {
    form.elements.campaigns.value = String(campaigns);
    form.elements.seo.value = calcSeo.checked ? '1' : '';
    form.elements.extras.value = extras.join(',');
    const lines = document.querySelector('#form-estimate-lines');
    lines.replaceChildren(estimateRow('Разработка', `от ${estimateMoney(oneTime)}`));
    if (calcAds.checked || calcSeo.checked) lines.append(estimateRow('Продвижение', `от ${estimateMoney(monthly)}/мес.`));
    if (extras.length) {
      const extraLine = document.createElement('p');
      extraLine.textContent = `Допзадачи: ${extras.map(id => siteSettings.extras[id].label).join(', ')}.`;
      lines.append(extraLine);
    }
    if (unknown.length) {
      const extraNote = document.createElement('p');
      extraNote.textContent = 'Стоимость допзадач нужно уточнить.';
      lines.append(extraNote);
    }
  }
  document.querySelector('#calculator-live').textContent = `Разработка от ${estimateMoney(oneTime)}${monthly ? `; продвижение от ${estimateMoney(monthly)} в месяц` : ''}${unknown.length ? '; допработы требуют отдельной оценки' : ''}.`;
}
calcFormatInputs.forEach(input => input.addEventListener('change', () => {
  activateEstimate();
  form.elements.service.value = input.value;
  syncPackage();
}));
calcAds.addEventListener('change', () => {
  activateEstimate();
  promotion.checked = calcAds.checked;
  syncPackage();
});
calcSeo.addEventListener('change', () => { activateEstimate(); renderCalculator(); });
calcExtraInputs.forEach(input => input.addEventListener('change', () => { activateEstimate(); renderCalculator(); }));
calcCampaigns.addEventListener('input', () => { activateEstimate(); renderCalculator(); });
calcCampaigns.addEventListener('change', () => { calcCampaigns.value = String(estimateCampaigns()); renderCalculator(); });
calculatorBox.querySelectorAll('[data-campaign-delta]').forEach(button => button.addEventListener('click', () => {
  activateEstimate();
  calcCampaigns.value = String(Math.max(1, Math.min(siteSettings.calculator.maxCampaigns, estimateCampaigns() + Number(button.dataset.campaignDelta))));
  renderCalculator();
}));
document.querySelector('#calculator-transfer').addEventListener('click', () => {
  activateEstimate();
  form.elements.service.value = calcFormatInputs.find(input => input.checked).value;
  syncPackage();
});
form.addEventListener('quotechange', renderCalculator);
form.addEventListener('reset', () => queueMicrotask(() => {
  form.elements.estimate_source.value = '';
  form.elements.campaigns.value = '1';
  form.elements.seo.value = '';
  form.elements.extras.value = '';
  calcSeo.checked = false;
  calcCampaigns.value = '1';
  calcExtraInputs.forEach(input => { input.checked = false; });
  renderCalculator();
}));
renderCalculator();
calculatorBox.hidden = false;
document.querySelector('.calculator-fallback').hidden = true;

// Keep keyboard focus inside the modal consistently across browsers.
contactDialog.addEventListener('keydown', event => {
  if (event.key !== 'Tab') return;
  const focusable = [...contactDialog.querySelectorAll('a[href], button:not(:disabled), input:not(:disabled), select:not(:disabled), textarea:not(:disabled), [tabindex="0"]')]
    .filter(element => element.getClientRects().length > 0);
  if (!focusable.length) return;
  const first = focusable[0];
  const last = focusable[focusable.length - 1];
  if (event.shiftKey && (document.activeElement === first || document.activeElement === contactDialog)) {
    event.preventDefault();
    last.focus();
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault();
    first.focus();
  }
});
