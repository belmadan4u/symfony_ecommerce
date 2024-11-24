import { Application } from "@hotwired/stimulus";
import CartController from "./controllers/cart_controller.js";
import CreditCardController from './controllers/credit_card_controller.js'
import ImgProdSliderController from './controllers/img_prod_slider_controller.js'
import LiveController from '@symfony/ux-live-component';

// Register the application and the controller
window.Stimulus = Application.start();
Stimulus.register("cart", CartController);
Stimulus.register("credit-card", CreditCardController);
Stimulus.register("img-prod-slider", ImgProdSliderController);
Stimulus.register("live", LiveController);


console.log('Stimulus initialized with CartController');
console.log('Stimulus initialized with CreditCardController');
console.log('Stimulus initialized with ImgProdSliderController');
console.log('Stimulus initialized with LiveController');