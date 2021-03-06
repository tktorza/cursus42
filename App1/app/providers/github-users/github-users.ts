import { Injectable } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import {User} from '../../models/user';
/*
  Generated class for the GithubUsers provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class GithubUsers {
 githubUsers: any = null;

  constructor(public http: Http) {}

 load() {
    if (this.githubUsers) {
      // already loaded users
      return Promise.resolve(this.githubUsers);
    }

    // don't have the users yet

    return new Promise(resolve => {

      this.http.get('https://api.github.com/users')
        .map(res => <Array<User>>(res.json()))
        .subscribe(users => {
          // we've got back the raw users, now generate the core schedule users
          // and save the users for later reference
          this.githubUsers = users;
          resolve(this.githubUsers);
        });
    });
  }
  loadDetails(login: string) {
    // get the data from the api and return it as a promise
    return new Promise<User>(resolve => {
      // Change the url to match https://api.github.com/users/{username}
      this.http.get(`https://api.github.com/users/${login}`)
        .map(res => <User>(res.json()))
        .subscribe(user => {
          resolve(user);
        });
    });
  }
}
