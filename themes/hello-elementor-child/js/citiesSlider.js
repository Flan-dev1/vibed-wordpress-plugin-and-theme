//TODO: make cities dynamic
const cities = ["Makati", "Manila", "BGC"];

const cityImages = [];

for (const c of cities) {
  const media = await fetch(`/wp-json/wp/v2/media?slug=${c}`).then((r) =>
    r.json(),
  );

  cityImages.push({
    name: c,
    source_url: media[0]?.source_url ?? null,
  });
}

export function initCitiesSlider() {
  const citiesWrapper = document.getElementById("cities-wrapper");

  for (const city of cityImages) {
    const slide = document.createElement("div");
    slide.className = "swiper-slide city";

    const imageElement = document.createElement("img");
    imageElement.src = city.source_url;
    imageElement.className = "city-image";
    imageElement.id = `slide-${city.name}`;

    const detailsElement = document.createElement("div");
    detailsElement.className = "city-details";
    detailsElement.innerText = `${city.name}`;

    slide.appendChild(imageElement);
    slide.appendChild(detailsElement);

    citiesWrapper.appendChild(slide);
  }

  // const el = document.createElement("div");
  // el.className = "swiper-slide";
  // el.textContent = "TEST_SLIDER";
  // citiesWrapper.appendChild(el);

  const citiesSwiper = new Swiper(".swiper.cities", {
    // Optional parameters
    direction: "horizontal",
    loop: true,
    centeredSlides: false,
    slidesPerView: 3,
    spaceBetween: 30,

    // Navigation arrows
    navigation: {
      nextEl: ".swiper-button-next.cities",
      prevEl: ".swiper-button-prev.cities",
    },
  });
}
