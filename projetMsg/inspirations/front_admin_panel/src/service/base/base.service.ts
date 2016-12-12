import {Injectable} from '@angular/core';

@Injectable()
export class BaseService {
    constructor () {}

    toQueryString(data) {
        var out = new Array();

        for (let key in data) {
            if (data[key] !== null) {
                out.push(key + '=' + data[key]);
            }
        }

        return out.join('&');
    }
}