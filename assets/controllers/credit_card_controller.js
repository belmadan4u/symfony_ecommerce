import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["cardsContainer", "cardPrototype","form"];

    submitForm(event) {
        event.preventDefault();

        const formData = new FormData(this.formTarget);
        console.log(formData)
        
        fetch(this.formTarget.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(data)
            if (data.success) {
                this.addCard(data.cardData);
            } else {
                this.displayErrors(data.errors);
            }
        })
        .catch(error => console.error("Erreur réseau:", error));
    }

    addCard(cardData) {
        this.cardsContainerTarget.innerHTML += `
            <div class="credit-card-form">
                <p><strong>Number:</strong> ${cardData.number}</p>
                <p><strong>Expiration Date:</strong> ${cardData.expirationDate}</p>
                <p><strong>CVV:</strong> ${cardData.cvv}</p>
                <button data-action="credit-card#removeCard" data-card-id="${cardData.id}">Remove</button>
                <a href='/order/withCard/${cardData.id}'>{{'Passer la commande avec cette carte'|trans}}</a>
            </div>
        `;

        this.formTarget.reset();
    }

    displayErrors(errors) {
        const errorElements = this.formTarget.querySelectorAll(".error-message");
        errorElements.forEach(element => element.remove());

        Object.keys(errors).forEach(field => {
            const input = this.formTarget.querySelector(`[name="credit_card_form[${field}]"]`);
            if (input) {
                const errorMessage = document.createElement("div");
                errorMessage.classList.add("error-message");
                errorMessage.textContent = errors[field];
                input.parentElement.appendChild(errorMessage);
            }
        });
    }
    
    removeCard(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const cardId = button.dataset.cardId;
        const token = button.dataset.token;

        fetch(`/creditcard/${cardId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ _token: token })
        })
        .then(response => {
            console.log(response)
            if (response.ok) {
                button.closest('.credit-card-form').remove();
                if (this.cardsContainerTarget.children.length === 0) {
                    this.cardsContainerTarget.innerHTML = '<p>Vous n\'avez aucune carte enregistrée</p>';
                }
            } else {
                console.error('Failed to delete the card');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
