import requests
import pytest
from datetime import datetime, timedelta

BASE_URL = "http://localhost/camping-rental-apps/api"


# ============================================
# FIXTURES & HELPERS
# ============================================

# @pytest.fixture(scope="session", autouse=True)
# def setup_test_environment():
#     """Setup before all tests"""
#     print("\n🚀 Starting API tests...")
#     yield
#     print("\n✅ All tests completed!")

# ============================================
# TEST: GET BOOKING
# ============================================

def test_create_booking_success():
    """Test create booking dengan data valid (looping notes)"""

    for i in range(1, 4):  # ubah 4 sesuai kebutuhan
        start_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
        end_date = (datetime.now() + timedelta(days=3)).strftime('%Y-%m-%d')
        
        payload = {
            "user_id": 2,
            "item_id": 17,
            "start_date": start_date,
            "end_date": end_date,
            "quantity": 1,
            "notes": f"Create success test #{i}"
        }
        
        response = requests.post(
            f"{BASE_URL}/create_booking.php",
            json=payload
        )
        assert response.status_code == 200
        
        data = response.json()
        assert data["status"] == "success"
        assert "total_price" in data
        assert "days" in data

        booking_id = int(data["booking_id"])
        assert booking_id > 0


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


# ============================================
# TEST: CREATE BOOKING
# ============================================

def test_create_booking_success():
    """Test create booking dengan data valid"""
    start_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
    end_date = (datetime.now() + timedelta(days=3)).strftime('%Y-%m-%d')
    
    payload = {
        "user_id": 2,
        "item_id": 17,
        "start_date": start_date,
        "end_date": end_date,
        "quantity": 1,
        "notes": "Test booking from pytest"
    }
    
    response = requests.post(f"{BASE_URL}/create_booking.php", json=payload)
    assert response.status_code == 200
    
    data = response.json()
    assert data["status"] == "success"
    assert "total_price" in data
    assert "days" in data

    booking_id = int(data["booking_id"])
    assert isinstance(booking_id, int)
    assert booking_id > 0


def test_create_booking_missing_fields():
    """Test create booking tanpa field required"""
    payload = {
        "user_id": 3,
        "item_id": 17
        # Missing: start_date, end_date, quantity
    }
    
    response = requests.post(f"{BASE_URL}/create_booking.php", json=payload)
    data = response.json()
    
    assert data["status"] == "error"
    assert "required" in data["message"].lower()


def test_create_booking_invalid_dates():
    """Test create booking dengan tanggal invalid (start > end)"""
    start_date = (datetime.now() + timedelta(days=5)).strftime('%Y-%m-%d')
    end_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
    
    payload = {
        "user_id": 3,
        "item_id": 5,
        "start_date": start_date,
        "end_date": end_date,
        "quantity": 1
    }
    
    response = requests.post(f"{BASE_URL}/create_booking.php", json=payload)
    data = response.json()
    
    assert data["status"] == "error"
    assert "before" in data["message"].lower()


def test_create_booking_item_not_found():
    """Test create booking dengan item_id yang tidak ada"""
    start_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
    end_date = (datetime.now() + timedelta(days=3)).strftime('%Y-%m-%d')
    
    payload = {
        "user_id": 3,
        "item_id": 99999,  # ID yang tidak ada
        "start_date": start_date,
        "end_date": end_date,
        "quantity": 1
    }
    
    response = requests.post(f"{BASE_URL}/create_booking.php", json=payload)
    data = response.json()
    
    assert data["status"] == "error"
    assert "not found" in data["message"].lower() or "unavailable" in data["message"].lower()


def test_create_booking_insufficient_quantity():
    """Test create booking dengan quantity melebihi stok"""
    start_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
    end_date = (datetime.now() + timedelta(days=3)).strftime('%Y-%m-%d')
    
    payload = {
        "user_id": 3,
        "item_id": 10,
        "start_date": start_date,
        "end_date": end_date,
        "quantity": 999  # Quantity berlebihan
    }
    
    response = requests.post(f"{BASE_URL}/create_booking.php", json=payload)
    data = response.json()
    
    assert data["status"] == "error"
    assert "insufficient" in data["message"].lower() or "available" in data["message"].lower()


# ============================================
# TEST: INTEGRATION
# ============================================

def test_create_and_get_booking_integration():
    """Test integrasi: create & get booking (looping notes)"""

    created_booking_ids = []

    for i in range(1, 3):  # ubah 4 sesuai kebutuhan
        start_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
        end_date = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')

        create_payload = {
            "user_id": 3,
            "item_id": 20,
            "start_date": start_date,
            "end_date": end_date,
            "quantity": 1,
            "notes": f"Integration test #{i}"
        }

        create_response = requests.post(
            f"{BASE_URL}/create_booking.php",
            json=create_payload
        )
        create_data = create_response.json()

        assert create_data["status"] == "success"
        booking_id = int(create_data["booking_id"])
        assert booking_id > 0

        created_booking_ids.append(booking_id)

    # GET booking user yang sama
    get_response = requests.get(
        f"{BASE_URL}/get_booking.php?user_id=3"
    )
    get_data = get_response.json()

    assert get_data["status"] == "success"
    assert len(get_data["bookings"]) > 0

    # Validasi semua booking hasil looping ada
    booking_ids = [int(b["id"]) for b in get_data["bookings"]]

    for booking_id in created_booking_ids:
        assert booking_id in booking_ids





if __name__ == "__main__":
    pytest.main([__file__, "-v", "--tb=short"])