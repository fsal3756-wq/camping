import requests

API_URL = "http://localhost/camping-rental-apps/api/items.php"

def test_get_items():
    response = requests.get(API_URL)
    assert response.status_code == 200, "Status bukan 200"
    data = response.json()

    # Tes struktur response
    assert "status" in data
    assert "total" in data
    assert "data" in data
    # Tes nilai
    assert data["status"] == "success"
    assert isinstance(data["data"], list)
    print("\n✅ HASIL TEST API ITEMS")
    print("Total data:", data["total"])
    for item in data["data"]:
        assert "id" in item
        assert "name" in item
        assert "price_per_day" in item
        assert "quantity_available" in item

        print(f"- {item['name']} | Price: {item['price_per_day']} | Qty: {item['quantity_available']}")
