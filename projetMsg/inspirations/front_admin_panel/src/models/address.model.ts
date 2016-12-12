export class Address {
    id:number;
    name:string;
    street:string;
    zip:string;
    city:string;
    latitude:number;
    longitude:number;

  constructor(data:any) {
      this.id = data.id;
      this.name = data.name;
      this.street = data.street;
      this.zip = data.zip;
      this.city = data.city;
      this.latitude = data.latitude;
      this.longitude = data.longitude;
  };
}