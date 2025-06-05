// Airport.js
import { Flight } from './Flight.js';

export class Airport {
    constructor() {
        this.flight = new Flight();

        this.initSearch_Airport("departure");
        this.initSearch_Airport("arrival");

        setTimeout(() => {
            console.log("⏳ Delay 100ms → cập nhật visibility lại");
            this.flight.updateVisibilityByRadioValue();
        }, 100);

        console.log("✅ Airport constructor đã chạy");
    }

    Check_select_Flight() {
        const checkboxes = document.querySelectorAll('.typeflight-checkbox');
        let isChecked = false;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                isChecked = true;
            }
        });

        if (!isChecked) {
            alert('Vui lòng chọn loại chuyến bay.');
            return false;
        }
        return true;
    }

    initSearch_Airport(type) {
        const inputField = document.querySelector(`input[name='${type}']`);
        const resultContainer = document.getElementById(`result_${type}`);
        if (!inputField) return;

        inputField.addEventListener("keyup", (event) => {
            event.preventDefault();
            const query = inputField.value.trim();
            if (query.length >= 1) {
                resultContainer.style.display = "block";
                this.fetchAirportData_Airport(type, query);
            } else {
                resultContainer.style.display = "none";
            }
        });
    }
    fetchAirportData_Airport(type, query) {
        $.ajax({
            url: "/DemoAjax/handle/controller.php?action=search_airports",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({ [`object_airport_${type}`]: query }),
            success: (data) => {
                if (typeof data === "string") {
                    try {
                        data = JSON.parse(data);
                    } catch (e) {
                        console.error("❌ Lỗi parse JSON:", e);
                        return;
                    }
                }
                this.displayResults_Airport(type, data); // OK vì arrow function giữ đúng this
            },
            error: function (xhr, status, error) {
                console.error(`❌ Lỗi khi AJAX dữ liệu ${type}:`, error);
            }
        });
    }
    displayResults_Airport(type, data) {
        const resultContainer = document.getElementById(`result_${type}`);
        if (!resultContainer) return;
        let resultHTML = `
            <div class="search-result-title">Kết quả tìm kiếm:</div>
            <ul class="search-result-list">
        `;

        if (data.length === 0) {
            resultHTML += `<li class="search-result-item empty">Không tìm thấy kết quả</li>`;
        } else {
            data.forEach(row => {
                resultHTML += `
                    <li class="search-result-item">
                        <button class="search-airport-btn-${type}" 
                            data-type${type}="${type}" 
                            data-iata${type}="${row.IATA_code_airport}">
                            <strong>(${row.IATA_code_airport})</strong> ${row.airport_name}
                        </button>
                    </li>`;
            });
        }

        resultHTML += `</ul>`;
        resultContainer.innerHTML = resultHTML;

        setTimeout(() => {
            this.flight.setupButtonClick_AirportDeparture(type);
        }, 100);
    }
}

new Airport();