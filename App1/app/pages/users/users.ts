import { Component } from '@angular/core';
import { NavController } from 'ionic-angular';

import {GithubUsers} from '../../providers/github-users/github-users';
import {UserDetailsPage} from '../user-details/user-details';
import {User} from '../../models/user'

@Component({
  templateUrl: 'build/pages/users/users.html',
  providers: [GithubUsers]
})

export class UsersPage {
  // Declare users as an array of User model
  users: User[];

  // Inject the GithubUsers in the constructor of our page component
  constructor(public nav: NavController, githubUsers: GithubUsers) {
    // Test whether the github provider returns data
    githubUsers
      .load()
      // User arrow function notation
      .then(users => this.users = users);
  }

  goToDetails(event, login) {
   this.nav.push(UserDetailsPage, {
     login: login
   });
   }
   }
