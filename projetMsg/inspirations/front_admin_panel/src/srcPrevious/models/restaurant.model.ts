import {Address} from './address.model';
import {User} from './user.model';

export class Restaurant {
    id:number;
    created:Date;
    status:number;
    isOnline:boolean;
    hasCaisse:boolean;
    flatTimeSheet:any;
    name:string;
    avgPrice:number;
    isOpen:string;
    todayPlanning:any;
    avgReviewScore:number;
    avgPriceScore:number;
    avgCleanScore:number;
    avgServiceScore:number;
    avgCookScore:number;
    slug:string;
    isClickeat:boolean;
    isWhite:boolean;
    isTtt:boolean;
    isMobile:boolean;
    description:string;
    phone:string;
    email:string;
    address:Address;
    slogan:string;
    cover:string;
    coverSmall:string;
    coverFull:string;
    coverBig:string;
    apiCover:string;
    distance:string;
    flatTags:any;
    publicGallery: any;
    gallery:any;
    menuGallery:any;
    reviews: any;
    socialProfile:string;
    bestReview: any;
    friendsWhoLiked:User[];
    isInFavorites:boolean;
    services:any;
    website:string;
    category: any;
    activeDiscount: any;
    categories:any;
    coverDefault: boolean;

  constructor(data:any) {
      this.id = data.id;
      this.description = data.description;
      this.gallery = data.gallery;
      this.publicGallery = data.public_gallery;
      this.menuGallery = data.galleryMenu;
      this.created = data.created;
      this.phone = data.phone;
      this.email = data.email;
      this.status = data.status;
      this.isOnline = data.is_online;
      this.hasCaisse = data.hasCaisse;
      this.flatTags = data.flat_tags;
      this.socialProfile = data.social_profile;
      this.flatTimeSheet = data.flat_time_sheet;
      this.name = data.name;
      this.avgPrice = data.average_price;
      this.isOpen = data.is_open;
      this.reviews = data.reviews;
      this.bestReview = data.best_review;
      this.address = data.address;
      this.cover = data.cover;
      this.services = data.services;
      this.website = data.website;
      this.distance = data.distance;
      this.isInFavorites = data.is_in_favorites;
      this.activeDiscount = data.active_discount;
      this.avgReviewScore = parseInt(data.avg_review_score);
      this.avgPriceScore = parseInt(data.avg_price_score);
      this.avgCleanScore = parseInt(data.avg_clean_score);
      this.avgServiceScore = parseInt(data.avg_service_score);
      this.avgCookScore = parseInt(data.avgCookScore);
      this.friendsWhoLiked = data.friends_who_liked;
      this.distance = data.distance;
      this.coverDefault = data.cover_default;
  };

  setIsInFavorites(isInFavorites) {
      this.isInFavorites = isInFavorites;
  }

  setCategories(categories) {
      this.categories = categories;
  }

  getDefaultImage() {
    let 
        tags = ['Japonais', 'Kebab', 'Chinois', 'Pizza', 'Rotisserie', 'Sandwiches-Salades', 'Burger', 'Bagel', 'FastFood', 'Sandwich'],
        tag = null
    ;

    if (this.flatTags && this.flatTags.category) {
        tag = this.flatTags.category.find(function(tag) {
            return tags.indexOf(tag) >= 0;
        })
    }

    if (tag ) {
      return `build/img/${tag.toLowerCase()}.png`;
    }

    return `build/img/background-icon.png`;
  }

  getTags() {
      let tags = '';

      if (!this.flatTags || !this.flatTags.category || !this.flatTags.category.length) {
          return tags;
      }
      
      this.flatTags.category.forEach((tag, index) => {
          tags += tag;

          if (index != this.flatTags.category.length-1) {
            tags += ', ';
          }
      });

      return tags;
  }
}