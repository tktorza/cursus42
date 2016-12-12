import {Restaurant} from './restaurant.model';
import {User} from './user.model';
import {OrderType} from './orderType.model';
import {DateHelper} from '../helpers/date.helper';

export class Order {

    id: number;
    restaurant: Restaurant;
    cart: any;
    created: Date;
    updated: Date;
    time: Date;
    orderType: OrderType;
    profile: User;
    paymentOnline: boolean;
    price: number;
    state: number;
    preparationState: number;

    constructor(data:any) {
      this.id = data.id;
      this.restaurant = data.restaurant;
      this.cart = data.cart;
      this.created = new Date(data.created);
      this.updated = new Date(data.updated);
      this.time = new Date(data.time);
      this.orderType = data.order_type;
      this.profile = data.profile;
      this.paymentOnline = data.payment_online;
      this.price = data.price;
      this.state = data.state;
      this.preparationState = data.preparation_state ? data.preparation_state : 0;
    }

    private getState() {
        switch(this.state) {
            case 100:
                return "En cours de traitement";
            case 200:
                return "En attente de paiement";
            case 400:
                return "Finalisée";
            case 500:
                return "Annulée";
        }
    }

    private getCreated() {
        return DateHelper.ddMMyyyy(this.time);
    }

    private getTime() {
        return DateHelper.yyyyMMddHHmm(this.time);
    }

    private getPreparationState() {
        switch(this.preparationState) {
            case 0:
                return "En attente";
            case 1:
                return "En preparation";
            case 2:
                return "En livraison";
            case 3:
                return "Délivrée";
            case 4:
                return "Probleme de livraison";
        }
    }

    static buildOrders(ordersData): Order[] {
        let orders = [];
        
        for (let order of ordersData) {
            orders.push(new Order(order));
        }

        return orders;
    }
}