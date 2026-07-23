export function initFeaturedSlider() {
  const featuredWrapper = document.getElementById("featured-wrapper");

  if (!featuredWrapper || !featuredWrapper.children.length) {
    return;
  }

  new Swiper(".swiper.featured", {
    // Optional parameters
    direction: "horizontal",
    loop: true,
    speed: 2500,
    autoplay: {
      delay: 3000,
      pauseOnMouseEnter: true,
    },
    // Navigation arrows
    navigation: {
      nextEl: ".swiper-button-next.featured",
      prevEl: ".swiper-button-prev.featured",
    },
  });
}
