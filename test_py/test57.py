import pytest
from datetime import datetime

# ========================================
# 1. LOGIN SYSTEM TESTS
# ========================================

def test_login_admin_valid():
    """Login admin dengan data valid"""
    username = "admin"
    password = "admin123"

    valid_users = {
        "admin": {"password": "admin123", "role": "admin"},
        "user1": {"password": "admin123", "role": "user"}
    }

    is_valid = username in valid_users and valid_users[username]["password"] == password

    assert is_valid == True
    assert valid_users[username]["role"] == "admin"

    print("✅ Login admin berhasil")


# ========================================
# 2. PENJUMLAHAN HARGA BOOKING
# ========================================

def test_calculate_booking_price():
    """Perhitungan harga booking (tanpa diskon)"""
    price_per_day = 150000
    days = 3
    quantity = 2
    tax_rate = 10

    subtotal = price_per_day * days * quantity
    tax = subtotal * (tax_rate / 100)
    total = subtotal + tax

    assert subtotal == 900000
    assert tax == 90000
    assert total == 990000
    print("✅ Perhitungan harga booking benar")


# ========================================
# 3. PENGAMBILAN DATA API
# ========================================

def test_get_items_from_api():
    """Mengambil data barang dari API (mock)"""
    mock_response = {
        "status": "success",
        "data": [
            {
                "id": 1,
                "name": "Tenda",
                "price_per_day": 150000,
                "quantity_available": 5,
                "status": "available"
            },
            {
                "id": 2,
                "name": "Sleeping Bag",
                "price_per_day": 50000,
                "quantity_available": 7,
                "status": "available"
            }
        ]
    }

    assert mock_response["status"] == "success"
    assert isinstance(mock_response["data"], list)
    assert len(mock_response["data"]) == 2

    for item in mock_response["data"]:
        assert "id" in item
        assert "name" in item
        assert "price_per_day" in item
        assert "quantity_available" in item
        assert item["status"] in ["available", "unavailable"]

    print("✅ Data item dari API berhasil diuji")


def test_calculate_booking_price():
    price_per_day = 150000
    days = 3
    quantity = 2
    tax_rate = 10

    subtotal = price_per_day * days * quantity
    tax = subtotal * (tax_rate / 100)
    total = subtotal + tax

    assert subtotal == 900000
    assert tax == 90000
    assert total == 990000


def test_calculate_booking_price_with_discount():
    price_per_day = 100000
    days = 7
    quantity = 1
    discount_rate = 5
    tax_rate = 10

    subtotal = price_per_day * days * quantity
    discount = subtotal * (discount_rate / 100) if days > 5 else 0
    subtotal_after_discount = subtotal - discount
    tax = subtotal_after_discount * (tax_rate / 100)
    total = subtotal_after_discount + tax

    assert subtotal == 700000
    assert discount == 35000
    assert subtotal_after_discount == 665000
    assert tax == 66500
    assert total == 731500
