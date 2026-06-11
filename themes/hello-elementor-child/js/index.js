import { initFeaturedSlider } from "./featuredSlider.js";
import { initCitiesSlider } from "./citiesSlider.js";

function init() {
  //console.log(propertyData); //logging meta data
  // fetch("/wp-json/wp/v2/properties")
  //   .then((res) => res.json())
  //   .then((data) => console.log(data));

  initFeaturedSlider();
  initCitiesSlider();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
