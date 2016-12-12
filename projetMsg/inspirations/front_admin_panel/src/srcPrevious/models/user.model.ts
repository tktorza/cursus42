import {Address} from './address.model';
import {Restaurant} from './restaurant.model';

export class User {
    id:number;
    email:string;
    username: string;
    cover:string;
    firstName:string;
    lastName:string;
    phone:string;
    birthday:Date;
    isMale:boolean;
    zipcode:string;
    homeAddress:Address;
    jobAddress:Address;
    description:string;
    website:string;    
    favorites:[any];
    countPhotos:number;
    reviews:[any];
    lastSearch:any;
    favoriteSearch:any;
    myFriends:User[];
    friendsWithMe:User[];
    followed:User[];
    followers:User[];
    followed_count: number;
    followers_count: number;
    favorites_restaurants: Restaurant[];
    gallery: any;

    constructor(data: any) {
        let fakeAddress: Address;

        fakeAddress = new Address({
            id: '',
            name: '',
            street: '',
            city: ''
        });

        this.id = data.id;
        this.email = data.email;
        this.username = data.username;
        this.cover = data.cover;
        this.firstName = data.first_name;
        this.lastName = data.last_name;
        this.phone = data.phone;
        this.birthday = data.birthday;
        this.isMale = data.is_male;
        this.zipcode = data.zipcode;
        this.homeAddress = data.home_address && data.home_address.street ? data.home_address : fakeAddress;
        this.jobAddress = data.job_address && data.job_address.street ? data.job_address : fakeAddress;
        this.description = data.description;
        this.website = data.website;
        this.favorites = data.favorites;
        this.countPhotos = data.countPhotos;
        this.reviews = data.reviews;
        this.lastSearch = data.last_search;
        this.favoriteSearch = data.favoriteSearch;
        this.myFriends = data.myFriends;
        this.friendsWithMe = data.friendsWithMe;
        this.followed = data.followed;
        this.followers = data.followers;
        this.followed_count = data.followed_count;
        this.followers_count = data.followers_count;
        this.favorites_restaurants = this.buildRestaurants(data.favorites_restaurants);
        this.gallery = data.gallery;
    };

    buildRestaurants (restaurantsData) {
        let restaurants = restaurantsData ? restaurantsData : [];
        let restaurantCollection = [];

        for (let restaurant of restaurants) {
            let restaurantObject = new Restaurant(restaurant);
            restaurantCollection.push(restaurantObject);
        }

        return restaurantCollection;
    }

    getHomeAddress() {
        let address = this.homeAddress;

        return `${address.street}, ${address.zip} ${address.city}`;
    }

    getWorkAddress() {
        let address = this.jobAddress;

        return `${address.street}, ${address.zip} ${address.city}`;
    }
}
