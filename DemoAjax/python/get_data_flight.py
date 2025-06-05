import requests

api_key = "750ed4a6650e6acb9f73362fa1d0dd3b"
callsign = "VJ870"
url = f"http://api.aviationstack.com/v1/flights?access_key={api_key}&flight_iata={callsign}"

response = requests.get(url)
data = response.json()

# Kiểm tra xem có dữ liệu hay không
if 'data' in data and len(data['data']) > 0:
    for flight in data['data']:
        departure_airport = flight['departure']['airport']
        arrival_airport = flight['arrival']['airport']
        departure_time = flight['departure']['scheduled']
        arrival_time = flight['arrival']['scheduled']
        status = flight['flight_status']
        print(f"Chuyến bay {callsign} khởi hành từ {departure_airport} lúc {departure_time}, đến {arrival_airport} lúc {arrival_time}. Trạng thái: {status}")
else:
    print("Không tìm thấy chuyến bay.")
