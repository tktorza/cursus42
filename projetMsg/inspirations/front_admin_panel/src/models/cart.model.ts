export class Cart {
    elements: any;
    coupon: any;
    discount: number;
    discountAmount: number;
    discountPrice: number;
    couponAmount: number;
    totalPrice: number;
    totalProducts: number;
    woodSticks: number;
    loyalties: any;

    constructor(data: any){
        this.elements = data.elements;
        this.coupon = data.coupon;
        this.discount = data.discount;
        this.discountAmount = data.discount_amount;
        this.discountPrice = data.discount_price;
        this.couponAmount = data.coupon_amount;
        this.totalPrice = data.total_price;
        this.totalProducts = data.total_products;
        this.woodSticks = data.wood_sticks;
        this.loyalties = data.loyalties;
    }
}