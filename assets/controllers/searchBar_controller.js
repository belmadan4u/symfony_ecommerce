// searchbar_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["input", "productList", "products", "pagination"];

  searchProducts() {
    const query = this.inputTarget.value;
    this.pasRangerSearchBarQuandUtiliser();
    if (query.length > 2) {
      fetch(`/search?q=${encodeURIComponent(query)}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(products => {
          this.productListTarget.innerHTML = '';  
          this.productsTarget.style.display = 'none';
          this.paginationTarget.style.display = 'none';

          if(products.length == 0){
            this.productListTarget.innerHTML += `
              <p>{{ 'aucun_produit_disponible'|trans}}</p>
            `
          }else {
            products.forEach(product => {
              const categories = (product.category || []).map((cat, index) => 
                `${cat}${index < product.category.length - 1 ? ', ' : ''}`
              ).join('') || 'Aucune catégorie disponible';

              const images = (product.images || []).map(imgUrl => 
                `<img src="${imgUrl}" alt="Image de ${product.name}" />`
              ).join('') || `<p>Aucune image disponible</p>`;

              this.productListTarget.innerHTML += `
                <a href="/product/${product.id}">
                  <div class="product">
                    <h2>${product.name}</h2>
                    <p class="price">${product.price} €</p>
                    <p class="description">${product.description}</p>
                    <p>${product.status}</p>
                    ${images}
                    <p class="category">${categories}</p>
                  </div>
                </a>`;
            });
          }
          
        })
        .catch(error => console.error('Error fetching products:', error));
    } else {
      this.productListTarget.innerHTML = '';
      this.productsTarget.style.display = 'grid';
      this.paginationTarget.style.display = 'flex';
    }
  }

  pasRangerSearchBarQuandUtiliser() {
    if (this.inputTarget.value.length > 0) {
      this.inputTarget.classList.add("not-empty");
    } else {
      this.inputTarget.classList.remove("not-empty");
    }
  }
}
