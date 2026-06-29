export async function initFeaturedSlider() {
  const featuredProperties = await fetch("/wp-json/wp/v2/properties?_embed")
    .then((r) => r.json())
    .then(async (r) => {
      // TODO: query featured category id dynamically
      const featured = r.filter((property) =>
        property.es_categories.includes(121),
      );

      return Promise.all(
        featured.map(async (property, i) => {
          // const mediaResponse = await fetch(
          //   `/wp-json/wp/v2/media/${property.featured_media}`,
          // );
          const media = property._embedded["wp:featuredmedia"][0].source_url;

          return {
            index: i,
            id: property.id,
            name: property.title.rendered,
            media: property.featured_media,
            imageLink: media,
            price: property.price,
          };
        }),
      );
    });

  const featuredWrapper = document.getElementById("featured-wrapper");

  for (const property of featuredProperties) {
    const slide = document.createElement("div");
    slide.className = "swiper-slide";

    const slideWrapper = document.createElement("div");
    slideWrapper.className = "slide-wrapper";

    const imageElement = document.createElement("img");
    imageElement.src = property.imageLink;
    imageElement.className = "featured-image";
    imageElement.id = `slide-${property.index + 1}`;

    const detailsElement = document.createElement("div");
    detailsElement.className = "featured-details";
    detailsElement.innerHTML = `
      <div>PHP${property.price}</div>
      <div>${property.name}</div>
    `;

    //detailsElement.textContent = `PHP${property.price}\n${property.name}`;

    const overlayElement = document.createElement("div");
    overlayElement.className = "slide-overlay";

    slideWrapper.appendChild(overlayElement);
    slideWrapper.appendChild(imageElement);
    slideWrapper.appendChild(detailsElement);
    slide.appendChild(slideWrapper);
    featuredWrapper.appendChild(slide);

    //dupe
    featuredWrapper.appendChild(slide.cloneNode(true));
    console.log(featuredWrapper.children);
  }

  const featuredSwiper = new Swiper(".swiper.featured", {
    // Optional parameters
    direction: "horizontal",
    loop: true,
    autoplay: {
      delay: 5000,
      pauseOnMouseEnter: true,
    },
    speed: 550,
    // Navigation arrows
    navigation: {
      nextEl: ".swiper-button-next.featured",
      prevEl: ".swiper-button-prev.featured",
    },
  });
}
