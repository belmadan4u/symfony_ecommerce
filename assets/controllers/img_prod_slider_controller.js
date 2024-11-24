import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["images", "slider"];
  
  connect() {
    this.interval = null;
  }

  moveSlide(event) {
    const direction = parseInt(event.target.dataset.direction, 10);
    console.log(direction)
    const slider = event.target.closest(".image-slider");
    console.log('Slider:', slider); 

    const images = slider ? slider.querySelectorAll(".slider-images img") : null;
    console.log('Images:', images); 

    // Trouver l'image active actuelle
    let activeIndex = Array.from(images).findIndex(img => img.classList.contains("active"));
    
    // Si aucune image n'est active, activez la première par défaut
    if (activeIndex === -1) {
      activeIndex = 0;
      images[activeIndex].classList.add("active");
    } else {
      images[activeIndex].classList.remove("active");
      // Calculer le nouvel index
      activeIndex = (activeIndex + direction + images.length) % images.length;
      images[activeIndex].classList.add("active");
    }
  }

  startAutoSlide(event, direction, slider) {
    // Effacer tout intervalle existant pour éviter de superposer les appels
    if (this.interval) clearInterval(this.interval);

    // Démarrer l'intervalle pour le mouvement continu (si nécessaire)
    // Cette partie n'est pas nécessaire si vous ne voulez pas d'auto-slide au survol
    this.interval = setInterval(() => {
      this.moveSlide(event);
    }, 1000);
    
    // Ajouter un écouteur pour arrêter le slider lorsque la souris quitte le bouton
    event.target.addEventListener("mouseleave", () => {
      clearInterval(this.interval);
      this.interval = null;
    });
  }
}
