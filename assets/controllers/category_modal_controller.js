import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["modal", "input"];

  open() {
    this.modalTarget.classList.remove("hidden");
  }

  close() {
    this.modalTarget.classList.add("hidden");
  }

  save() {
    const categoryName = this.inputTarget.value.trim();

    if (!categoryName) {
        alert("Le nom de la catégorie ne peut pas être vide !");
        return;
    }

    fetch("/product/category/add", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ name: categoryName }),
    })
    .then((response) => {
        if (!response.ok) {
            throw new Error("Erreur lors de l'ajout de la catégorie");
        }
        return response.json();
    })
    .then((data) => {
      console.log(data)
        alert("Catégorie ajoutée avec succès !");
        this.inputTarget.value = "";
        let newInputCateg = document.createElement('input')
        newInputCateg.type = 'checkbox';
        newInputCateg.id = "product_form_category_" + data.id
        newInputCateg.value = data.id
        newInputCateg.name = "produit_form[category][]"

        let newLabelInputCateg = document.createElement('label')
        newLabelInputCateg.for = newInputCateg.id
        newLabelInputCateg.innerText = data.name

        document.getElementById('produit_form_category').appendChild(newInputCateg)
        document.getElementById('produit_form_category').appendChild(newLabelInputCateg)
        this.close();
    })
    .catch((error) => {
        console.error(error);
        alert("Une erreur s'est produite");
    });
  }
}
