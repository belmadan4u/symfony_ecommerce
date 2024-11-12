import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["cartCount", "cartTotal", "quantityInput", "cartDropdown", "cartContent"];

  addToCart(event) {
    const productId = event.currentTarget.dataset.productId;
    const quantity = this.quantityInputTarget.value;

    fetch(`/cart/add/${productId}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({ quantity: quantity }),
    })
      .then((response) => response.json())
      .then((data) => {
        const cart = data.cart;
        
        if (cart.cartCount > 0) {
          let itemsHtml = '';
          for (const [id, item] of Object.entries(cart)) {
            if (id !== "cartCount" && id !== "cartTotal") {
              itemsHtml += `
                <li>
                  ${item.product.name} x ${item.quantity} - ${(item.product.price * item.quantity).toFixed(2)} €
                  <button data-action="cart#removeItem" data-id="${id}">Remove</button>
                </li>
              `;
            }
          }
          this.cartDropdownTarget.innerHTML = `
            <h4>My Cart</h4>
            <ul>${itemsHtml}</ul>
            <p><strong>Total:</strong> ${cart.cartTotal.toFixed(2)} €</p>
            <a href="/creditcard/index" class="btn btn-primary">Checkout</a>
          `;
        } else {
          this.cartDropdownTarget.innerHTML = `<p>Your cart is empty.</p>`;
        }
      })
      .catch((error) => console.error("Error adding product to cart:", error));
  }

  updateQuantity(event){
    const productId = event.target.dataset.cartProductId;

    fetch(`/cart/update`, { 
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body : JSON.stringify({ 
        productId: productId,
        quantity : this.quantityInputTarget.value
      })
    })
    .then(response => response.json())
    .then(data => {
      const cart = data.cart;
      if (cart.cartCount > 0) {
          let updatedHtml = `
              <h2>Contenu de votre commande</h2>
              <ul>
          `;
          for (const [key, item] of Object.entries(cart)) {
              if (key !== 'cartCount' && key !== 'cartTotal') {
                  updatedHtml += `
                      <li>
                          ${item.product.name} 
                          <input data-action="cart#updateQuantity" data-cart-target="quantityInput" data-cart-product-id="${item.product.id}" type="number" name="quantity" value="${item.quantity}" min="1">
                          - ${(item.product.price * item.quantity).toFixed(2)} €
                          <button data-action="cart#removeItem" data-id="${item.product.id}">Supprimer</button>
                      </li>
                  `;
              }
          }
          updatedHtml += `
              </ul>
              <p><strong>Nombre de produits:</strong> ${cart.cartCount}</p>
              <p><strong>Total:</strong> ${cart.cartTotal.toFixed(2)} €</p>
          `;
          this.cartContentTarget.innerHTML = updatedHtml;
      } else {
          this.cartContentTarget.innerHTML = `<p>Votre panier est vide.</p>`;
      }
    });
  }

  removeItem(event) {
    const productId = event.currentTarget.dataset.id;

    fetch(`/cart/remove/${productId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    })
    .then(response => response.json())
    .then(data => {
      const cart = data.cart;
      
      if (cart.cartCount > 0) {
        let itemsHtml = '';
        for (const [id, item] of Object.entries(cart)) {
          if (id !== "cartCount" && id !== "cartTotal") {
            itemsHtml += `
              <li>
                ${item.product.name} x ${item.quantity} - ${(item.product.price * item.quantity).toFixed(2)} €
                <button data-action="cart#removeItem" data-id="${id}">Remove</button>
              </li>
            `;
          }
        }
        this.cartDropdownTarget.innerHTML = `
          <h4>My Cart</h4>
          <ul>${itemsHtml}</ul>
          <p><strong>Total:</strong> ${cart.cartTotal.toFixed(2)} €</p>
          <a href="/creditcard/index" class="btn btn-primary">Checkout</a>
        `;
      } else {
        this.cartDropdownTarget.innerHTML = `<p>Your cart is empty.</p>`;
      }
    });
  }

  removeItemCartOrder(event) { 
      const productId = event.target.dataset.cartProductId;

      fetch('/cart/remove', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ productId: productId })
      })
      .then(response => response.json())
      .then(data => {
        console.log(data)
          const cart = data.cart;
          if (cart.cartCount > 0) {
            let itemsHtml = '';
            for (const [id, item] of Object.entries(cart)) {
                if (id !== "cartCount" && id !== "cartTotal") {
                    itemsHtml += `
                        <li>
                            ${item.product.name} 
                            <input data-action="cart#updateQuantity" data-cart-target="quantityInput" data-cart-product-id="${id}" type="number" value="${item.quantity}" min="1"> x
                            - ${(item.product.price * item.quantity).toFixed(2)} €
                            <button data-action="cart#removeItemCartOrder" data-cart-product-id="${id}">Supprimer</button>
                        </li>
                    `;
                }
            }
            this.cartContentTarget.innerHTML = `
                <h2>{{ 'Contenu de votre commande'|trans }}</h2>
                <ul>${itemsHtml}</ul>
                <p><strong>Nombre de produits:</strong> ${cart.cartCount}</p>
                <p><strong>Total:</strong> ${cart.cartTotal.toFixed(2)} €</p>
            `;
        } else {
            this.cartContentTarget.innerHTML = `<p>Votre panier est vide.</p>`;
        }
      });
  }
}