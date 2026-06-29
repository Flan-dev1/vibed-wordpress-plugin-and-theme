import { initFeaturedSlider } from "./featuredSlider.js";
import { initCitiesSlider } from "./citiesSlider.js";

function init() {
  //console.log(propertyData); //logging meta data
  // fetch("/wp-json/wp/v2/properties")
  //   .then((res) => res.json())
  //   .then((data) => console.log(data));

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
    header.classList.toggle("header--blurred", window.scrollY > 0);
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
