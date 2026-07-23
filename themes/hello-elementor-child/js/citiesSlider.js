export async function initCitiesSlider() {
  const citiesSwiper = new Swiper(".swiper.cities", {
    // Optional parameters
    direction: "horizontal",
    loop: true,
    centeredSlides: false,
    spaceBetween: 15,
    slidesPerView: 1,
    autoplay: {
      delay: 2500,
      pauseOnMouseEnter: true,
    },
    speed: 3000,
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
