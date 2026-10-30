const form = document.querySelector('#project-form');
const status = document.querySelector('#form-status');
const submitButton = form.querySelector('button[type="submit"]');
const submitLabel = document.querySelector('#submit-label');
const promotion = document.querySelector('#promotion');
const packageButtons = [...document.querySelectorAll('[data-package]')];
const formatPrices = {
  'Лендинг': 400,
  'Сайт компании': 700,
  'Интернет-магазин': 900,
  'Индивидуальное решение': 400,
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
  document.querySelector('#package-site-price').textContent = formatPrices[service];
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
    if (!result) throw new Error('Сервер не смог обработать заявку. Напишите в Telegram или попробуйте позже.');
    if (!response.ok || result.ok !== true) throw new Error(result.message || 'Не удалось отправить заявку.');
    status.textContent = result.message || 'Спасибо! Заявка отправлена.';
    status.dataset.state = 'success';
    form.reset();
    syncPackage();
  } catch (error) {
    status.textContent = error.name === 'AbortError'
      ? 'Сервер не ответил вовремя. Напишите в Telegram или попробуйте позже.'
      : error.message === 'Failed to fetch'
        ? 'Нет связи с сервером. Попробуйте позже или напишите в Telegram.'
        : error.message;
    status.dataset.state = 'error';
  } finally {
    clearTimeout(timeout);
    submitButton.disabled = false;
    form.removeAttribute('aria-busy');
    submitLabel.textContent = 'Получить расчёт';
  }
});
