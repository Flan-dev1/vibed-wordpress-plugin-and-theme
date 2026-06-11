const city = await fetch(
  "/wp-json/wp/v2/media?search=hero-video-placeholder.mp4",
)
  .then((r) => r.json())
  .then((r) => console.log(r));

export function initCitiesSlider() {
  const citiesWrapper = document.getElementById("cities-wrapper");
  const el = document.createElement("div");
  el.className = "swiper-slide";
  el.textContent = "TEST_SLIDER";
  citiesWrapper.appendChild(el);

  const citiesSwiper = new Swiper(".swiper.cities", {
    // Optional parameters
    direction: "horizontal",
    loop: true,
    centeredSlides: false,
    slidesPerGroup: 3,
    // Navigation arrows
    navigation: {
      nextEl: ".swiper-button-next.cities",
      prevEl: ".swiper-button-prev.cities",
    },
  });
}
