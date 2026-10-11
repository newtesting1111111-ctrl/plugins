import { contactConfig } from "./config.js";
import Lenis from "lenis";
import "lenis/dist/lenis.css";

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

const quickContact = document.querySelector("#quick-contact");
const contactToggle = quickContact.querySelector("summary");
function closeQuickContact(restoreFocus = false) {
  quickContact.open = false;
  if (restoreFocus) contactToggle.focus();
}
quickContact.addEventListener("toggle", () => {
  contactToggle.setAttribute(
    "aria-label",
    quickContact.open ? "Закрыть способы связи" : "Открыть способы связи",
  );
  if (quickContact.open) closeMenu();
});
document.addEventListener("click", (event) => {
  if (quickContact.open && !quickContact.contains(event.target))
    closeQuickContact();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && quickContact.open) closeQuickContact(true);
});
quickContact.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", () => closeQuickContact(true));
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
  closeQuickContact();
  smoothScroll?.stop();
  dialog.showModal();
});
dialog.addEventListener("close", () => smoothScroll?.start());

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
const desktopPointer = window.matchMedia("(pointer: fine)");
let smoothScroll;
function updateSmoothScroll() {
  if (motionPreference.matches || !desktopPointer.matches) {
    smoothScroll?.destroy();
    smoothScroll = undefined;
    return;
  }
  if (!smoothScroll) {
    smoothScroll = new Lenis({
      autoRaf: true,
      lerp: 0.12,
      smoothWheel: true,
      syncTouch: false,
      anchors: true,
      prevent: (node) =>
        node.matches("dialog, textarea, #quick-contact, #mobile-menu"),
    });
    if (dialog.open) smoothScroll.stop();
  }
}
updateSmoothScroll();
motionPreference.addEventListener("change", updateSmoothScroll);
desktopPointer.addEventListener("change", updateSmoothScroll);

// Reviews remain horizontally scrollable without JavaScript. No autoplay:
// visitors decide when to move to the next review.
const reviewTrack = document.querySelector("#review-track");
const reviewSlides = [...reviewTrack.querySelectorAll(".review-slide")];
const reviewPrevious = document.querySelector("#review-prev");
const reviewNext = document.querySelector("#review-next");
const reviewCurrent = document.querySelector("#review-current");
let reviewIndex = 0;
let reviewScrollTimeout;
function updateReviewNavigation(index) {
  reviewIndex = index;
  reviewCurrent.textContent = String(index + 1).padStart(2, "0");
  reviewPrevious.disabled = index === 0;
  reviewNext.disabled = index === reviewSlides.length - 1;
}
function showReview(index, animate = true) {
  const next = Math.max(0, Math.min(index, reviewSlides.length - 1));
  updateReviewNavigation(next);
  reviewTrack.scrollTo({
    left: next * reviewTrack.clientWidth,
    behavior: animate && !motionPreference.matches ? "smooth" : "instant",
  });
}
document.querySelector(".review-navigation").hidden = false;
document.querySelector(".review-controls").hidden = false;
reviewPrevious.addEventListener("click", () => showReview(reviewIndex - 1));
reviewNext.addEventListener("click", () => showReview(reviewIndex + 1));
reviewTrack.addEventListener("keydown", (event) => {
  const targets = {
    ArrowLeft: reviewIndex - 1,
    ArrowRight: reviewIndex + 1,
    Home: 0,
    End: reviewSlides.length - 1,
  };
  if (!(event.key in targets)) return;
  event.preventDefault();
  showReview(targets[event.key]);
});
reviewTrack.addEventListener("scroll", () => {
  clearTimeout(reviewScrollTimeout);
  reviewScrollTimeout = setTimeout(() => {
    updateReviewNavigation(
      Math.round(reviewTrack.scrollLeft / reviewTrack.clientWidth),
    );
  }, 120);
});
new ResizeObserver(() => showReview(reviewIndex, false)).observe(reviewTrack);

if (!motionPreference.matches && "IntersectionObserver" in window) {
  const revealTargets = document.querySelectorAll(
    ".approach-grid > *, .pricing-heading, .price-item, .process-step, .handoff-copy, .handoff-visual, .reviews-intro, .review-carousel, .faq-intro, .faq-list, .contact-copy, .project-form",
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
    const siblings = [...target.parentElement.children].filter((item) =>
      [...revealTargets].includes(item),
    );
    target.style.setProperty(
      "--reveal-delay",
      `${Math.min(siblings.indexOf(target), 3) * 60}ms`,
    );
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
