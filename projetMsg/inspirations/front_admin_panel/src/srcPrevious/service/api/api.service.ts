import { Injectable } from "@angular/core";
import { Response, Headers, Http } from "@angular/http";
import {Config} from "../../config";

import {BaseService} from "../base/base.service";

import "rxjs/add/operator/map";

@Injectable()
export class ApiService extends BaseService{

    private token: string;
    private headers: {headers:Headers};

    constructor(private http: Http) {
        super();

        let headers = new Headers();
        headers.append('Content-Type', 'application/json');

        this.headers = {headers: headers};
    }

    get(url: string, params: any) {

        if(params) {
            url += "?"+this.toQueryString(params);
        }

        return this
            .http
            .get(url)
            .map((response: Response) => response.json());
    }

    setToken(email, password) {

        let url = `${Config.API_HOST}/api/token`;
        let self =this;
        console.log(url);
        return this
            .http
            .post(url, {email: email, password: password})
            .map((response: Response) => {
                    this.token = response.json().token;
                    if (this.token) {
                        localStorage.setItem('token', self.token);
                        self.createHeaderToken();

                        return true;
                    } else {
                    return false;
                    }
                }
            )
        ;
    }

    createHeaderToken() {
        this.headers.headers.append('Authorization', 'Bearer '+ this.token);
    }

    getHeaders() {
        return this.headers;
    }

}
