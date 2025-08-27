const animateOnViewElements = document.querySelectorAll(".animate-on-view");

animateOnViewElements.forEach((el) => {
  const observerOptions = {
    root: null,
    rootMargin: "0px",
    threshold: 0.5,
  };

  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const animation = entry.target.animate(
          {
            opacity: [0, 1],
            transform: ["translateY(50px)", "translateY(0)"],
          },
          {
            duration: 500,
            easing: "ease-out",
          }
        );

        animation.addEventListener("finish", () => {
          entry.target.classList.add("animate-on-view-animated");
          observer.unobserve(entry.target);
        });
      }
    });
  }, observerOptions);

  observer.observe(el);
});

const map = L.map("map").setView([-14.235, -51.9253], 5);

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: "© OpenStreetMap contributors",
}).addTo(map);

const pumps = [
  { nome: "Colatina", latitude: -19.5382, longitude: -40.6324 },
];

pumps.forEach(function (cidade) {
  L.marker([cidade.latitude, cidade.longitude]).addTo(map).bindPopup(cidade.nome);
});
 
