export class Cart_layout {
    constructor() {
        this.container = document.getElementById("cart-container");
        this.init();
    }

    async init() {
        const user_id = await this.Get_user_id();
        if (!user_id) {
            this.container.innerHTML = "<p>🔒 Bạn cần đăng nhập để xem giỏ hàng</p>";
            return;
        }

        await this.fetchCart(user_id); // ✅ Truyền user_id vào đây
    }

    async Get_user_id() {
        try {
            const res = await fetch("/DemoAjax/handle/controller.php?action=get_user_id", {
                method: "GET",
                credentials: "include"
            });

            const data = await res.json();
            return data.user_id || null;
        } catch (error) {
            console.error("❌ Lỗi khi lấy user_id:", error);
            return null;
        }
    }

async fetchCart(user_id) {
    try {
        const response = await fetch(`/DemoAjax/handle/controller.php?action=getCart`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ user_id }) // ✅ Gửi user_id vào PHP
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        this.LayoutCart(data);
    } catch (error) {
            console.error("❌ Lỗi khi fetch giỏ hàng:", error);
    }
}
LayoutCart(data, flightType = "") {
    if (!Array.isArray(data) || data.length === 0) {
        this.container.innerHTML = "<p>🛒 Giỏ hàng trống</p>";
        return;
    }

    this.container.innerHTML = data.map(item => `
        <div class="cart-item">
            <div class="airline-logo">
                <img src="/DemoAjax/img/${item.airline_logo}" alt="${item.airline_name}">
            </div>

            <div class="flight-info">
                <div class="airline-code">${item.IATA_code_airline}</div>
                <div class="flight-time">${item.departure_time}</div>
                <div class="flight-time">${item.arrival_time}</div>
                <div class="flight-price">${Number(item.price).toLocaleString()} VND</div>
            </div>

            <div class="flight-actions">
                <button class="chonchuyenbay" data-flight="${item.flight_id}" data-type="${flightType}">Chọn</button>
                <button class="xemchitiet" data-flight="${item.flight_id}" data-type="${flightType}">
                    <i class="fa fa-angle-down"></i>
                </button>
            </div>
        </div>
        <div class="container_xemchitiet"></div>
    `).join("");
}


}

new Cart_layout();
