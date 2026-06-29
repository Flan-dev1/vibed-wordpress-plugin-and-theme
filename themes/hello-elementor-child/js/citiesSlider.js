export async function initCitiesSlider() {
  //TODO: make cities dynamic
  const cities = ["Makati", "Manila", "BGC"];

  const cityImages = await Promise.all(
    cities.map(async (city) => {
      const media = await fetch(`/wp-json/wp/v2/media?slug=${city}`).then((r) =>
        r.json(),
      );

      return {
        name: city,
        source_url: media[0]?.source_url ?? null,
      };
    }),
  );

  const citiesWrapper = document.getElementById("cities-wrapper");

  for (const city of cityImages) {
    const slide = document.createElement("div");
    slide.className = "swiper-slide city";

    const slideWrapper = document.createElement("div");
    slideWrapper.className = "slide-wrapper";

    const imageElement = document.createElement("img");
    imageElement.src = city.source_url;
    imageElement.className = "city-image";
    imageElement.id = `slide-${city.name}`;

    const overlayElement = document.createElement("div");
    overlayElement.className = "slide-overlay";

    const detailsElement = document.createElement("div");
    detailsElement.className = "city-details";
    detailsElement.innerText = `${city.name}`;

    slideWrapper.appendChild(overlayElement);
    slideWrapper.appendChild(imageElement);
    slideWrapper.appendChild(detailsElement);
    slide.appendChild(slideWrapper);

    citiesWrapper.appendChild(slide);
  }

  //dupe
  for (const city of cityImages) {
    const slide = document.createElement("div");
    slide.className = "swiper-slide city";

    const slideWrapper = document.createElement("div");
    slideWrapper.className = "slide-wrapper cities";

    const imageElement = document.createElement("img");
    imageElement.src = city.source_url;
    imageElement.className = "city-image";
    imageElement.id = `slide-${city.name}`;

    const overlayElement = document.createElement("div");
    overlayElement.className = "slide-overlay";

    const detailsElement = document.createElement("div");
    detailsElement.className = "city-details";
    detailsElement.innerText = `${city.name}`;

    slideWrapper.appendChild(overlayElement);
    slideWrapper.appendChild(imageElement);
    slideWrapper.appendChild(detailsElement);
    slide.appendChild(slideWrapper);

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
    spaceBetween: 15,
    slidesPerView: 1,
    autoplay: {
      delay: 5000,
      pauseOnMouseEnter: true,
    },
    speed: 450,
    breakpoints: {
      1024: {
        spaceBetween: 30,
        slidesPerView: 3,
      },
      768: {
        slidesPerView: 3,
      },
      640: {
        slidesPerView: 2,
      },
    },
    // Navigation arrows
    navigation: {
      nextEl: ".swiper-button-next.cities",
      prevEl: ".swiper-button-prev.cities",
    },
  });
}
