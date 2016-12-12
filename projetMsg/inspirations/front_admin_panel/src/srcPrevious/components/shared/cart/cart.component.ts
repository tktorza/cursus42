import { Component, Input } from "@angular/core";

import {Cart} from "../../../models/cart.model"

@Component({
    selector: "cart",
    templateUrl: `client/components/shared/cart/cart.component.html`
})
export class CartComponent {

    @Input('cart') cart: Cart;
    
    constructor() {
    }
}
