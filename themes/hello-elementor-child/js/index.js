import { initFeaturedSlider } from "./featuredSlider.js";
import { initCitiesSlider } from "./citiesSlider.js";

window.renderNavCities = function renderNavCities(cities) {
  const placeholder = document.querySelector(".side-nav-cities");

  if (!placeholder || !Array.isArray(cities)) {
    return;
  }

  cities.forEach((city) => {
    const link = document.createElement("a");
    link.className = "link";
    link.href = city.url;
    link.textContent = city.name;
    placeholder.parentNode.insertBefore(link, placeholder);
  });

  placeholder.remove();

  const nav = document.querySelector('ul.sub-menu');
  nav.firstElementChild.remove();


  cities.forEach((city) => {
    const item = document.createElement("li");
    const link = document.createElement("a");
    link.className = "link";
    link.href = city.url;
    link.textContent = city.name;
    item.appendChild(link);
    nav.appendChild(item);
  });
};

function init() {
  initNavMenu();
  initHeaderBlur();
  if (window.location.pathname === `/` || window.location.pathname === ``) {
    initFeaturedSlider();
    initCitiesSlider();
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    init();
  });
} else {
  init();
}

function initHeaderBlur() {
  const header = document.querySelector(".site-header");

  function updateHeaderBlur() {
    header.classList.toggle("header--scrolled", window.scrollY > 0);
  }

  window.addEventListener("scroll", updateHeaderBlur, { passive: true });

  // Set initial state on page load
  updateHeaderBlur();
}

function initNavMenu() {
  const icons = [document.querySelector("#bars"), document.querySelector("#x")];
  const side = document.querySelector(".side-nav");

  icons.forEach((icon) => {
    icon.addEventListener("click", () => {
      icons.forEach((icon) => {
        icon.classList.toggle("hide");
        console.log(icon.id, "->", icon.classList);
      });
      side.classList.toggle("show-side");
    });
  });

  const exit = document.querySelector(".side-nav-exit");
  exit.addEventListener("click", () => {
    icons.forEach((icon) => {
      icon.classList.toggle("hide");
      console.log(icon.id, "->", icon.classList);
    });
    side.classList.toggle("show-side");
  });
}
