import { Injectable } from "@angular/core";
import { AuthHttp } from "angular2-jwt";
import { Response, Headers, Http } from "@angular/http";
import {Config} from "../../config";
import {BaseService} from "../base/base.service";
import {Order} from "../../models/order.model";

import "rxjs/add/operator/map";

@Injectable()
export class OrderService extends BaseService{

     private headers: {headers:Headers};

    constructor(private authHttp: AuthHttp, private http: Http) {
        super();
    }

    getRestaurantOrders(restaurantId, params) {

        let url = `${Config.API_HOST}/api/v1/restaurant/${restaurantId}/orders?${this.toQueryString(params)}`;
        
        return this
            .http
            .get(url, this.headers)
            .map((response: Response) => Order.buildOrders(response.json()));
    }

    getClientOrders() {

    }

    setHeaders(headers: {headers:Headers}): OrderService {
        this.headers = headers;

        return this;
    }
}
