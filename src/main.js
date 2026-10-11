import { contactConfig } from "./config.js";
import Lenis from "lenis";
import "lenis/dist/lenis.css";

let smoothScroll;

// Keep the supplied illustration; show the decorative search window
// only when that image cannot load. Works if the error happened before this script.
const seoImage = document.querySelector(".seo-hero-image");
if (seoImage) {
  const updateSeoImage = () => {
    seoImage
      .closest(".seo-picture")
      .classList.toggle(
        "is-unavailable",
        seoImage.complete && seoImage.naturalWidth === 0,
      );
  };
  seoImage.addEventListener("error", updateSeoImage);
  seoImage.addEventListener("load", updateSeoImage);
  updateSeoImage();
}

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

// Reuse the same contact links in the menu dialog and the floating widget.
// Native dialog provides focus containment and Escape; links still work without JS.
const contactDialog = document.createElement("dialog");
contactDialog.id = "contact-dialog";
contactDialog.className = "contact-modal";
contactDialog.setAttribute("aria-labelledby", "contact-dialog-title");
contactDialog.setAttribute("data-lenis-prevent", "");
const dialogClose = document.createElement("button");
dialogClose.type = "button";
dialogClose.className = "contact-modal-close";
dialogClose.setAttribute("aria-label", "Закрыть контакты");
dialogClose.autofocus = true;
dialogClose.innerHTML = '<span aria-hidden="true">×</span>';
const dialogOptions = quickContact.querySelector(".contact-options").cloneNode(true);
dialogOptions.id = "contact-dialog-options";
dialogOptions.setAttribute("aria-label", "Способы связи");
dialogOptions.querySelector(".contact-options-title").id = "contact-dialog-title";
contactDialog.append(dialogClose, dialogOptions);
document.body.append(contactDialog);
let contactOpener;
document.querySelectorAll("[data-contact-modal]").forEach((link) => {
  link.setAttribute("aria-haspopup", "dialog");
  link.setAttribute("aria-controls", contactDialog.id);
  link.addEventListener("click", (event) => {
    if (typeof contactDialog.showModal !== "function") return;
    event.preventDefault();
    contactOpener = link;
    closeMenu();
    closeQuickContact();
    contactDialog.showModal();
    document.documentElement.classList.add("contact-modal-open");
    smoothScroll?.stop();
  });
});
dialogClose.addEventListener("click", () => contactDialog.close());
contactDialog.addEventListener("click", (event) => {
  const bounds = contactDialog.getBoundingClientRect();
  if (
    event.target === contactDialog &&
    (event.clientX < bounds.left || event.clientX > bounds.right ||
      event.clientY < bounds.top || event.clientY > bounds.bottom)
  ) contactDialog.close();
});
dialogOptions.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", () => contactDialog.close());
});
contactDialog.addEventListener("close", () => {
  document.documentElement.classList.remove("contact-modal-open");
  smoothScroll?.start();
  const focusTarget = mobileMenu.contains(contactOpener) || !contactOpener?.getClientRects().length
    ? menuButton
    : contactOpener;
  focusTarget?.focus({ preventScroll: true });
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
const formStatus = document.querySelector("#form-status");
const submitButton = form.querySelector('button[type="submit"]');
const submitLabel = document.querySelector("#submit-label");
let submitting = false;

function setFormStatus(message, state) {
  formStatus.textContent = message;
  formStatus.dataset.state = state;
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  if (submitting) return;
  ["name", "contact", "site"].forEach((name) => {
    const input = form.elements.namedItem(name);
    if (input) input.value = input.value.trim();
  });
  if (!form.reportValidity()) return;

  const fields = new FormData(form);
  const payload = Object.fromEntries(fields.entries());
  submitting = true;
  submitButton.disabled = true;
  form.setAttribute("aria-busy", "true");
  submitLabel.textContent = "Отправляем…";
  setFormStatus("Отправляем вашу заявку…", "sending");
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 15000);

  try {
    const response = await fetch(contactConfig.formEndpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
      signal: controller.signal,
      credentials: "same-origin",
    });
    const result = await response.json().catch(() => null);
    if (!response.ok || result?.ok !== true) {
      const message =
        response.status === 429
          ? "Слишком много попыток. Попробуйте через несколько минут или напишите мне в Telegram."
          : response.status === 422
            ? "Проверьте поля формы, затем отправьте заявку снова."
            : "Не удалось отправить заявку. Попробуйте ещё раз или напишите мне в Telegram.";
      throw new Error(message);
    }
    setFormStatus(
      "Заявка отправлена. Спасибо! Свяжусь с вами по указанному контакту.",
      "success",
    );
    form.reset();
  } catch (error) {
    setFormStatus(
      error.name === "AbortError"
        ? "Не удалось подтвердить отправку. Попробуйте ещё раз или напишите мне в Telegram."
        : error instanceof TypeError
          ? "Проверьте соединение с интернетом и попробуйте отправить заявку снова."
          : error.message,
      "error",
    );
  } finally {
    clearTimeout(timeout);
    submitting = false;
    submitButton.disabled = false;
    form.removeAttribute("aria-busy");
    submitLabel.textContent = "Отправить заявку";
  }
});
document.querySelector("#year").textContent = String(new Date().getFullYear());

// The owner supplied these projects and outcomes. Only the project changes;
// the PPC chart shows the supplied overall range, not invented monthly figures.
const casePanel = document.querySelector(".case-panel");
if (casePanel) {
  const cases = [...casePanel.querySelectorAll(".project-case")];
  const caseButtons = [...casePanel.querySelectorAll("[data-case]")];
  function showCase(index) {
    cases.forEach((item, current) => {
      item.hidden = current !== index;
    });
    caseButtons.forEach((button, current) => {
      button.setAttribute("aria-pressed", String(current === index));
    });
  }
  caseButtons.forEach((button, index) => {
    button.addEventListener("click", () => showCase(index));
  });
  casePanel.dataset.enhanced = "true";
  casePanel.querySelector(".case-navigation").hidden = false;
  showCase(0);
}

// Reveal each section once; content stays visible without JavaScript or with
// reduced motion enabled. Native <details> also works without JavaScript.
const motionPreference = window.matchMedia("(prefers-reduced-motion: reduce)");
const desktopPointer = window.matchMedia("(pointer: fine)");
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
      prevent: (node) => node.matches("textarea, #quick-contact, #mobile-menu, .contact-modal"),
    });
  }
  if (contactDialog.open) smoothScroll.stop();
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
const reviewCounter = document.querySelector(".review-counter");
let reviewIndex = 0;
let reviewScrollTimeout;
function getReviewLayout() {
  const cardWidth = reviewSlides[0].getBoundingClientRect().width;
  const gap = parseFloat(getComputedStyle(reviewTrack).columnGap) || 0;
  const visibleCount = Math.max(
    1,
    Math.round((reviewTrack.clientWidth + gap) / (cardWidth + gap)),
  );
  return {
    step: cardWidth + gap,
    visibleCount,
    lastIndex: Math.max(0, reviewSlides.length - visibleCount),
  };
}
function updateReviewNavigation(index) {
  const { visibleCount, lastIndex } = getReviewLayout();
  reviewIndex = Math.max(0, Math.min(index, lastIndex));
  const first = String(reviewIndex + 1).padStart(2, "0");
  const last = String(reviewIndex + visibleCount).padStart(2, "0");
  reviewCurrent.textContent = visibleCount > 1 ? `${first}–${last}` : first;
  reviewCounter.setAttribute(
    "aria-label",
    visibleCount > 1
      ? `Отзывы ${reviewIndex + 1}–${reviewIndex + visibleCount} из ${reviewSlides.length}`
      : `Отзыв ${reviewIndex + 1} из ${reviewSlides.length}`,
  );
  reviewPrevious.disabled = reviewIndex === 0;
  reviewNext.disabled = reviewIndex === lastIndex;
}
function showReview(index, animate = true) {
  updateReviewNavigation(index);
  reviewTrack.scrollTo({
    left: reviewIndex * getReviewLayout().step,
    behavior: animate && !motionPreference.matches ? "smooth" : "instant",
  });
}
document.querySelector(".review-navigation").hidden = false;
document.querySelector(".review-controls").hidden = false;
reviewPrevious.addEventListener("click", () =>
  showReview(reviewIndex - getReviewLayout().visibleCount),
);
reviewNext.addEventListener("click", () =>
  showReview(reviewIndex + getReviewLayout().visibleCount),
);
reviewTrack.addEventListener("keydown", (event) => {
  const targets = {
    ArrowLeft: reviewIndex - getReviewLayout().visibleCount,
    ArrowRight: reviewIndex + getReviewLayout().visibleCount,
    Home: 0,
    End: getReviewLayout().lastIndex,
  };
  if (!(event.key in targets)) return;
  event.preventDefault();
  showReview(targets[event.key]);
});
reviewTrack.addEventListener("scroll", () => {
  clearTimeout(reviewScrollTimeout);
  reviewScrollTimeout = setTimeout(() => {
    updateReviewNavigation(
      Math.round(reviewTrack.scrollLeft / getReviewLayout().step),
    );
  }, 120);
});
new ResizeObserver(() => showReview(reviewIndex, false)).observe(reviewTrack);

if (!motionPreference.matches && "IntersectionObserver" in window) {
  const revealTargets = document.querySelectorAll(
    ".approach-grid > *, .results-heading, .case-panel, .pricing-heading, .price-item, .process-step, .handoff-copy, .handoff-visual, .reviews-intro, .review-carousel, .faq-intro, .faq-list, .contact-copy, .project-form",
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
