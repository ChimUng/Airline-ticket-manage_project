
export class Payment {
    constructor() {
        document.addEventListener("DOMContentLoaded", () => {
            this.init();
        });
    }
    init() {
        this.Submit_Hoanthanh();
        this.fetchPayment();
    }
    
    //Hàm xuất dữ liệu thanh toán
    async fetchPayment() {
        try {
            const response = await fetch(`/DemoAjax/handle/controller.php?action=search_payment`, {
                method: "GET",
                credentials: "include"
            });
    
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            const data = await response.json();
            this.LayoutPayment(data);
        } catch (error) {
            console.error("❌ Lỗi khi fetch dữ liệu:", error);
        }
    }
    // Out html payment
    LayoutPayment(flightData) {
        const payment_layout = document.getElementById("payment_layout");
        payment_layout.innerHTML = "";
        
        // Check if payment methods exist and loop through them
            flightData.forEach(method => {
                payment_layout.innerHTML += `
                    <input type="radio" class="payment-checkbox" 
                        value="${method.payment_methods_id}" 
                        id="payment_${method.payment_methods_id}" 
                        name="payment_method" required>
                        <label for="payment_${method.payment_methods_id}">
                            <img src="/DemoAjax/img/${method.logo}" style="max-width:50px; height:auto;">
                            <span>${method.description}</span>
                        </label>
                `;
            });
        }
    // Hàm xuất và fetch
    async fetchFlight(flight_id) {
        try {
            const response = await fetch(`/DemoAjax/handle/controller.php?action=search_flightdata`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ flight_id })
            });
    
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            const data = await response.json();
            return data;
        } catch (error) {
            console.error("❌ Lỗi khi fetch dữ liệu:", error);
        }
    }   
    // Kiểm Tra đã chọn hình thức thanh toán chưa
    Check_select_payment(){
        const checkboxes = document.querySelectorAll('.payment-checkbox');
        let isChecked = false;
    
        checkboxes.forEach(cb => {
            if (cb.checked) {
                isChecked = true;
            }
        });
    
        if (!isChecked) {
            alert('Vui lòng chọn ít nhất một hình thức thanh toán.');
            return false;
        }
        return true;
    }
    // Hàm tính tổng số tiền 
    Total_price() {
        const departureFlight = JSON.parse(sessionStorage.getItem("departureFlight") || '{}');
        const returnFlight = JSON.parse(sessionStorage.getItem("returnFlight") || '{}');
        const bookingData = JSON.parse(sessionStorage.getItem('passengers') || '{"passengers": []}');
        const passengers = Array.isArray(bookingData.passengers) ? bookingData.passengers : [];

        let total = 0;

        // Giá vé chiều đi
        if (departureFlight?.flightData?.price) {
            total += parseFloat(departureFlight.flightData.price) || 0;
        }

        // Giá vé chiều về
        if (returnFlight?.flightData?.price) {
            total += parseFloat(returnFlight.flightData.price) || 0;
        }

        // Ghế + hành lý chiều đi + chiều về
        passengers.forEach(p => {
            total += parseFloat(p.seat_price) || 0;
            total += parseFloat(p.seat_return_price) || 0;
            total += parseFloat(p.baggage_add_price) || 0;
            total += parseFloat(p.baggage_add_return_price) || 0;
        });

        return total;
    }

    // Tạo mã pnr
    generatePNR(length = 6) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let pnr = '';
        for (let i = 0; i < length; i++) {
          pnr += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return pnr;
      }
    // Lấy Thông tin gửi về sever
    async Get_Information() {
        const checkboxes = document.querySelector('input[name="payment_method"]:checked').value;
        const information = JSON.parse(sessionStorage.getItem("passengers"));
        
        const user_id = await this.Get_user_id(); // Đợi lấy user_id xong đã
        let payment = {
            payment_id: checkboxes,
            amount_total: this.Total_price()
        };
    
        return {
            representative: information.representative,
            passengers: information.passengers,
            payment: payment,
            user_id: user_id,
            pnr : this.generatePNR()
        };
    }
    
    // Gửi thông tin đến sever
    async Post_Information(){
        const data = await this.Get_Information();
        console.log(data);
        try {
            const response = await fetch("/DemoAjax/handle/controller.php?action=complete_flight", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({data})
        });                
            if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
            } catch (error) {
                console.error("❌ Lỗi khi fetch dữ liệu:", error);
            }
    }
    async Get_user_id() {
        const res = await fetch("/DemoAjax/handle/controller.php?action=get_user_id", {
            method: "GET",
            credentials: "include"
        });
        const data = await res.json();
        this.user_id = data.user_id; // Lưu lại nếu cần
        return data.user_id;
    }    
    // Hàm Xử lí submit
    Submit_Hoanthanh(){
        document.getElementById("submit_hoantat").addEventListener("click",()=>{
            if(!this.Check_select_payment()){
                return;
            }
            this.Post_Information();
            // Nếu POST thành công -> Xoá sessionStorage
            sessionStorage.removeItem("departureFlight");
            sessionStorage.removeItem("returnFlight");
            sessionStorage.removeItem("passengers");
            window.location.href = "/DemoAjax/layout/Ticket_history.php";
        })
    }
}
new Payment();