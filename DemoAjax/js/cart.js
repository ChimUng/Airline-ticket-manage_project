import { Flight_product } from "./Flight_product.js";
import { Flight_product_search } from "./Flight_product_Search.js";

export class Cart {
    constructor() {
        this.init();
    };

    init() {
        this.Addcart();
    }

    Addcart() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.add-to-cart-btn');
            if (!btn) return;
            this.flightID = btn.dataset.flight;
            this.fetchcart(); // Call fetchcart properly using 'this'
        });
    }

    async Get_user_id() {
        const res = await fetch("/DemoAjax/handle/controller.php?action=get_user_id", {
            method: "GET",
            credentials: "include"
        });
        const data = await res.json();
        this.user_id = data.user_id; // Store if needed
        return data.user_id;
    }

    async Get_Information() {
        const user_id = await this.Get_user_id();  
        return {
            flightID: this.flightID,
            user_id: user_id
        };
    }

    async fetchcart() {
        const data = await this.Get_Information();
        console.log(data);
        try {
            const response = await fetch(`/DemoAjax/handle/controller.php?action=addcart`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });
    
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        } catch (error) {
            console.error("❌ Error fetching data:", error);
        }
    }
}

new Cart();
