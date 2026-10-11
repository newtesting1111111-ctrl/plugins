import { contactConfig } from "./config.js";

const menuButton = document.querySelector(".menu-toggle");
const mobileMenu = document.querySelector("#mobile-menu");
function closeMenu() {
  mobileMenu.hidden = true;
  menuButton.setAttribute("aria-expanded", "false");
  menuButton.setAttribute("aria-label", "Открыть меню");
}
menuButton.addEventListener("click", () => {
  const isOpen = menuButton.getAttribute("aria-expanded") === "true";
  mobileMenu.hidden = isOpen;
  menuButton.setAttribute("aria-expanded", String(!isOpen));
  menuButton.setAttribute(
    "aria-label",
    isOpen ? "Открыть меню" : "Закрыть меню",
  );
});
mobileMenu
  .querySelectorAll("a")
  .forEach((link) => link.addEventListener("click", closeMenu));
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !mobileMenu.hidden) {
    closeMenu();
    menuButton.focus();
  }
});
document.addEventListener("click", (event) => {
  if (!mobileMenu.hidden && !event.target.closest(".header")) closeMenu();
});
window.matchMedia("(min-width: 901px)").addEventListener("change", (event) => {
  if (event.matches) closeMenu();
});

document.querySelectorAll("[data-service]").forEach((link) => {
  link.addEventListener("click", () => {
    const input = [...document.querySelectorAll('input[name="service"]')].find(
      (item) => item.value === link.dataset.service,
    );
    if (input) input.checked = true;
  });
});

const form = document.querySelector("#project-form");
const dialog = document.querySelector("#brief-dialog");
const briefText = document.querySelector("#brief-text");
const briefStatus = document.querySelector("#brief-status");
const sendBrief = document.querySelector("#send-brief");
let brief = "";
const email = contactConfig.email.trim();
const telegram = contactConfig.telegram.replace(/^@/, "").trim();
const hasEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
const hasTelegram = /^[a-zA-Z][a-zA-Z0-9_]{4,31}$/.test(telegram);

if (hasEmail || hasTelegram) {
  document.querySelector("#submit-label").textContent = "Обсудить проект";
  document.querySelector("#form-note").textContent = hasEmail
    ? "Подготовим письмо с вашей заявкой. Отправить его можно в почтовом приложении."
    : "Подготовим бриф для сообщения в Telegram.";
}

form.addEventListener("submit", (event) => {
  event.preventDefault();
  ["name", "contact"].forEach((name) => {
    form.elements.namedItem(name).value = form.elements
      .namedItem(name)
      .value.trim();
  });
  if (!form.reportValidity()) return;
  const data = new FormData(form);
  brief = `Заявка на разработку сайта\n\nФормат: ${data.get("service")}\nИмя: ${data.get("name")}\nКонтакт: ${data.get("contact")}\n\nО проекте:\n${String(data.get("message")).trim() || "Обсудим при знакомстве."}`;
  briefText.textContent = brief;
  briefStatus.textContent = "";
  sendBrief.hidden = true;
  if (hasEmail) {
    sendBrief.href = `mailto:${email}?subject=${encodeURIComponent("Заявка на разработку сайта — uweb.by")}&body=${encodeURIComponent(brief)}`;
    sendBrief.hidden = false;
    sendBrief.replaceChildren(
      "Открыть письмо ",
      document.querySelector(".icon-arrow").cloneNode(true),
    );
    document.querySelector("#brief-explanation").textContent =
      "Бриф готов. Откройте письмо и отправьте заявку из своего почтового приложения.";
  } else if (hasTelegram) {
    sendBrief.href = `https://t.me/${telegram}`;
    sendBrief.target = "_blank";
    sendBrief.rel = "noopener noreferrer";
    sendBrief.hidden = false;
    sendBrief.replaceChildren(
      "Открыть Telegram ",
      document.querySelector(".icon-arrow").cloneNode(true),
    );
    document.querySelector("#brief-explanation").textContent =
      "Бриф готов. Скопируйте его, откройте Telegram и отправьте нам сообщение.";
  }
  dialog.showModal();
});

document
  .querySelector(".dialog-close")
  .addEventListener("click", () => dialog.close());
dialog.addEventListener("click", (event) => {
  const bounds = dialog.getBoundingClientRect();
  if (
    event.clientX < bounds.left ||
    event.clientX > bounds.right ||
    event.clientY < bounds.top ||
    event.clientY > bounds.bottom
  )
    dialog.close();
});

document.querySelector("#copy-brief").addEventListener("click", async () => {
  try {
    await navigator.clipboard.writeText(brief);
    briefStatus.textContent = "Бриф скопирован.";
  } catch {
    const selection = window.getSelection();
    const range = document.createRange();
    range.selectNodeContents(briefText);
    selection.removeAllRanges();
    selection.addRange(range);
    briefStatus.textContent =
      "Выделили текст. Скопируйте его вручную или сохраните файлом.";
  }
});
document.querySelector("#download-brief").addEventListener("click", () => {
  const url = URL.createObjectURL(
    new Blob([brief], { type: "text/plain;charset=utf-8" }),
  );
  const link = document.createElement("a");
  link.href = url;
  link.download = "uweb-project-brief.txt";
  link.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
  briefStatus.textContent = "Бриф сохранён в текстовый файл.";
});
document.querySelector("#year").textContent = String(new Date().getFullYear());

// Reveal each section once; content stays visible without JavaScript or with
// reduced motion enabled. Native <details> also works without JavaScript.
const motionPreference = window.matchMedia("(prefers-reduced-motion: reduce)");
if (!motionPreference.matches && "IntersectionObserver" in window) {
  const revealTargets = document.querySelectorAll(
    ".approach-grid > *, .pricing-heading, .price-item, .process-step, .faq-intro, .faq-list, .contact-copy, .project-form",
  );
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.08 },
  );
  revealTargets.forEach((target) => {
    target.classList.add("scroll-reveal");
    observer.observe(target);
  });
  motionPreference.addEventListener("change", (event) => {
    if (event.matches) {
      revealTargets.forEach((target) => target.classList.add("is-visible"));
      observer.disconnect();
    }
  });
}
