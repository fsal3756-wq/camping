import requests
import pytest

BASE_URL = "http://localhost/camping-rental-apps/api"

def test_get_booking_success():
    """Test jika user_id valid, API harus mengembalikan data booking"""

    response = requests.get(f"{BASE_URL}/get_booking.php?user_id=1")
    assert response.status_code == 200

    data = response.json()

    # Struktur wajib
    assert "status" in data
    assert data["status"] == "success"

    assert "bookings" in data
    assert isinstance(data["bookings"], list)

    # Jika ada data, cek struktur per booking
    if data["bookings"]:
        booking = data["bookings"][0]

        assert "id" in booking
        assert "item_id" in booking
        assert "item_name" in booking
        assert "start_date" in booking
        assert "end_date" in booking
        assert "total_price" in booking
        assert "status" in booking

        # Validasi tipe data
        assert isinstance(booking["id"], int)
        assert isinstance(booking["item_name"], str)
        assert isinstance(booking["total_price"], (int, float))

def test_get_booking_no_userid():
    """Jika user_id tidak dikirim, harus error"""

    response = requests.get(f"{BASE_URL}/get_booking.php")
    data = response.json()

    assert data["status"] == "error"
    assert "required" in data["message"].lower()

def test_get_booking_invalid_userid():
    """Jika user_id tidak ditemukan, API tetap success tetapi data kosong"""

    response = requests.get(f"{BASE_URL}/get_booking.php?user_id=99999")
    data = response.json()

    assert data["status"] == "success"
    assert "bookings" in data
    assert isinstance(data["bookings"], list)
    assert len(data["bookings"]) == 0
