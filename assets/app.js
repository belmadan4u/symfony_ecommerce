import { Application } from "@hotwired/stimulus";
import SearchController from "./controllers/searchBar_controller.js";
import CartController from "./controllers/cart_controller.js";
import CreditCardController from './controllers/credit_card_controller.js'

// Register the application and the controller
window.Stimulus = Application.start();
Stimulus.register("search", SearchController);
Stimulus.register("cart", CartController);
Stimulus.register("credit-card", CreditCardController);


console.log('Stimulus initialized with SearchController');
console.log('Stimulus initialized with CartController');
console.log('Stimulus initialized with CreditCardController');