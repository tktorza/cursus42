export class OrderType{

    id: number;
    name: string;
    slug: string;

    constructor(data:any) {
      this.id = data.id;
      this.name = data.name;
      this.slug = data.slug;
    }
}